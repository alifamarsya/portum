<?php

namespace Tests\Unit;

use App\Services\AuditHasher;
use PHPUnit\Framework\TestCase;

class AuditHasherTest extends TestCase
{
    public function test_hash_konsisten_untuk_input_sama(): void
    {
        $hasher = new AuditHasher();
        $waktu = '2026-01-01 10:00:00';

        $hash1 = $hasher->hitungHash('genesis', 'CREATE', 'aset_logistik', 'AsAset', 1, 'Aset baru', $waktu);
        $hash2 = $hasher->hitungHash('genesis', 'CREATE', 'aset_logistik', 'AsAset', 1, 'Aset baru', $waktu);

        $this->assertEquals($hash1, $hash2);
    }

    public function test_hash_berbeda_jika_prev_hash_berbeda(): void
    {
        $hasher = new AuditHasher();
        $waktu = '2026-01-01 10:00:00';

        $hashA = $hasher->hitungHash('hash-lama-1', 'CREATE', 'aset_logistik', 'AsAset', 1, 'Aset baru', $waktu);
        $hashB = $hasher->hitungHash('hash-lama-2', 'CREATE', 'aset_logistik', 'AsAset', 1, 'Aset baru', $waktu);

        $this->assertNotEquals($hashA, $hashB);
    }

    public function test_hash_berubah_jika_keterangan_diubah_sedikit_saja(): void
    {
        $hasher = new AuditHasher();
        $waktu = '2026-01-01 10:00:00';

        $hashAsli = $hasher->hitungHash('genesis', 'UPDATE', 'aset_logistik', 'AsTemuan', 5, 'Kondisi baik', $waktu);
        $hashDiubah = $hasher->hitungHash('genesis', 'UPDATE', 'aset_logistik', 'AsTemuan', 5, 'Kondisi baik.', $waktu);

        $this->assertNotEquals($hashAsli, $hashDiubah, 'Perubahan sekecil apa pun pada data harus mengubah hash -- ini bukti utama integritas hash chain.');
    }

    public function test_cocok_mendeteksi_hash_valid(): void
    {
        $hasher = new AuditHasher();
        $hash = $hasher->hitungHash('genesis', 'CREATE', 'pengadaan', 'PgSpk', 3, 'SPK baru', '2026-01-01 10:00:00');

        $this->assertTrue($hasher->cocok($hash, $hash));
    }

    public function test_cocok_mendeteksi_hash_tidak_valid(): void
    {
        $hasher = new AuditHasher();
        $hashAsli = $hasher->hitungHash('genesis', 'CREATE', 'pengadaan', 'PgSpk', 3, 'SPK baru', '2026-01-01 10:00:00');

        $this->assertFalse($hasher->cocok($hashAsli, 'hash-palsu-hasil-manipulasi'));
    }
}