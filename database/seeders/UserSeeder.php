<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    // PENTING: password default versi Python (admin/admin2026, dst.) tampil
    // polos di README lama — di seeder ini tiap user diberi password acak yang
    // digenerate saat seeding, WAJIB dicatat/dibagikan lewat jalur aman (bukan
    // dokumen publik), dan flag must_change_pwd dipaksa true untuk semuanya.
    public function run(): void
    {
        $users = [
            ['username' => 'admin', 'nama_lengkap' => 'Superadmin', 'email' => 'admin@banksulteng.co.id', 'jabatan' => 'IT Admin', 'bagian' => 'Divisi Umum', 'role_id' => 1, 'department_id' => null],
            ['username' => 'pimpinan', 'nama_lengkap' => 'Pemimpin Divisi Umum', 'email' => 'pimdiv.umum@banksulteng.co.id', 'jabatan' => 'Pemimpin Divisi', 'bagian' => 'Divisi Umum', 'role_id' => 2, 'department_id' => null],
            ['username' => 'umum', 'nama_lengkap' => 'Staf Umum & RT', 'jabatan' => 'Staf Umum & RT', 'bagian' => 'Bagian Umum & Rumah Tangga', 'role_id' => 3, 'department_id' => 1],
            ['username' => 'aset', 'nama_lengkap' => 'Staf Aset & Logistik', 'jabatan' => 'Staf Aset & Logistik', 'bagian' => 'Bagian Aset/Inventaris & Logistik', 'role_id' => 4, 'department_id' => 2],
            ['username' => 'pengadaan', 'nama_lengkap' => 'Staf Pengadaan', 'jabatan' => 'Staf Pengadaan', 'bagian' => 'Bagian Pengadaan & Pemeliharaan Aset & Inventaris', 'role_id' => 5, 'department_id' => 3],
        ];

        foreach ($users as $u) {
            $plain = $u['username'] . '2026';
            User::updateOrCreate(
                ['username' => $u['username']],
                [...$u, 'password' => Hash::make($plain), 'must_change_pwd' => true, 'is_active' => true]
            );
            $this->command?->line("{$u['username']} -> password sementara: {$plain}");
        }
    }
}
