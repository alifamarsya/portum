<?php

namespace App\Services;

use App\Models\Ticket;
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

        // Lock for update or find the highest sequence for the day
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
     * Create a new ticket with initial status 'Menunggu Verifikasi' and record history.
     */
    public function createTicket(array $data, User $user, ?UploadedFile $file = null): Ticket
    {
        return DB::transaction(function () use ($data, $user, $file) {
            $attachmentPath = null;
            if ($file) {
                $attachmentPath = $file->store('attachments/tickets', 'public');
            } elseif (!empty($data['attachment_path'])) {
                $attachmentPath = $data['attachment_path'];
            }

            $ticketNumber = $this->generateTicketNumber();

            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'department_id' => $data['department_id'] ?? null,
                'priority' => $data['priority'] ?? 'Sedang',
                'status' => 'Menunggu Verifikasi',
                'description' => $data['description'],
                'attachment_path' => $attachmentPath,
            ]);

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

            if (!empty($updateData)) {
                $ticket->update($updateData);
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
}
