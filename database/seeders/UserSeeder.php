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
            ['username' => 'staf 1', 'nama_lengkap' => 'Dirli', 'email' => 'umum@banksulteng.co.id', 'jabatan' => 'Staf Umum & RT', 'bagian' => 'Bagian Umum & Rumah Tangga', 'role_id' => 3, 'department_id' => 1],
            ['username' => 'staf 2', 'nama_lengkap' => 'Marsya', 'email' => 'umum@banksulteng.co.id', 'jabatan' => 'Staf Umum & RT', 'bagian' => 'Bagian Umum & Rumah Tangga', 'role_id' => 3, 'department_id' => 1],
            ['username' => 'staf 3', 'nama_lengkap' => 'Zahra', 'email' => 'umum@banksulteng.co.id', 'jabatan' => 'Staf Umum & RT', 'bagian' => 'Bagian Umum & Rumah Tangga', 'role_id' => 3, 'department_id' => 1],
            ['username' => 'staf 5', 'nama_lengkap' => 'Marsya', 'email' => 'aset@banksulteng.co.id', 'jabatan' => 'Staf Aset & Logistik', 'bagian' => 'Bagian Aset/Inventaris & Logistik', 'role_id' => 4, 'department_id' => 2],
            ['username' => 'staf 4', 'nama_lengkap' => 'Dirli', 'email' => 'aset@banksulteng.co.id', 'jabatan' => 'Staf Aset/Inventaris & Logistik', 'bagian' => 'Bagian Aset/Inventaris & Logistik', 'role_id' => 4, 'department_id' => 2],
            ['username' => 'staf 6', 'nama_lengkap' => 'Zahra', 'email' => 'aset@banksulteng.co.id', 'jabatan' => 'Staf Aset/Inventaris & Logistik', 'bagian' => 'Bagian Aset/Inventaris & Logistik', 'role_id' => 4, 'department_id' => 2],
            ['username' => 'staf 7', 'nama_lengkap' => 'Dirli', 'email' => 'pengadaan@banksulteng.co.id', 'jabatan' => 'Staf Pengadaan & Pemeliharaan', 'bagian' => 'Bagian Pengadaan & Pemeliharaan Aset & Inventaris', 'role_id' => 5, 'department_id' => 3],
            ['username' => 'staf 8', 'nama_lengkap' => 'Marsya', 'email' => 'pengadaan@banksulteng.co.id', 'jabatan' => 'staf Pengadaan serta Pemeliharaan Aset dan Inventaris', 'bagian' => 'Bagian Pengadaan & Pemeliharaan Aset & Inventaris', 'role_id' => 5, 'department_id' => 3],
            ['username' => 'staf 9', 'nama_lengkap' => 'Zahra', 'email' => 'pengadaan@banksulteng.co.id', 'jabatan' => 'staf Pengadaan serta Pemeliharaan Aset dan Inventaris', 'bagian' => 'Bagian Pengadaan & Pemeliharaan Aset & Inventaris', 'role_id' => 5, 'department_id' => 3],
            ['username' => 'operator', 'nama_lengkap' => 'Operator', 'email' => 'operator@banksulteng.co.id', 'jabatan' => 'Operator Helpdesk', 'bagian' => 'Divisi Umum', 'role_id' => 7, 'department_id' => null],
            ['username' => 'kabag umum', 'nama_lengkap' => 'Kepala Bagian Umum & Rumah Tangga', 'email' => 'kabagumum@banksulteng.co.id', 'jabatan' => 'Kepala bagian', 'bagian' => 'Kantor Pusat', 'role_id' => 10, 'department_id' => 1],
            ['username' => 'kabag aset', 'nama_lengkap' => 'Kepala Bagian Aset/Inventaris & Logistik', 'email' => 'kabagaset@banksulteng.co.id', 'jabatan' => 'Kepala bagian', 'bagian' => 'Kantor Pusat', 'role_id' => 11, 'department_id' => 2],
            ['username' => 'kabag pengadaan', 'nama_lengkap' => 'Kepala Bagian Pengadaan serta Pemeliharaan Aset dan Inventaris', 'email' => 'kabagpengadaan@banksulteng.co.id', 'jabatan' => 'Kepala bagian', 'bagian' => 'Kantor Pusat', 'role_id' => 12, 'department_id' => 3],
        ];

        // Role Multi-User (Pemohon Layanan Cabang & Divisi)
        $multiRoleUsers = [
            ['username' => 'cabang tawaeli', 'nama_lengkap' => 'Staf Cabang Tawaeli', 'email' => 'cabang.palu@banksulteng.co.id', 'jabatan' => 'Staff Customer Service', 'bagian' => 'Kantor Cabang Palu', 'role_id' => 6, 'department_id' => null],
            ['username' => 'Reza', 'nama_lengkap' => 'Reza Gilang Kenanza', 'email' => 'ukksiber@banksulteng.co.id', 'jabatan' => 'Pemimpin Unit Kerja Analis Ketahanan &  Keamanan Siber', 'bagian' => 'Divisi Unit Khusus Keamanan Siber Kantor Pusat', 'role_id' => 6, 'department_id' => null],
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
