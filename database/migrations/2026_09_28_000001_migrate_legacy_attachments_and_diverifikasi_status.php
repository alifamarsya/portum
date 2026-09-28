<?php

use App\Models\InternalDepartment;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Migrasi file lampiran lama (single attachment_path) ke tabel ticket_attachments
        Ticket::whereNotNull('attachment_path')->chunk(50, function ($tickets) {
            foreach ($tickets as $ticket) {
                $alreadyExists = TicketAttachment::where('ticket_id', $ticket->id)
                    ->where('file_path', $ticket->attachment_path)
                    ->exists();

                if (!$alreadyExists && !empty($ticket->attachment_path)) {
                    $size = 0;
                    try {
                        if (Storage::disk('public')->exists($ticket->attachment_path)) {
                            $size = Storage::disk('public')->size($ticket->attachment_path);
                        }
                    } catch (\Throwable $e) {
                        $size = 0;
                    }

                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'file_path' => $ticket->attachment_path,
                        'file_name' => basename($ticket->attachment_path),
                        'file_size' => $size,
                    ]);
                }
            }
        });

        // 2. Ubah status 'Dialokasikan' menjadi 'Diverifikasi' pada tabel tickets
        Ticket::where('status', 'Dialokasikan')->update([
            'status' => 'Diverifikasi',
        ]);

        // 3. Ubah status 'Dialokasikan' pada ticket_histories
        TicketHistory::where('new_status', 'Dialokasikan')->update([
            'new_status' => 'Diverifikasi',
        ]);

        TicketHistory::where('old_status', 'Dialokasikan')->update([
            'old_status' => 'Diverifikasi',
        ]);

        // 4. Perbaiki kalimat uraian lama pada riwayat verifikasi jika ada
        TicketHistory::where('notes', 'like', "%Status diubah dari 'Menunggu Verifikasi' menjadi 'Dialokasikan'%")
            ->orWhere('notes', 'like', "%Status diubah dari 'Menunggu Verifikasi' menjadi 'Diverifikasi'%")
            ->get()
            ->each(function ($h) {
                $ticket = $h->ticket;
                $deptName = $ticket?->department?->name ?? 'Bagian Terkait';
                $h->update([
                    'notes' => "Tiket diterima dan langsung diproses oleh {$deptName}",
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for data normalization
    }
};
