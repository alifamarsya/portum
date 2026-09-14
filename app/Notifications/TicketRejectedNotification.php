<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $reason
    ) {}

    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'          => 'ticket_rejected',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => "Tiket {$this->ticket->ticket_number} Ditolak",
            'judul'         => "Tiket {$this->ticket->ticket_number} Ditolak",
            'message'       => "Permohonan tiket Anda telah ditolak. Alasan: {$this->reason}",
            'pesan'         => "Permohonan tiket Anda telah ditolak. Alasan: {$this->reason}",
            'reason'        => $this->reason,
            'action_url'    => route('tickets.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Permohonan Tiket {$this->ticket->ticket_number} Ditolak")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Permohonan tiket Anda dengan nomor **{$this->ticket->ticket_number}** belum dapat diproses / ditolak.")
            ->line("**Alasan Penolakan:** {$this->reason}")
            ->action('Lihat Detail Tiket', route('tickets.show', $this->ticket))
            ->line('Terima kasih.');
    }
}
