<?php

namespace Tests\Unit;

use App\Services\AmortisasiCalculator;
use PHPUnit\Framework\TestCase;

class AmortisasiCalculatorTest extends TestCase
{
    public function test_hitung_nilai_per_bulan(): void
    {
        $calc = new AmortisasiCalculator();
        $this->assertEquals(100000.0, $calc->hitungNilaiPerBulan(1200000, 12));
    }

    public function test_umur_bulan_nol_melempar_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new AmortisasiCalculator())->hitungNilaiPerBulan(1000000, 0);
    }

    public function test_umur_bulan_negatif_melempar_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new AmortisasiCalculator())->hitungNilaiPerBulan(1000000, -5);
    }

    public function test_hitung_akumulasi(): void
    {
        $calc = new AmortisasiCalculator();
        $this->assertEquals(500000.0, $calc->hitungAkumulasi(100000, 5));
    }

    public function test_akumulasi_bulan_negatif_dianggap_nol(): void
    {
        $calc = new AmortisasiCalculator();
        $this->assertEquals(0.0, $calc->hitungAkumulasi(100000, -3));
    }

    public function test_nilai_buku_tidak_boleh_negatif(): void
    {
        $calc = new AmortisasiCalculator();
        $this->assertEquals(0.0, $calc->hitungNilaiBuku(100000, 150000));
    }

    public function test_nilai_buku_normal(): void
    {
        $calc = new AmortisasiCalculator();
        $this->assertEquals(700000.0, $calc->hitungNilaiBuku(1200000, 500000));
    }
}