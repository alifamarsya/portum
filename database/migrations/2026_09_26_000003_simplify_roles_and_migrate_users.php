<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat atau perbarui 3 Role Bagian baru
        $bagianRoles = [
            [
                'nama' => 'bagian_umum',
                'label' => 'Bagian Umum & Rumah Tangga',
                'deskripsi' => 'Pengelolaan operasional umum, rumah tangga, kearsipan persuratan, dan layanan ticketing bagian umum',
            ],
            [
                'nama' => 'bagian_aset',
                'label' => 'Bagian Aset/Inventaris & Logistik',
                'deskripsi' => 'Pengelolaan inventarisasi aset, logistik, pengadaan sewa, mutasi aset, dan layanan ticketing bagian aset',
            ],
            [
                'nama' => 'bagian_pengadaan',
                'label' => 'Bagian Pengadaan serta Pemeliharaan Aset dan Inventaris',
                'deskripsi' => 'Pengelolaan pengadaan barang/jasa, pemeliharaan sarana/prasarana operasional, dan layanan ticketing bagian pengadaan',
            ],
        ];

        $roleMap = [];
        foreach ($bagianRoles as $rData) {
            $role = Role::firstOrCreate(['nama' => $rData['nama']], $rData);
            $role->update(['label' => $rData['label'], 'deskripsi' => $rData['deskripsi']]);
            $roleMap[$rData['nama']] = $role->id;
        }

        // 2. Beri permissions untuk role bagian baru
        $perms = [
            $roleMap['bagian_umum'] => [
                'dashboard' => 1, 'ticketing' => 1, 'umum_rt' => 1, 'dokumen_arsip' => 1,
                'risalah' => 1, 'panduan' => 1, 'ref_akun' => 1
            ],
            $roleMap['bagian_aset'] => [
                'dashboard' => 1, 'ticketing' => 1, 'administrasi_aset' => 1, 'logistik_pelaporan' => 1,
                'mutasi_aset' => 1, 'risalah' => 1, 'panduan' => 1, 'ref_akun' => 1
            ],
            $roleMap['bagian_pengadaan'] => [
                'dashboard' => 1, 'ticketing' => 1, 'pengadaan' => 1, 'pemeliharaan_pengawasan' => 1,
                'risalah' => 1, 'panduan' => 1, 'ref_akun' => 1
            ],
        ];

        foreach ($perms as $roleId => $permissions) {
            foreach ($permissions as $key => $write) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'perm_key' => $key],
                    ['can_write' => $write]
                );
            }
        }

        // 3. Migrasikan akun pengguna lama ke role Bagian yang sesuai
        $oldUmumRoleIds = Role::whereIn('nama', ['kabag_umum', 'uk_umum_rt', 'uk_dokumen', 'umum_rt'])->pluck('id');
        User::whereIn('role_id', $oldUmumRoleIds)->update([
            'role_id' => $roleMap['bagian_umum'],
            'department_id' => 1,
        ]);

        $oldAsetRoleIds = Role::whereIn('nama', ['kabag_aset', 'uk_administrasi_aset', 'uk_logistik', 'aset'])->pluck('id');
        User::whereIn('role_id', $oldAsetRoleIds)->update([
            'role_id' => $roleMap['bagian_aset'],
            'department_id' => 2,
        ]);

        $oldPengadaanRoleIds = Role::whereIn('nama', ['kabag_pengadaan', 'uk_pengadaan', 'uk_pemeliharaan', 'pengadaan'])->pluck('id');
        User::whereIn('role_id', $oldPengadaanRoleIds)->update([
            'role_id' => $roleMap['bagian_pengadaan'],
            'department_id' => 3,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert is not strictly needed for data consolidation, but role_permissions can be cleaned if needed.
    }
};
