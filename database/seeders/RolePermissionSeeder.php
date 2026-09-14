<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    // File ini otomatis disinkronisasi saat Admin melakukan perubahan hak akses lewat website
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'nama' => 'admin', 'label' => 'Admin', 'deskripsi' => 'Administrator sistem dengan akses manajemen pengguna, peran & izin, audit log, dan supervisi tiket'],
            ['id' => 2, 'nama' => 'pimpinan', 'label' => 'Pimpinan Divisi', 'deskripsi' => 'Pimpinan Divisi dengan hak monitoring seluruh operasional, analitik data, dan tindak lanjut'],
            ['id' => 6, 'nama' => 'user', 'label' => 'User', 'deskripsi' => 'User / Pemohon tiket layanan'],
            ['id' => 7, 'nama' => 'operator', 'label' => 'Operator', 'deskripsi' => 'Operator / Helpdesk penerima dan verifikator tiket'],
            ['id' => 10, 'nama' => 'kabag_umum', 'label' => 'Kepala Bagian Umum & Rumah Tangga', 'deskripsi' => 'Mengecek dan menindaklanjuti permintaan sebelum di serahkan ke staf'],
            ['id' => 11, 'nama' => 'kabag_aset', 'label' => 'Kepala Bagian Aset/Inventaris & Logistik', 'deskripsi' => 'Mengecek dan menindaklanjuti permintaan sebelum di serahkan ke staf'],
            ['id' => 12, 'nama' => 'kabag_pengadaan', 'label' => 'Kepala Bagian Pengadaan serta Pemeliharaan Aset dan Inventaris', 'deskripsi' => 'Mengecek dan menindaklanjuti permintaan sebelum di serahkan ke staf'],
            ['id' => 13, 'nama' => 'uk_umum_rt', 'label' => 'Staf Unit Kerja Umum & Rumah Tangga', 'deskripsi' => 'Staf Unit Kerja Umum & RT — akses modul operasional internal + ticketing'],
            ['id' => 14, 'nama' => 'uk_dokumen', 'label' => 'Staf Unit Kerja Pengelolaan Dokumen & Kearsipan', 'deskripsi' => 'Staf Unit Kerja Dokumen & Kearsipan — akses arsip surat, memo, dokumen, dan ticketing'],
            ['id' => 15, 'nama' => 'uk_administrasi_aset', 'label' => 'Staf Unit Kerja Administrasi Aset & Inventaris', 'deskripsi' => 'Staf Unit Kerja Administrasi Aset & Inventaris — akses inventarisasi, mutasi, disposal, amortisasi, rekonsiliasi, dan ticketing'],
            ['id' => 16, 'nama' => 'uk_logistik', 'label' => 'Staf Unit Kerja Logistik & Pelaporan', 'deskripsi' => 'Staf Unit Kerja Logistik & Pelaporan — akses invoice sewa, PKS, penerimaan & distribusi barang, pembayaran tagihan, dan ticketing'],
            // Fase 3 — Unit Kerja Bagian Pengadaan & Pemeliharaan
            ['id' => 17, 'nama' => 'uk_pengadaan', 'label' => 'Staf Unit Kerja Pengadaan Aset & Inventaris', 'deskripsi' => 'Staf Unit Kerja Pengadaan — akses modul pengadaan internal (memo, penawaran, negosiasi, draft, SPK, reminder, perencanaan) dan ticketing'],
            ['id' => 18, 'nama' => 'uk_pemeliharaan', 'label' => 'Staf Unit Kerja Pemeliharaan & Pengawasan Aset/Inventaris', 'deskripsi' => 'Staf Unit Kerja Pemeliharaan — akses modul pemeliharaan & pengawasan (jadwal, monitoring kondisi, pengawasan penggunaan, tindak lanjut) dan ticketing'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['id' => $r['id']], $r);
        }

        $perms = [
            [1, 'audit_log', 1],
            [1, 'dashboard', 1],
            [1, 'panduan', 0],
            [1, 'ref_akun', 0],
            [1, 'risalah', 0],
            [1, 'role_mgmt', 1],
            [1, 'ticketing', 1],
            [1, 'user_mgmt', 1],
            [2, 'administrasi_aset', 1],
            [2, 'dashboard', 0],
            [2, 'dokumen_arsip', 0],
            [2, 'logistik_pelaporan', 1],
            [2, 'panduan', 0],
            [2, 'pengadaan', 1],
            [2, 'ref_akun', 0],
            [2, 'risalah', 0],
            [2, 'ticketing', 0],
            [2, 'umum_rt', 1],
            [6, 'dashboard', 1],
            [6, 'ticketing', 1],
            [7, 'dashboard', 1],
            [7, 'panduan', 1],
            [7, 'ref_akun', 1],
            [7, 'risalah', 1],
            [7, 'ticketing', 1],
            [10, 'dashboard', 1],
            [10, 'dokumen_arsip', 1],
            [10, 'panduan', 1],
            [10, 'ref_akun', 1],
            [10, 'risalah', 1],
            [10, 'ticketing', 1],
            [10, 'umum_rt', 1],
            [11, 'administrasi_aset', 1],
            [11, 'dashboard', 1],
            [11, 'logistik_pelaporan', 1],
            [11, 'panduan', 1],
            [11, 'ref_akun', 1],
            [11, 'risalah', 1],
            [11, 'ticketing', 1],
            [12, 'dashboard', 1],
            [12, 'panduan', 1],
            [12, 'pengadaan', 1],
            [12, 'pemeliharaan_pengawasan', 1], // kabag akses semua sub-modul bagiannya
            [12, 'ref_akun', 1],
            [12, 'risalah', 1],
            [12, 'ticketing', 1],
            [13, 'dashboard', 1],
            [13, 'panduan', 1],
            [13, 'ref_akun', 1],
            [13, 'risalah', 1],
            [13, 'ticketing', 1],
            [13, 'umum_rt', 1],
            [14, 'dashboard', 1],
            [14, 'dokumen_arsip', 1],
            [14, 'panduan', 1],
            [14, 'ref_akun', 1],
            [14, 'risalah', 1],
            [14, 'ticketing', 1],
            [15, 'administrasi_aset', 1],
            [15, 'dashboard', 1],
            [15, 'panduan', 1],
            [15, 'ref_akun', 1],
            [15, 'risalah', 1],
            [15, 'ticketing', 1],
            [16, 'dashboard', 1],
            [16, 'logistik_pelaporan', 1],
            [16, 'panduan', 1],
            [16, 'ref_akun', 1],
            [16, 'risalah', 1],
            [16, 'ticketing', 1],
            // Fase 3 — uk_pengadaan (id=17)
            [17, 'dashboard', 1],
            [17, 'panduan', 1],
            [17, 'pengadaan', 1],
            [17, 'ref_akun', 1],
            [17, 'risalah', 1],
            [17, 'ticketing', 1],
            // Fase 3 — uk_pemeliharaan (id=18)
            [18, 'dashboard', 1],
            [18, 'panduan', 1],
            [18, 'pemeliharaan_pengawasan', 1],
            [18, 'ref_akun', 1],
            [18, 'risalah', 1],
            [18, 'ticketing', 1],
        ];

        DB::table('role_permissions')->truncate();
        foreach ($perms as [$roleId, $key, $write]) {
            DB::table('role_permissions')->insert([
                'role_id' => $roleId,
                'perm_key' => $key,
                'can_write' => $write,
            ]);
        }
    }
}
