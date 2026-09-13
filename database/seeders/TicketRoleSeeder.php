<?php

namespace Database\Seeders;

use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\TicketCategory;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TicketRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Departemen Internal Sesuai Struktur Organisasi Sebenarnya
        $departments = [
            ['id' => 1, 'name' => 'Bagian Umum & Rumah Tangga'],
            ['id' => 2, 'name' => 'Bagian Aset/Inventaris & Logistik'],
            ['id' => 3, 'name' => 'Bagian Pengadaan & Pemeliharaan Aset & Inventaris'],
        ];

        foreach ($departments as $dept) {
            InternalDepartment::updateOrCreate(['id' => $dept['id']], $dept);
        }

        // 2. Unit Kerja (Fase 1)
        $unitKerja = [
            ['id' => 1, 'department_id' => 1, 'kode' => 'UK-URT', 'nama' => 'Unit Kerja Umum & Rumah Tangga',               'deskripsi' => 'Mengelola kendaraan operasional, biaya harian, fasilitas kantor, pemeliharaan gedung, kebersihan, keamanan, dan K3.', 'is_active' => true],
            ['id' => 2, 'department_id' => 1, 'kode' => 'UK-DOK', 'nama' => 'Unit Kerja Pengelolaan Dokumen & Kearsipan',    'deskripsi' => 'Mengelola surat masuk/keluar, memo, arsip dokumen fisik & digital, dan dokumen legalitas.', 'is_active' => true],
        ];

        foreach ($unitKerja as $uk) {
            UnitKerja::updateOrCreate(['id' => $uk['id']], $uk);
        }

        // 3. Kategori Layanan Tiket (Fase 1 — sesuai unit kerja)
        $categories = [
            // UK-URT
            ['id' => 1, 'name' => 'Permintaan Sarana & Prasarana',   'default_sla_hours' => 24],
            ['id' => 2, 'name' => 'Layanan Umum & Kebersihan',        'default_sla_hours' => 24],
            ['id' => 3, 'name' => 'Keamanan & K3',                    'default_sla_hours' => 12],
            ['id' => 4, 'name' => 'Pemeliharaan Gedung & Utilitas',   'default_sla_hours' => 48],
            // UK-DOK
            ['id' => 5, 'name' => 'Pengelolaan Dokumen & Arsip',      'default_sla_hours' => 24],
            // General (multi-unit)
            ['id' => 6, 'name' => 'Pengadaan Barang & Jasa',          'default_sla_hours' => 72],
            ['id' => 7, 'name' => 'Perbaikan & Pemeliharaan Aset',    'default_sla_hours' => 48],
        ];

        foreach ($categories as $cat) {
            TicketCategory::updateOrCreate(['id' => $cat['id']], $cat);
        }

        // 4. Bersihkan role generik 'bagian_internal' & user dummy jika ada
        DB::table('role_permissions')->where('role_id', 8)->delete();
        User::whereIn('username', ['staf_internal_umum', 'staf_internal_aset'])->delete();
        Role::where('id', 8)->orWhere('nama', 'bagian_internal')->delete();

        // 5. Pastikan Role Pemohon (User) dan Operator Tersedia
        $roles = [
            ['id' => 6, 'nama' => 'user',     'label' => 'User',     'deskripsi' => 'User / Pemohon tiket layanan'],
            ['id' => 7, 'nama' => 'operator', 'label' => 'Operator', 'deskripsi' => 'Operator / Helpdesk penerima dan verifikator tiket'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['id' => $r['id']], $r);
        }

        // 6. Matriks Permission Ticketing untuk Semua Role Terkait
        $perms = [
            [1,  'ticketing', 1],
            [2,  'ticketing', 0],
            [6,  'ticketing', 1],
            [7,  'ticketing', 1],
            [10, 'ticketing', 1],
            [11, 'ticketing', 1],
            [12, 'ticketing', 1],
            [13, 'ticketing', 1],
            [14, 'ticketing', 1],
            [15, 'ticketing', 1],
            [16, 'ticketing', 1],
            // Fase 3
            [17, 'ticketing', 1],
            [18, 'ticketing', 1],
        ];

        foreach ($perms as [$roleId, $key, $write]) {
            if (Role::where('id', $roleId)->exists()) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'perm_key' => $key],
                    ['can_write' => $write]
                );
            }
        }

        // 7. Hubungkan User Eksisting ke Departemen Masing-Masing (jika belum)
        User::where('username', 'umum')->update(['department_id' => 1]);
        User::where('username', 'aset')->update(['department_id' => 2]);
        User::where('username', 'pengadaan')->update(['department_id' => 3]);

        // 8. Akun Pemohon & Operator (fallback jika belum ada dari UserSeeder)
        $additionalUsers = [
            [
                'username'     => 'pemohon_user',
                'nama_lengkap' => 'Staff Pemohon Layanan',
                'email'        => 'pemohon@banksulteng.co.id',
                'jabatan'      => 'Staff Cabang',
                'bagian'       => 'Cabang Utama',
                'department_id'=> null,
                'unit_kerja_id'=> null,
                'role_id'      => 6,
            ],
            [
                'username'     => 'operator_helpdesk',
                'nama_lengkap' => 'Operator Helpdesk',
                'email'        => 'operator@banksulteng.co.id',
                'jabatan'      => 'Helpdesk Operator',
                'bagian'       => 'Divisi Umum',
                'department_id'=> null,
                'unit_kerja_id'=> null,
                'role_id'      => 7,
            ],
        ];

        foreach ($additionalUsers as $u) {
            $plain = $u['username'] . '2026';
            User::updateOrCreate(
                ['username' => $u['username']],
                [...$u, 'password' => Hash::make($plain), 'must_change_pwd' => false, 'is_active' => true]
            );
        }
    }
}
