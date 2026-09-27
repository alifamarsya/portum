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

        // 2. Unit Kerja
        $unitKerja = [
            ['id' => 1, 'department_id' => 1, 'kode' => 'UK-URT', 'nama' => 'Unit Kerja Umum & Rumah Tangga',               'deskripsi' => 'Mengelola kendaraan operasional, biaya harian, fasilitas kantor, pemeliharaan gedung, kebersihan, keamanan, dan K3.', 'is_active' => true],
            ['id' => 2, 'department_id' => 1, 'kode' => 'UK-DOK', 'nama' => 'Unit Kerja Pengelolaan Dokumen & Kearsipan',    'deskripsi' => 'Mengelola surat masuk/keluar, memo, arsip dokumen fisik & digital, dan dokumen legalitas.', 'is_active' => true],
        ];

        foreach ($unitKerja as $uk) {
            UnitKerja::updateOrCreate(['id' => $uk['id']], $uk);
        }

        // 3. Kategori Sistem Tiket Dinamis (Permintaan & Permasalahan untuk tiap Bagian)
        $categories = [
            // === Bagian Umum & Rumah Tangga (Dept 1) ===
            [
                'id' => 1,
                'name' => 'Perbaikan AC & Pendingin Ruangan',
                'jenis_pengajuan' => 'Permasalahan',
                'sla_resolution_hours' => 24,
                'default_sla_hours' => 24,
                'department_id' => 1,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Gangguan Listrik, Lampu & Genset',
                'jenis_pengajuan' => 'Permasalahan',
                'sla_resolution_hours' => 12,
                'default_sla_hours' => 12,
                'department_id' => 1,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'id' => 3,
                'name' => 'Kerusakan Fasilitas Gedung / Kebocoran Atap',
                'jenis_pengajuan' => 'Permasalahan',
                'sla_resolution_hours' => 48,
                'default_sla_hours' => 48,
                'department_id' => 1,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'id' => 4,
                'name' => 'Kendala Kendaraan Operasional / Mobil Dinas',
                'jenis_pengajuan' => 'Permasalahan',
                'sla_resolution_hours' => 24,
                'default_sla_hours' => 24,
                'department_id' => 1,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'id' => 5,
                'name' => 'Permintaan ATK & Kebutuhan Operasional',
                'jenis_pengajuan' => 'Permintaan',
                'sla_resolution_hours' => 24,
                'default_sla_hours' => 24,
                'department_id' => 1,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'id' => 6,
                'name' => 'Peminjaman Kendaraan Dinas & Driver',
                'jenis_pengajuan' => 'Permintaan',
                'sla_resolution_hours' => 12,
                'default_sla_hours' => 12,
                'department_id' => 1,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'id' => 7,
                'name' => 'Layanan Kebersihan Khusus & Penataan Ruang',
                'jenis_pengajuan' => 'Permintaan',
                'sla_resolution_hours' => 24,
                'default_sla_hours' => 24,
                'department_id' => 1,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'id' => 8,
                'name' => 'Pengambilan / Pengiriman Dokumen & Arsip',
                'jenis_pengajuan' => 'Permintaan',
                'sla_resolution_hours' => 24,
                'default_sla_hours' => 24,
                'department_id' => 1,
                'is_active' => true,
                'sort_order' => 8,
            ],

            // === Bagian Aset/Inventaris & Logistik (Dept 2) ===
            [
                'id' => 9,
                'name' => 'Kerusakan Peralatan Kerja / Meja & Kursi',
                'jenis_pengajuan' => 'Permasalahan',
                'sla_resolution_hours' => 48,
                'default_sla_hours' => 48,
                'department_id' => 2,
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'id' => 10,
                'name' => 'Kendala Pengiriman Logistik / Ekspedisi Cabang',
                'jenis_pengajuan' => 'Permasalahan',
                'sla_resolution_hours' => 24,
                'default_sla_hours' => 24,
                'department_id' => 2,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'id' => 11,
                'name' => 'Permintaan Distribusi Barang & Inventaris',
                'jenis_pengajuan' => 'Permintaan',
                'sla_resolution_hours' => 48,
                'default_sla_hours' => 48,
                'department_id' => 2,
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'id' => 12,
                'name' => 'Pengajuan Mutasi Lokasi / Penanggung Jawab Aset',
                'jenis_pengajuan' => 'Permintaan',
                'sla_resolution_hours' => 72,
                'default_sla_hours' => 72,
                'department_id' => 2,
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'id' => 13,
                'name' => 'Usulan Penghapusan / Disposal Aset Rusak Berat',
                'jenis_pengajuan' => 'Permintaan',
                'sla_resolution_hours' => 72,
                'default_sla_hours' => 72,
                'department_id' => 2,
                'is_active' => true,
                'sort_order' => 13,
            ],

            // === Bagian Pengadaan & Pemeliharaan (Dept 3) ===
            [
                'id' => 14,
                'name' => 'Kerusakan Komputer / Server / Hardware IT',
                'jenis_pengajuan' => 'Permasalahan',
                'sla_resolution_hours' => 24,
                'default_sla_hours' => 24,
                'department_id' => 3,
                'is_active' => true,
                'sort_order' => 14,
            ],
            [
                'id' => 15,
                'name' => 'Kerusakan Mesin ATM & Perangkat Teller/CS',
                'jenis_pengajuan' => 'Permasalahan',
                'sla_resolution_hours' => 8,
                'default_sla_hours' => 8,
                'department_id' => 3,
                'is_active' => true,
                'sort_order' => 15,
            ],
            [
                'id' => 16,
                'name' => 'Pengajuan Pengadaan Barang / Jasa Baru',
                'jenis_pengajuan' => 'Permintaan',
                'sla_resolution_hours' => 72,
                'default_sla_hours' => 72,
                'department_id' => 3,
                'is_active' => true,
                'sort_order' => 16,
            ],
            [
                'id' => 17,
                'name' => 'Jadwal Servis Berkala & Pemeliharaan Sarana',
                'jenis_pengajuan' => 'Permintaan',
                'sla_resolution_hours' => 48,
                'default_sla_hours' => 48,
                'department_id' => 3,
                'is_active' => true,
                'sort_order' => 17,
            ],
        ];

        foreach ($categories as $cat) {
            TicketCategory::updateOrCreate(['id' => $cat['id']], $cat);
        }

        // 4. Role Penyederhanaan (Role Bagian)
        $bagianRoles = [
            ['nama' => 'bagian_umum',      'label' => 'Bagian Umum & Rumah Tangga',                                'deskripsi' => 'Pengelolaan operasional umum, rumah tangga, kearsipan persuratan, dan penanganan tiket'],
            ['nama' => 'bagian_aset',      'label' => 'Bagian Aset/Inventaris & Logistik',                         'deskripsi' => 'Pengelolaan inventarisasi aset, logistik, pengadaan sewa, mutasi aset, dan penanganan tiket'],
            ['nama' => 'bagian_pengadaan', 'label' => 'Bagian Pengadaan serta Pemeliharaan Aset dan Inventaris',   'deskripsi' => 'Pengelolaan pengadaan barang/jasa, pemeliharaan sarana/prasarana, dan penanganan tiket'],
            ['nama' => 'user',             'label' => 'User',                                                      'deskripsi' => 'User / Pemohon tiket layanan'],
            ['nama' => 'operator',         'label' => 'Operator',                                                  'deskripsi' => 'Operator / Helpdesk penerima dan verifikator tiket'],
        ];

        foreach ($bagianRoles as $r) {
            Role::firstOrCreate(['nama' => $r['nama']], $r);
        }

        // 5. Matriks Permission Ticketing untuk Semua Role Terkait
        $rolesWithTicketing = Role::whereIn('nama', ['admin', 'user', 'operator', 'bagian_umum', 'bagian_aset', 'bagian_pengadaan'])->get();
        foreach ($rolesWithTicketing as $role) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $role->id, 'perm_key' => 'ticketing'],
                ['can_write' => 1]
            );
        }

        // 6. Hubungkan User Eksisting ke Departemen Masing-Masing
        User::where('username', 'umum')->update(['department_id' => 1]);
        User::where('username', 'aset')->update(['department_id' => 2]);
        User::where('username', 'pengadaan')->update(['department_id' => 3]);

        // 7. Akun Pemohon & Operator
        $userRole = Role::where('nama', 'user')->first();
        $operatorRole = Role::where('nama', 'operator')->first();

        $additionalUsers = [
            [
                'username'     => 'pemohon_user',
                'nama_lengkap' => 'Staff Pemohon Layanan',
                'email'        => 'pemohon@banksulteng.co.id',
                'jabatan'      => 'Staff Cabang',
                'bagian'       => 'Cabang Utama',
                'department_id'=> null,
                'unit_kerja_id'=> null,
                'role_id'      => $userRole?->id ?? 6,
            ],
            [
                'username'     => 'operator_helpdesk',
                'nama_lengkap' => 'Operator Helpdesk',
                'email'        => 'operator@banksulteng.co.id',
                'jabatan'      => 'Helpdesk Operator',
                'bagian'       => 'Divisi Umum',
                'department_id'=> null,
                'unit_kerja_id'=> null,
                'role_id'      => $operatorRole?->id ?? 7,
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
