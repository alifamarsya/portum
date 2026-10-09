<?php

namespace Database\Seeders;

use App\Models\AsAmortisasi;
use App\Models\AsAset;
use App\Models\AsAsetHistory;
use App\Models\AsDisposalAset;
use App\Models\AsDistribusiBarang;
use App\Models\AsInvoiceSewa;
use App\Models\AsMemoSewaCabang;
use App\Models\AsMutasiAset;
use App\Models\AsPembayaranTagihan;
use App\Models\AsPenerimaanBarang;
use App\Models\AsPks;
use App\Models\AsRekonsiliasiAset;
use App\Models\AsTemuan;
use App\Models\DkArsipDokumen;
use App\Models\DkDokumenLegalitas;
use App\Models\PgDraftDokumen;
use App\Models\PgMemoInternal;
use App\Models\PgNegosiasi;
use App\Models\PgPenawaran;
use App\Models\PgReminder;
use App\Models\PgSpk;
use App\Models\PmJadwalPemeliharaan;
use App\Models\PmMonitoringKondisi;
use App\Models\PmPengawasanPenggunaan;
use App\Models\PmPerencanaanKebutuhan;
use App\Models\PmTindakLanjutPerbaikan;
use App\Models\SrMemoKeluar;
use App\Models\SrMemoMasuk;
use App\Models\SrSuratKeluar;
use App\Models\SrSuratMasuk;
use App\Models\UmBiayaHarian;
use App\Models\UmChecklistKebersihan;
use App\Models\UmFasilitasKantor;
use App\Models\UmK3Insiden;
use App\Models\UmKendaraan;
use App\Models\UmPemeliharaanGedung;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OperationalModulesDummySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin?->id ?? 1;

        // ═════════════════════════════════════════════════════════════════════════
        // BAGIAN 1: UMUM & RUMAH TANGGA
        // ═════════════════════════════════════════════════════════════════════════

        // 1.1 Kendaraan & Driver
        $kendaraanBaru = [
            ['no_polisi' => 'DN 1450 SB', 'jenis' => 'Sedan', 'merk' => 'Toyota Vios 1.5 G', 'tahun' => '2023', 'peruntukan' => 'Operasional Protokoler', 'driver' => 'Muhammad Ilham', 'status' => 'Aktif', 'keterangan' => 'Standby penjemputan tamu VIP'],
            ['no_polisi' => 'DN 1888 XY', 'jenis' => 'MPV', 'merk' => 'Toyota Veloz 1.5 Q CVT', 'tahun' => '2024', 'peruntukan' => 'Operasional Sekretariat', 'driver' => 'Agus Prayitno', 'status' => 'Aktif', 'keterangan' => 'Unit baru operasional sekper'],
            ['no_polisi' => 'DN 8902 AB', 'jenis' => 'Pickup Box', 'merk' => 'Daihatsu Gran Max 1.5 Box', 'tahun' => '2023', 'peruntukan' => 'Distribusi Logistik Berat', 'driver' => 'Dedi Kurniawan', 'status' => 'Aktif', 'keterangan' => 'Jadwal distribusi mingguan'],
            ['no_polisi' => 'DN 1099 SB', 'jenis' => 'SUV', 'merk' => 'Toyota Fortuner 2.8 VRZ', 'tahun' => '2024', 'peruntukan' => 'Dinas Direksi Luar Kota', 'driver' => 'Wahyu Hidayat', 'status' => 'Aktif', 'keterangan' => 'Perjalanan dinas ke Morowali & Luwuk'],
            ['no_polisi' => 'DN 1566 CB', 'jenis' => 'Minibus', 'merk' => 'Toyota HiAce Premio Luxury', 'tahun' => '2023', 'peruntukan' => 'Kunjungan Kerja Delegasi', 'driver' => 'Mansyur R.', 'status' => 'Aktif', 'keterangan' => 'Kendaraan rombongan rapat kerja wilayah'],
        ];
        foreach ($kendaraanBaru as $k) {
            UmKendaraan::updateOrCreate(['no_polisi' => $k['no_polisi']], $k);
        }

        // 1.2 Fasilitas Kantor (8 Fasilitas)
        UmFasilitasKantor::truncate();
        $fasilitas = [
            ['nama_fasilitas' => 'Gedung Kantor Pusat PT Bank Sulteng', 'kode' => 'FAS-KP-001', 'kategori' => 'Ruangan', 'lokasi' => 'Jl. Sultan Hasanuddin No. 20, Palu', 'kondisi' => 'Baik', 'jumlah' => 1, 'status' => 'Aktif', 'keterangan' => 'Gedung utama 4 lantai', 'dibuat_oleh' => 'Administrator'],
            ['nama_fasilitas' => 'Ruang Data Center Tier 2', 'kode' => 'FAS-TI-002', 'kategori' => 'Ruangan', 'lokasi' => 'Lantai 2 Sayap Timur Kantor Pusat', 'kondisi' => 'Baik', 'jumlah' => 1, 'status' => 'Aktif', 'keterangan' => 'Dilengkapi AC Precision & FM200 Fire Suppression', 'dibuat_oleh' => 'Administrator'],
            ['nama_fasilitas' => 'Genset Silent Perkins 150 kVA Standby', 'kode' => 'FAS-EL-003', 'kategori' => 'Peralatan', 'lokasi' => 'Power House Lantai Dasar Belakang', 'kondisi' => 'Baik', 'jumlah' => 1, 'status' => 'Aktif', 'keterangan' => 'Back up listrik otomatis (ATS)', 'dibuat_oleh' => 'Administrator'],
            ['nama_fasilitas' => 'Lift Penumpang Schindler 1 (Barat)', 'kode' => 'FAS-GD-004', 'kategori' => 'Peralatan', 'lokasi' => 'Lobby Utama Lantai 1-4', 'kondisi' => 'Baik', 'jumlah' => 1, 'status' => 'Aktif', 'keterangan' => 'Kapasitas 1000 kg / 13 orang', 'dibuat_oleh' => 'Administrator'],
            ['nama_fasilitas' => 'Lift Penumpang Schindler 2 (Timur)', 'kode' => 'FAS-GD-005', 'kategori' => 'Peralatan', 'lokasi' => 'Lobby Utama Lantai 1-4', 'kondisi' => 'Baik', 'jumlah' => 1, 'status' => 'Aktif', 'keterangan' => 'Servis berkala bulanan', 'dibuat_oleh' => 'Administrator'],
            ['nama_fasilitas' => 'Aula Pertemuan Torpedo Hall Lantai 3', 'kode' => 'FAS-RU-006', 'kategori' => 'Ruangan', 'lokasi' => 'Lantai 3 Kantor Pusat', 'kondisi' => 'Baik', 'jumlah' => 1, 'status' => 'Aktif', 'keterangan' => 'Kapasitas 150 orang dilengkapi sound system', 'dibuat_oleh' => 'Administrator'],
            ['nama_fasilitas' => 'Pos Keamanan & Portal Barrier Gate', 'kode' => 'FAS-SC-007', 'kategori' => 'Furnitur', 'lokasi' => 'Gerbang Masuk & Keluar Utama', 'kondisi' => 'Baik', 'jumlah' => 2, 'status' => 'Aktif', 'keterangan' => 'Dilengkapi CCTV ANPR pembaca plat nomor', 'dibuat_oleh' => 'Administrator'],
            ['nama_fasilitas' => 'Musholla Al-Barakah Bank Sulteng', 'kode' => 'FAS-UM-008', 'kategori' => 'Ruangan', 'lokasi' => 'Lantai 1 Sayap Barat Belakang', 'kondisi' => 'Baik', 'jumlah' => 1, 'status' => 'Aktif', 'keterangan' => 'Fasilitas ibadah karyawan dan nasabah', 'dibuat_oleh' => 'Administrator'],
        ];
        foreach ($fasilitas as $f) {
            UmFasilitasKantor::create($f);
        }

        // 1.3 Pemeliharaan Gedung (8 Pekerjaan)
        UmPemeliharaanGedung::truncate();
        $pemeliharaanGedung = [
            ['jenis_pekerjaan' => 'AC & Ventilasi', 'lokasi_gedung' => 'Gedung Kantor Pusat', 'jadwal_rencana' => now()->subDays(20)->toDateString(), 'tanggal_realisasi' => now()->subDays(18)->toDateString(), 'pelaksana' => 'CV Palu Mandiri Pendingin', 'biaya' => 4500000, 'status' => 'Selesai', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(19), 'catatan_approval' => 'Disetujui sesuai pagu operasional', 'dibuat_oleh' => 'Staf Umum', 'keterangan' => 'Perawatan rutin & cuci filter AC Cassette Daikin lantai 1-3'],
            ['jenis_pekerjaan' => 'Lift', 'lokasi_gedung' => 'Shaft Lift Kantor Pusat', 'jadwal_rencana' => now()->subDays(12)->toDateString(), 'tanggal_realisasi' => now()->subDays(11)->toDateString(), 'pelaksana' => 'PT Schindler Indonesia', 'biaya' => 8800000, 'status' => 'Selesai', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(11), 'catatan_approval' => 'Disetujui uji kelayakan tahunan', 'dibuat_oleh' => 'Staf Umum', 'keterangan' => 'Uji kelayakan tahunan & pelumasan sling wire rope Lift 1 & 2'],
            ['jenis_pekerjaan' => 'Listrik', 'lokasi_gedung' => 'Ruang Panel Utama', 'jadwal_rencana' => now()->subDays(8)->toDateString(), 'tanggal_realisasi' => now()->subDays(7)->toDateString(), 'pelaksana' => 'CV Celebes Power Solution', 'biaya' => 3200000, 'status' => 'Selesai', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(7), 'catatan_approval' => 'Disetujui pemeliharaan preventif', 'dibuat_oleh' => 'Staf Umum', 'keterangan' => 'Pengecekan panel MDP, kapasitor bank, dan grounding arrester'],
            ['jenis_pekerjaan' => 'Gedung', 'lokasi_gedung' => 'Fasad Luar Gedung Kantor Pusat', 'jadwal_rencana' => now()->subDays(3)->toDateString(), 'tanggal_realisasi' => null, 'pelaksana' => 'CV Karya Gemilang', 'biaya' => 12500000, 'status' => 'Dalam Pengerjaan', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(3), 'catatan_approval' => 'Disetujui pekerjaan renovasi luar', 'dibuat_oleh' => 'Staf Umum', 'keterangan' => 'Pengecatan ulang fasad luar lantai 4 pasca musim hujan'],
            ['jenis_pekerjaan' => 'Air & Sanitasi', 'lokasi_gedung' => 'Ruang Pompa Belakang', 'jadwal_rencana' => now()->subDays(1)->toDateString(), 'tanggal_realisasi' => null, 'pelaksana' => 'Toko Teknik Abadi', 'biaya' => 2800000, 'status' => 'Dijadwalkan', 'maker_id' => $adminId, 'checker_id' => null, 'approval_status' => 'Diajukan', 'approved_at' => null, 'catatan_approval' => null, 'dibuat_oleh' => 'Staf Umum', 'keterangan' => 'Penggantian motor pompa booster air bersih sumur bor 2'],
            ['jenis_pekerjaan' => 'Gedung', 'lokasi_gedung' => 'Dak Atap Lantai 4', 'jadwal_rencana' => now()->addDays(5)->toDateString(), 'tanggal_realisasi' => null, 'pelaksana' => 'CV Karya Gemilang', 'biaya' => 6400000, 'status' => 'Dijadwalkan', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(1), 'catatan_approval' => 'Disetujui perbaikan atap bocor', 'dibuat_oleh' => 'Staf Umum', 'keterangan' => 'Perbaikan atap dak beton bocor di atas ruang arsip lantai 3'],
            ['jenis_pekerjaan' => 'Taman', 'lokasi_gedung' => 'Halaman Depan & Parkiran', 'jadwal_rencana' => now()->addDays(10)->toDateString(), 'tanggal_realisasi' => null, 'pelaksana' => 'CV Palu Lestari', 'biaya' => 1750000, 'status' => 'Dijadwalkan', 'maker_id' => $adminId, 'checker_id' => null, 'approval_status' => 'Diajukan', 'approved_at' => null, 'catatan_approval' => null, 'dibuat_oleh' => 'Staf Umum', 'keterangan' => 'Penataan lanskap taman depan & pemangkasan dahan pohon peneduh'],
            ['jenis_pekerjaan' => 'Lainnya', 'lokasi_gedung' => 'Keliling Gedung Kantor Pusat', 'jadwal_rencana' => now()->subDays(25)->toDateString(), 'tanggal_realisasi' => now()->subDays(24)->toDateString(), 'pelaksana' => 'PT Sumber Proteksi Api', 'biaya' => 3500000, 'status' => 'Selesai', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(24), 'catatan_approval' => 'Disetujui uji proteksi kebakaran', 'dibuat_oleh' => 'Staf Umum', 'keterangan' => 'Pemeriksaan rutin hydrant pilar, box hydrant, dan siamese connection'],
        ];
        foreach ($pemeliharaanGedung as $pg) {
            UmPemeliharaanGedung::create($pg);
        }

        // 1.4 Checklist Kebersihan & Keamanan (8 Checklist)
        UmChecklistKebersihan::truncate();
        $checklists = [
            ['tanggal' => now()->toDateString(), 'area_ruangan' => 'Banking Hall & Lobby Lantai 1', 'shift' => 'Pagi', 'aspek_kebersihan' => 'Bersih & Mengkilap', 'aspek_keamanan' => 'Aman & Terkendali', 'status' => 'Selesai', 'petugas' => 'Hasan (ISS)', 'catatan' => 'Lantai telah dipoles, dispenser hand sanitizer terisi penuh', 'dibuat_oleh' => 'Pengawas CS'],
            ['tanggal' => now()->toDateString(), 'area_ruangan' => 'Toilet Karyawan Lantai 1-4', 'shift' => 'Pagi', 'aspek_kebersihan' => 'Bersih & Kering', 'aspek_keamanan' => 'Aman', 'status' => 'Selesai', 'petugas' => 'Siti (ISS)', 'catatan' => 'Semua kran berfungsi normal, tisu & sabun lengkap', 'dibuat_oleh' => 'Pengawas CS'],
            ['tanggal' => now()->toDateString(), 'area_ruangan' => 'Portal Akses Gerbang Basemen', 'shift' => 'Pagi', 'aspek_kebersihan' => 'Cukup Bersih', 'aspek_keamanan' => 'Aman & Berfungsi Baik', 'status' => 'Selesai', 'petugas' => 'Bripka Rustam / Satpam Firman', 'catatan' => 'Palang pintu otomatis lancar, sensor kartu RFID aktif', 'dibuat_oleh' => 'Komandan Regu'],
            ['tanggal' => now()->subDays(1)->toDateString(), 'area_ruangan' => 'Perimeter Keliling Gedung', 'shift' => 'Malam', 'aspek_kebersihan' => 'Bersih', 'aspek_keamanan' => 'Aman & Pintu Terkunci', 'status' => 'Selesai', 'petugas' => 'Satpam Budi & Satpam Ilham', 'catatan' => 'Kondisi aman, seluruh pintu darurat terkunci gembok ganda', 'dibuat_oleh' => 'Komandan Regu'],
            ['tanggal' => now()->subDays(1)->toDateString(), 'area_ruangan' => 'Ruang Khazanah Kasir Utama', 'shift' => 'Sore', 'aspek_kebersihan' => 'Sangat Bersih', 'aspek_keamanan' => 'Ketat Sesuai SOP', 'status' => 'Selesai', 'petugas' => 'Petugas Khusus Didampingi Staf', 'catatan' => 'Pembersihan debu rutin sesuai SOP protokol keamanan', 'dibuat_oleh' => 'Petugas Kas'],
            ['tanggal' => now()->subDays(2)->toDateString(), 'area_ruangan' => 'Pantry & Ruang Makan Lantai 3', 'shift' => 'Siang', 'aspek_kebersihan' => 'Bersih & Rapi', 'aspek_keamanan' => 'Aman', 'status' => 'Selesai', 'petugas' => 'Rina (ISS)', 'catatan' => 'Kulkas dibersihkan, tempat sampah organik/anorganik dipilah', 'dibuat_oleh' => 'Pengawas CS'],
            ['tanggal' => now()->subDays(3)->toDateString(), 'area_ruangan' => 'Ruang Control Security CCTV', 'shift' => 'Malam', 'aspek_kebersihan' => 'Bersih', 'aspek_keamanan' => '64 Kamera Online', 'status' => 'Selesai', 'petugas' => 'Satpam Irfan', 'catatan' => '64 kamera online, recording penyimpanan 90 hari aktif', 'dibuat_oleh' => 'Komandan Regu'],
            ['tanggal' => now()->subDays(4)->toDateString(), 'area_ruangan' => 'Kaca Luar Lantai 1-2', 'shift' => 'Pagi', 'aspek_kebersihan' => 'Bening & Mengkilap', 'aspek_keamanan' => 'Aman & Memakai APD', 'status' => 'Selesai', 'petugas' => 'Tim Gondola ISS', 'catatan' => 'Selesai tanpa kendala keselamatan', 'dibuat_oleh' => 'Pengawas CS'],
        ];
        foreach ($checklists as $cl) {
            UmChecklistKebersihan::create($cl);
        }

        // 1.5 Catatan K3 & Lingkungan (6 Catatan)
        UmK3Insiden::truncate();
        $insidenK3 = [
            ['tanggal_kejadian' => now()->subDays(4)->toDateString(), 'lokasi' => 'Tangga Darurat Sayap Timur Lt 2', 'jenis_insiden' => 'Nearmiss (Nyaris Celaka)', 'tingkat_keparahan' => 'Rendah', 'kronologi' => 'Tumpahan sisa air galon dispenser menyebabkan lantai licin bagi staf yang melintas', 'tindak_lanjut' => 'Segera dipasang wet floor sign dan dibersihkan oleh cleaning service', 'status' => 'Closed', 'petugas_k3' => 'Officer K3 Bank Sulteng', 'dibuat_oleh' => 'Officer K3'],
            ['tanggal_kejadian' => now()->subDays(14)->toDateString(), 'lokasi' => 'Seluruh Lantai Gedung Kantor Pusat', 'jenis_insiden' => 'Inspeksi Sarana K3', 'tingkat_keparahan' => 'Sedang', 'kronologi' => 'Inspeksi berkala 32 unit tabung APAR Powder & CO2', 'tindak_lanjut' => '2 unit APAR di pantry lt 2 direfill ulang karena jarum di zona merah', 'status' => 'Closed', 'petugas_k3' => 'Officer K3 Bank Sulteng', 'dibuat_oleh' => 'Officer K3'],
            ['tanggal_kejadian' => now()->subDays(28)->toDateString(), 'lokasi' => 'Titik Kumpul Halaman Depan', 'jenis_insiden' => 'Simulasi Tanggap Darurat', 'tingkat_keparahan' => 'Rendah', 'kronologi' => 'Simulasi mitigasi tanggap darurat gempa bumi dan kebakaran terpadu', 'tindak_lanjut' => 'Evaluasi waktu evakuasi rata-rata 3 menit 45 detik (memenuhi standar)', 'status' => 'Closed', 'petugas_k3' => 'Officer K3 Bank Sulteng', 'dibuat_oleh' => 'Officer K3'],
            ['tanggal_kejadian' => now()->subDays(35)->toDateString(), 'lokasi' => 'Pantry Lantai 3', 'jenis_insiden' => 'Potensi Bahaya Kelistrikan', 'tingkat_keparahan' => 'Sedang', 'kronologi' => 'Korsleting pada stop kontak microwave yang kelebihan beban', 'tindak_lanjut' => 'Penggantian colokan kabel dan pemisahan jalur MCB khusus alat pemanas', 'status' => 'Closed', 'petugas_k3' => 'Officer K3 Bank Sulteng', 'dibuat_oleh' => 'Officer K3'],
            ['tanggal_kejadian' => now()->subDays(60)->toDateString(), 'lokasi' => 'Bak Penampungan Limbah Domestik', 'jenis_insiden' => 'Insiden Lingkungan', 'tingkat_keparahan' => 'Sedang', 'kronologi' => 'Saluran grease trap penyaring minyak dari pantry tersumbat', 'tindak_lanjut' => 'Penyedotan dan pengurasan bak kontrol oleh vendor limbah berizin', 'status' => 'Closed', 'petugas_k3' => 'Officer K3 Bank Sulteng', 'dibuat_oleh' => 'Officer K3'],
            ['tanggal_kejadian' => now()->subDays(5)->toDateString(), 'lokasi' => 'Ruang Genset Belakang', 'jenis_insiden' => 'Pemeriksaan APD Kebisingan', 'tingkat_keparahan' => 'Rendah', 'kronologi' => 'Uji kebisingan ruang mesin genset saat running test mingguan', 'tindak_lanjut' => 'Penambahan earmuff pelindung telinga gantung di pintu masuk genset', 'status' => 'Closed', 'petugas_k3' => 'Officer K3 Bank Sulteng', 'dibuat_oleh' => 'Officer K3'],
        ];
        foreach ($insidenK3 as $k3) {
            UmK3Insiden::create($k3);
        }

        // 1.6 Surat Masuk (8 Dokumen)
        SrSuratMasuk::truncate();
        $suratMasuk = [
            ['nomor_agenda' => 201, 'no_surat' => 'S-142/KO.0601/2026', 'pengirim' => 'Otoritas Jasa Keuangan (OJK) Provinsi Sulawesi Tengah', 'perihal' => 'Penyampaian Evaluasi Profil Risiko Kepatuhan Perbankan Daerah Q1', 'tanggal' => now()->subDays(3)->toDateString(), 'penerima' => 'Direktur Kepatuhan & Manajemen Risiko', 'lokasi_arsip' => 'Bantex Sekper No. 04', 'masa_retensi' => 10, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Sekretariat'],
            ['nomor_agenda' => 202, 'no_surat' => '28/04/PLU/Srt/B', 'pengirim' => 'Kantor Perwakilan Bank Indonesia Sulawesi Tengah', 'perihal' => 'Koordinasi Penyelenggaraan Kas Keliling Idul Fitri 1447 H', 'tanggal' => now()->subDays(6)->toDateString(), 'penerima' => 'Divisi Operasional & Jaringan', 'lokasi_arsip' => 'Bantex Operasional No. 08', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Sekretariat'],
            ['nomor_agenda' => 203, 'no_surat' => '045/Bapenda/I/2026', 'pengirim' => 'Badan Pendapatan Daerah Provinsi Sulawesi Tengah', 'perihal' => 'Rekonsiliasi Penerimaan Pajak Daerah via e-Samsat & QRIS Bank Sulteng', 'tanggal' => now()->subDays(9)->toDateString(), 'penerima' => 'Divisi TI & Hubungan Kelembagaan', 'lokasi_arsip' => 'Bantex Kelembagaan No. 02', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Sekretariat'],
            ['nomor_agenda' => 204, 'no_surat' => '112/ASURANSI-JK/II/2026', 'pengirim' => 'PT Jamkrida Sulawesi Tengah', 'perihal' => 'Perpanjangan Kerjasama Penjaminan Kredit Modal Kerja & Konstruksi', 'tanggal' => now()->subDays(12)->toDateString(), 'penerima' => 'Divisi Bisnis & Pemasaran', 'lokasi_arsip' => 'Bantex Kerjasama No. 11', 'masa_retensi' => 10, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Sekretariat'],
            ['nomor_agenda' => 205, 'no_surat' => '880/TELKOM/PLU/2026', 'pengirim' => 'PT Telkom Indonesia Wilayah Telekomunikasi Sulteng', 'perihal' => 'Pemberitahuan Pemeliharaan Jaringan Metro-E & Fiber Optic Cabang Tolitoli', 'tanggal' => now()->subDays(15)->toDateString(), 'penerima' => 'Divisi TI & Jaringan', 'lokasi_arsip' => 'Bantex TI No. 15', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Sekretariat'],
            ['nomor_agenda' => 206, 'no_surat' => '301/PLN-UP3/PLU/2026', 'pengirim' => 'PT PLN (Persero) UP3 Palu', 'perihal' => 'Penyesuaian Tarif Daya Bisnis Premium & Keandalan Suplai 2 Penyulang', 'tanggal' => now()->subDays(18)->toDateString(), 'penerima' => 'Divisi Umum & Rumah Tangga', 'lokasi_arsip' => 'Bantex Umum No. 05', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Sekretariat'],
            ['nomor_agenda' => 207, 'no_surat' => '560/DISNAKER-ST/2026', 'pengirim' => 'Dinas Tenaga Kerja & Transmigrasi Provinsi Sulteng', 'perihal' => 'Verifikasi Kepatuhan Norma K3 & Laporan P2K3 Semester II', 'tanggal' => now()->subDays(22)->toDateString(), 'penerima' => 'Divisi SDM & Divisi Umum', 'lokasi_arsip' => 'Bantex SDM No. 19', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Sekretariat'],
            ['nomor_agenda' => 208, 'no_surat' => '77/BPKP-SULTENG/2026', 'pengirim' => 'Perwakilan BPKP Provinsi Sulawesi Tengah', 'perihal' => 'Surat Tugas Evaluasi Tata Kelola Good Corporate Governance (GCG)', 'tanggal' => now()->subDays(26)->toDateString(), 'penerima' => 'Satuan Kerja Audit Intern (SKAI)', 'lokasi_arsip' => 'Bantex GCG No. 01', 'masa_retensi' => 10, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Sekretariat'],
        ];
        foreach ($suratMasuk as $sm) {
            SrSuratMasuk::create($sm);
        }

        // 1.7 Surat Keluar (8 Dokumen)
        SrSuratKeluar::truncate();
        $suratKeluar = [
            ['nomor_agenda' => 301, 'no_surat' => '012/DIR-BS/SRT/III/2026', 'pengirim' => 'Direktur Utama PT Bank Sulteng', 'perihal' => 'Tanggapan dan Tindak Lanjut Hasil Pengawasan Khusus OJK Q1', 'tanggal' => now()->subDays(2)->toDateString(), 'penerima' => 'Kepala OJK Provinsi Sulawesi Tengah', 'lokasi_arsip' => 'Bantex Sekper No. 04', 'masa_retensi' => 10, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Sekretariat Perusahaan'],
            ['nomor_agenda' => 302, 'no_surat' => '045/DIR-OPR/SRT/III/2026', 'pengirim' => 'Direktur Operasional PT Bank Sulteng', 'perihal' => 'Konfirmasi Kesiapan Partisipasi Penukaran Uang Bersama BI Sulteng', 'tanggal' => now()->subDays(5)->toDateString(), 'penerima' => 'Kepala Perwakilan BI Provinsi Sulawesi Tengah', 'lokasi_arsip' => 'Bantex Operasional No. 08', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Divisi Operasional'],
            ['nomor_agenda' => 303, 'no_surat' => '078/UM-BS/SRT/II/2026', 'pengirim' => 'Pemimpin Divisi Umum Bank Sulteng', 'perihal' => 'Permohonan Penerbitan Rekomendasi Laik Fungsi (SLF) Kantor Cabang Luwuk', 'tanggal' => now()->subDays(8)->toDateString(), 'penerima' => 'Dinas PUPR Kabupaten Banggai', 'lokasi_arsip' => 'Bantex Umum No. 09', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Bagian Umum'],
            ['nomor_agenda' => 304, 'no_surat' => '102/DIR-BS/SRT/II/2026', 'pengirim' => 'Direksi PT Bank Sulteng', 'perihal' => 'Undangan Rapat Umum Pemegang Saham (RUPS) Tahunan Tahun Buku 2025', 'tanggal' => now()->subDays(14)->toDateString(), 'penerima' => 'Gubernur & Bupati/Walikota Selaku Pemegang Saham', 'lokasi_arsip' => 'Bantex RUPS No. 01', 'masa_retensi' => 30, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Sekretariat Perusahaan'],
            ['nomor_agenda' => 305, 'no_surat' => '133/UM-BS/SRT/II/2026', 'pengirim' => 'Pemimpin Divisi Umum Bank Sulteng', 'perihal' => 'Konfirmasi Perpanjangan Polis Asuransi Cash in Transit (CIT) dan Cash in Safe (CIS)', 'tanggal' => now()->subDays(17)->toDateString(), 'penerima' => 'PT Asuransi Bangun Askrida Cabang Palu', 'lokasi_arsip' => 'Bantex Asuransi No. 03', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Bagian Aset & Logistik'],
            ['nomor_agenda' => 306, 'no_surat' => '165/TI-BS/SRT/II/2026', 'pengirim' => 'Pemimpin Divisi TI Bank Sulteng', 'perihal' => 'Permohonan Uji Penetrasi Sistem Mobile Banking & Keamanan Siber', 'tanggal' => now()->subDays(21)->toDateString(), 'penerima' => 'Badan Siber dan Sandi Negara (BSSN)', 'lokasi_arsip' => 'Bantex TI No. 12', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Divisi TI'],
            ['nomor_agenda' => 307, 'no_surat' => '190/SDM-BS/SRT/I/2026', 'pengirim' => 'Pemimpin Divisi SDM Bank Sulteng', 'perihal' => 'Penyampaian Laporan Wajib Ketenagakerjaan Perusahaan (WLKP) Online', 'tanggal' => now()->subDays(25)->toDateString(), 'penerima' => 'Dinas Tenaga Kerja Kota Palu', 'lokasi_arsip' => 'Bantex SDM No. 07', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Divisi SDM'],
            ['nomor_agenda' => 308, 'no_surat' => '210/UM-BS/SRT/I/2026', 'pengirim' => 'Pemimpin Divisi Umum Bank Sulteng', 'perihal' => 'Surat Permohonan Pengawalan Personil Satuan Brimob untuk Distribusi Kas Luwuk', 'tanggal' => now()->subDays(29)->toDateString(), 'penerima' => 'Komandan Satuan Brimob Polda Sulawesi Tengah', 'lokasi_arsip' => 'Bantex Pengamanan No. 02', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Bagian Logistik'],
        ];
        foreach ($suratKeluar as $sk) {
            SrSuratKeluar::create($sk);
        }

        // 1.8 Memo Masuk (8 Memo)
        SrMemoMasuk::truncate();
        $memoMasuk = [
            ['nomor_agenda' => 401, 'no_surat' => 'MEMO/KC-LWK/012/III/2026', 'pengirim' => 'Pemimpin Kantor Cabang Luwuk', 'perihal' => 'Permohonan Penggantian 4 Unit Kursi Kerja CS & 1 Unit Brankas Rusak', 'tanggal' => now()->subDays(2)->toDateString(), 'penerima' => 'Pemimpin Divisi Umum', 'lokasi_arsip' => 'Folder Memo Cabang 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'KC Luwuk'],
            ['nomor_agenda' => 402, 'no_surat' => 'MEMO/KC-TLI/025/III/2026', 'pengirim' => 'Pemimpin Kantor Cabang Tolitoli', 'perihal' => 'Usulan Penambahan Daya Listrik PLN dari 23 kVA ke 33 kVA', 'tanggal' => now()->subDays(4)->toDateString(), 'penerima' => 'Pemimpin Divisi Umum', 'lokasi_arsip' => 'Folder Memo Cabang 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'KC Tolitoli'],
            ['nomor_agenda' => 403, 'no_surat' => 'MEMO/DIV-TI/044/II/2026', 'pengirim' => 'Pemimpin Divisi Teknologi Informasi', 'perihal' => 'Permohonan Pengadaan Lisensi Database Oracle Backup Server', 'tanggal' => now()->subDays(8)->toDateString(), 'penerima' => 'Pemimpin Divisi Umum (Bagian Pengadaan)', 'lokasi_arsip' => 'Folder Memo TI 2026', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Divisi TI'],
            ['nomor_agenda' => 404, 'no_surat' => 'MEMO/KC-PSO/018/II/2026', 'pengirim' => 'Pemimpin Kantor Cabang Poso', 'perihal' => 'Pengajuan Penggantian Filter dan Freon AC Ruang Khazanah Kas', 'tanggal' => now()->subDays(11)->toDateString(), 'penerima' => 'Bagian Pemeliharaan Aset', 'lokasi_arsip' => 'Folder Memo Cabang 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'KC Poso'],
            ['nomor_agenda' => 405, 'no_surat' => 'MEMO/DIV-OPR/031/II/2026', 'pengirim' => 'Pemimpin Divisi Operasional', 'perihal' => 'Permohonan Pengadaan 20.000 Lembar Formulir Pembukaan Rekening Tabungan', 'tanggal' => now()->subDays(15)->toDateString(), 'penerima' => 'Bagian Logistik & ATK', 'lokasi_arsip' => 'Folder Memo Logistik 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Divisi Operasional'],
            ['nomor_agenda' => 406, 'no_surat' => 'MEMO/KCP-PRG/007/II/2026', 'pengirim' => 'Pemimpin KCP Parigi', 'perihal' => 'Permohonan Dispensasi Pemakaian Mobil Operasional untuk Layanan Kas Luar Kota', 'tanggal' => now()->subDays(19)->toDateString(), 'penerima' => 'Bagian Umum & RT', 'lokasi_arsip' => 'Folder Memo Cabang 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'KCP Parigi'],
            ['nomor_agenda' => 407, 'no_surat' => 'MEMO/DIV-KRD/015/I/2026', 'pengirim' => 'Pemimpin Divisi Kredit & Komersial', 'perihal' => 'Permintaan Fasilitas 2 Unit Laptop untuk Analis Kredit Lapangan', 'tanggal' => now()->subDays(24)->toDateString(), 'penerima' => 'Bagian Aset & Inventaris', 'lokasi_arsip' => 'Folder Memo Aset 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Divisi Kredit'],
            ['nomor_agenda' => 408, 'no_surat' => 'MEMO/SKAI/005/I/2026', 'pengirim' => 'Kepala Satuan Kerja Audit Intern (SKAI)', 'perihal' => 'Permintaan Akses Log Audit Fisik Aset dan Dokumen PKS Periode 2025', 'tanggal' => now()->subDays(28)->toDateString(), 'penerima' => 'Pemimpin Divisi Umum', 'lokasi_arsip' => 'Folder Memo SKAI 2026', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'SKAI'],
        ];
        foreach ($memoMasuk as $mm) {
            SrMemoMasuk::create($mm);
        }

        // 1.9 Memo Keluar (8 Memo)
        SrMemoKeluar::truncate();
        $memoKeluar = [
            ['nomor_agenda' => 501, 'no_surat' => 'MEMO-INT/UM/089/III/2026', 'pengirim' => 'Pemimpin Divisi Umum', 'perihal' => 'Instruksi Penghematan Energi Listrik dan Disiplin Penggunaan AC Kantor', 'tanggal' => now()->subDays(1)->toDateString(), 'penerima' => 'Seluruh Pemimpin Divisi & Kantor Cabang', 'lokasi_arsip' => 'Folder Edaran Umum 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Bagian Umum & RT'],
            ['nomor_agenda' => 502, 'no_surat' => 'MEMO-INT/UM/075/III/2026', 'pengirim' => 'Pemimpin Divisi Umum', 'perihal' => 'Jadwal Pelaksanaan Sensus & Rekonsiliasi Fisik Aset Semester I 2026', 'tanggal' => now()->subDays(4)->toDateString(), 'penerima' => 'Seluruh Unit Kerja & Kantor Cabang', 'lokasi_arsip' => 'Folder Sensus Aset 2026', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Bagian Aset'],
            ['nomor_agenda' => 503, 'no_surat' => 'MEMO-INT/UM/062/II/2026', 'pengirim' => 'Pemimpin Divisi Umum', 'perihal' => 'Pemberitahuan Pelaksanaan General Cleaning Kaca Luar Gedung Kantor Pusat', 'tanggal' => now()->subDays(7)->toDateString(), 'penerima' => 'Seluruh Karyawan Kantor Pusat', 'lokasi_arsip' => 'Folder Kebersihan 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Bagian Umum & RT'],
            ['nomor_agenda' => 504, 'no_surat' => 'MEMO-INT/UM/051/II/2026', 'pengirim' => 'Pemimpin Divisi Umum', 'perihal' => 'Persetujuan Penggantian Brankas Kasir Teller KC Luwuk', 'tanggal' => now()->subDays(12)->toDateString(), 'penerima' => 'Pemimpin KC Luwuk', 'lokasi_arsip' => 'Folder Memo Cabang 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Bagian Logistik'],
            ['nomor_agenda' => 505, 'no_surat' => 'MEMO-INT/UM/038/II/2026', 'pengirim' => 'Pemimpin Divisi Umum', 'perihal' => 'Sosialisasi Alur Permohonan Tiket Layanan Helpdesk & Mutasi Aset Digital', 'tanggal' => now()->subDays(16)->toDateString(), 'penerima' => 'Seluruh Karyawan Bank Sulteng', 'lokasi_arsip' => 'Folder Helpdesk 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Admin Portum'],
            ['nomor_agenda' => 506, 'no_surat' => 'MEMO-INT/UM/024/I/2026', 'pengirim' => 'Pemimpin Divisi Umum', 'perihal' => 'Pemberitahuan Pemeliharaan Rutin Trafo dan Genset Kantor Pusat (Uji Beban)', 'tanggal' => now()->subDays(20)->toDateString(), 'penerima' => 'Seluruh Divisi Kantor Pusat', 'lokasi_arsip' => 'Folder Utilitas 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Bagian Pemeliharaan'],
            ['nomor_agenda' => 507, 'no_surat' => 'MEMO-INT/UM/015/I/2026', 'pengirim' => 'Pemimpin Divisi Umum', 'perihal' => 'Penertiban Area Parkir Karyawan & Penempatan Kendaraan Operasional', 'tanggal' => now()->subDays(25)->toDateString(), 'penerima' => 'Seluruh Karyawan Kantor Pusat', 'lokasi_arsip' => 'Folder Keamanan 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Bagian Umum & RT'],
            ['nomor_agenda' => 508, 'no_surat' => 'MEMO-INT/UM/004/I/2026', 'pengirim' => 'Pemimpin Divisi Umum', 'perihal' => 'Instruksi Pengembalian Inventaris Laptop untuk Pembaruan Lisensi Endpoint', 'tanggal' => now()->subDays(30)->toDateString(), 'penerima' => 'Divisi Terkait', 'lokasi_arsip' => 'Folder Aset 2026', 'masa_retensi' => 3, 'status_arsip' => 'Aktif', 'dibuat_oleh' => 'Bagian Aset'],
        ];
        foreach ($memoKeluar as $mk) {
            SrMemoKeluar::create($mk);
        }

        // 1.10 Master Arsip Dokumen (8 Arsip)
        DkArsipDokumen::truncate();
        $arsipDokumen = [
            ['kode_arsip' => 'ARS-CORP-2024-001', 'judul' => 'Akta Berita Acara Rapat Umum Pemegang Saham (RUPS) Tahunan Tahun Buku 2024', 'jenis' => 'Akta Notaris', 'kategori' => 'Korporasi', 'tanggal_dokumen' => '2025-04-18', 'lokasi_arsip' => 'Ruang Khazanah Arsip Lemari A-01', 'masa_retensi' => 30, 'status_arsip' => 'Aktif', 'keterangan' => 'Dokumen fisik otentik Notaris Muhammad Ridwan, S.H.', 'dibuat_oleh' => 'Arsiparis'],
            ['kode_arsip' => 'ARS-FIN-2024-002', 'judul' => 'Laporan Keuangan PT Bank Sulteng Audited oleh KAP Tanudiredja Wibisana & Rekan (PwC)', 'jenis' => 'Laporan Audit', 'kategori' => 'Keuangan', 'tanggal_dokumen' => '2025-03-25', 'lokasi_arsip' => 'Ruang Khazanah Arsip Lemari B-03', 'masa_retensi' => 10, 'status_arsip' => 'Aktif', 'keterangan' => 'Opini Wajar Tanpa Pengecualian (WTP)', 'dibuat_oleh' => 'Arsiparis'],
            ['kode_arsip' => 'ARS-PKS-2023-003', 'judul' => 'Perjanjian Kerjasama Induk Co-Branding Kartu Debit Bank Sulteng dengan GPN & Mastercard', 'jenis' => 'PKS', 'kategori' => 'Bisnis', 'tanggal_dokumen' => '2023-07-10', 'lokasi_arsip' => 'Ruang Khazanah Arsip Lemari C-02', 'masa_retensi' => 15, 'status_arsip' => 'Aktif', 'keterangan' => 'Perjanjian berlaku 5 tahun', 'dibuat_oleh' => 'Arsiparis'],
            ['kode_arsip' => 'ARS-SDM-2025-004', 'judul' => 'Perjanjian Kerja Bersama (PKB) Periode 2025-2027 antara Direksi dan SP Bank Sulteng', 'jenis' => 'PKB', 'kategori' => 'Ketenagakerjaan', 'tanggal_dokumen' => '2025-01-05', 'lokasi_arsip' => 'Ruang Khazanah Arsip Lemari D-01', 'masa_retensi' => 5, 'status_arsip' => 'Aktif', 'keterangan' => 'Disahkan oleh Kemenaker RI', 'dibuat_oleh' => 'Arsiparis'],
            ['kode_arsip' => 'ARS-ASET-2022-005', 'judul' => 'Sertifikat Hak Guna Bangunan (HGB) No. 0045/Palu Barat Tanah Gedung Kantor Pusat', 'jenis' => 'Sertifikat Tanah', 'kategori' => 'Legalitas Aset', 'tanggal_dokumen' => '2022-11-20', 'lokasi_arsip' => 'Safe Deposit Box Utama Khasanah Bank', 'masa_retensi' => 50, 'status_arsip' => 'Aktif', 'keterangan' => 'Sertifikat Asli BPN Kota Palu masa berlaku s/d 2045', 'dibuat_oleh' => 'Arsiparis'],
            ['kode_arsip' => 'ARS-TI-2024-006', 'judul' => 'Master Dokumen Disaster Recovery Plan (DRP) & Business Continuity Plan (BCP) 2024', 'jenis' => 'Pedoman Teknis', 'kategori' => 'Teknologi Informasi', 'tanggal_dokumen' => '2024-09-14', 'lokasi_arsip' => 'Ruang Khazanah Arsip Lemari TI-01', 'masa_retensi' => 10, 'status_arsip' => 'Aktif', 'keterangan' => 'Pedoman penanganan kontinjensi bencana daerah', 'dibuat_oleh' => 'Arsiparis'],
            ['kode_arsip' => 'ARS-PKS-2024-007', 'judul' => 'Kontrak SPK Pengadaan Core Banking System Integration Upgrade Versi 12.4', 'jenis' => 'SPK / Kontrak', 'kategori' => 'Pengadaan', 'tanggal_dokumen' => '2024-05-19', 'lokasi_arsip' => 'Ruang Khazanah Arsip Lemari E-04', 'masa_retensi' => 10, 'status_arsip' => 'Aktif', 'keterangan' => 'Mitra PT Multipolar Technology', 'dibuat_oleh' => 'Arsiparis'],
            ['kode_arsip' => 'ARS-SKAI-2024-008', 'judul' => 'Laporan Hasil Pemeriksaan Audit Komprehensif Seluruh Kantor Cabang Tahun 2024', 'jenis' => 'LHA SKAI', 'kategori' => 'Pengawasan', 'tanggal_dokumen' => '2025-01-30', 'lokasi_arsip' => 'Ruang Khazanah Arsip Lemari SKAI-02', 'masa_retensi' => 7, 'status_arsip' => 'Aktif', 'keterangan' => 'Sifat Rahasia untuk Dewan Komisaris & Direksi', 'dibuat_oleh' => 'Arsiparis'],
        ];
        foreach ($arsipDokumen as $ad) {
            DkArsipDokumen::create($ad);
        }

        // 1.11 Dokumen Legalitas (8 Dokumen)
        DkDokumenLegalitas::truncate();
        $legalitas = [
            ['nama_dokumen' => 'Izin Usaha Bank Umum Konvensional Bank Sulteng', 'jenis' => 'Izin Operasional', 'no_dokumen' => 'Kep-15/D.03/1999', 'penerbit' => 'Otoritas Jasa Keuangan (d/h Bank Indonesia)', 'tanggal_terbit' => '1999-04-01', 'tanggal_berlaku' => null, 'status' => 'Aktif', 'keterangan' => 'Berlaku selamanya selama bank beroperasi'],
            ['nama_dokumen' => 'Nomor Induk Berusaha (NIB) Berbasis Risiko', 'jenis' => 'Legalitas Berusaha', 'no_dokumen' => '9120004510928', 'penerbit' => 'Kementerian Investasi / BKPM RI (OSS RBA)', 'tanggal_terbit' => '2021-08-15', 'tanggal_berlaku' => null, 'status' => 'Aktif', 'keterangan' => 'Sektor Jasa Keuangan Perbankan'],
            ['nama_dokumen' => 'Sertifikat Laik Fungsi (SLF) Gedung Kantor Pusat Palu', 'jenis' => 'Kelaikan Gedung', 'no_dokumen' => 'SLF-7271-2023-004', 'penerbit' => 'Dinas Penanaman Modal & PTSP Kota Palu', 'tanggal_terbit' => '2023-05-10', 'tanggal_berlaku' => '2028-05-10', 'status' => 'Aktif', 'keterangan' => 'Wajib diperpanjang 5 tahun sekali'],
            ['nama_dokumen' => 'Persetujuan Kesesuaian Kegiatan Pemanfaatan Ruang (PKKPR)', 'jenis' => 'Tata Ruang', 'no_dokumen' => 'PKKPR/72/2022/88', 'penerbit' => 'Kementerian ATR / BPN', 'tanggal_terbit' => '2022-03-12', 'tanggal_berlaku' => null, 'status' => 'Aktif', 'keterangan' => 'Zona perkantoran dan perdagangan komersial'],
            ['nama_dokumen' => 'Polis Asuransi Bankers Blanket Bond (BBB) & Comprehensive Crime', 'jenis' => 'Asuransi Perbankan', 'no_dokumen' => 'POLIS-BBB-2025-001', 'penerbit' => 'PT Asuransi Bangun Askrida', 'tanggal_terbit' => '2025-01-01', 'tanggal_berlaku' => '2026-01-01', 'status' => 'Aktif', 'keterangan' => 'Menjamin risiko fraud, pemalsuan, perampokan kasir'],
            ['nama_dokumen' => 'Izin Penyelenggaraan QRIS & Mobile Banking', 'jenis' => 'Sistem Pembayaran', 'no_dokumen' => '23/110/DKSP/Srt/B', 'penerbit' => 'Bank Indonesia Departemen Kebijakan Sistem Pembayaran', 'tanggal_terbit' => '2021-12-20', 'tanggal_berlaku' => null, 'status' => 'Aktif', 'keterangan' => 'Penyelenggara Jasa Pembayaran (PJP) Kategori 1'],
            ['nama_dokumen' => 'Surat Pengukuhan Pengusaha Kena Pajak (SPPKP)', 'jenis' => 'Perpajakan', 'no_dokumen' => 'S-44PKP/WPJ.15/KP.0103/2012', 'penerbit' => 'KPP Pratama Palu', 'tanggal_terbit' => '2012-02-14', 'tanggal_berlaku' => null, 'status' => 'Aktif', 'keterangan' => 'NPWP 01.123.456.7-831.000'],
            ['nama_dokumen' => 'Sertifikat Kepesertaan Lembaga Penjamin Simpanan (LPS)', 'jenis' => 'Penjaminan Simpanan', 'no_dokumen' => 'LPS/PESERTA/2005/019', 'penerbit' => 'Lembaga Penjamin Simpanan (LPS)', 'tanggal_terbit' => '2005-09-22', 'tanggal_berlaku' => null, 'status' => 'Aktif', 'keterangan' => 'Penjaminan simpanan nasabah sampai Rp 2 Miliar'],
        ];
        foreach ($legalitas as $leg) {
            DkDokumenLegalitas::create($leg);
        }

        // ═════════════════════════════════════════════════════════════════════════
        // BAGIAN 2: ASET / INVENTARIS & LOGISTIK
        // ═════════════════════════════════════════════════════════════════════════

        // 2.1 Aset Tambahan
        $asetBaru = [
            ['kode_aset' => 'AST-TI-2025-007', 'nama_aset' => 'Core Switch Cisco Catalyst 9300 48-Port PoE+', 'kategori' => 'Teknologi Informasi', 'lokasi' => 'Rack Server Data Center Lt 2', 'tanggal_perolehan' => '2025-01-10', 'nilai_perolehan' => 175000000, 'umur_ekonomis' => 60, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Divisi TI', 'keterangan' => 'Switch tulang punggung jaringan'],
            ['kode_aset' => 'AST-OP-2025-008', 'nama_aset' => 'Mesin Sortir & Hitung Uang Kertas Kisan Newton 3', 'kategori' => 'Peralatan Operasional', 'lokasi' => 'Khazanah Utama Kantor Pusat', 'tanggal_perolehan' => '2025-02-15', 'nilai_perolehan' => 145000000, 'umur_ekonomis' => 48, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Bagian Kasir Kas', 'keterangan' => 'Sortir uang layak edar'],
            ['kode_aset' => 'AST-SC-2024-009', 'nama_aset' => 'CCTV NVR Hikvision 64 Channel + 48 IP Camera 4K', 'kategori' => 'Peralatan Keamanan', 'lokasi' => 'Ruang Control Security & Perimeter', 'tanggal_perolehan' => '2024-11-05', 'nilai_perolehan' => 210000000, 'umur_ekonomis' => 48, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Bagian Keamanan', 'keterangan' => 'Pengawasan perimeter terpadu'],
            ['kode_aset' => 'AST-FN-2024-010', 'nama_aset' => 'Workstation Modular CS Ergonomis (8 Set)', 'kategori' => 'Furnitur', 'lokasi' => 'Banking Hall Front Office', 'tanggal_perolehan' => '2024-07-22', 'nilai_perolehan' => 68000000, 'umur_ekonomis' => 60, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Bagian Umum & RT', 'keterangan' => 'Partisi dan meja kerja frontliner'],
            ['kode_aset' => 'AST-TI-2025-011', 'nama_aset' => 'Mobile Workstation Lenovo ThinkPad P16s Gen 2', 'kategori' => 'Teknologi Informasi', 'lokasi' => 'Ruang SKAI Lantai 3', 'tanggal_perolehan' => '2025-03-01', 'nilai_perolehan' => 38000000, 'umur_ekonomis' => 36, 'kondisi' => 'Baik', 'penanggung_jawab' => 'SKAI', 'keterangan' => 'Unit audit investigasi'],
            ['kode_aset' => 'AST-GD-2023-012', 'nama_aset' => 'Chiller AC Sentral Daikin 25 PK Sayap Barat', 'kategori' => 'Gedung & Utilitas', 'lokasi' => 'Rooftop Lantai 4', 'tanggal_perolehan' => '2023-04-18', 'nilai_perolehan' => 285000000, 'umur_ekonomis' => 120, 'kondisi' => 'Baik', 'penanggung_jawab' => 'Bagian Pemeliharaan', 'keterangan' => 'Pendingin sentral sayap barat'],
        ];
        foreach ($asetBaru as $ab) {
            AsAset::updateOrCreate(['kode_aset' => $ab['kode_aset']], $ab);
        }

        // 2.2 Riwayat Pergerakan Aset (8 Riwayat)
        $firstAset = AsAset::first();
        $firstAsetId = $firstAset?->id ?? 1;
        AsAsetHistory::truncate();
        $asetHistories = [
            ['aset_id' => $firstAsetId, 'user_id' => $adminId, 'field_changed' => 'lokasi', 'old_value' => 'Gudang Logistik Basemen', 'new_value' => 'Data Center Lantai 2', 'keterangan' => 'Instalasi server baru selesai, diaktifkan di rak A-02', 'changed_at' => now()->subDays(60)],
            ['aset_id' => $firstAsetId, 'user_id' => $adminId, 'field_changed' => 'kondisi', 'old_value' => 'Perlu Servis', 'new_value' => 'Baik', 'keterangan' => 'Penggantian power supply redundant unit', 'changed_at' => now()->subDays(45)],
            ['aset_id' => $firstAsetId, 'user_id' => $adminId, 'field_changed' => 'penanggung_jawab', 'old_value' => 'Staf TI Jaringan', 'new_value' => 'Database Administrator', 'keterangan' => 'Alih kelola operasional database utama', 'changed_at' => now()->subDays(30)],
            ['aset_id' => $firstAsetId, 'user_id' => $adminId, 'field_changed' => 'lokasi', 'old_value' => 'Ruang Rapat Direksi', 'new_value' => 'Aula Torpedo Lantai 3', 'keterangan' => 'Relokasi smart display interaktif untuk rapat kerja', 'changed_at' => now()->subDays(20)],
            ['aset_id' => $firstAsetId, 'user_id' => $adminId, 'field_changed' => 'penanggung_jawab', 'old_value' => 'Staf CS Teller', 'new_value' => 'Staf Customer Service KC Palu', 'keterangan' => 'Penyerahan unit PC All-in-One pengganti', 'changed_at' => now()->subDays(15)],
            ['aset_id' => $firstAsetId, 'user_id' => $adminId, 'field_changed' => 'kondisi', 'old_value' => 'Baik', 'new_value' => 'Perlu Pemeliharaan', 'keterangan' => 'Ditemukan getaran motor fan pendingin saat inspeksi rutin', 'changed_at' => now()->subDays(8)],
            ['aset_id' => $firstAsetId, 'user_id' => $adminId, 'field_changed' => 'lokasi', 'old_value' => 'KC Palu Barat', 'new_value' => 'KC Luwuk', 'keterangan' => 'Mutasi mesin hitung uang Glory antar kantor cabang', 'changed_at' => now()->subDays(5)],
            ['aset_id' => $firstAsetId, 'user_id' => $adminId, 'field_changed' => 'penanggung_jawab', 'old_value' => 'Regu Pengamanan 1', 'new_value' => 'Regu Pengamanan 2', 'keterangan' => 'Serah terima inventaris handy talkie Motorola', 'changed_at' => now()->subDays(2)],
        ];
        foreach ($asetHistories as $ah) {
            AsAsetHistory::create($ah);
        }

        // 2.3 Penghapusan Aset / Disposal (6 Record)
        AsDisposalAset::truncate();
        $disposals = [
            ['no_disposal' => 'DSP-2026-001', 'aset_id' => $firstAsetId, 'tanggal_pengajuan' => now()->subDays(40)->toDateString(), 'alasan_penghapusan' => 'Mobil Dinas Isuzu Panther 2011 rusak berat dan biaya perawatan melebihi nilai ekonomis', 'metode' => 'Dijual (Lelang)', 'nilai_buku_terakhir' => 15000000, 'status' => 'Selesai', 'approval_status' => 'Disetujui', 'maker_id' => $adminId, 'checker_id' => $adminId, 'keterangan' => 'Terjual via lelang KPKNL Palu sebesar Rp 28.500.000'],
            ['no_disposal' => 'DSP-2026-002', 'aset_id' => $firstAsetId, 'tanggal_pengajuan' => now()->subDays(25)->toDateString(), 'alasan_penghapusan' => 'Pemusnahan 15 unit Harddisk Server SAS rusak fisik untuk keamanan data nasabah', 'metode' => 'Dimusnahkan', 'nilai_buku_terakhir' => 0, 'status' => 'Selesai', 'approval_status' => 'Disetujui', 'maker_id' => $adminId, 'checker_id' => $adminId, 'keterangan' => 'Dihancurkan dengan alat degausser disaksikan SKAI & Kepatuhan'],
            ['no_disposal' => 'DSP-2026-003', 'aset_id' => $firstAsetId, 'tanggal_pengajuan' => now()->subDays(15)->toDateString(), 'alasan_penghapusan' => 'Hibah 10 unit PC Core i3 eks frontliner yang telah diganti ke SMK Negeri Binaan', 'metode' => 'Dihibahkan', 'nilai_buku_terakhir' => 5000000, 'status' => 'Disetujui', 'approval_status' => 'Disetujui', 'maker_id' => $adminId, 'checker_id' => $adminId, 'keterangan' => 'CSR Pendidikan & Literasi Keuangan Bank Sulteng'],
            ['no_disposal' => 'DSP-2026-004', 'aset_id' => $firstAsetId, 'tanggal_pengajuan' => now()->subDays(8)->toDateString(), 'alasan_penghapusan' => '8 unit AC Split 1 PK lantai dasar bocor evaporator dan kompresor mati total', 'metode' => 'Dihapusbukukan', 'nilai_buku_terakhir' => 1200000, 'status' => 'Diajukan', 'approval_status' => 'Diajukan', 'maker_id' => $adminId, 'checker_id' => null, 'keterangan' => 'Menunggu verifikasi fisik oleh tim taksasi'],
            ['no_disposal' => 'DSP-2026-005', 'aset_id' => $firstAsetId, 'tanggal_pengajuan' => now()->subDays(4)->toDateString(), 'alasan_penghapusan' => '1 unit Genset Open 50 kVA tahun 2013 tidak dapat suku cadang', 'metode' => 'Dijual (Lelang)', 'nilai_buku_terakhir' => 8000000, 'status' => 'Diajukan', 'approval_status' => 'Diajukan', 'maker_id' => $adminId, 'checker_id' => null, 'keterangan' => 'Pengajuan persiapan berkas lelang'],
            ['no_disposal' => 'DSP-2026-006', 'aset_id' => $firstAsetId, 'tanggal_pengajuan' => now()->subDays(1)->toDateString(), 'alasan_penghapusan' => 'Kursi putar teller 6 unit patah hidrolik dan busa robek', 'metode' => 'Dimusnahkan', 'nilai_buku_terakhir' => 450000, 'status' => 'Diajukan', 'approval_status' => 'Diajukan', 'maker_id' => $adminId, 'checker_id' => null, 'keterangan' => 'Sudah tidak layak pakai di banking hall'],
        ];
        foreach ($disposals as $dsp) {
            AsDisposalAset::create($dsp);
        }

        // 2.4 Rekonsiliasi & Reklasifikasi Aset (6 Record)
        AsRekonsiliasiAset::truncate();
        $rekonsiliasi = [
            ['no_rekonsiliasi' => 'REK-2026-001', 'periode' => 'Semester II 2025', 'tanggal' => now()->subDays(45)->toDateString(), 'aset_id' => $firstAsetId, 'jenis' => 'Rekonsiliasi', 'kategori_awal' => 'Peralatan Kantor', 'kategori_baru' => 'Peralatan Kantor', 'kondisi_awal' => 'Baik', 'kondisi_baru' => 'Baik', 'hasil_rekonsiliasi' => 'Kesesuaian fisik dan buku 99.4%, selisih barcode telah dicetak ulang', 'status' => 'Selesai', 'petugas' => 'Ferryanto (Staf Aset)', 'keterangan' => 'Sensus tahunan seluruh cabang'],
            ['no_rekonsiliasi' => 'REK-2026-002', 'periode' => 'Kuartal I 2026', 'tanggal' => now()->subDays(20)->toDateString(), 'aset_id' => $firstAsetId, 'jenis' => 'Reklasifikasi', 'kategori_awal' => 'Biaya Renovasi Gedung', 'kategori_baru' => 'Aset Gedung & Bangunan', 'kondisi_awal' => 'Baik', 'kondisi_baru' => 'Baik', 'hasil_rekonsiliasi' => 'Kapitalisasi biaya renovasi fasad sebesar Rp 85.000.000 ke aset tetap', 'status' => 'Selesai', 'petugas' => 'Andi Wijaya (Staf Aset)', 'keterangan' => 'Sesuai PSAK 16 Aset Tetap'],
            ['no_rekonsiliasi' => 'REK-2026-003', 'periode' => 'Bulan Februari 2026', 'tanggal' => now()->subDays(15)->toDateString(), 'aset_id' => $firstAsetId, 'jenis' => 'Rekonsiliasi', 'kategori_awal' => 'Teknologi Informasi', 'kategori_baru' => 'Teknologi Informasi', 'kondisi_awal' => 'Baik', 'kondisi_baru' => 'Baik', 'hasil_rekonsiliasi' => 'Pencocokan 25 unit laptop analis kredit dengan penanggung jawab per user', 'status' => 'Selesai', 'petugas' => 'Ferryanto (Staf Aset)', 'keterangan' => 'Seluruh unit terkonfirmasi fisik'],
            ['no_rekonsiliasi' => 'REK-2026-004', 'periode' => 'Kuartal I 2026', 'tanggal' => now()->subDays(10)->toDateString(), 'aset_id' => $firstAsetId, 'jenis' => 'Reklasifikasi', 'kategori_awal' => 'Beban Pemeliharaan', 'kategori_baru' => 'Peralatan Operasional', 'kondisi_awal' => 'Baik', 'kondisi_baru' => 'Baik', 'hasil_rekonsiliasi' => 'Koreksi pencatatan pembelian genset darurat portabel 5 kVA dari beban ke aset', 'status' => 'Selesai', 'petugas' => 'Dewi Lestari (Staf Aset)', 'keterangan' => 'Memo koreksi jurnal bagian akuntansi'],
            ['no_rekonsiliasi' => 'REK-2026-005', 'periode' => 'Kuartal I 2026', 'tanggal' => now()->subDays(5)->toDateString(), 'aset_id' => $firstAsetId, 'jenis' => 'Rekonsiliasi', 'kategori_awal' => 'Kendaraan Dinas', 'kategori_baru' => 'Kendaraan Dinas', 'kondisi_awal' => 'Baik', 'kondisi_baru' => 'Baik', 'hasil_rekonsiliasi' => 'Pengecekan fisik nomor rangka dan nomor mesin 10 unit kendaraan operasional', 'status' => 'Selesai', 'petugas' => 'Andi Wijaya (Staf Aset)', 'keterangan' => 'Semua nomor identifikasi sesuai BPKB'],
            ['no_rekonsiliasi' => 'REK-2026-006', 'periode' => 'Bulan Maret 2026', 'tanggal' => now()->subDays(2)->toDateString(), 'aset_id' => $firstAsetId, 'jenis' => 'Rekonsiliasi', 'kategori_awal' => 'Furnitur & Meubelair', 'kategori_baru' => 'Furnitur & Meubelair', 'kondisi_awal' => 'Baik', 'kondisi_baru' => 'Perlu Perbaikan', 'hasil_rekonsiliasi' => 'Pemeriksaan kursi kerja KC Tolitoli, ditemukan 3 unit hidrolik turun', 'status' => 'Selesai', 'petugas' => 'Ferryanto (Staf Aset)', 'keterangan' => 'Diteruskan ke pemeliharaan'],
        ];
        foreach ($rekonsiliasi as $rk) {
            AsRekonsiliasiAset::create($rk);
        }

        // 2.5 Tindak Lanjut Temuan (6 Record)
        AsTemuan::truncate();
        $temuan = [
            ['no_temuan' => 'TMN-SKAI-2025-01', 'sumber' => 'Audit Rutin SKAI', 'uraian' => 'Terdapat 14 unit laptop kerja yang belum tertempel stiker barcode QR Code Aset', 'tanggal_temuan' => now()->subDays(50)->toDateString(), 'batas_tindak_lanjut' => now()->subDays(20)->toDateString(), 'status' => 'Closed', 'penanggung_jawab' => 'Bagian Administrasi Aset', 'tindak_lanjut' => 'Seluruh 14 unit telah diberi label barcode tahan cuaca dan diverifikasi oleh SKAI'],
            ['no_temuan' => 'TMN-SKAI-2025-02', 'sumber' => 'Audit Rutin SKAI', 'uraian' => 'Keterlambatan perpanjangan polis asuransi kebakaran gedung Kantor Cabang Tolitoli', 'tanggal_temuan' => now()->subDays(35)->toDateString(), 'batas_tindak_lanjut' => now()->subDays(10)->toDateString(), 'status' => 'Closed', 'penanggung_jawab' => 'Bagian Logistik & PKS', 'tindak_lanjut' => 'Polis baru telah diterbitkan oleh PT Askrida No. POL-F-2026-081'],
            ['no_temuan' => 'TMN-OJK-2026-03', 'sumber' => 'Pemeriksaan Khusus OJK', 'uraian' => 'Penyimpanan bukti kepemilikan BPKB kendaraan operasional belum di ruang khazanah tahan api', 'tanggal_temuan' => now()->subDays(20)->toDateString(), 'batas_tindak_lanjut' => now()->addDays(10)->toDateString(), 'status' => 'In Progress', 'penanggung_jawab' => 'Bagian Umum & Logistik', 'tindak_lanjut' => 'Seluruh BPKB telah dipindahkan ke Lemari Chubb Safes Khazanah Utama'],
            ['no_temuan' => 'TMN-BPKP-2026-04', 'sumber' => 'Evaluasi GCG BPKP', 'uraian' => 'SOP penghapusan aset inventaris belum memuat klausul pemusnahan media penyimpanan magnetik', 'tanggal_temuan' => now()->subDays(12)->toDateString(), 'batas_tindak_lanjut' => now()->addDays(20)->toDateString(), 'status' => 'In Progress', 'penanggung_jawab' => 'Bagian Aset & Divisi TI', 'tindak_lanjut' => 'Draft revisi SOP telah selesai dibahas bersama Divisi Hukum'],
            ['no_temuan' => 'TMN-INT-2026-05', 'sumber' => 'Inspeksi Mandiri Divisi Umum', 'uraian' => 'Suhu ruang server cabang pembantu Parigi melebihi 24 derajat celcius saat siang hari', 'tanggal_temuan' => now()->subDays(8)->toDateString(), 'batas_tindak_lanjut' => now()->addDays(5)->toDateString(), 'status' => 'Open', 'penanggung_jawab' => 'Bagian Pemeliharaan Aset', 'tindak_lanjut' => 'SPK pemasangan AC tambahan 1.5 PK telah diterbitkan ke CV Palu Mandiri Pendingin'],
            ['no_temuan' => 'TMN-INT-2026-06', 'sumber' => 'Inspeksi Mandiri Divisi Umum', 'uraian' => 'Catatan log sheet pengisian BBM mobil operasional DN 1245 AB tidak lengkap tanda tangan SPBU', 'tanggal_temuan' => now()->subDays(3)->toDateString(), 'batas_tindak_lanjut' => now()->addDays(7)->toDateString(), 'status' => 'Open', 'penanggung_jawab' => 'Bagian Rumah Tangga & Driver', 'tindak_lanjut' => 'Instruksi tertulis kepada seluruh driver operasional agar melampirkan struk asli SPBU'],
        ];
        foreach ($temuan as $tm) {
            AsTemuan::create($tm);
        }

        // 2.6 Invoice Sewa (6 Record)
        AsInvoiceSewa::truncate();
        $invoices = [
            ['no_invoice' => 'INV-SEWA-2026-01', 'vendor' => 'H. Abdul Rahman (Pemilik Ruko Tolitoli)', 'jenis_sewa' => 'Sewa Gedung Kantor Cabang', 'periode_mulai' => '2026-01-01', 'periode_selesai' => '2026-12-31', 'nilai' => 180000000, 'jatuh_tempo' => now()->subDays(10)->toDateString(), 'status' => 'Lunas', 'keterangan' => 'Pembayaran sewa gedung KC Tolitoli tahun 2026'],
            ['no_invoice' => 'INV-SEWA-2026-02', 'vendor' => 'PT Angkasa Pura Indonesia Bandara Palu', 'jenis_sewa' => 'Sewa Space Galeri ATM', 'periode_mulai' => '2026-01-01', 'periode_selesai' => '2026-06-30', 'nilai' => 36000000, 'jatuh_tempo' => now()->subDays(5)->toDateString(), 'status' => 'Lunas', 'keterangan' => 'Space ATM Terminal Kedatangan Bandara Mutiara'],
            ['no_invoice' => 'INV-SEWA-2026-03', 'vendor' => 'PT Serasi Autoraya (TRAC Astra)', 'jenis_sewa' => 'Sewa Truk Kas Pengawalan', 'periode_mulai' => '2026-03-01', 'periode_selesai' => '2026-03-31', 'nilai' => 24500000, 'jatuh_tempo' => now()->addDays(10)->toDateString(), 'status' => 'Belum Bayar', 'keterangan' => 'Tagihan sewa armada pengawalan kas bulan Maret'],
            ['no_invoice' => 'INV-SEWA-2026-04', 'vendor' => 'PT Astra Graphia Tbk (Fuji Xerox)', 'jenis_sewa' => 'Sewa Mesin Fotokopi Multifungsi', 'periode_mulai' => '2026-02-01', 'periode_selesai' => '2026-02-28', 'nilai' => 14200000, 'jatuh_tempo' => now()->addDays(15)->toDateString(), 'status' => 'Belum Bayar', 'keterangan' => 'Sewa 4 unit mesin fotokopi kantor pusat & klik charge'],
            ['no_invoice' => 'INV-SEWA-2026-05', 'vendor' => 'Dra. Hj. Nurhaida (Pemilik Properti Parigi)', 'jenis_sewa' => 'Sewa Ruko KCP Parigi', 'periode_mulai' => '2026-04-01', 'periode_selesai' => '2027-03-31', 'nilai' => 90000000, 'jatuh_tempo' => now()->addDays(25)->toDateString(), 'status' => 'Belum Bayar', 'keterangan' => 'Tagihan perpanjangan sewa tahun ke-3 KCP Parigi'],
            ['no_invoice' => 'INV-SEWA-2026-06', 'vendor' => 'PT Telkom Indonesia (Wifi Corner)', 'jenis_sewa' => 'Sewa Bandwidth Dedicated Internet', 'periode_mulai' => '2026-02-01', 'periode_selesai' => '2026-02-28', 'nilai' => 45000000, 'jatuh_tempo' => now()->subDays(1)->toDateString(), 'status' => 'Lunas', 'keterangan' => 'Jalur Astinet 200 Mbps Kantor Pusat'],
        ];
        foreach ($invoices as $inv) {
            AsInvoiceSewa::create($inv);
        }

        // 2.7 Memo Sewa Cabang (6 Record)
        AsMemoSewaCabang::truncate();
        $memoSewa = [
            ['no_memo' => 'MEMO-SEWA-2026-001', 'cabang' => 'KC Tolitoli', 'jenis' => 'Perpanjangan Sewa Gedung', 'tanggal' => now()->subDays(30)->toDateString(), 'nilai' => 180000000, 'status_persetujuan' => 'Disetujui', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approved_at' => now()->subDays(28), 'keterangan' => 'Disetujui perpanjangan sewa ruko kantor 1 tahun'],
            ['no_memo' => 'MEMO-SEWA-2026-002', 'cabang' => 'KC Luwuk', 'jenis' => 'Negosiasi Penurunan Tarif Sewa', 'tanggal' => now()->subDays(20)->toDateString(), 'nilai' => 240000000, 'status_persetujuan' => 'Disetujui', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approved_at' => now()->subDays(18), 'keterangan' => 'Negosiasi berhasil efisiensi Rp 20.000.000 dari harga awal'],
            ['no_memo' => 'MEMO-SEWA-2026-003', 'cabang' => 'KCP Ampana', 'jenis' => 'Relokasi Kantor Kas Baru', 'tanggal' => now()->subDays(12)->toDateString(), 'nilai' => 75000000, 'status_persetujuan' => 'Diajukan', 'maker_id' => $adminId, 'checker_id' => null, 'approved_at' => null, 'keterangan' => 'Usulan pindah lokasi ke dekat pusat pasar Ampana'],
            ['no_memo' => 'MEMO-SEWA-2026-004', 'cabang' => 'KC Morowali', 'jenis' => 'Sewa Rumah Dinas Pimpinan Cabang', 'tanggal' => now()->subDays(8)->toDateString(), 'nilai' => 48000000, 'status_persetujuan' => 'Diajukan', 'maker_id' => $adminId, 'checker_id' => null, 'approved_at' => null, 'keterangan' => 'Sesuai plafon tunjangan fasilitas rumah dinas'],
            ['no_memo' => 'MEMO-SEWA-2026-005', 'cabang' => 'KC Poso', 'jenis' => 'Sewa Lahan ATM Drive Thru', 'tanggal' => now()->subDays(5)->toDateString(), 'nilai' => 30000000, 'status_persetujuan' => 'Disetujui', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approved_at' => now()->subDays(3), 'keterangan' => 'Kerjasama penempatan galeri ATM di SPBU Poso'],
            ['no_memo' => 'MEMO-SEWA-2026-006', 'cabang' => 'KCP Tondo Palu', 'jenis' => 'Perpanjangan Sewa Kantor Kas', 'tanggal' => now()->subDays(2)->toDateString(), 'nilai' => 55000000, 'status_persetujuan' => 'Diajukan', 'maker_id' => $adminId, 'checker_id' => null, 'approved_at' => null, 'keterangan' => 'Lokasi strategis dekat kampus Universitas Tadulako'],
        ];
        foreach ($memoSewa as $ms) {
            AsMemoSewaCabang::create($ms);
        }

        // 2.8 Penerimaan Barang (8 Record)
        AsPenerimaanBarang::truncate();
        $penerimaan = [
            ['no_penerimaan' => 'LPB-2026-001', 'tanggal' => now()->subDays(28)->toDateString(), 'vendor' => 'CV Palu Mandiri Stationery', 'nama_barang' => 'Kertas HVS PaperOne A4 80gr', 'jumlah' => 150, 'satuan' => 'Rim', 'kondisi' => 'Baik', 'penerima' => 'Staf Logistik', 'status' => 'Disimpan', 'keterangan' => 'Sesuai pesanan SPK ATK Triwulan I'],
            ['no_penerimaan' => 'LPB-2026-002', 'tanggal' => now()->subDays(24)->toDateString(), 'vendor' => 'PT Mitra Komputindo Utama', 'nama_barang' => 'PC All-in-One HP ProOne 440 G9 Core i5', 'jumlah' => 10, 'satuan' => 'Unit', 'kondisi' => 'Baik', 'penerima' => 'Staf Aset TI', 'status' => 'Diterima', 'keterangan' => 'Pengujian fungsi dan instalasi OS lancar'],
            ['no_penerimaan' => 'LPB-2026-003', 'tanggal' => now()->subDays(18)->toDateString(), 'vendor' => 'PT Sentra Pratama Brankas', 'nama_barang' => 'Lemari Brankas Teller Tahan Api Chubb Safes', 'jumlah' => 2, 'satuan' => 'Unit', 'kondisi' => 'Baik', 'penerima' => 'Staf Logistik & Keamanan', 'status' => 'Diterima', 'keterangan' => 'Ditempatkan di ruang teller KC Luwuk'],
            ['no_penerimaan' => 'LPB-2026-004', 'tanggal' => now()->subDays(14)->toDateString(), 'vendor' => 'CV Celebes Graha Pratama', 'nama_barang' => 'Kursi Kerja Ergonomis Staff Kursi CS', 'jumlah' => 15, 'satuan' => 'Unit', 'kondisi' => 'Baik', 'penerima' => 'Staf Umum', 'status' => 'Disimpan', 'keterangan' => 'Siap didistribusikan ke cabang'],
            ['no_penerimaan' => 'LPB-2026-005', 'tanggal' => now()->subDays(9)->toDateString(), 'vendor' => 'CV Palu Mandiri Stationery', 'nama_barang' => 'Buku Tabungan Simpeda & Formulir Slip Setoran', 'jumlah' => 500, 'satuan' => 'Buku', 'kondisi' => 'Baik', 'penerima' => 'Staf Logistik', 'status' => 'Disimpan', 'keterangan' => 'Stok cadangan logistik cetakan kasir'],
            ['no_penerimaan' => 'LPB-2026-006', 'tanggal' => now()->subDays(6)->toDateString(), 'vendor' => 'PT Multi Sarana Komputer', 'nama_barang' => 'Toner Cartridge HP LaserJet Original 85A & 26A', 'jumlah' => 20, 'satuan' => 'Pcs', 'kondisi' => 'Baik', 'penerima' => 'Staf Logistik', 'status' => 'Disimpan', 'keterangan' => 'Segel hologram terverifikasi resmi'],
            ['no_penerimaan' => 'LPB-2026-007', 'tanggal' => now()->subDays(3)->toDateString(), 'vendor' => 'CV Sumber Rezeki Palu', 'nama_barang' => 'Air Mineral Galon Aqua & Kebutuhan Pantry', 'jumlah' => 80, 'satuan' => 'Galon', 'kondisi' => 'Baik', 'penerima' => 'Staf RT', 'status' => 'Diterima', 'keterangan' => 'Konsumsi harian karyawan kantor pusat'],
            ['no_penerimaan' => 'LPB-2026-008', 'tanggal' => now()->subDays(1)->toDateString(), 'vendor' => 'CV Cahaya Abadi Seragam', 'nama_barang' => 'Pakaian Dinas Seragam Frontliner Batik Sulteng', 'jumlah' => 60, 'satuan' => 'Set', 'kondisi' => 'Baik', 'penerima' => 'Staf Logistik & SDM', 'status' => 'Diperiksa', 'keterangan' => 'Pemeriksaan kesesuaian ukuran karyawan'],
        ];
        foreach ($penerimaan as $pn) {
            AsPenerimaanBarang::create($pn);
        }

        // 2.9 Distribusi Barang (8 Record)
        AsDistribusiBarang::truncate();
        $distribusi = [
            ['no_distribusi' => 'SJB-2026-001', 'tanggal' => now()->subDays(25)->toDateString(), 'nama_barang' => 'Kertas HVS A4 & Formulir Slip Setoran', 'jumlah' => 40, 'satuan' => 'Rim / Buku', 'tujuan_unit' => 'Kantor Cabang Luwuk', 'penerima' => 'Kabag Operasional KC Luwuk', 'status' => 'Diterima', 'keterangan' => 'Terkirim via kurir ekspedisi logistik'],
            ['no_distribusi' => 'SJB-2026-002', 'tanggal' => now()->subDays(20)->toDateString(), 'nama_barang' => 'PC All-in-One HP ProOne 440 G9', 'jumlah' => 3, 'satuan' => 'Unit', 'tujuan_unit' => 'Kantor Cabang Tolitoli', 'penerima' => 'Staf TI KC Tolitoli', 'status' => 'Diterima', 'keterangan' => 'Penggantian PC front office yang lambat'],
            ['no_distribusi' => 'SJB-2026-003', 'tanggal' => now()->subDays(16)->toDateString(), 'nama_barang' => 'Brankas Lemari Besi Teller Chubb Safes', 'jumlah' => 1, 'satuan' => 'Unit', 'tujuan_unit' => 'Kantor Cabang Pembantu Parigi', 'penerima' => 'Pemimpin KCP Parigi', 'status' => 'Diterima', 'keterangan' => 'Pengawalan mobil kas dan Brimob'],
            ['no_distribusi' => 'SJB-2026-004', 'tanggal' => now()->subDays(12)->toDateString(), 'nama_barang' => 'Kursi Kerja CS Ergonomis', 'jumlah' => 5, 'satuan' => 'Unit', 'tujuan_unit' => 'Kantor Cabang Poso', 'penerima' => 'Staf Umum KC Poso', 'status' => 'Diterima', 'keterangan' => 'Standarisasi corporate identity CS'],
            ['no_distribusi' => 'SJB-2026-005', 'tanggal' => now()->subDays(8)->toDateString(), 'nama_barang' => 'Toner Cartridge HP LaserJet', 'jumlah' => 6, 'satuan' => 'Pcs', 'tujuan_unit' => 'Kantor Cabang Morowali', 'penerima' => 'Staf Logistik KC Morowali', 'status' => 'Terkirim', 'keterangan' => 'Dalam proses perjalanan ekspedisi'],
            ['no_distribusi' => 'SJB-2026-006', 'tanggal' => now()->subDays(5)->toDateString(), 'nama_barang' => 'Buku Tabungan & Warkat Kliring', 'jumlah' => 100, 'satuan' => 'Buku', 'tujuan_unit' => 'Kantor Cabang Pembantu Tondo', 'penerima' => 'Supervisor Operasional KCP Tondo', 'status' => 'Diterima', 'keterangan' => 'Diambil langsung oleh kurir kas'],
            ['no_distribusi' => 'SJB-2026-007', 'tanggal' => now()->subDays(2)->toDateString(), 'nama_barang' => 'PC All-in-One HP ProOne', 'jumlah' => 2, 'satuan' => 'Unit', 'tujuan_unit' => 'Divisi Kredit & Komersial KP', 'penerima' => 'Staf Administrasi Kredit', 'status' => 'Diterima', 'keterangan' => 'Penempatan di ruang analis lantai 2'],
            ['no_distribusi' => 'SJB-2026-008', 'tanggal' => now()->toDateString(), 'nama_barang' => 'Seragam Dinas Frontliner Batik Sulteng', 'jumlah' => 20, 'satuan' => 'Set', 'tujuan_unit' => 'Kantor Cabang Palu Utama', 'penerima' => 'Staf SDM KC Palu', 'status' => 'Disiapkan', 'keterangan' => 'Proses packing dan verifikasi nama'],
        ];
        foreach ($distribusi as $dst) {
            AsDistribusiBarang::create($dst);
        }

        // 2.10 Administrasi Pembayaran Tagihan (8 Record)
        AsPembayaranTagihan::truncate();
        $pembayaran = [
            ['no_tagihan' => 'BYR-2026-001', 'tanggal_tagihan' => now()->subDays(25)->toDateString(), 'tanggal_bayar' => now()->subDays(24)->toDateString(), 'vendor' => 'PT PLN (Persero) UP3 Palu', 'uraian' => 'Pembayaran tagihan listrik rekening gedung Kantor Pusat periode Februari 2026', 'nilai' => 48500000, 'status' => 'Lunas', 'no_rekening' => '54010023412', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(24), 'keterangan' => 'Lunas via overbooking'],
            ['no_tagihan' => 'BYR-2026-002', 'tanggal_tagihan' => now()->subDays(22)->toDateString(), 'tanggal_bayar' => now()->subDays(21)->toDateString(), 'vendor' => 'PT Telkom Indonesia (Astinet)', 'uraian' => 'Tagihan sewa internet dedicated 200 Mbps Kantor Pusat & VPN-IP Cabang', 'nilai' => 52000000, 'status' => 'Lunas', 'no_rekening' => '00109923841', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(21), 'keterangan' => 'Lunas via transfer'],
            ['no_tagihan' => 'BYR-2026-003', 'tanggal_tagihan' => now()->subDays(18)->toDateString(), 'tanggal_bayar' => now()->subDays(17)->toDateString(), 'vendor' => 'PT ISS Indonesia', 'uraian' => 'Jasa Cleaning Service & Kebersihan Gedung Kantor Pusat bulan Februari', 'nilai' => 30000000, 'status' => 'Lunas', 'no_rekening' => '10200889211', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(17), 'keterangan' => 'Lunas termin bulanan'],
            ['no_tagihan' => 'BYR-2026-004', 'tanggal_tagihan' => now()->subDays(14)->toDateString(), 'tanggal_bayar' => now()->subDays(13)->toDateString(), 'vendor' => 'PT Bravo Satria Perkasa', 'uraian' => 'Jasa Pengamanan & Satpam Gedung Kantor Pusat & Kantor Kas Palu', 'nilai' => 70800000, 'status' => 'Lunas', 'no_rekening' => '01299831001', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(13), 'keterangan' => 'Lunas jasa satpam'],
            ['no_tagihan' => 'BYR-2026-005', 'tanggal_tagihan' => now()->subDays(10)->toDateString(), 'tanggal_bayar' => now()->subDays(9)->toDateString(), 'vendor' => 'PT Schindler Indonesia', 'uraian' => 'Tagihan jasa pemeliharaan berkala Lift Penumpang 1 & 2 triwulan I', 'nilai' => 8800000, 'status' => 'Lunas', 'no_rekening' => '21009841201', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(9), 'keterangan' => 'Lunas servis lift'],
            ['no_tagihan' => 'BYR-2026-006', 'tanggal_tagihan' => now()->subDays(5)->toDateString(), 'tanggal_bayar' => null, 'vendor' => 'PT Serasi Autoraya (TRAC)', 'uraian' => 'Tagihan sewa armada truk kas pengawalan periode bulan Februari 2026', 'nilai' => 24500000, 'status' => 'Proses', 'no_rekening' => '00988712391', 'maker_id' => $adminId, 'checker_id' => null, 'approval_status' => 'Diajukan', 'approved_at' => null, 'keterangan' => 'Menunggu verifikasi bukti jalan'],
            ['no_tagihan' => 'BYR-2026-007', 'tanggal_tagihan' => now()->subDays(3)->toDateString(), 'tanggal_bayar' => null, 'vendor' => 'PDAM Kota Palu', 'uraian' => 'Pembayaran tagihan air bersih PDAM Kantor Pusat bulan Februari', 'nilai' => 3850000, 'status' => 'Belum Bayar', 'no_rekening' => '10023812991', 'maker_id' => $adminId, 'checker_id' => null, 'approval_status' => 'Diajukan', 'approved_at' => null, 'keterangan' => 'Menunggu tanggal jatuh tempo'],
            ['no_tagihan' => 'BYR-2026-008', 'tanggal_tagihan' => now()->subDays(1)->toDateString(), 'tanggal_bayar' => null, 'vendor' => 'CV Palu Mandiri Pendingin', 'uraian' => 'Tagihan jasa cuci & servis AC berkala seluruh lantai gedung utama', 'nilai' => 4500000, 'status' => 'Belum Bayar', 'no_rekening' => '00129841299', 'maker_id' => $adminId, 'checker_id' => null, 'approval_status' => 'Diajukan', 'approved_at' => null, 'keterangan' => 'BAP pekerjaan telah ditandatangani'],
        ];
        foreach ($pembayaran as $pb) {
            AsPembayaranTagihan::create($pb);
        }

        // 2.11 Mutasi Aset (Tambah data realistis)
        $mutasiAset = [
            ['no_mutasi' => 'MUT-2026-004', 'aset_id' => $firstAsetId, 'pengaju_id' => $adminId, 'nama_pemohon' => 'Siti Rahma', 'jabatan_pemohon' => 'Head Teller', 'username_pemohon' => 'siti_rahma', 'dari_lokasi' => 'Khazanah Kantor Pusat', 'ke_lokasi' => 'Khazanah KC Luwuk', 'dari_penanggung_jawab' => 'Kasir Utama KP', 'ke_penanggung_jawab' => 'Head Teller KC Luwuk', 'alasan' => 'Dukungan operasional penambahan teller baru di cabang Luwuk', 'status' => 'Disetujui', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(4), 'keterangan' => 'Dalam proses pengiriman ekspedisi berpenumpang aman'],
            ['no_mutasi' => 'MUT-2026-005', 'aset_id' => $firstAsetId, 'pengaju_id' => $adminId, 'nama_pemohon' => 'Ahmad Fauzi', 'jabatan_pemohon' => 'Staf Sekper', 'username_pemohon' => 'ahmad_fauzi', 'dari_lokasi' => 'Lantai 3 Ruang Rapat', 'ke_lokasi' => 'Lantai 1 Banking Hall', 'dari_penanggung_jawab' => 'Staf Sekper', 'ke_penanggung_jawab' => 'Staf Customer Service', 'alasan' => 'Pemanfaatan Smart TV interaktif untuk display antrian nasabah', 'status' => 'Selesai', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(10), 'keterangan' => 'Pemasangan bracket dan konfigurasi running text selesai'],
            ['no_mutasi' => 'MUT-2026-006', 'aset_id' => $firstAsetId, 'pengaju_id' => $adminId, 'nama_pemohon' => 'Rian Hidayat', 'jabatan_pemohon' => 'Network Engineer', 'username_pemohon' => 'rian_ti', 'dari_lokasi' => 'Basemen Gudang Aset', 'ke_lokasi' => 'Lantai 2 Ruang TI', 'dari_penanggung_jawab' => 'Staf Logistik', 'ke_penanggung_jawab' => 'Network Engineer', 'alasan' => 'Penggantian Switch Core jaringan yang mengalami port error', 'status' => 'Selesai', 'maker_id' => $adminId, 'checker_id' => $adminId, 'approval_status' => 'Disetujui', 'approved_at' => now()->subDays(15), 'keterangan' => 'Switch telah dikonfigurasi VLAN dan routing operasional'],
            ['no_mutasi' => 'MUT-2026-007', 'aset_id' => $firstAsetId, 'pengaju_id' => $adminId, 'nama_pemohon' => 'Dewi Lestari', 'jabatan_pemohon' => 'Supervisor CS', 'username_pemohon' => 'dewi_cs', 'dari_lokasi' => 'KC Palu Barat', 'ke_lokasi' => 'KCP Parigi', 'dari_penanggung_jawab' => 'Kabag Operasional KC Palu', 'ke_penanggung_jawab' => 'Pemimpin KCP Parigi', 'alasan' => 'Kebutuhan mesin hitung uang tambahan jelang hari raya Idul Fitri', 'status' => 'Diajukan', 'maker_id' => $adminId, 'checker_id' => null, 'approval_status' => 'Diajukan', 'approved_at' => null, 'keterangan' => 'Menunggu verifikasi ketersediaan armada kurir kas'],
        ];
        foreach ($mutasiAset as $mut) {
            AsMutasiAset::updateOrCreate(['no_mutasi' => $mut['no_mutasi']], $mut);
        }

        // ═════════════════════════════════════════════════════════════════════════
        // BAGIAN 3: PENGADAAN SERTA PEMELIHARAAN ASET & INVENTARIS
        // ═════════════════════════════════════════════════════════════════════════

        // 3.1 Draft Dokumen SPK (6 Draft)
        PgDraftDokumen::truncate();
        $draftDokumen = [
            ['jenis' => 'SPK Pengadaan', 'no_dokumen' => 'DRAFT-SPK-2026-01', 'judul' => 'Draft SPK Pengadaan 25 Unit PC Workstation All-in-One Frontliner', 'vendor' => 'PT Mitra Komputindo Utama', 'tanggal' => now()->subDays(15)->toDateString(), 'status' => 'Review Legal', 'file' => 'draft_spk_pc.docx', 'keterangan' => 'Review klausul garansi on-site 3 tahun'],
            ['jenis' => 'Kontrak Pemeliharaan', 'no_dokumen' => 'DRAFT-PKS-2026-02', 'judul' => 'Draft PKS Pemeliharaan & Uji Kelaikan Lift Penumpang Schindler 2026-2027', 'vendor' => 'PT Schindler Indonesia', 'tanggal' => now()->subDays(10)->toDateString(), 'status' => 'Review Teknis', 'file' => 'draft_lift_contract.docx', 'keterangan' => 'Klausul emergency call-out 24 jam'],
            ['jenis' => 'SPK Pekerjaan', 'no_dokumen' => 'DRAFT-SPK-2026-03', 'judul' => 'Draft SPK Pengecatan Fasad dan Waterproofing Dak Beton Gedung Utama', 'vendor' => 'CV Karya Gemilang', 'tanggal' => now()->subDays(8)->toDateString(), 'status' => 'Final Draft', 'file' => 'draft_spk_fasad.docx', 'keterangan' => 'Siap penandatanganan Direksi'],
            ['jenis' => 'PKS Jasa', 'no_dokumen' => 'DRAFT-PKS-2026-04', 'judul' => 'Draft Perjanjian Jasa Cleaning Service & Hygiene Terpadu', 'vendor' => 'PT ISS Indonesia', 'tanggal' => now()->subDays(6)->toDateString(), 'status' => 'Review Rekanan', 'file' => 'draft_iss_2026.docx', 'keterangan' => 'Penyesuaian UMP Kota Palu 2026'],
            ['jenis' => 'SPK Pengadaan', 'no_dokumen' => 'DRAFT-SPK-2026-05', 'judul' => 'Draft SPK Pengadaan Formulir Slip & Cetakan Perbankan Semester I 2026', 'vendor' => 'CV Palu Mandiri Stationery', 'tanggal' => now()->subDays(4)->toDateString(), 'status' => 'Draft Awal', 'file' => 'draft_spk_cetakan.docx', 'keterangan' => 'Menunggu approval HPS bagian pengadaan'],
            ['jenis' => 'PKS Jasa', 'no_dokumen' => 'DRAFT-PKS-2026-06', 'judul' => 'Draft Perjanjian Maintenance & Lisensi Firewall Fortinet FortiGate Cluster', 'vendor' => 'PT Multipolar Technology', 'tanggal' => now()->subDays(2)->toDateString(), 'status' => 'Review Teknis', 'file' => 'draft_fortinet.docx', 'keterangan' => 'Masa proteksi UTM 12 bulan'],
        ];
        foreach ($draftDokumen as $dd) {
            PgDraftDokumen::create($dd);
        }

        // 3.2 Perencanaan Kebutuhan (8 Record)
        PmPerencanaanKebutuhan::truncate();
        $perencanaan = [
            ['no_rencana' => 'RKB-2026-001', 'periode' => 'Semester I 2026', 'jenis_kebutuhan' => 'Barang', 'nama_item' => 'PC All-in-One Core i5 untuk Customer Service & Teller Cabang', 'jumlah' => 30, 'satuan' => 'Unit', 'spesifikasi' => 'Core i5 Gen 13, RAM 16GB, SSD 512GB NVMe, Layar 23.8 inch IPS', 'estimasi_harga' => 450000000, 'prioritas' => 'Tinggi', 'status' => 'Disetujui', 'maker_id' => $adminId, 'keterangan' => 'Peremajaan perangkat lawas di 6 kantor cabang'],
            ['no_rencana' => 'RKB-2026-002', 'periode' => 'Semester I 2026', 'jenis_kebutuhan' => 'Aset', 'nama_item' => 'Mobil Operasional Kas Keliling Pedesaan Double Cabin 4x4', 'jumlah' => 2, 'satuan' => 'Unit', 'spesifikasi' => 'Toyota Hilux 2.4 D-Cab dilengkapi brankas tanam dan solar cell genset', 'estimasi_harga' => 1100000000, 'prioritas' => 'Tinggi', 'status' => 'Disetujui', 'maker_id' => $adminId, 'keterangan' => 'Program inklusi keuangan daerah terpencil'],
            ['no_rencana' => 'RKB-2026-003', 'periode' => 'Semester I 2026', 'jenis_kebutuhan' => 'Jasa', 'nama_item' => 'Jasa Pemeliharaan Rutin AC Central & Split Gedung Kantor Pusat', 'jumlah' => 1, 'satuan' => 'Paket', 'spesifikasi' => 'Perawatan bulanan 38 unit AC split dan 2 unit Chiller 25 PK', 'estimasi_harga' => 60000000, 'prioritas' => 'Normal', 'status' => 'Disetujui', 'maker_id' => $adminId, 'keterangan' => 'Kontrak payung 1 tahun kalender'],
            ['no_rencana' => 'RKB-2026-004', 'periode' => 'Kuartal II 2026', 'jenis_kebutuhan' => 'Aset', 'nama_item' => 'Mesin Hitung & Sortir Uang Kertas 3 Pocket Berkualitas Tinggi', 'jumlah' => 4, 'satuan' => 'Unit', 'spesifikasi' => 'Kisan Newton 3, deteksi uang palsu UV/MG/IR/CIS ganda', 'estimasi_harga' => 580000000, 'prioritas' => 'Tinggi', 'status' => 'Diajukan', 'maker_id' => $adminId, 'keterangan' => 'Kebutuhan kas titipan KC Luwuk & KC Tolitoli'],
            ['no_rencana' => 'RKB-2026-005', 'periode' => 'Kuartal II 2026', 'jenis_kebutuhan' => 'Barang', 'nama_item' => 'Pengadaan APAR Clean Agent & CO2 Standar NFPA', 'jumlah' => 20, 'satuan' => 'Tabung', 'spesifikasi' => 'Tabung 5 kg Clean Agent ramah lingkungan untuk ruang server', 'estimasi_harga' => 45000000, 'prioritas' => 'Normal', 'status' => 'Diajukan', 'maker_id' => $adminId, 'keterangan' => 'Pembaruan sistem proteksi kebakaran data center'],
            ['no_rencana' => 'RKB-2026-006', 'periode' => 'Semester II 2026', 'jenis_kebutuhan' => 'Jasa', 'nama_item' => 'Renovasi Interior & Desain Banking Hall KCP Parigi', 'jumlah' => 1, 'satuan' => 'Paket', 'spesifikasi' => 'Pekerjaan partisi akustik, teller counter, signage akrilik LED', 'estimasi_harga' => 185000000, 'prioritas' => 'Normal', 'status' => 'Draft', 'maker_id' => $adminId, 'keterangan' => 'Standarisasi gerai cabang pembantu'],
            ['no_rencana' => 'RKB-2026-007', 'periode' => 'Kuartal II 2026', 'jenis_kebutuhan' => 'Aset', 'nama_item' => 'Uninterruptible Power Supply (UPS) Data Center 40 kVA', 'jumlah' => 1, 'satuan' => 'Unit', 'spesifikasi' => 'APC Schneider Galaxy 3L Modular On-Line 3-Phase', 'estimasi_harga' => 380000000, 'prioritas' => 'Darurat', 'status' => 'Diajukan', 'maker_id' => $adminId, 'keterangan' => 'Mengganti UPS lama yang baterainya telah melewati daur hidup'],
            ['no_rencana' => 'RKB-2026-008', 'periode' => 'Semester II 2026', 'jenis_kebutuhan' => 'Barang', 'nama_item' => 'Formulir Cetakan Perbankan & Kuitansi Transaksi Semester II', 'jumlah' => 1, 'satuan' => 'Paket', 'spesifikasi' => 'Slip setoran, tarikan, formulir rekening giro, bilyet deposito', 'estimasi_harga' => 120000000, 'prioritas' => 'Normal', 'status' => 'Draft', 'maker_id' => $adminId, 'keterangan' => 'Stok pemenuhan semester genap'],
        ];
        foreach ($perencanaan as $prn) {
            PmPerencanaanKebutuhan::create($prn);
        }

        // 3.3 Jadwal Pemeliharaan Rutin (8 Jadwal)
        PmJadwalPemeliharaan::truncate();
        $jadwalPemeliharaan = [
            ['no_jadwal' => 'SCH-2026-001', 'aset_id' => $firstAsetId, 'nama_aset' => 'Genset Silent Perkins 150 kVA', 'jenis_pemeliharaan' => 'Rutin Bulanan', 'tanggal_rencana' => now()->subDays(15)->toDateString(), 'tanggal_realisasi' => now()->subDays(15)->toDateString(), 'pelaksana' => 'Teknisi CV Celebes Teknik', 'status' => 'Selesai', 'maker_id' => $adminId, 'keterangan' => 'Penggantian filter oli, cek baterai aki starter 24V'],
            ['no_jadwal' => 'SCH-2026-002', 'aset_id' => $firstAsetId, 'nama_aset' => 'Lift Penumpang Schindler 1 (Barat)', 'jenis_pemeliharaan' => 'Rutin Bulanan', 'tanggal_rencana' => now()->subDays(10)->toDateString(), 'tanggal_realisasi' => now()->subDays(10)->toDateString(), 'pelaksana' => 'PT Schindler Indonesia', 'status' => 'Selesai', 'maker_id' => $adminId, 'keterangan' => 'Pemeriksaan limit switch, governor, and guide rail lubrication'],
            ['no_jadwal' => 'SCH-2026-003', 'aset_id' => $firstAsetId, 'nama_aset' => 'AC Precision Data Center Lt 2', 'jenis_pemeliharaan' => 'Rutin 2 Mingguan', 'tanggal_rencana' => now()->subDays(5)->toDateString(), 'tanggal_realisasi' => now()->subDays(5)->toDateString(), 'pelaksana' => 'CV Palu Mandiri Pendingin', 'status' => 'Selesai', 'maker_id' => $adminId, 'keterangan' => 'Pembersihan kondensor & pengecekan level kelembaban 50%'],
            ['no_jadwal' => 'SCH-2026-004', 'aset_id' => $firstAsetId, 'nama_aset' => 'Mobil Dinas Direksi Toyota Zenix DN 1001 SB', 'jenis_pemeliharaan' => 'Servis Berkala 10.000 KM', 'tanggal_rencana' => now()->subDays(2)->toDateString(), 'tanggal_realisasi' => now()->subDays(2)->toDateString(), 'pelaksana' => 'Bengkel Resmi Nasmoco Palu', 'status' => 'Selesai', 'maker_id' => $adminId, 'keterangan' => 'Ganti oli mesin sintetis, tune up, spooring balancing'],
            ['no_jadwal' => 'SCH-2026-005', 'aset_id' => $firstAsetId, 'nama_aset' => 'Mesin Hitung Uang Glory GFB-800 Khazanah', 'jenis_pemeliharaan' => 'Kalibrasi Sensor Rutin', 'tanggal_rencana' => now()->addDays(3)->toDateString(), 'tanggal_realisasi' => null, 'pelaksana' => 'Teknisi Glory Palu', 'status' => 'Direncanakan', 'maker_id' => $adminId, 'keterangan' => 'Pembersihan sensor debu optik uang kertas'],
            ['no_jadwal' => 'SCH-2026-006', 'aset_id' => $firstAsetId, 'nama_aset' => 'Pompa Hydrant & Jockey Pump Pemadam Kebakaran', 'jenis_pemeliharaan' => 'Running Test Mingguan', 'tanggal_rencana' => now()->addDays(7)->toDateString(), 'tanggal_realisasi' => null, 'pelaksana' => 'Staf Pemeliharaan Bank', 'status' => 'Direncanakan', 'maker_id' => $adminId, 'keterangan' => 'Uji tekanan air hydrant mencapai 7 bar'],
            ['no_jadwal' => 'SCH-2026-007', 'aset_id' => $firstAsetId, 'nama_aset' => 'UPS Schneider Galaxy Data Center', 'jenis_pemeliharaan' => 'Uji Beban & Baterai Discharge', 'tanggal_rencana' => now()->addDays(12)->toDateString(), 'tanggal_realisasi' => null, 'pelaksana' => 'PT Mitra Komputindo', 'status' => 'Direncanakan', 'maker_id' => $adminId, 'keterangan' => 'Verifikasi ketahanan runtime baterai 45 menit'],
            ['no_jadwal' => 'SCH-2026-008', 'aset_id' => $firstAsetId, 'nama_aset' => 'Chiller AC Sentral Rooftop Lantai 4', 'jenis_pemeliharaan' => 'Chemical Cleaning Evaporator', 'tanggal_rencana' => now()->addDays(18)->toDateString(), 'tanggal_realisasi' => null, 'pelaksana' => 'CV Palu Mandiri Pendingin', 'status' => 'Direncanakan', 'maker_id' => $adminId, 'keterangan' => 'Pembersihan kerak pipa sirkulasi air pendingin'],
        ];
        foreach ($jadwalPemeliharaan as $jp) {
            PmJadwalPemeliharaan::create($jp);
        }

        // 3.4 Monitoring Kondisi Fisik (8 Monitoring)
        PmMonitoringKondisi::truncate();
        $monitoringKondisi = [
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Server Dell PowerEdge R750 Data Center', 'tanggal_inspeksi' => now()->subDays(3)->toDateString(), 'kondisi' => 'Baik', 'temuan' => 'Suhu CPU 42 C, tidak ada amber light pada panel disk', 'rekomendasi' => 'Pertahankan suhu pendingin ruangan data center di rentang 18-21 C', 'petugas' => 'Rahmat Hidayat (TI)', 'status' => 'Selesai', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Genset Perkins 150 kVA Standby', 'tanggal_inspeksi' => now()->subDays(7)->toDateString(), 'kondisi' => 'Baik', 'temuan' => 'Solar terisi penuh 600 liter, tegangan aki 26.4 Volt normal', 'rekomendasi' => 'Lanjutkan jadwal pemanasan mesin tiap hari kamis', 'petugas' => 'Ilham Pratama (Pemeliharaan)', 'status' => 'Selesai', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Lift Penumpang Schindler Timur', 'tanggal_inspeksi' => now()->subDays(12)->toDateString(), 'kondisi' => 'Baik', 'temuan' => 'Gerakan pintu halus, display indikator lantai menyala normal', 'rekomendasi' => 'Pembersihan celah rel pintu lift dari kotoran debu', 'petugas' => 'Hendra Wijaya', 'status' => 'Selesai', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Mobil Dinas Pajero Sport DN 1122 SB', 'tanggal_inspeksi' => now()->subDays(18)->toDateString(), 'kondisi' => 'Rusak Ringan', 'temuan' => 'Ketebalan ban depan kanan tersisa 30%, terdapat retak rambut', 'rekomendasi' => 'Segera lakukan penggantian 2 unit ban depan Bridgestone Dueler', 'petugas' => 'Rizal Pratama (Driver)', 'status' => 'Perlu Tindak Lanjut', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'AC Floor Standing 5 PK Banking Hall', 'tanggal_inspeksi' => now()->subDays(22)->toDateString(), 'kondisi' => 'Rusak Ringan', 'temuan' => 'Air pembuangan menetes pada baki penampung dalam', 'rekomendasi' => 'Pembersihan pipa drainase pembuangan air AC yang tersumbat lumut', 'petugas' => 'Staf Umum & RT', 'status' => 'Perlu Tindak Lanjut', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Brankas Khasanah Europa Grade V', 'tanggal_inspeksi' => now()->subDays(30)->toDateString(), 'kondisi' => 'Baik', 'temuan' => 'Kombinasi kunci putar dan handle tuas berputar lancar tanpa hambatan', 'rekomendasi' => 'Pelumasan berkala engsel pintu berat dengan grease khusus', 'petugas' => 'Pengawas Kas', 'status' => 'Selesai', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Hydrant Pillar & Hose Nozzle Halaman', 'tanggal_inspeksi' => now()->subDays(40)->toDateString(), 'kondisi' => 'Baik', 'temuan' => 'Kran putar kuningan tidak berkarat, selang kanvas dalam kondisi rapi', 'rekomendasi' => 'Pengecatan ulang warna merah pelindung kotak hydrant yang mulai pudar', 'petugas' => 'Regu K3', 'status' => 'Selesai', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Portal Barrier Gate Parkir Basemen', 'tanggal_inspeksi' => now()->subDays(45)->toDateString(), 'kondisi' => 'Baik', 'temuan' => 'Sensor inframerah keselamatan anti-tabrak mobil berfungsi responsif', 'rekomendasi' => 'Pemeriksaan pegas mekanik penyeimbang palang gerbang', 'petugas' => 'Security', 'status' => 'Selesai', 'maker_id' => $adminId],
        ];
        foreach ($monitoringKondisi as $mk) {
            PmMonitoringKondisi::create($mk);
        }

        // 3.5 Pengawasan Penggunaan (6 Record)
        PmPengawasanPenggunaan::truncate();
        $pengawasan = [
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Kendaraan Dinas Toyota Zenix DN 1001 SB', 'tanggal' => now()->subDays(1)->toDateString(), 'pengguna' => 'Direksi PT Bank Sulteng', 'uraian_penggunaan' => 'Perjalanan dinas koordinasi dengan OJK dan Pemprov Sulteng', 'kesesuaian' => 'Sesuai', 'catatan' => 'Logsheet KM tercatat rapi, penggunaan BBM Pertamax Turbo sesuai pagu', 'petugas' => 'Staf Protokoler', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Aula Pertemuan Torpedo Hall Lt 3', 'tanggal' => now()->subDays(4)->toDateString(), 'pengguna' => 'Divisi Sumber Daya Manusia (SDM)', 'uraian_penggunaan' => 'Pelatihan Service Excellence dan Refreshment Teller Front Office', 'kesesuaian' => 'Sesuai', 'catatan' => 'Peralatan proyektor dan mic wireless dikembalikan lengkap', 'petugas' => 'Staf Umum', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Mobil Box Logistik Daihatsu Gran Max DN 1334 DB', 'tanggal' => now()->subDays(7)->toDateString(), 'pengguna' => 'Bagian Logistik & ATK', 'uraian_penggunaan' => 'Pengiriman berkas warkat tabungan & slip ke KC Luwuk & Poso', 'kesesuaian' => 'Sesuai', 'catatan' => 'Muatan barang tidak melebihi kapasitas tonase armada', 'petugas' => 'Staf Logistik', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Laptop Mobile Workstation ThinkPad P16s', 'tanggal' => now()->subDays(11)->toDateString(), 'pengguna' => 'Satuan Kerja Audit Intern (SKAI)', 'uraian_penggunaan' => 'Audit lapangan kelaikan operasional Kantor Cabang Tolitoli', 'kesesuaian' => 'Sesuai', 'catatan' => 'Enkripsi data BitLocker aktif sesuai standar keamanan data bank', 'petugas' => 'Security TI', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Smart Display Interaktif Rapat Lantai 3', 'tanggal' => now()->subDays(15)->toDateString(), 'pengguna' => 'Divisi Pemasaran & Bisnis', 'uraian_penggunaan' => 'Presentasi pencapaian target kredit dan funding kuartal I', 'kesesuaian' => 'Sesuai', 'catatan' => 'Kondisi layar bersih tanpa goresan pasca pemakaian', 'petugas' => 'Staf Umum', 'maker_id' => $adminId],
            ['aset_id' => $firstAsetId, 'nama_aset' => 'Mobil Double Cabin Hilux DN 1245 AB', 'tanggal' => now()->subDays(20)->toDateString(), 'pengguna' => 'Divisi Operasional & Kasir Kas', 'uraian_penggunaan' => 'Pengawalan drop kas titipan Kantor Kas Kasimbar', 'kesesuaian' => 'Sesuai', 'catatan' => 'Didampingi 2 personil Brimob bersenjata sesuai standar SOP kas', 'petugas' => 'Pengawas Kas', 'maker_id' => $adminId],
        ];
        foreach ($pengawasan as $pgw) {
            PmPengawasanPenggunaan::create($pgw);
        }

        // 3.6 Tindak Lanjut Perbaikan (8 Record)
        PmTindakLanjutPerbaikan::truncate();
        $perbaikan = [
            ['no_tindak_lanjut' => 'TLP-2026-001', 'aset_id' => $firstAsetId, 'nama_aset' => 'AC Precision Ruang Data Center', 'ticket_id' => null, 'sumber' => 'Inspeksi Mandiri', 'uraian_kerusakan' => 'Sensor humiditas mendeteksi kelembaban turun ke 35% akibat kebocoran pipa drain', 'tanggal_laporan' => now()->subDays(14)->toDateString(), 'tanggal_perbaikan' => now()->subDays(13)->toDateString(), 'teknisi' => 'Joko (CV Palu Mandiri)', 'hasil_perbaikan' => 'Penggantian solenoid valve dan setting ulang sensor kelembaban ke 50%', 'status' => 'Selesai', 'maker_id' => $adminId],
            ['no_tindak_lanjut' => 'TLP-2026-002', 'aset_id' => $firstAsetId, 'nama_aset' => 'Pintu Khasanah Khazanah Utama', 'ticket_id' => null, 'sumber' => 'Laporan Staf Kasir', 'uraian_kerusakan' => 'Engsel baja pintu terasa seret dan mengeluarkan bunyi decit saat dibuka', 'tanggal_laporan' => now()->subDays(10)->toDateString(), 'tanggal_perbaikan' => now()->subDays(9)->toDateString(), 'teknisi' => 'Supriadi (Teknisi Chubb)', 'hasil_perbaikan' => 'Pelumasan roda gigi pengunci dan penyetelan bearing beban engsel', 'status' => 'Selesai', 'maker_id' => $adminId],
            ['no_tindak_lanjut' => 'TLP-2026-003', 'aset_id' => $firstAsetId, 'nama_aset' => 'Motor Pompa Air Booster Gedung', 'ticket_id' => null, 'sumber' => 'Inspeksi Mandiri', 'uraian_kerusakan' => 'Otomatis pressure switch pompa sering mati mendadak saat tekanan puncak', 'tanggal_laporan' => now()->subDays(7)->toDateString(), 'tanggal_perbaikan' => now()->subDays(6)->toDateString(), 'teknisi' => 'Agus (Toko Teknik Abadi)', 'hasil_perbaikan' => 'Penggantian pressure switch dan kapasitor dinamo pompa', 'status' => 'Selesai', 'maker_id' => $adminId],
            ['no_tindak_lanjut' => 'TLP-2026-004', 'aset_id' => $firstAsetId, 'nama_aset' => 'PC All-in-One HP Customer Service 2', 'ticket_id' => null, 'sumber' => 'Tiket Helpdesk Layanan', 'uraian_kerusakan' => 'Keyboard wireless macet dan port USB samping tidak membaca flashdisk', 'tanggal_laporan' => now()->subDays(5)->toDateString(), 'tanggal_perbaikan' => now()->subDays(4)->toDateString(), 'teknisi' => 'Rian (Staf TI)', 'hasil_perbaikan' => 'Penggantian unit keyboard Logitech baru dan re-install driver USB', 'status' => 'Selesai', 'maker_id' => $adminId],
            ['no_tindak_lanjut' => 'TLP-2026-005', 'aset_id' => $firstAsetId, 'nama_aset' => 'Mobil Dinas Pajero Sport DN 1122 SB', 'ticket_id' => null, 'sumber' => 'Inspeksi Mandiri', 'uraian_kerusakan' => 'Ban depan kanan mengalami retak rambut dan aus tidak merata', 'tanggal_laporan' => now()->subDays(3)->toDateString(), 'tanggal_perbaikan' => now()->subDays(2)->toDateString(), 'teknisi' => 'Mekanik Nasmoco Palu', 'hasil_perbaikan' => 'Pemasangan 2 ban baru Bridgestone Dueler dan spooring 3D roda depan', 'status' => 'Selesai', 'maker_id' => $adminId],
            ['no_tindak_lanjut' => 'TLP-2026-006', 'aset_id' => $firstAsetId, 'nama_aset' => 'AC Floor Standing 5 PK Banking Hall', 'ticket_id' => null, 'sumber' => 'Laporan Staf CS', 'uraian_kerusakan' => 'Air buangan kondensasi meluap ke karpet ruang tunggu nasabah', 'tanggal_laporan' => now()->subDays(2)->toDateString(), 'tanggal_perbaikan' => null, 'teknisi' => 'CV Palu Mandiri Pendingin', 'hasil_perbaikan' => 'Sedang dilakukan pembersihan jalur pipa pembuangan dan pembersihan lumut', 'status' => 'Dalam Proses', 'maker_id' => $adminId],
            ['no_tindak_lanjut' => 'TLP-2026-007', 'aset_id' => $firstAsetId, 'nama_aset' => 'Lift Penumpang Schindler 2 (Timur)', 'ticket_id' => null, 'sumber' => 'Inspeksi Mandiri', 'uraian_kerusakan' => 'Lampu indikator tombol lantai 3 berkedip redup saat ditekan', 'tanggal_laporan' => now()->subDays(1)->toDateString(), 'tanggal_perbaikan' => null, 'teknisi' => 'Teknisi Schindler', 'hasil_perbaikan' => 'Menunggu pengiriman modul tombol pengganti dari Makassar', 'status' => 'Dilaporkan', 'maker_id' => $adminId],
            ['no_tindak_lanjut' => 'TLP-2026-008', 'aset_id' => $firstAsetId, 'nama_aset' => 'Mesin Penghancur Kertas Shredder Sekper', 'ticket_id' => null, 'sumber' => 'Laporan Staf Sekper', 'uraian_kerusakan' => 'Mata pisau pencacah tersangkut paper clip dan macet total', 'tanggal_laporan' => now()->toDateString(), 'tanggal_perbaikan' => null, 'teknisi' => 'Staf Pemeliharaan Umum', 'hasil_perbaikan' => 'Unit telah diambil untuk pembersihan manual dan pelumasan oli mesin', 'status' => 'Dalam Proses', 'maker_id' => $adminId],
        ];
        foreach ($perbaikan as $pbk) {
            PmTindakLanjutPerbaikan::create($pbk);
        }

        $this->command->info('OperationalModulesDummySeeder berhasil mengisi seluruh data dummy untuk ketiga Bagian Divisi Umum.');
    }
}
