<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 6 Role Akun Tunggal (1 Role = 1 Akun User)
        $singleRoleUsers = [
            [
                'username' => 'admin',
                'nama_lengkap' => 'Admin',
                'email' => 'admin@banksulteng.co.id',
                'jabatan' => 'Administrator IT',
                'bagian' => 'Divisi Umum',
                'role_id' => 1,
                'department_id' => null,
            ],
            [
                'username' => 'pimpinan',
                'nama_lengkap' => 'Pimpinan Divisi',
                'email' => 'pimpinan@banksulteng.co.id',
                'jabatan' => 'Pimpinan Divisi',
                'bagian' => 'Divisi Umum',
                'role_id' => 2,
                'department_id' => null,
            ],
            [
                'username' => 'umum',
                'nama_lengkap' => 'Staf Umum & Rumah Tangga',
                'email' => 'umum@banksulteng.co.id',
                'jabatan' => 'Staf Umum & RT',
                'bagian' => 'Bagian Umum & Rumah Tangga',
                'role_id' => 3,
                'department_id' => 1,
            ],
            [
                'username' => 'aset',
                'nama_lengkap' => 'Staf Aset/Inventaris & Logistik',
                'email' => 'aset@banksulteng.co.id',
                'jabatan' => 'Staf Aset & Logistik',
                'bagian' => 'Bagian Aset/Inventaris & Logistik',
                'role_id' => 4,
                'department_id' => 2,
            ],
            [
                'username' => 'pengadaan',
                'nama_lengkap' => 'Staf Pengadaan serta Pemeliharaan Aset dan Inventaris',
                'email' => 'pengadaan@banksulteng.co.id',
                'jabatan' => 'Staf Pengadaan & Pemeliharaan',
                'bagian' => 'Bagian Pengadaan & Pemeliharaan Aset & Inventaris',
                'role_id' => 5,
                'department_id' => 3,
            ],
            [
                'username' => 'operator',
                'nama_lengkap' => 'Operator',
                'email' => 'operator@banksulteng.co.id',
                'jabatan' => 'Operator Helpdesk',
                'bagian' => 'Divisi Umum',
                'role_id' => 7,
                'department_id' => null,
            ],
        ];

        // 1 Role Multi-User (Role User = Pemohon Layanan Tiket dari Berbagai Cabang & Divisi)
        $multiRoleUsers = [
            [
                'username' => 'pemohon_user',
                'nama_lengkap' => 'Staff Pemohon Layanan',
                'email' => 'pemohon@banksulteng.co.id',
                'jabatan' => 'Staff Operasional Cabang',
                'bagian' => 'Kantor Cabang Utama',
                'role_id' => 6,
                'department_id' => null,
            ],
            [
                'username' => 'user_cabang_palu',
                'nama_lengkap' => 'Staff Layanan Cabang Palu',
                'email' => 'cabang.palu@banksulteng.co.id',
                'jabatan' => 'Staff Customer Service',
                'bagian' => 'Kantor Cabang Palu',
                'role_id' => 6,
                'department_id' => null,
            ],
            [
                'username' => 'user_div_sdm',
                'nama_lengkap' => 'Staff Pemohon Divisi SDM',
                'email' => 'sdm.pemohon@banksulteng.co.id',
                'jabatan' => 'Staff Personalia',
                'bagian' => 'Divisi SDM & Umum',
                'role_id' => 6,
                'department_id' => null,
            ],
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
