<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketAllocatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $departmentName
    ) {}

    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'            => 'ticket_allocated',
            'ticket_id'       => $this->ticket->id,
            'ticket_number'   => $this->ticket->ticket_number,
            'title'           => "Tiket {$this->ticket->ticket_number} Sedang Ditangani",
            'judul'           => "Tiket {$this->ticket->ticket_number} Sedang Ditangani",
            'message'         => "Permohonan tiket Anda telah diterima dan diteruskan ke {$this->departmentName} untuk ditindaklanjuti.",
            'pesan'           => "Permohonan tiket Anda telah diterima dan diteruskan ke {$this->departmentName} untuk ditindaklanjuti.",
            'department_name' => $this->departmentName,
            'action_url'      => route('tickets.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tiket {$this->ticket->ticket_number} Sedang Ditangani")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Permohonan tiket Anda dengan nomor **{$this->ticket->ticket_number}** telah diverifikasi dan diteruskan ke unit **{$this->departmentName}** untuk ditindaklanjuti.")
            ->action('Pantau Perkembangan Tiket', route('tickets.show', $this->ticket))
            ->line('Terima kasih.');
    }
}
