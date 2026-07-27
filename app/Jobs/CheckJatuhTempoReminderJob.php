<?php

namespace App\Jobs;

use App\Models\AsInvoiceSewa;
use App\Models\AsPks;
use App\Models\PgReminder;
use App\Models\Role;
use App\Models\User;
use App\Notifications\JatuhTempoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

// CPMK Sistem Komputasi Terdistribusi: job ini dijalankan oleh queue worker
// terpisah dari proses web, dipicu oleh Task Scheduler (lihat routes/console.php).
// Di versi Python lama, reminder cuma dihitung on-the-fly saat halaman Dashboard
// dibuka -- kalau tidak ada yang buka dashboard, reminder tidak pernah "terkirim".
class CheckJatuhTempoReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const REMINDER_HARI = 90;

    public function handle(): void
    {
         Log::info('[distributed-proof] CheckJatuhTempoReminderJob diproses', [
            'hostname' => gethostname(),
            'pid' => getmypid(),
            'connection' => config('queue.default'),
        ]);

        $batas = now()->addDays(self::REMINDER_HARI)->toDateString();
        $penerima = User::whereHas('role', fn ($q) => $q->whereIn('nama', ['pimpinan', 'aset', 'pengadaan', 'superadmin']))
            ->where('is_active', true)
            ->get();

        PgReminder::where('status', 'Aktif')
            ->whereDate('tanggal_jatuh_tempo', '<=', $batas)
            ->each(fn ($r) => $this->notify($penerima, $r->judul, $r->kategori, $r->tanggal_jatuh_tempo, $r->catatan));

        AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
            ->whereDate('jatuh_tempo', '<=', $batas)
            ->each(fn ($p) => $this->notify($penerima, "PKS: {$p->judul}", 'PKS', $p->jatuh_tempo, "Vendor: {$p->vendor}"));

        AsInvoiceSewa::where('status', '!=', 'Lunas')
            ->whereDate('jatuh_tempo', '<=', $batas)
            ->each(fn ($i) => $this->notify($penerima, "Invoice Sewa: {$i->no_invoice}", 'Sewa', $i->jatuh_tempo, "Vendor: {$i->vendor}"));
    }

    private function notify($penerima, string $judul, string $kategori, $tanggal, string $catatan): void
    {
        foreach ($penerima as $user) {
            $user->notify(new JatuhTempoNotification($judul, $kategori, (string) $tanggal, $catatan));
        }
    }
}
