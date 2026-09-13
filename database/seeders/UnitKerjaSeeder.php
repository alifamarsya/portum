<?php

namespace Database\Seeders;

use App\Models\UnitKerja;
use Illuminate\Database\Seeder;

class UnitKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $unitKerja = [
            [
                'id'            => 1,
                'department_id' => 1, // Bagian Umum & Rumah Tangga
                'kode'          => 'UK-URT',
                'nama'          => 'Unit Kerja Umum & Rumah Tangga',
                'deskripsi'     => 'Mengelola kendaraan operasional, biaya harian, fasilitas kantor, pemeliharaan gedung, kebersihan, keamanan, dan K3.',
                'is_active'     => true,
            ],
            [
                'id'            => 2,
                'department_id' => 1, // Bagian Umum & Rumah Tangga
                'kode'          => 'UK-DOK',
                'nama'          => 'Unit Kerja Pengelolaan Dokumen & Kearsipan',
                'deskripsi'     => 'Mengelola surat masuk/keluar, memo, arsip dokumen fisik & digital, dan dokumen legalitas.',
                'is_active'     => true,
            ],
            [
                'id'            => 3,
                'department_id' => 2, // Bagian Aset/Inventaris & Logistik
                'kode'          => 'UK-ADM-ASET',
                'nama'          => 'Unit Kerja Administrasi Aset & Inventaris',
                'deskripsi'     => 'Mengelola inventarisasi, kodefikasi & pelabelan aset, rekonsiliasi data, perhitungan penyusutan, reklasifikasi, dan penghapusan aset (disposal).',
                'is_active'     => true,
            ],
            [
                'id'            => 4,
                'department_id' => 2, // Bagian Aset/Inventaris & Logistik
                'kode'          => 'UK-LOGISTIK',
                'nama'          => 'Unit Kerja Logistik & Pelaporan',
                'deskripsi'     => 'Mengelola logistik operasional, koordinasi penerimaan & distribusi barang/jasa, penyusunan laporan aset, dan administrasi pembayaran tagihan.',
                'is_active'     => true,
            ],
            // Fase 3 — Bagian Pengadaan & Pemeliharaan Aset & Inventaris
            [
                'id'            => 5,
                'department_id' => 3, // Bagian Pengadaan & Pemeliharaan Aset & Inventaris
                'kode'          => 'UK-PENGADAAN',
                'nama'          => 'Unit Kerja Pengadaan Aset & Inventaris',
                'deskripsi'     => 'Mengelola perencanaan kebutuhan, seleksi vendor & negosiasi, proses pengadaan sesuai SOP, dan administrasi kontrak & dokumen pengadaan.',
                'is_active'     => true,
            ],
            [
                'id'            => 6,
                'department_id' => 3, // Bagian Pengadaan & Pemeliharaan Aset & Inventaris
                'kode'          => 'UK-PEMELIHARAAN',
                'nama'          => 'Unit Kerja Pemeliharaan & Pengawasan Aset/Inventaris',
                'deskripsi'     => 'Mengelola pemeliharaan rutin aset & inventaris, monitoring kondisi fisik, pengawasan penggunaan aset, serta tindak lanjut perbaikan & penghapusan aset.',
                'is_active'     => true,
            ],
        ];

        foreach ($unitKerja as $uk) {
            UnitKerja::updateOrCreate(['id' => $uk['id']], $uk);
        }
    }
}
