<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCategory;
use App\Models\TicketHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    /**
     * Generate unique ticket number with format: REQ-YYYYMMDD-0001
     */
    public function generateTicketNumber(?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();
        $prefix = 'REQ-' . $date->format('Ymd') . '-';

        $lastTicket = Ticket::where('ticket_number', 'LIKE', $prefix . '%')
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($lastTicket) {
            $lastSequence = (int) substr($lastTicket->ticket_number, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $prefix . str_pad((string) $newSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new ticket with initial status 'Menunggu Verifikasi', dynamic category SLA, and multiple PDF attachments.
     */
    public function createTicket(array $data, User $user, array|UploadedFile|null $files = null): Ticket
    {
        return DB::transaction(function () use ($data, $user, $files) {
            $slaService = app(TicketSlaService::class);
            $now = now();
            $startAt = $slaService->calculateSlaStart($now);
            $dueAt = $slaService->calculateResponseDueAt($now);
            $ticketNumber = $this->generateTicketNumber();

            // Ambil Kategori & SLA jika dipilih
            $category = null;
            $slaResolutionHours = null;
            $departmentId = $data['department_id'] ?? null;

            if (!empty($data['category_id'])) {
                $category = TicketCategory::find($data['category_id']);
                if ($category) {
                    $slaResolutionHours = $category->sla_resolution_hours;
                    $departmentId = $departmentId ?? $category->department_id;
                }
            }

            // Map priority dari SLA Resolution jam jika tidak diisi manual
            $priority = $data['priority'] ?? null;
            if (!$priority && $slaResolutionHours) {
                if ($slaResolutionHours <= 8) {
                    $priority = 'Kritis';
                } elseif ($slaResolutionHours <= 12) {
                    $priority = 'Tinggi';
                } elseif ($slaResolutionHours <= 48) {
                    $priority = 'Sedang';
                } else {
                    $priority = 'Rendah';
                }
            }
            $priority = $priority ?? 'Sedang';

            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'title'         => $data['title'] ?? null,
                'user_id' => $user->id,
                'category_id' => $data['category_id'] ?? null,
                'department_id' => $departmentId,
                'priority' => $priority,
                'jenis_pengajuan' => $data['jenis_pengajuan'] ?? ($category?->jenis_pengajuan ?? 'Permintaan'),
                'status' => 'Menunggu Verifikasi',
                'description' => $data['description'],
                'sla_resolution_hours' => $slaResolutionHours,
                'sla_response_start_at' => $startAt,
                'sla_response_due_at' => $dueAt,
                'sla_response_status' => 'Menunggu',
            ]);

            // Tangani upload lampiran (single atau multiple PDF)
            $uploadedFiles = [];
            if ($files instanceof UploadedFile) {
                $uploadedFiles = [$files];
            } elseif (is_array($files)) {
                $uploadedFiles = $files;
            }

            $firstAttachmentPath = null;
            foreach ($uploadedFiles as $file) {
                if ($file instanceof UploadedFile) {
                    $storedPath = $file->store('attachments/tickets', 'public');
                    if (!$firstAttachmentPath) {
                        $firstAttachmentPath = $storedPath;
                    }

                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'file_path' => $storedPath,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            if ($firstAttachmentPath) {
                $ticket->update(['attachment_path' => $firstAttachmentPath]);
            }

            // Insert initial history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'old_status' => null,
                'new_status' => 'Menunggu Verifikasi',
                'notes' => $data['notes'] ?? 'Tiket baru berhasil dibuat oleh pemohon.',
            ]);

            return $ticket;
        });
    }

    /**
     * Update ticket and automatically record to TicketHistories if status or department changes.
     */
    public function updateTicket(Ticket $ticket, array $data, User $user): Ticket
    {
        return DB::transaction(function () use ($ticket, $data, $user) {
            $oldStatus = $ticket->status;
            $oldDepartmentId = $ticket->department_id;

            $newStatus = $data['status'] ?? $oldStatus;
            $newDepartmentId = array_key_exists('department_id', $data) ? $data['department_id'] : $oldDepartmentId;

            $isStatusChanged = $oldStatus !== $newStatus;
            $isDepartmentChanged = (string) $oldDepartmentId !== (string) $newDepartmentId;

            // Update ticket fields
            $updateData = [];
            if (isset($data['status'])) {
                $updateData['status'] = $newStatus;
            }
            if (array_key_exists('department_id', $data)) {
                $updateData['department_id'] = $newDepartmentId;
            }
            if (isset($data['priority'])) {
                $updateData['priority'] = $data['priority'];
            }
            if (isset($data['category_id'])) {
                $updateData['category_id'] = $data['category_id'];
            }
            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }
            if (array_key_exists('assigned_to', $data)) {
                $updateData['assigned_to'] = $data['assigned_to'];
            }
            if (isset($data['disposition_notes'])) {
                $updateData['disposition_notes'] = $data['disposition_notes'];
            }
            if (isset($data['disposed_by'])) {
                $updateData['disposed_by'] = $data['disposed_by'];
            }
            if (isset($data['disposed_at'])) {
                $updateData['disposed_at'] = $data['disposed_at'];
            }

            if (!empty($updateData)) {
                $ticket->update($updateData);
            }

            // Jika tiket diverifikasi (berpindah dari Menunggu Verifikasi), catat verifikasi SLA
            if ($oldStatus === 'Menunggu Verifikasi' && $newStatus !== 'Menunggu Verifikasi' && is_null($ticket->verified_at)) {
                app(TicketSlaService::class)->recordVerification($ticket, $user);
            }

            // Saat Operator memverifikasi (status → Dialokasikan): mulai timer SLA Resolution
            if ($oldStatus === 'Menunggu Verifikasi' && $newStatus === 'Dialokasikan' && is_null($ticket->sla_resolution_start_at)) {
                $ticket->refresh();
                app(TicketSlaService::class)->startResolutionSla(
                    $ticket,
                    ['sla_resolution_hours' => $ticket->sla_resolution_hours],
                    $user
                );
            }

            // Notifikasi pemohon saat tiket diterima / dialokasikan
            if ($oldStatus === 'Menunggu Verifikasi' && in_array($newStatus, ['Dialokasikan', 'Diverifikasi']) && $ticket->user) {
                $deptName = $ticket->fresh()->department?->name ?? 'Unit Terkait';
                $ticket->user->notify(new \App\Notifications\TicketAllocatedNotification($ticket, $deptName));
            }

            // Jika role Bagian atau staf mengubah status menjadi 'Selesai'
            if ($newStatus === 'Selesai' && $oldStatus !== 'Selesai') {
                $completedTime = now();
                $slaService = app(TicketSlaService::class);

                // Catat SLA Resolusi jika belum tercatat
                if (is_null($ticket->resolved_at)) {
                    $slaService->recordResolution($ticket);
                }

                // Hitung batas waktu konfirmasi 2 x 24 jam kerja (48 jam kerja)
                $deadline = $slaService->calculateConfirmationDeadline($completedTime);

                $ticket->update([
                    'completed_at'          => $completedTime,
                    'confirmation_deadline' => $deadline,
                ]);

                // Kirim notifikasi konfirmasi ke pemohon
                if ($ticket->user) {
                    $formattedDeadline = $deadline->translatedFormat('l, d F Y H:i') . ' WITA';
                    $ticket->user->notify(new \App\Notifications\TicketCompletedNotification($ticket, $formattedDeadline));
                }
            }

            // Jika tiket ditolak
            if ($newStatus === 'Ditolak' && $oldStatus !== 'Ditolak' && $ticket->user) {
                $reason = $data['reject_reason'] ?? ($data['notes'] ?? 'Permohonan ditolak oleh petugas.');
                if (preg_match('/Alasan:\s*(.+)$/i', $reason, $m)) {
                    $reason = trim($m[1]);
                }
                $ticket->user->notify(new \App\Notifications\TicketRejectedNotification($ticket, $reason));
            }

            // Jika tiket ditutup (konfirmasi pemohon atau auto-close)
            if (in_array($newStatus, ['Ditutup Pemohon', 'Ditutup Otomatis (Sistem)']) && is_null($ticket->closed_at)) {
                $ticket->update(['closed_at' => now()]);
            }

            // Notifikasi jika ditutup otomatis oleh sistem
            if ($newStatus === 'Ditutup Otomatis (Sistem)' && $oldStatus !== 'Ditutup Otomatis (Sistem)' && $ticket->user) {
                $ticket->user->notify(new \App\Notifications\TicketAutoClosedNotification($ticket));
            }

            // If status or department changed, insert record to ticket_histories
            if ($isStatusChanged || $isDepartmentChanged || !empty($data['force_history'])) {
                $notes = $data['notes'] ?? null;

                if (empty($notes)) {
                    $changes = [];
                    if ($isStatusChanged) {
                        $changes[] = "Status diubah dari '{$oldStatus}' menjadi '{$newStatus}'";
                    }
                    if ($isDepartmentChanged) {
                        $deptName = $ticket->fresh()->department?->name ?? 'Belum Ditugaskan';
                        $changes[] = "Departemen dialokasikan ke '{$deptName}'";
                    }
                    $notes = implode('. ', $changes);
                }

                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'notes' => $notes,
                ]);
            }

            return $ticket;
        });
    }

    /**
     * Mengalokasikan tiket dari status 'Menunggu Verifikasi' ke department oleh Operator.
     * Status berubah menjadi 'Dialokasikan'.
     */
    public function allocateTicket(Ticket $ticket, int $departmentId, string $notes, User $operator): Ticket
    {
        return $this->updateTicket($ticket, [
            'status'        => 'Dialokasikan',
            'department_id' => $departmentId,
            'notes'         => $notes,
        ], $operator);
    }

    /**
     * Menerima tiket oleh role Bagian dan langsung mengubah status menjadi 'Dalam Proses'.
     * Sekaligus memulai timer SLA Resolution secara otomatis.
     */
    public function acceptTicket(Ticket $ticket, User $user, ?string $notes = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $user, $notes) {
            $oldStatus = $ticket->status;
            $newStatus = 'Dalam Proses';

            $roleLabel = $user->role?->label ?? 'Bagian Penanganan';
            $noteText = $notes ?? "Tiket diterima dan langsung diproses oleh {$user->nama_lengkap} ({$roleLabel}).";

            $ticket->update([
                'assigned_to' => $user->id,
                'disposed_by' => $user->id,
                'disposed_at' => now(),
                'status' => $newStatus,
            ]);

            // Mulai SLA Resolusi jika belum berjalan
            if (is_null($ticket->sla_resolution_start_at)) {
                app(TicketSlaService::class)->startResolutionSla(
                    $ticket,
                    ['sla_resolution_hours' => $ticket->sla_resolution_hours],
                    $user
                );
            }

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'notes' => $noteText,
            ]);

            return $ticket;
        });
    }

    /**
     * Menambahkan catatan / uraian update progres berkala oleh role Bagian.
     */
    public function addProgress(Ticket $ticket, User $user, string $notes): TicketHistory
    {
        return DB::transaction(function () use ($ticket, $user, $notes) {
            $history = TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'old_status' => $ticket->status,
                'new_status' => $ticket->status,
                'notes' => "Update Progres: " . $notes,
            ]);

            $ticket->touch();

            return $history;
        });
    }

    /**
     * Konfirmasi selesai oleh role Bagian.
     */
    public function completeTicket(Ticket $ticket, User $user, ?string $notes = null): Ticket
    {
        $noteText = $notes 
            ? "Pekerjaan dinyatakan selesai oleh {$user->nama_lengkap}. Uraian: {$notes}" 
            : "Pekerjaan dinyatakan selesai oleh {$user->nama_lengkap} ({$user->role?->label}). Menunggu konfirmasi pemohon.";

        return $this->updateTicket($ticket, [
            'status' => 'Selesai',
            'notes' => $noteText,
        ], $user);
    }

    /**
     * Penolakan tiket oleh role Bagian atau Operator.
     */
    public function rejectTicket(Ticket $ticket, User $user, string $reason): Ticket
    {
        $noteText = "Tiket ditolak oleh {$user->nama_lengkap} ({$user->role?->label}). Alasan: {$reason}";

        return $this->updateTicket($ticket, [
            'status' => 'Ditolak',
            'notes' => $noteText,
            'reject_reason' => $reason,
        ], $user);
    }

    /**
     * Dispose ticket (kompatibilitas jika dibutuhkan pembagian spesifik ke rekan tim).
     */
    public function disposeTicket(Ticket $ticket, User $staff, string $notes, User $kabag, array $resolutionData = []): Ticket
    {
        return DB::transaction(function () use ($ticket, $staff, $notes, $kabag, $resolutionData) {
            $oldStatus = $ticket->status;
            $newStatus = 'Didistribusikan';

            $ticket->update([
                'assigned_to' => $staff->id,
                'disposed_by' => $kabag->id,
                'disposed_at' => now(),
                'disposition_notes' => $notes,
                'status' => $newStatus,
            ]);

            app(TicketSlaService::class)->startResolutionSla($ticket, $resolutionData, $kabag);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $kabag->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'notes' => "Disposisi ke {$staff->nama_lengkap} ({$staff->username}). Catatan: {$notes}",
            ]);

            return $ticket;
        });
    }

    /**
     * Otomatis menutup tiket berstatus 'Selesai' yang telah melewati confirmation_deadline (2 hari kerja).
     */
    public function autoCloseExpiredTickets(): int
    {
        $expiredTickets = Ticket::where('status', 'Selesai')
            ->whereNotNull('confirmation_deadline')
            ->where('confirmation_deadline', '<=', now())
            ->whereNull('closed_at')
            ->with(['user'])
            ->get();

        $count = 0;
        foreach ($expiredTickets as $ticket) {
            $user = $ticket->user ?? User::where('username', 'system')->first() ?? User::first();
            $this->updateTicket($ticket, [
                'status' => 'Ditutup Otomatis (Sistem)',
                'notes'  => 'Tiket ditutup secara otomatis oleh sistem karena telah melewati batas waktu konfirmasi 2 hari kerja.',
                'closed_at' => now(),
            ], $user);
            $count++;
        }

        return $count;
    }
}
