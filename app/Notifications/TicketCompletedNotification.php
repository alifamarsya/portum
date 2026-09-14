<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $deadlineFormatted
    ) {}

    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'ticket_id'           => $this->ticket->id,
            'ticket_number'       => $this->ticket->ticket_number,
            'judul'               => "Pekerjaan Tiket {$this->ticket->ticket_number} Telah Selesai",
            'pesan'               => "Staf pelaksana telah menandai tiket {$this->ticket->ticket_number} sebagai Selesai. Silakan lakukan konfirmasi dalam 2x24 jam kerja (sebelum {$this->deadlineFormatted}). Jika tidak ada respon, tiket akan ditutup otomatis oleh sistem.",
            'confirmation_deadline' => $this->ticket->confirmation_deadline?->toIso8601String(),
            'action_url'          => route('tickets.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pekerjaan Tiket {$this->ticket->ticket_number} Telah Selesai — Butuh Konfirmasi Anda")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Permintaan tiket Anda dengan nomor **{$this->ticket->ticket_number}** telah diselesaikan oleh staf terkait.")
            ->line("**Uraian Permintaan:** {$this->ticket->description}")
            ->line("Harap periksa dan konfirmasikan hasil pekerjaan sebelum **{$this->deadlineFormatted}** (2 x 24 jam kerja).")
            ->line("Apabila dalam batas waktu tersebut tidak ada konfirmasi dari Anda, maka sistem akan menutup tiket ini secara otomatis.")
            ->action('Konfirmasi Penyelesaian Tiket', route('tickets.show', $this->ticket))
            ->line('Terima kasih telah menggunakan layanan Helpdesk Bank Sulteng.');
    }
}
