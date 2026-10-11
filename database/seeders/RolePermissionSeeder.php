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
            ['id' => 19, 'nama' => 'bagian_umum', 'label' => 'Bagian Umum & Rumah Tangga', 'deskripsi' => 'Pengelolaan operasional umum, rumah tangga, kearsipan persuratan, dan layanan ticketing bagian umum'],
            ['id' => 20, 'nama' => 'bagian_aset', 'label' => 'Bagian Aset/Inventaris & Logistik', 'deskripsi' => 'Pengelolaan inventarisasi aset, logistik, pengadaan sewa, mutasi aset, dan layanan ticketing bagian aset'],
            ['id' => 21, 'nama' => 'bagian_pengadaan', 'label' => 'Bagian Pengadaan serta Pemeliharaan Aset dan Inventaris', 'deskripsi' => 'Pengelolaan pengadaan barang/jasa, pemeliharaan sarana/prasarana operasional, dan layanan ticketing bagian pengadaan'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['id' => $r['id']], $r);
        }

        $perms = [
            [1, 'audit_log', 1],
            [1, 'config_field_aset', 1],
            [1, 'config_ticket', 1],
            [1, 'dashboard', 1],
            [1, 'mutasi_aset', 0],
            [1, 'panduan', 0],
            [1, 'ref_akun', 0],
            [1, 'risalah', 0],
            [1, 'role_mgmt', 1],
            [1, 'ticketing', 1],
            [1, 'user_mgmt', 1],
            [2, 'dashboard', 0],
            [2, 'mutasi_aset', 0],
            [2, 'panduan', 0],
            [2, 'ref_akun', 0],
            [2, 'risalah', 0],
            [2, 'ticketing', 0],
            [6, 'dashboard', 1],
            [6, 'mutasi_aset', 1],
            [6, 'ticketing', 1],
            [7, 'config_field_aset', 1],
            [7, 'config_ticket', 1],
            [7, 'dashboard', 1],
            [7, 'mutasi_aset', 1],
            [7, 'panduan', 1],
            [7, 'ref_akun', 1],
            [7, 'risalah', 1],
            [7, 'ticketing', 1],
            [19, 'dashboard', 1],
            [19, 'dokumen_arsip', 1],
            [19, 'panduan', 1],
            [19, 'ref_akun', 1],
            [19, 'risalah', 1],
            [19, 'ticketing', 1],
            [19, 'umum_rt', 1],
            [20, 'administrasi_aset', 1],
            [20, 'config_field_aset', 1],
            [20, 'dashboard', 1],
            [20, 'logistik_pelaporan', 1],
            [20, 'mutasi_aset', 1],
            [20, 'panduan', 1],
            [20, 'ref_akun', 1],
            [20, 'risalah', 1],
            [20, 'ticketing', 1],
            [21, 'dashboard', 1],
            [21, 'panduan', 1],
            [21, 'pemeliharaan_pengawasan', 1],
            [21, 'pengadaan', 1],
            [21, 'ref_akun', 1],
            [21, 'risalah', 1],
            [21, 'ticketing', 1],
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
