<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketAutoClosedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket
    ) {}

    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'          => 'ticket_auto_closed',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => "Tiket {$this->ticket->ticket_number} Ditutup Otomatis",
            'judul'         => "Tiket {$this->ticket->ticket_number} Ditutup Otomatis",
            'message'       => "Tiket {$this->ticket->ticket_number} telah ditutup otomatis oleh sistem karena melewati batas waktu konfirmasi 2 hari kerja.",
            'pesan'         => "Tiket {$this->ticket->ticket_number} telah ditutup otomatis oleh sistem karena melewati batas waktu konfirmasi 2 hari kerja.",
            'action_url'    => route('tickets.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tiket {$this->ticket->ticket_number} Telah Ditutup Otomatis")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Tiket dengan nomor **{$this->ticket->ticket_number}** telah ditutup secara otomatis oleh sistem karena batas waktu konfirmasi (2x24 jam kerja) telah terlampaui.")
            ->action('Lihat Tiket', route('tickets.show', $this->ticket))
            ->line('Jika kendala masih berlanjut, Anda dapat mengajukan tiket permohonan baru.');
    }
}
