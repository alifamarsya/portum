<?php

namespace App\Services;

class AmortisasiCalculator
{
    public function hitungNilaiPerBulan(float $nilaiPerolehan, int $umurBulan): float
    {
        if ($umurBulan <= 0) {
            throw new \InvalidArgumentException('Umur bulan harus lebih dari 0');
        }

        return round($nilaiPerolehan / $umurBulan, 2);
    }

    public function hitungAkumulasi(float $nilaiPerBulan, int $bulanBerjalan): float
    {
        return round($nilaiPerBulan * max(0, $bulanBerjalan), 2);
    }

    public function hitungNilaiBuku(float $nilaiPerolehan, float $akumulasi): float
    {
        return max(0, round($nilaiPerolehan - $akumulasi, 2));
    }

    /**
     * Hitung bulan berjalan dari tanggal_mulai amortisasi sampai hari ini.
     * Dipakai ModuleController saat menyimpan/memperbarui data as_amortisasi.
     */
    public function hitungBulanBerjalan(\DateTimeInterface $tanggalMulai): int
    {
        $mulai = \Carbon\Carbon::instance($tanggalMulai)->startOfMonth();
        $sekarang = \Carbon\Carbon::now()->startOfMonth();

        return max(0, $mulai->diffInMonths($sekarang));
    }
}