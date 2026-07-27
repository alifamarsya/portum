<?php

namespace App\Jobs;

use App\Models\UmBiayaHarian;
use App\Models\UmGenerateLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

// CPMK Sistem Komputasi Terdistribusi: rekap biaya bulanan sengaja dipindah
// ke background job, bukan dihitung langsung saat user klik "Generate" di
// halaman (seperti versi Python) -- supaya request HTTP tidak nge-block
// kalau datanya sudah besar, dan bisa di-retry otomatis kalau gagal.
class GenerateLaporanBiayaBulananJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $periodeAwal,
        public string $periodeAkhir,
        public ?string $kategori = null,
        public ?string $dibuatOleh = null,
    ) {}

    public function handle(): void
    {
        Log::info('[distributed-proof] GenerateLaporanBiayaBulananJob diproses', [
            'hostname' => gethostname(),
            'pid' => getmypid(),
            'connection' => config('queue.default'),
        ]);

        $query = UmBiayaHarian::whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir])
            ->where('approval_status', 'Disetujui');

        if ($this->kategori) {
            $query->where('kategori', $this->kategori);
        }

        $items = $query->get();

        UmGenerateLog::create([
            'periode_awal' => $this->periodeAwal,
            'periode_akhir' => $this->periodeAkhir,
            'kategori' => $this->kategori,
            'jumlah_item' => $items->count(),
            'total' => $items->sum('jumlah'),
            'dibuat_oleh' => $this->dibuatOleh,
        ]);
    }
}
