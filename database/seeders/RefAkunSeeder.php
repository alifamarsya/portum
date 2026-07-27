<?php

namespace Database\Seeders;

use App\Models\RefAkun;
use Illuminate\Database\Seeder;

class RefAkunSeeder extends Seeder
{
    // Referensi akun biaya, dipindahkan dari data Excel BIAYA 2026 yang
    // sebelumnya di-hardcode di portum.py.
    public function run(): void
    {
        $akun = [
            ['BEBAN BAHAN BAKAR UNTUK KANTOR', '000.00.6012109.001.360', 'Biaya Pembelian BBM Pertalite Kendaraan Operasional'],
            ['BEBAN JAMUAN KEPADA PEGAWAI', '000.00.6020123.001.360', 'Biaya Jamuan Kepada Pegawai dalam rangka Rapat'],
            ['BEBAN JAMUAN TAMU', '000.00.6020116.001.360', 'Biaya Jamuan Kepada Tamu'],
            ['BEBAN ALAT TULIS KANTOR', '000.00.6012101.001.360', 'Biaya Pembelian ATK'],
            ['BEBAN BARANG TERDAFTAR', '000.00.6012105.001.360', 'Biaya Pembelian Barang Terdaftar'],
            ['BEBAN TELFON/TELEX/FAX & TELEGRAM', '000.00.6012108.001.360', 'Biaya komunikasi'],
            ['PEMELIHARAAN PERBAIKAN INVENTARIS KANTOR', '000.00.6011703.001.360', 'Biaya Perbaikan/Service inventaris kantor'],
            ['PEMELIHARAAN PERBAIKAN KEND. BERMOTOR', '000.00.6011702.001.360', 'Biaya perbaikan/perawatan kendaraan bermotor'],
            ['BEBAN REKREASI & OLAHRAGA', '000.00.6020108.001.360', 'Biaya kegiatan olahraga/rekreasi'],
            ['BEBAN JARINGAN KOMUNIKASI DATA', '000.00.6012115.002.360', 'Biaya perangkat/jaringan komunikasi data'],
        ];
        foreach ($akun as [$nama, $rek, $ket]) {
            RefAkun::updateOrCreate(['nama_beban' => $nama], ['rekening_debet' => $rek, 'contoh_keterangan' => $ket]);
        }
    }
}
