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
            ['username' => 'admin', 'nama_lengkap' => 'Admin', 'email' => 'admin@banksulteng.co.id', 'jabatan' => 'Administrator IT', 'bagian' => 'Divisi Umum', 'role_id' => 1, 'department_id' => null],
            ['username' => 'pimpinan', 'nama_lengkap' => 'Pimpinan Divisi', 'email' => 'pimpinan@banksulteng.co.id', 'jabatan' => 'Pimpinan Divisi', 'bagian' => 'Divisi Umum', 'role_id' => 2, 'department_id' => null],
            ['username' => 'umum', 'nama_lengkap' => 'Staf Umum & Rumah Tangga', 'email' => 'umum@banksulteng.co.id', 'jabatan' => 'Staf Umum & RT', 'bagian' => 'Bagian Umum & Rumah Tangga', 'role_id' => 3, 'department_id' => 1],
            ['username' => 'aset', 'nama_lengkap' => 'Staf Aset/Inventaris & Logistik', 'email' => 'aset@banksulteng.co.id', 'jabatan' => 'Staf Aset & Logistik', 'bagian' => 'Bagian Aset/Inventaris & Logistik', 'role_id' => 4, 'department_id' => 2],
            ['username' => 'pengadaan', 'nama_lengkap' => 'Staf Pengadaan serta Pemeliharaan Aset dan Inventaris', 'email' => 'pengadaan@banksulteng.co.id', 'jabatan' => 'Staf Pengadaan & Pemeliharaan', 'bagian' => 'Bagian Pengadaan & Pemeliharaan Aset & Inventaris', 'role_id' => 5, 'department_id' => 3],
            ['username' => 'operator', 'nama_lengkap' => 'Operator', 'email' => 'operator@banksulteng.co.id', 'jabatan' => 'Operator Helpdesk', 'bagian' => 'Divisi Umum', 'role_id' => 7, 'department_id' => null],
            ['username' => 'kabag umum', 'nama_lengkap' => 'Kepala Bagian Umum & Rumah Tangga', 'email' => 'kabagumum@banksulteng.co.id', 'jabatan' => 'Kepala bagian', 'bagian' => 'Kantor Pusat', 'role_id' => 10, 'department_id' => null],
            ['username' => 'kabag aset', 'nama_lengkap' => 'Kepala Bagian Aset/Inventaris & Logistik', 'email' => 'kabagaset@banksulteng.co.id', 'jabatan' => 'Kepala bagian', 'bagian' => 'Kantor Pusat', 'role_id' => 11, 'department_id' => null],
            ['username' => 'kabag pengadaan', 'nama_lengkap' => 'Kepala Bagian Pengadaan serta Pemeliharaan Aset dan Inventaris', 'email' => 'kabagpengadaan@banksulteng.co.id', 'jabatan' => 'Kepala bagian', 'bagian' => 'Kantor Pusat', 'role_id' => 12, 'department_id' => null],
        ];

        // Role Multi-User (Pemohon Layanan Cabang & Divisi)
        $multiRoleUsers = [
            ['username' => 'user', 'nama_lengkap' => 'Staf Pemohon Layanan', 'email' => 'pemohon@banksulteng.co.id', 'jabatan' => 'Staff Operasional Cabang', 'bagian' => 'Kantor Cabang Utama', 'role_id' => 6, 'department_id' => null],
            ['username' => 'cabang', 'nama_lengkap' => 'Staf Cabang Tawaeli', 'email' => 'cabang.palu@banksulteng.co.id', 'jabatan' => 'Staff Customer Service', 'bagian' => 'Kantor Cabang Palu', 'role_id' => 6, 'department_id' => null],
            ['username' => 'kantor pusat', 'nama_lengkap' => 'Staf Divisi SDM', 'email' => 'sdm.pemohon@banksulteng.co.id', 'jabatan' => 'Staff Personalia', 'bagian' => 'Divisi SDM & Umum', 'role_id' => 6, 'department_id' => null],
        ];

        foreach (array_merge($singleRoleUsers, $multiRoleUsers) as $u) {
            $plain = $u['username'] . '2026';
            User::updateOrCreate(
                ['username' => $u['username']],
                [...$u, 'password' => Hash::make($plain), 'must_change_pwd' => false, 'is_active' => true]
            );
        }
    }
}
