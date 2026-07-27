<?php

namespace App\Console\Commands;

use App\Models\AsAmortisasi;
use App\Models\DimKategori;
use App\Models\DimUnitKerja;
use App\Models\DimVendor;
use App\Models\DimWaktu;
use App\Models\FactAmortisasiAset;
use App\Models\FactBiayaBulanan;
use App\Models\FactPengadaan;
use App\Models\PgNegosiasi;
use App\Models\UmBiayaHarian;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

// CPMK Data Warehouse: proses ETL (Extract dari tabel OLTP -> Transform
// agregasi bulanan -> Load ke fact table) untuk 3 domain: biaya, amortisasi
// aset, dan pengadaan. Dijadwalkan bulanan lewat routes/console.php.
class EtlDataWarehouseCommand extends Command
{
    protected $signature = 'dw:etl {--bulan=} {--tahun=}';
    protected $description = 'Jalankan ETL bulanan ke tabel data warehouse (fact_biaya_bulanan, fact_amortisasi_aset, fact_pengadaan)';

    public function handle(): int
    {
        $tahun = (int) ($this->option('tahun') ?: now()->subMonth()->year);
        $bulan = (int) ($this->option('bulan') ?: now()->subMonth()->month);
        $periodeAwal = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $periodeAkhir = $periodeAwal->copy()->endOfMonth();

        $this->info("ETL periode {$periodeAwal->translatedFormat('F Y')}");

        $dimWaktu = DimWaktu::updateOrCreate(
            ['tanggal' => $periodeAwal->toDateString()],
            [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'nama_bulan' => $periodeAwal->translatedFormat('F'),
                'kuartal' => (int) ceil($bulan / 3),
            ]
        );

        $this->etlBiaya($dimWaktu, $periodeAwal, $periodeAkhir);
        $this->etlAmortisasi($dimWaktu, $periodeAwal);
        $this->etlPengadaan($dimWaktu, $periodeAwal, $periodeAkhir);

        $this->info('ETL selesai.');
        return self::SUCCESS;
    }

    private function etlBiaya(DimWaktu $dimWaktu, Carbon $awal, Carbon $akhir): void
    {
        UmBiayaHarian::whereBetween('tanggal', [$awal, $akhir])
            ->where('approval_status', 'Disetujui')
            ->selectRaw('kategori, count(*) as jml, sum(jumlah) as total')
            ->groupBy('kategori')
            ->get()
            ->each(function ($row) use ($dimWaktu) {
                $kategori = DimKategori::firstOrCreate(
                    ['jenis_kategori' => 'biaya', 'nama_kategori' => $row->kategori ?: 'Lainnya']
                );
                FactBiayaBulanan::updateOrCreate(
                    ['dim_waktu_id' => $dimWaktu->id, 'dim_kategori_id' => $kategori->id, 'dim_unit_kerja_id' => null],
                    ['total_biaya' => $row->total, 'jumlah_transaksi' => $row->jml]
                );
            });
        $this->line('  - fact_biaya_bulanan diperbarui');
    }

    private function etlAmortisasi(DimWaktu $dimWaktu, Carbon $periode): void
    {
        AsAmortisasi::all()->each(function (AsAmortisasi $a) use ($dimWaktu, $periode) {
            FactAmortisasiAset::updateOrCreate(
                ['dim_waktu_id' => $dimWaktu->id, 'as_amortisasi_id' => $a->id],
                [
                    'nilai_penyusutan_bulan' => $a->nilai_per_bulan,
                    'akumulasi' => $a->akumulasi,
                    'nilai_buku' => $a->nilai_buku,
                ]
            );
        });
        $this->line('  - fact_amortisasi_aset diperbarui');
    }

    private function etlPengadaan(DimWaktu $dimWaktu, Carbon $awal, Carbon $akhir): void
    {
        PgNegosiasi::whereBetween('tanggal', [$awal, $akhir])
            ->selectRaw('vendor, count(*) as jml, sum(nilai_nego) as total')
            ->groupBy('vendor')
            ->get()
            ->each(function ($row) use ($dimWaktu) {
                $vendor = DimVendor::firstOrCreate(['nama_vendor' => $row->vendor]);
                FactPengadaan::updateOrCreate(
                    ['dim_waktu_id' => $dimWaktu->id, 'dim_vendor_id' => $vendor->id, 'dim_kategori_id' => null],
                    ['total_nilai' => $row->total, 'jumlah_transaksi' => $row->jml]
                );
            });
        $this->line('  - fact_pengadaan diperbarui');
    }
}
