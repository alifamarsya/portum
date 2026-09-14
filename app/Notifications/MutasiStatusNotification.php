<?php

namespace App\Notifications;

use App\Models\AsMutasiAset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MutasiStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AsMutasiAset $mutasi,
        public string $judul,
        public string $pesan,
        public string $tipe = 'info'
    ) {}

    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'mutasi_id'    => $this->mutasi->id,
            'no_mutasi'    => $this->mutasi->no_mutasi,
            'aset_nama'    => $this->mutasi->aset?->nama_aset,
            'judul'        => $this->judul,
            'pesan'        => $this->pesan,
            'tipe'         => $this->tipe,
            'status'       => $this->mutasi->status,
            'status_hasil' => $this->mutasi->status_hasil,
            'action_url'   => route('mutasi-aset.show', $this->mutasi),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("[Mutasi Aset] {$this->judul} — {$this->mutasi->no_mutasi}")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line($this->pesan)
            ->line("**No. Mutasi:** {$this->mutasi->no_mutasi}")
            ->line("**Nama Aset:** " . ($this->mutasi->aset?->nama_aset ?? '-'))
            ->line("**Lokasi Asal ➔ Tujuan:** {$this->mutasi->dari_lokasi} ➔ {$this->mutasi->ke_lokasi}")
            ->line("**Penanggung Jawab:** {$this->mutasi->dari_penanggung_jawab} ➔ {$this->mutasi->ke_penanggung_jawab}");

        if ($this->mutasi->alasan_penolakan) {
            $mail->line("**Alasan Penolakan:** {$this->mutasi->alasan_penolakan}");
        } elseif ($this->mutasi->catatan_verifikasi && $this->mutasi->status_hasil === 'Tidak Valid') {
            $mail->line("**Catatan Verifikasi:** {$this->mutasi->catatan_verifikasi}");
        }

        return $mail
            ->action('Lihat Detail Mutasi Aset', route('mutasi-aset.show', $this->mutasi))
            ->line('Terima kasih telah menggunakan Portal Terpadu Bank Sulteng.');
    }
}
