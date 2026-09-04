<?php

namespace Database\Seeders;

use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\TicketCategory;
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

        // 2. Kategori Layanan Tiket
        $categories = [
            ['id' => 1, 'name' => 'Permintaan Sarana & Prasarana', 'default_sla_hours' => 24],
            ['id' => 2, 'name' => 'Perbaikan & Pemeliharaan Aset', 'default_sla_hours' => 48],
            ['id' => 3, 'name' => 'Pengadaan Barang & Jasa', 'default_sla_hours' => 72],
            ['id' => 4, 'name' => 'Layanan & Dukungan Operasional', 'default_sla_hours' => 24],
        ];

        foreach ($categories as $cat) {
            TicketCategory::updateOrCreate(['id' => $cat['id']], $cat);
        }

        // 3. Bersihkan role generik 'bagian_internal' & user dummy jika ada
        DB::table('role_permissions')->where('role_id', 8)->delete();
        User::whereIn('username', ['staf_internal_umum', 'staf_internal_aset'])->delete();
        Role::where('id', 8)->orWhere('nama', 'bagian_internal')->delete();

        // 4. Pastikan Role Pemohon (User) dan Operator Tersedia
        $roles = [
            ['id' => 6, 'nama' => 'user', 'label' => 'User', 'deskripsi' => 'User / Pemohon tiket layanan'],
            ['id' => 7, 'nama' => 'operator', 'label' => 'Operator', 'deskripsi' => 'Operator / Helpdesk penerima dan verifikator tiket'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['id' => $r['id']], $r);
        }

        // 5. Matriks Permission Ticketing untuk Semua Role Terkait
        $perms = [
            // Superadmin (1): Full Access
            [1, 'ticketing', 1],
            // Pimpinan Divisi (2): Read-Only Monitoring
            [2, 'ticketing', 0],
            // Staf Umum & Rumah Tangga (3): Internal Actor
            [3, 'ticketing', 1],
            // Staf Aset & Logistik (4): Internal Actor
            [4, 'ticketing', 1],
            // Staf Pengadaan (5): Internal Actor
            [5, 'ticketing', 1],
            // User / Pemohon (6): Create & View Own
            [6, 'ticketing', 1],
            // Operator / Helpdesk (7): Verification & Allocation
            [7, 'ticketing', 1],
        ];

        foreach ($perms as [$roleId, $key, $write]) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $roleId, 'perm_key' => $key],
                ['can_write' => $write]
            );
        }

        // 6. Hubungkan User Eksisting ke Departemen Masing-Masing
        User::where('username', 'umum')->update(['department_id' => 1]);
        User::where('username', 'aset')->update(['department_id' => 2]);
        User::where('username', 'pengadaan')->update(['department_id' => 3]);

        // 7. Akun Pemohon & Operator
        $additionalUsers = [
            [
                'username' => 'pemohon_user',
                'nama_lengkap' => 'Staff Pemohon Layanan',
                'email' => 'pemohon@banksulteng.co.id',
                'jabatan' => 'Staff Cabang',
                'bagian' => 'Cabang Utama',
                'department_id' => null,
                'role_id' => 6,
            ],
            [
                'username' => 'operator_helpdesk',
                'nama_lengkap' => 'Operator Helpdesk',
                'email' => 'operator@banksulteng.co.id',
                'jabatan' => 'Helpdesk Operator',
                'bagian' => 'Divisi Umum',
                'department_id' => null,
                'role_id' => 7,
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
