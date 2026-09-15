<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketSlaWarningNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $remainingText
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    /**
     * Tipe notifikasi di kolom database notifications.
     */
    public function databaseType(object $notifiable): string
    {
        return 'sla_warning';
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toDatabase($notifiable): array
    {
        $message = "Tiket {$this->ticket->ticket_number} sisa kurang dari 2 jam ({$this->remainingText}). Segera selesaikan.";

        return [
            'type'            => 'sla_warning',
            'ticket_id'       => $this->ticket->id,
            'ticket_number'   => $this->ticket->ticket_number,
            'title'           => 'Peringatan SLA – Tiket hampir melebihi batas waktu',
            'judul'           => 'Peringatan SLA – Tiket hampir melebihi batas waktu',
            'message'         => $message,
            'pesan'           => $message,
            'remaining_time'  => $this->remainingText,
            'action_url'      => route('tickets.show', $this->ticket),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Peringatan SLA – Tiket {$this->ticket->ticket_number} Hampir Melebihi Batas Waktu")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Tiket **{$this->ticket->ticket_number}** yang ditugaskan kepada Anda memiliki sisa waktu resolusi kurang dari 2 jam (**{$this->remainingText}**).")
            ->line("Harap segera menyelesaikan permohonan tersebut sebelum batas waktu SLA terlampaui.")
            ->action('Lihat Detail Tiket', route('tickets.show', $this->ticket))
            ->line('Terima kasih.');
    }
}
