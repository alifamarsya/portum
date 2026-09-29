<?php

namespace App\Notifications;

use App\Models\AsDisposalAset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisposalStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AsDisposalAset $disposal,
        public string $judul,
        public string $pesan,
        public string $tipe = 'info',
        public ?string $actionUrl = null
    ) {}

    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'disposal_id' => $this->disposal->id,
            'no_disposal' => $this->disposal->no_disposal,
            'aset_nama'   => $this->disposal->aset?->nama_aset,
            'judul'       => $this->judul,
            'title'       => $this->judul,
            'pesan'       => $this->pesan,
            'message'     => $this->pesan,
            'tipe'        => $this->tipe,
            'type'        => $this->tipe,
            'status'      => $this->disposal->approval_status,
            'action_url'  => $this->actionUrl ?? route('disposal-aset.riwayat'),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("[Penghapusan Aset] {$this->judul} — {$this->disposal->no_disposal}")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line($this->pesan)
            ->line("**No. Pengajuan:** {$this->disposal->no_disposal}")
            ->line("**Nama Aset:** " . ($this->disposal->aset?->nama_aset ?? '-'))
            ->line("**Kode Aset:** " . ($this->disposal->aset?->kode_aset ?? '-'))
            ->line("**Alasan Penghapusan:** {$this->disposal->alasan_penghapusan}");

        if ($this->disposal->alasan_penolakan) {
            $mail->line("**Alasan Penolakan:** {$this->disposal->alasan_penolakan}");
        }

        $url = $this->actionUrl ?? route('disposal-aset.riwayat');

        return $mail->action('Lihat Detail Pengajuan', $url);
    }
}
