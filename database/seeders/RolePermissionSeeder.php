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
            ['id' => 3, 'nama' => 'umum_rt', 'label' => 'Staf Umum & Rumah Tangga', 'deskripsi' => 'Bagian Umum & Rumah Tangga (kendaraan operasional, biaya BBM/RT, dan permintaan ATK)'],
            ['id' => 4, 'nama' => 'aset', 'label' => 'Staf Aset/Inventaris & Logistik', 'deskripsi' => 'Bagian Aset/Inventaris & Logistik (data aset, invoice sewa, amortisasi, dan PKS)'],
            ['id' => 5, 'nama' => 'pengadaan', 'label' => 'Staf Pengadaan serta Pemeliharaan Aset dan Inventaris', 'deskripsi' => 'Bagian Pengadaan & Pemeliharaan (memo internal, penawaran vendor, SPK, dan reminder)'],
            ['id' => 6, 'nama' => 'user', 'label' => 'User', 'deskripsi' => 'User / Pemohon tiket layanan'],
            ['id' => 7, 'nama' => 'operator', 'label' => 'Operator', 'deskripsi' => 'Operator / Helpdesk penerima dan verifikator tiket'],
            ['id' => 10, 'nama' => 'kabag_umum', 'label' => 'Kepala Bagian Umum & Rumah Tangga', 'deskripsi' => 'Mengecek dan menindaklanjuti  permintaan sebelum di serahkan ke staf'],
            ['id' => 11, 'nama' => 'kabag_aset', 'label' => 'Kepala Bagian Aset/Inventaris & Logistik', 'deskripsi' => 'Mengecek dan menindaklanjuti permintaan sebelum di serahkan ke staf'],
            ['id' => 12, 'nama' => 'kabag_pengadaan', 'label' => 'Kepala Bagian Pengadaan serta Pemeliharaan Aset dan Inventaris', 'deskripsi' => 'Mengecek dan menindaklanjuti permintaan sebelum di serahkan ke staf'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['id' => $r['id']], $r);
        }

        $perms = [
            [1, 'aset_logistik', 1],
            [1, 'audit_log', 1],
            [1, 'dashboard', 1],
            [1, 'pengadaan', 1],
            [1, 'role_mgmt', 1],
            [1, 'ticketing', 1],
            [1, 'umum_rt', 1],
            [1, 'user_mgmt', 1],
            [2, 'aset_logistik', 1],
            [2, 'dashboard', 0],
            [2, 'panduan', 0],
            [2, 'pengadaan', 1],
            [2, 'ref_akun', 0],
            [2, 'risalah', 0],
            [2, 'ticketing', 0],
            [2, 'umum_rt', 1],
            [3, 'dashboard', 1],
            [3, 'panduan', 0],
            [3, 'ref_akun', 0],
            [3, 'risalah', 1],
            [3, 'ticketing', 1],
            [3, 'umum_rt', 1],
            [4, 'aset_logistik', 1],
            [4, 'dashboard', 1],
            [4, 'panduan', 0],
            [4, 'ref_akun', 0],
            [4, 'risalah', 1],
            [4, 'ticketing', 1],
            [5, 'dashboard', 1],
            [5, 'panduan', 0],
            [5, 'pengadaan', 1],
            [5, 'ref_akun', 0],
            [5, 'risalah', 1],
            [5, 'ticketing', 1],
            [6, 'dashboard', 1],
            [6, 'ticketing', 1],
            [7, 'dashboard', 1],
            [7, 'panduan', 1],
            [7, 'ref_akun', 1],
            [7, 'risalah', 1],
            [7, 'ticketing', 1],
            [10, 'dashboard', 1],
            [10, 'panduan', 1],
            [10, 'ref_akun', 1],
            [10, 'risalah', 1],
            [10, 'ticketing', 1],
            [10, 'umum_rt', 1],
            [11, 'aset_logistik', 1],
            [11, 'dashboard', 1],
            [11, 'panduan', 1],
            [11, 'ref_akun', 1],
            [11, 'risalah', 1],
            [11, 'ticketing', 1],
            [12, 'dashboard', 1],
            [12, 'panduan', 1],
            [12, 'pengadaan', 1],
            [12, 'ref_akun', 1],
            [12, 'risalah', 1],
            [12, 'ticketing', 1],
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
