<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class JatuhTempoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $judul,
        public string $kategori,
        public string $tanggalJatuhTempo,
        public string $catatan = '',
    ) {}

    // Dikirim lewat channel database (muncul di dashboard) + mail (opsional,
    // aktif hanya kalau user punya email). Dijalankan di queue worker,
    // bukan di request HTTP yang membuka dashboard.
    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'judul' => $this->judul,
            'kategori' => $this->kategori,
            'tanggal_jatuh_tempo' => $this->tanggalJatuhTempo,
            'catatan' => $this->catatan,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder jatuh tempo: {$this->judul}")
            ->line("Kategori: {$this->kategori}")
            ->line("Jatuh tempo: {$this->tanggalJatuhTempo}")
            ->line($this->catatan ?: 'Segera tindak lanjuti sebelum jatuh tempo.');
    }
}
