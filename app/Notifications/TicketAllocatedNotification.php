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
            'title'           => "Tiket {$this->ticket->ticket_number} Sedang Diproses",
            'judul'           => "Tiket {$this->ticket->ticket_number} Sedang Diproses",
            'message'         => "Permohonan tiket Anda telah diterima dan dialokasikan ke {$this->departmentName} untuk ditindaklanjuti.",
            'pesan'           => "Permohonan tiket Anda telah diterima dan dialokasikan ke {$this->departmentName} untuk ditindaklanjuti.",
            'department_name' => $this->departmentName,
            'action_url'      => route('tickets.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tiket {$this->ticket->ticket_number} Telah Dialokasikan")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Permohonan tiket Anda dengan nomor **{$this->ticket->ticket_number}** telah diverifikasi dan dialokasikan ke unit **{$this->departmentName}**.")
            ->action('Pantau Perkembangan Tiket', route('tickets.show', $this->ticket))
            ->line('Terima kasih.');
    }
}
