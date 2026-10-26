<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    // File ini otomatis disinkronisasi saat Admin melakukan perubahan pengguna lewat website
    public function run(): void
    {
        // Role Akun Tunggal
        $singleRoleUsers = [
            ['username' => 'admin_test2', 'nama_lengkap' => 'Admin Test 2', 'email' => '', 'jabatan' => '', 'bagian' => '', 'role_id' => 4, 'department_id' => null],
        ];

        // Role Multi-User (Pemohon Cabang & Divisi)
        $multiRoleUsers = [
            ['username' => 'staf_cabang_baru', 'nama_lengkap' => 'Staf Baru', 'email' => 'baru@banksulteng.co.id', 'jabatan' => 'Staff Cabang', 'bagian' => 'Cabang Baru', 'role_id' => 5, 'department_id' => null],
        ];

        foreach (array_merge($singleRoleUsers, $multiRoleUsers) as $u) {
            $plain = $u['username'] . '2026';
            $existing = User::where('username', $u['username'])->first();
            if ($existing) {
                // Hanya update data profil, JANGAN timpa password & must_change_pwd
                $existing->update([...$u, 'is_active' => true]);
            } else {
                // User baru: set password default + wajib ganti
                User::create([...$u, 'password' => Hash::make($plain), 'must_change_pwd' => true, 'is_active' => true]);
            }
        }
    }
}
