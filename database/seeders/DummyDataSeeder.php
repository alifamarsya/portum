<?php

namespace Database\Seeders;

use App\Models\AsAmortisasi;
use App\Models\AsAset;
use App\Models\AsInvoiceSewa;
use App\Models\AsMemoSewaCabang;
use App\Models\AsPks;
use App\Models\AsTemuan;
use App\Models\AuditLog;
use App\Models\DimKategori;
use App\Models\DimUnitKerja;
use App\Models\DimVendor;
use App\Models\DimWaktu;
use App\Models\FactAmortisasiAset;
use App\Models\FactBiayaBulanan;
use App\Models\FactPengadaan;
use App\Models\Panduan;
use App\Models\PgDraftDokumen;
use App\Models\PgMemoInternal;
use App\Models\PgNegosiasi;
use App\Models\PgPenawaran;
use App\Models\PgReminder;
use App\Models\PgSpk;
use App\Models\RisalahRapat;
use App\Models\SrMemoKeluar;
use App\Models\SrMemoMasuk;
use App\Models\SrSuratKeluar;
use App\Models\SrSuratMasuk;
use App\Models\UmBiayaHarian;
use App\Models\UmKendaraan;
use App\Models\UmPermintaanCabang;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $admin = User::first();
        $adminId = $admin?->id ?? 1;

        // 1. KENDARAAN OPERASIONAL
        $kendaraanList = [
            ['no_polisi' => 'DN 1001 SB', 'jenis' => 'Minibus', 'merk' => 'Toyota Innova Zenix 2.0 V', 'tahun' => '2024', 'peruntukan' => 'Kendaraan Dinas Direksi', 'driver' => 'Ahmad Fauzi', 'status' => 'Aktif', 'keterangan' => 'Kondisi sangat baik, servis rutin di Nasmoco'],
            ['no_polisi' => 'DN 1245 AB', 'jenis' => 'Double Cabin', 'merk' => 'Toyota Hilux 2.4 D-Cab', 'tahun' => '2023', 'peruntukan' => 'Operasional Logistik & Kas', 'driver' => 'Budi Santoso', 'status' => 'Aktif', 'keterangan' => 'Dilengkapi brankas dan GPS tracker'],
            ['no_polisi' => 'DN 1789 CB', 'jenis' => 'MPV', 'merk' => 'Toyota Avanza 1.5 G', 'tahun' => '2023', 'peruntukan' => 'Operasional Umum & RT', 'driver' => 'I Made Suartana', 'status' => 'Aktif', 'keterangan' => 'Operasional harian kantor pusat'],
            ['no_polisi' => 'DN 1122 SB', 'jenis' => 'SUV', 'merk' => 'Mitsubishi Pajero Sport Dakar', 'tahun' => '2024', 'peruntukan' => 'Dinas Pimpinan Kantor Pusat', 'driver' => 'Rizal Pratama', 'status' => 'Aktif', 'keterangan' => 'Perjalanan dinas luar kota'],
            ['no_polisi' => 'DN 1334 DB', 'jenis' => 'Blind Van', 'merk' => 'Daihatsu Gran Max 1.5', 'tahun' => '2022', 'peruntukan' => 'Distribusi Logistik ATK', 'driver' => 'Hendra Wijaya', 'status' => 'Aktif', 'keterangan' => 'Distribusi dokumen dan inventaris cabang'],
        ];
        foreach ($kendaraanList as $k) {
            UmKendaraan::updateOrCreate(['no_polisi' => $k['no_polisi']], $k);
        }

        // 2. BIAYA HARIAN OPERASIONAL (6 Bulan Terakhir)
        $biayaTemplates = [
            ['kategori' => 'BBM', 'kendaraan' => 'DN 1001 SB', 'nama_beban' => 'Beban BBM Operasional', 'rekening_debet' => '5.1.01.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Pengisian Pertamax Turbo Dinas Direksi', 'jumlah' => 450000],
            ['kategori' => 'BBM', 'kendaraan' => 'DN 1245 AB', 'nama_beban' => 'Beban BBM Logistik', 'rekening_debet' => '5.1.01.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'BBM Dexlite Pengawalan Kas Luwuk - Palu', 'jumlah' => 850000],
            ['kategori' => 'BBM', 'kendaraan' => 'DN 1789 CB', 'nama_beban' => 'Beban BBM Operasional', 'rekening_debet' => '5.1.01.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'BBM Pertalite Kurir dan Arsip', 'jumlah' => 250000],
            ['kategori' => 'Perawatan', 'kendaraan' => 'DN 1001 SB', 'nama_beban' => 'Beban Pemeliharaan Kendaraan', 'rekening_debet' => '5.1.02.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Servis berkala 20.000 KM & ganti oli', 'jumlah' => 2150000],
            ['kategori' => 'Perawatan', 'kendaraan' => 'DN 1245 AB', 'nama_beban' => 'Beban Pemeliharaan Kendaraan', 'rekening_debet' => '5.1.02.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Penggantian ban depan 2 unit Bridgestone', 'jumlah' => 3400000],
            ['kategori' => 'Rumah Tangga', 'kendaraan' => null, 'nama_beban' => 'Beban Keperluan Kantor', 'rekening_debet' => '5.1.03.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Pembelian air mineral galon & konsumsi pantry', 'jumlah' => 1250000],
            ['kategori' => 'Rumah Tangga', 'kendaraan' => null, 'nama_beban' => 'Beban Pemeliharaan Gedung', 'rekening_debet' => '5.1.02.02', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Perbaikan & cuci AC Ruang Server lantai 2', 'jumlah' => 1800000],
            ['kategori' => 'Lainnya', 'kendaraan' => null, 'nama_beban' => 'Beban Jamuan & Rapat', 'rekening_debet' => '5.1.04.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Konsumsi Rapat Evaluasi Kinerja Triwulan', 'jumlah' => 2850000],
        ];

        UmBiayaHarian::truncate();

        for ($m = 5; $m >= 0; $m--) {
            $monthDate = now()->copy()->subMonths($m);
            foreach ($biayaTemplates as $idx => $tmpl) {
                $day = min(3 + ($idx * 3), 27);
                $date = Carbon::create($monthDate->year, $monthDate->month, $day);
                
                // Variasi jumlah sedikit agar grafik dinamis
                $multiplier = 1 + (($idx % 3) * 0.15) - ($m * 0.05);
                $finalJumlah = round($tmpl['jumlah'] * max($multiplier, 0.75), -3);

                // Bulan berjalan sebagian status Diajukan untuk Approval
                $status = ($m === 0 && $idx >= 6) ? 'Diajukan' : 'Disetujui';
                $appStatus = $status === 'Disetujui' ? 'Disetujui' : 'Diajukan';

                UmBiayaHarian::create([
                    'tanggal' => $date->toDateString(),
                    'kategori' => $tmpl['kategori'],
                    'kendaraan' => $tmpl['kendaraan'],
                    'nama_beban' => $tmpl['nama_beban'],
                    'rekening_debet' => $tmpl['rekening_debet'],
                    'rekening_kredit' => $tmpl['rekening_kredit'],
                    'uraian' => $tmpl['uraian'],
                    'jumlah' => $finalJumlah,
                    'no_nota' => 'NOTA-' . $date->format('ymd') . '-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'status' => $status,
                    'maker_id' => $adminId,
                    'checker_id' => $status === 'Disetujui' ? $adminId : null,
                    'approval_status' => $appStatus,
                    'approved_at' => $status === 'Disetujui' ? $date->copy()->addHours(4) : null,
                    'catatan_approval' => $status === 'Disetujui' ? 'Disetujui sesuai pagu operasional' : null,
                    'dibuat_oleh' => 'Administrator',
                ]);
            }
        }

        // 3. PERMINTAAN CABANG (ATK & INVENTARIS)
        UmPermintaanCabang::truncate();
        $permintaanCabang = [
            ['no_permintaan' => 'REQ-2026-001', 'tanggal' => now()->subDays(2)->toDateString(), 'unit_kerja' => 'KC Luwuk', 'jenis' => 'ATK', 'uraian' => 'Kertas HVS A4 80gr PaperOne & Binder Clip', 'jumlah' => '50', 'satuan' => 'Rim', 'status' => 'Diajukan', 'approval_status' => 'Diajukan', 'petugas' => 'Siti Rahma'],
            ['no_permintaan' => 'REQ-2026-002', 'tanggal' => now()->subDays(4)->toDateString(), 'unit_kerja' => 'KC Tolitoli', 'jenis' => 'Inventaris', 'uraian' => 'Mesin Hitung Uang Glory GFB-800', 'jumlah' => '2', 'satuan' => 'Unit', 'status' => 'Diajukan', 'approval_status' => 'Diajukan', 'petugas' => 'Andi Wijaya'],
            ['no_permintaan' => 'REQ-2026-003', 'tanggal' => now()->subDays(10)->toDateString(), 'unit_kerja' => 'KC Poso', 'jenis' => 'ATK', 'uraian' => 'Buku Tabungan & Formulir Slip Setoran/Tarikan', 'jumlah' => '200', 'satuan' => 'Buku', 'status' => 'Disetujui', 'approval_status' => 'Disetujui', 'petugas' => 'Ferryanto'],
            ['no_permintaan' => 'REQ-2026-004', 'tanggal' => now()->subDays(15)->toDateString(), 'unit_kerja' => 'KCP Parigi', 'jenis' => 'Inventaris', 'uraian' => 'Kursi Kerja Ergonomis Staff CS', 'jumlah' => '4', 'satuan' => 'Unit', 'status' => 'Disetujui', 'approval_status' => 'Disetujui', 'petugas' => 'Dewi Lestari'],
            ['no_permintaan' => 'REQ-2026-005', 'tanggal' => now()->subDays(20)->toDateString(), 'unit_kerja' => 'KC Morowali', 'jenis' => 'ATK', 'uraian' => 'Toner Cartridge HP LaserJet Enterprise', 'jumlah' => '6', 'satuan' => 'Pcs', 'status' => 'Disetujui', 'approval_status' => 'Disetujui', 'petugas' => 'Wahyu Hidayat'],
        ];
        foreach ($permintaanCabang as $req) {
            UmPermintaanCabang::create(array_merge($req, ['maker_id' => $adminId, 'checker_id' => $adminId]));
        }

        // 4. DATA ASET & INVENTARIS
        AsAset::truncate();
        $asetList = [
            ['kode_aset' => 'AST-TI-2024-001', 'nama_aset' => 'Server Database Oracle Enterprise Dell PowerEdge R750', 'kategori' => 'Teknologi Informasi', 'lokasi' => 'Data Center Gedung Kantor Pusat', 'tanggal_perolehan' => '2024-03-15', 'nilai_perolehan' => 385000000, 'umur_ekonomis' => 60, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Divisi TI'],
            ['kode_aset' => 'AST-TI-2024-002', 'nama_aset' => 'Storage Area Network (SAN) Dell Unity XT 380', 'kategori' => 'Teknologi Informasi', 'lokasi' => 'Data Center Gedung Kantor Pusat', 'tanggal_perolehan' => '2024-04-10', 'nilai_perolehan' => 450000000, 'umur_ekonomis' => 60, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Divisi TI'],
            ['kode_aset' => 'AST-GD-2023-001', 'nama_aset' => 'Genset Silent Perkins 150 kVA Standby Power', 'kategori' => 'Gedung & Utilitas', 'lokasi' => 'Ruang Utilitas Kantor Pusat Palu', 'tanggal_perolehan' => '2023-06-20', 'nilai_perolehan' => 275000000, 'umur_ekonomis' => 120, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Divisi Umum'],
            ['kode_aset' => 'AST-OP-2024-003', 'nama_aset' => 'Mesin Hitung & Detektor Uang Palsu Glory GFB-800 (10 Unit)', 'kategori' => 'Peralatan Operasional', 'lokasi' => 'Khazanah & Kasir Kantor Pusat', 'tanggal_perolehan' => '2024-01-12', 'nilai_perolehan' => 95000000, 'umur_ekonomis' => 48, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Divisi Operasional'],
            ['kode_aset' => 'AST-OP-2023-004', 'nama_aset' => 'Brankas Lemari Besi Chubb Safes Europa Grade V', 'kategori' => 'Peralatan Operasional', 'lokasi' => 'Ruang Khasanah Utama', 'tanggal_perolehan' => '2023-02-05', 'nilai_perolehan' => 160000000, 'umur_ekonomis' => 240, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Divisi Operasional'],
            ['kode_aset' => 'AST-TI-2025-005', 'nama_aset' => 'Laptop Lenovo ThinkPad L14 Gen 4 Core i7 (25 Unit)', 'kategori' => 'Peralatan Kantor', 'lokasi' => 'Divisi Analisis Kredit & Auditor', 'tanggal_perolehan' => '2025-02-18', 'nilai_perolehan' => 387500000, 'umur_ekonomis' => 36, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Divisi Umum & RT'],
            ['kode_aset' => 'AST-TI-2025-006', 'nama_aset' => 'PC Desktop All-in-One HP ProOne 440 G9 (30 Unit)', 'kategori' => 'Peralatan Kantor', 'lokasi' => 'Front Office & Customer Service', 'tanggal_perolehan' => '2025-05-10', 'nilai_perolehan' => 420000000, 'umur_ekonomis' => 48, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Divisi Umum & RT'],
            ['kode_aset' => 'AST-GD-2024-007', 'nama_aset' => 'AC Floor Standing Daikin 5 PK (8 Unit)', 'kategori' => 'Gedung & Utilitas', 'lokasi' => 'Banking Hall & Lobby Utama', 'tanggal_perolehan' => '2024-08-25', 'nilai_perolehan' => 152000000, 'umur_ekonomis' => 60, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Divisi Umum'],
        ];
        foreach ($asetList as $ast) {
            AsAset::create($ast);
        }

        // 5. AMORTISASI ASET
        AsAmortisasi::truncate();
        $amortisasiList = [
            ['nama_biaya' => 'Lisensi Oracle Database Enterprise Edition 2024-2026', 'nilai_perolehan' => 360000000, 'tanggal_mulai' => '2024-01-01', 'umur_bulan' => 36, 'nilai_per_bulan' => 10000000, 'akumulasi' => 260000000, 'nilai_buku' => 100000000, 'keterangan' => 'Amortisasi tahun ke-3'],
            ['nama_biaya' => 'Software Security Antivirus Endpoint Kaspersky EDR', 'nilai_perolehan' => 96000000, 'tanggal_mulai' => '2025-01-01', 'umur_bulan' => 24, 'nilai_per_bulan' => 4000000, 'akumulasi' => 56000000, 'nilai_buku' => 40000000, 'keterangan' => 'Proteksi workstation seluruh cabang'],
            ['nama_biaya' => 'Renovasi & Desain Interior Banking Hall KC Luwuk', 'nilai_perolehan' => 240000000, 'tanggal_mulai' => '2024-06-01', 'umur_bulan' => 48, 'nilai_per_bulan' => 5000000, 'akumulasi' => 105000000, 'nilai_buku' => 135000000, 'keterangan' => 'Standarisasi Corporate Identity'],
            ['nama_biaya' => 'Sewa Gedung Kantor Cabang Pembantu Parigi 5 Tahun', 'nilai_perolehan' => 450000000, 'tanggal_mulai' => '2023-01-01', 'umur_bulan' => 60, 'nilai_per_bulan' => 7500000, 'akumulasi' => 322500000, 'nilai_buku' => 127500000, 'keterangan' => 'Sewa jangka panjang'],
        ];
        foreach ($amortisasiList as $am) {
            AsAmortisasi::create($am);
        }

        // 6. PERJANJIAN KERJASAMA (PKS) & REMINDER
        AsPks::truncate();
        $pksList = [
            ['no_pks' => 'PKS/012/BS/II/2024', 'judul' => 'Perjanjian Sewa Gedung Kantor Cabang Tolitoli', 'vendor' => 'H. Abdul Rahman (Pemilik Properti)', 'div_owner' => 'Divisi Umum & Aset', 'tanggal_mulai' => '2024-03-01', 'jatuh_tempo' => now()->addDays(45)->toDateString(), 'nilai' => 180000000, 'status' => 'Akan Jatuh Tempo', 'approval_status' => 'Disetujui'],
            ['no_pks' => 'PKS/045/BS/IV/2023', 'judul' => 'Perjanjian Maintenance Jaringan & ATM Switching', 'vendor' => 'PT Artajasa Pembayaran Elektronis', 'div_owner' => 'Divisi TI', 'tanggal_mulai' => '2023-05-01', 'jatuh_tempo' => now()->addDays(70)->toDateString(), 'nilai' => 420000000, 'status' => 'Akan Jatuh Tempo', 'approval_status' => 'Disetujui'],
            ['no_pks' => 'PKS/088/BS/VIII/2024', 'judul' => 'Jasa Pengamanan & Tenaga Satpam Gedung Kantor Pusat & KC Palu', 'vendor' => 'PT Bravo Satria Perkasa', 'div_owner' => 'Divisi Umum', 'tanggal_mulai' => '2024-08-01', 'jatuh_tempo' => now()->addDays(180)->toDateString(), 'nilai' => 850000000, 'status' => 'Aktif', 'approval_status' => 'Disetujui'],
            ['no_pks' => 'PKS/102/BS/X/2024', 'judul' => 'Jasa Kebersihan (Cleaning Service) Terpadu', 'vendor' => 'PT ISS Indonesia', 'div_owner' => 'Divisi Umum', 'tanggal_mulai' => '2024-10-01', 'jatuh_tempo' => now()->addDays(230)->toDateString(), 'nilai' => 360000000, 'status' => 'Aktif', 'approval_status' => 'Disetujui'],
            ['no_pks' => 'PKS/005/BS/I/2025', 'judul' => 'Sewa Kendaraan Operasional Logistik Truk Kas Titipan', 'vendor' => 'PT Serasi Autoraya (TRAC)', 'div_owner' => 'Divisi Umum & Logistik', 'tanggal_mulai' => '2025-01-01', 'jatuh_tempo' => now()->addDays(300)->toDateString(), 'nilai' => 290000000, 'status' => 'Aktif', 'approval_status' => 'Disetujui'],
        ];
        foreach ($pksList as $pks) {
            AsPks::create(array_merge($pks, ['maker_id' => $adminId, 'checker_id' => $adminId]));
        }

        // 7. PENGADAAN (MEMO, PENAWARAN, NEGOSIASI, SPK, REMINDER)
        PgMemoInternal::truncate();
        PgPenawaran::truncate();
        PgNegosiasi::truncate();
        PgSpk::truncate();
        PgReminder::truncate();

        // Reminder Aktif
        $reminders = [
            ['judul' => 'Perpanjangan Sewa Gedung Kantor Cabang Tolitoli', 'kategori' => 'Sewa Properti', 'tanggal_jatuh_tempo' => now()->addDays(45)->toDateString(), 'status' => 'Aktif', 'catatan' => 'Segera siapkan draft negosiasi harga perpanjangan 3 tahun ke depan.'],
            ['judul' => 'Pembaruan Kontrak Maintenance ATM Switching Artajasa', 'kategori' => 'Teknologi Informasi', 'tanggal_jatuh_tempo' => now()->addDays(70)->toDateString(), 'status' => 'Aktif', 'catatan' => 'Evaluasi SLA jaringan ketersediaan 99.8%.'],
            ['judul' => 'Uji Emisi & Pajak Tahunan Kendaraan Dinas DN 1001 SB', 'kategori' => 'Kendaraan', 'tanggal_jatuh_tempo' => now()->addDays(25)->toDateString(), 'status' => 'Aktif', 'catatan' => 'Pajak STNK tahunan di Samsat Palu.'],
        ];
        foreach ($reminders as $r) {
            PgReminder::create($r);
        }

        // Pengadaan Negosiasi & SPK (Data Warehouse Source)
        $procurements = [
            ['vendor' => 'PT Mitra Komputindo Utama', 'barang' => 'Pengadaan UPS 20 kVA Ruang Data Center', 'nilai_awal' => 195000000, 'nilai_nego' => 182000000, 'tgl' => now()->subMonths(1)->startOfMonth()->addDays(10)],
            ['vendor' => 'PT Multi Sarana Perkasa', 'barang' => 'Pengadaan 25 Unit PC Workstation All-in-One', 'nilai_awal' => 325000000, 'nilai_nego' => 305000000, 'tgl' => now()->subMonths(2)->startOfMonth()->addDays(12)],
            ['vendor' => 'CV Celebes Graha Pratama', 'barang' => 'Renovasi Ruang Rapat Direksi Lantai 3', 'nilai_awal' => 145000000, 'nilai_nego' => 135000000, 'tgl' => now()->subMonths(3)->startOfMonth()->addDays(15)],
            ['vendor' => 'PT Sentra Pratama Logistik', 'barang' => 'Pengadaan 10 Unit Brankas Teller Tahan Api', 'nilai_awal' => 110000000, 'nilai_nego' => 98000000, 'tgl' => now()->subMonths(4)->startOfMonth()->addDays(5)],
            ['vendor' => 'PT Mitra Komputindo Utama', 'barang' => 'Peremajaan Switch Core Jaringan Cisco', 'nilai_awal' => 240000000, 'nilai_nego' => 225000000, 'tgl' => now()->subMonths(5)->startOfMonth()->addDays(20)],
            ['vendor' => 'CV Palu Mandiri Stationery', 'barang' => 'Pengadaan Formulir & ATK Cetakan Semester I', 'nilai_awal' => 88000000, 'nilai_nego' => 82000000, 'tgl' => now()->subMonths(0)->startOfMonth()->addDays(5)],
        ];

        foreach ($procurements as $idx => $prc) {
            PgMemoInternal::create([
                'no_memo' => 'MEMO-INT-2026-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'dari_unit' => 'Divisi Umum & Logistik',
                'ke_unit' => 'Direksi',
                'perihal' => 'Usulan ' . $prc['barang'],
                'tanggal' => $prc['tgl']->copy()->subDays(10)->toDateString(),
                'jenis' => 'Pengadaan Barang & Jasa',
                'status' => 'Disetujui',
                'keterangan' => 'Sesuai anggaran RKA 2026',
            ]);

            PgPenawaran::create([
                'no_penawaran' => 'SPH-' . $idx . '/' . date('Y'),
                'vendor' => $prc['vendor'],
                'barang_jasa' => $prc['barang'],
                'nilai' => $prc['nilai_awal'],
                'tanggal' => $prc['tgl']->copy()->subDays(5)->toDateString(),
                'status' => 'Diterima',
            ]);

            PgNegosiasi::create([
                'no_berita_acara' => 'BAN-2026-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'vendor' => $prc['vendor'],
                'barang_jasa' => $prc['barang'],
                'nilai_awal' => $prc['nilai_awal'],
                'nilai_nego' => $prc['nilai_nego'],
                'tanggal' => $prc['tgl']->toDateString(),
                'hasil' => 'Disepakati efisiensi anggaran pengadaan sebesar Rp ' . number_format($prc['nilai_awal'] - $prc['nilai_nego'], 0, ',', '.'),
            ]);

            PgSpk::create([
                'no_spk' => 'SPK/BS/' . date('Y') . '/' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'vendor' => $prc['vendor'],
                'pekerjaan' => $prc['barang'],
                'nilai' => $prc['nilai_nego'],
                'tanggal_terbit' => $prc['tgl']->toDateString(),
                'tanggal_selesai' => $prc['tgl']->copy()->addDays(30)->toDateString(),
                'status' => 'Selesai',
                'maker_id' => $adminId,
                'checker_id' => $adminId,
                'approval_status' => 'Disetujui',
                'approved_at' => $prc['tgl']->copy()->addDay(),
            ]);
        }

        // 8. SURAT & ARSIP MEMO
        SrSuratMasuk::truncate();
        SrSuratMasuk::create([
            'nomor_agenda' => 101,
            'no_surat' => 'S-142/KO.0601/2026',
            'pengirim' => 'Otoritas Jasa Keuangan (OJK) Sulawesi Tengah',
            'perihal' => 'Penyampaian Hasil Evaluasi Profil Risiko Kepatuhan Semester II',
            'tanggal' => now()->subDays(5)->toDateString(),
            'penerima' => 'Direktur Utama PT Bank Sulteng',
            'lokasi_arsip' => 'Bantex Sekper No. 04',
            'dibuat_oleh' => 'Sekretariat Perusahaan',
        ]);
        SrSuratMasuk::create([
            'nomor_agenda' => 102,
            'no_surat' => '28/12/PLU/Srt/B',
            'pengirim' => 'Kantor Perwakilan Bank Indonesia Sulawesi Tengah',
            'perihal' => 'Koordinasi Ketersediaan Uang Layak Edar Kas Titipan Idul Fitri',
            'tanggal' => now()->subDays(8)->toDateString(),
            'penerima' => 'Divisi Operasional & Jaringan',
            'lokasi_arsip' => 'Bantex Operasional No. 12',
            'dibuat_oleh' => 'Divisi Operasional',
        ]);

        // 9. AUDIT LOG (Cryptographic Hash Chaining)
        AuditLog::truncate();
        $logs = [
            ['username' => 'Administrator', 'aksi' => 'LOGIN', 'modul' => 'Auth', 'keterangan' => 'User Administrator berhasil masuk ke sistem', 'ip' => '127.0.0.1', 'time' => now()->subHours(6)],
            ['username' => 'Administrator', 'aksi' => 'APPROVE', 'modul' => 'Biaya Harian', 'keterangan' => 'Menyetujui pengajuan biaya BBM & Pemeliharaan Kendaraan DN 1001 SB', 'ip' => '127.0.0.1', 'time' => now()->subHours(4)],
            ['username' => 'Administrator', 'aksi' => 'UPDATE', 'modul' => 'PKS & Sewa', 'keterangan' => 'Memperbarui status jatuh tempo PKS Gedung KC Tolitoli', 'ip' => '127.0.0.1', 'time' => now()->subHours(2)],
            ['username' => 'Administrator', 'aksi' => 'CREATE', 'modul' => 'Aset', 'keterangan' => 'Registrasi unit inventaris baru Laptop ThinkPad L14', 'ip' => '127.0.0.1', 'time' => now()->subHours(1)],
            ['username' => 'Administrator', 'aksi' => 'ETL_SYNC', 'modul' => 'Data Warehouse', 'keterangan' => 'Sinkronisasi ETL data warehouse bulanan berhasil', 'ip' => '127.0.0.1', 'time' => now()->subMinutes(15)],
        ];

        foreach ($logs as $l) {
            AuditLog::create([
                'user_id'    => $adminId,
                'username'   => $l['username'],
                'aksi'       => $l['aksi'],
                'modul'      => $l['modul'],
                'keterangan' => $l['keterangan'],
                'ip_address' => $l['ip'],
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => $l['time'],
            ]);
        }

        // 10. RUN ETL UNTUK 6 BULAN TERAKHIR KE DATA WAREHOUSE
        FactBiayaBulanan::truncate();
        FactAmortisasiAset::truncate();
        FactPengadaan::truncate();
        DimWaktu::truncate();
        DimKategori::truncate();
        DimVendor::truncate();
        DimUnitKerja::truncate();

        for ($m = 5; $m >= 0; $m--) {
            $periodeAwal = now()->copy()->subMonths($m)->startOfMonth();
            $periodeAkhir = $periodeAwal->copy()->endOfMonth();
            $tahun = $periodeAwal->year;
            $bulan = $periodeAwal->month;

            $dimWaktu = DimWaktu::create([
                'tanggal' => $periodeAwal->toDateString(),
                'tahun' => $tahun,
                'bulan' => $bulan,
                'nama_bulan' => $periodeAwal->translatedFormat('F'),
                'kuartal' => (int) ceil($bulan / 3),
            ]);

            // ETL Fact Biaya Bulanan
            UmBiayaHarian::whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
                ->where('approval_status', 'Disetujui')
                ->selectRaw('kategori, count(*) as jml, sum(jumlah) as total')
                ->groupBy('kategori')
                ->get()
                ->each(function ($row) use ($dimWaktu) {
                    $kategori = DimKategori::firstOrCreate(
                        ['jenis_kategori' => 'biaya', 'nama_kategori' => $row->kategori ?: 'Lainnya']
                    );
                    FactBiayaBulanan::create([
                        'dim_waktu_id' => $dimWaktu->id,
                        'dim_kategori_id' => $kategori->id,
                        'dim_unit_kerja_id' => null,
                        'total_biaya' => $row->total,
                        'jumlah_transaksi' => $row->jml,
                    ]);
                });

            // ETL Fact Amortisasi
            AsAmortisasi::all()->each(function (AsAmortisasi $a) use ($dimWaktu) {
                FactAmortisasiAset::create([
                    'dim_waktu_id' => $dimWaktu->id,
                    'as_amortisasi_id' => $a->id,
                    'nilai_penyusutan_bulan' => $a->nilai_per_bulan,
                    'akumulasi' => $a->akumulasi,
                    'nilai_buku' => $a->nilai_buku,
                ]);
            });

            // ETL Fact Pengadaan
            PgNegosiasi::whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
                ->selectRaw('vendor, count(*) as jml, sum(nilai_nego) as total')
                ->groupBy('vendor')
                ->get()
                ->each(function ($row) use ($dimWaktu) {
                    $vendor = DimVendor::firstOrCreate(['nama_vendor' => $row->vendor]);
                    FactPengadaan::create([
                        'dim_waktu_id' => $dimWaktu->id,
                        'dim_vendor_id' => $vendor->id,
                        'dim_kategori_id' => null,
                        'total_nilai' => $row->total,
                        'jumlah_transaksi' => $row->jml,
                    ]);
                });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
