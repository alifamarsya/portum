<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use App\Services\SeederSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    use LogsAudit;

    const SYSTEM_ROLES = [
        'admin',
        'pimpinan',
        'bagian_umum',
        'bagian_aset',
        'bagian_pengadaan',
        'kabag_umum',
        'kabag_aset',
        'kabag_pengadaan',
        'umum_rt',
        'aset',
        'pengadaan',
        'user',
        'operator',
    ];

    const PERMISSIONS = [
        'Pengajuan & Monitoring' => [
            'icon'  => 'inbox',
            'items' => [
                'dashboard'              => ['label' => 'Dashboard', 'unit' => 'Utama'],
                'ticketing'              => ['label' => 'Sistem Tiket', 'unit' => 'Helpdesk'],
                'mutasi_aset'            => ['label' => 'Mutasi Aset', 'unit' => 'Pemohon & Approval'],
                'persetujuan_hapus_aset' => ['label' => 'Persetujuan Hapus Aset', 'unit' => 'Kadiv & Approval'],
            ],
        ],
        'Modul Operasional' => [
            'icon'  => 'layers',
            'items' => [
                'umum_rt'                 => ['label' => 'UK Umum & Rumah Tangga', 'unit' => 'Bagian Umum'],
                'dokumen_arsip'           => ['label' => 'UK Dokumen & Kearsipan', 'unit' => 'Bagian Umum'],
                'administrasi_aset'       => ['label' => 'UK Administrasi Aset', 'unit' => 'Bagian Aset'],
                'logistik_pelaporan'      => ['label' => 'UK Logistik & Pelaporan', 'unit' => 'Bagian Aset'],
                'pengadaan'               => ['label' => 'UK Pengadaan', 'unit' => 'Bagian Pengadaan'],
                'pemeliharaan_pengawasan' => ['label' => 'UK Pemeliharaan', 'unit' => 'Bagian Pengadaan'],
            ],
        ],
        'Customized' => [
            'icon'  => 'sliders',
            'items' => [
                'config_field_aset' => ['label' => 'Field Mutasi Aset', 'unit' => 'Aset, Mutasi, Riwayat'],
                'config_ticket'     => ['label' => 'Field Sistem Tiket', 'unit' => 'Kategori & Field Tiket'],
            ],
        ],
        'Dokumentasi & Referensi' => [
            'icon'  => 'book',
            'items' => [
                'risalah'  => ['label' => 'Risalah Rapat', 'unit' => 'Notulensi'],
                'panduan'  => ['label' => 'Buku Panduan & SOP', 'unit' => 'Dokumentasi'],
                'ref_akun' => ['label' => 'Referensi Akun (COA)', 'unit' => 'Master Keuangan'],
            ],
        ],
        'Administrasi Sistem' => [
            'icon'  => 'shield',
            'items' => [
                'user_mgmt' => ['label' => 'Manajemen User', 'unit' => 'Akun Pengguna'],
                'role_mgmt' => ['label' => 'Peran & Hak Akses', 'unit' => 'RBAC Matrix'],
                'audit_log' => ['label' => 'Audit Log & Hash', 'unit' => 'Integritas SHA-256'],
            ],
        ],
    ];

    public static function allKeys(): array
    {
        $keys = [];
        foreach (self::PERMISSIONS as $group) {
            foreach (array_keys($group['items']) as $k) {
                $keys[] = $k;
            }
        }
        return $keys;
    }

    public function index()
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('id')->get();
        return view('admin.roles.index', [
            'roles' => $roles,
            'groupedPermissions' => self::PERMISSIONS,
            'systemRoles' => self::SYSTEM_ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'nama' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9_\-]+$/|unique:roles,nama',
            'deskripsi' => 'nullable|string|max:500',
        ]);

        $slug = !empty($data['nama']) 
            ? Str::slug($data['nama'], '_') 
            : Str::slug($data['label'], '_');

        if (Role::where('nama', $slug)->exists()) {
            $slug = $slug . '_' . time();
        }

        $role = Role::create([
            'nama' => $slug,
            'label' => $data['label'],
            'deskripsi' => $data['deskripsi'] ?? null,
        ]);

        // Default initial permission (dashboard)
        DB::table('role_permissions')->insert([
            'role_id' => $role->id,
            'perm_key' => 'dashboard',
            'can_write' => 0,
        ]);

        $this->audit('CREATE', 'Manajemen Role', 'Role', $role->id, "Menambah role baru {$role->nama} ({$role->label})");

        // Otomatis sinkronkan ke RolePermissionSeeder.php
        SeederSyncService::syncRolePermissions();

        return back()->with('status', "Role '{$role->label}' berhasil ditambahkan ke sistem.");
    }

    public function update(Request $request, Role $role)
    {
        $isSystemRole = in_array($role->nama, self::SYSTEM_ROLES);

        $rules = [
            'label' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:500',
        ];

        // Jika bukan role sistem, izinkan edit slug unik
        if (!$isSystemRole) {
            $rules['nama'] = 'required|string|max:100|regex:/^[a-zA-Z0-9_\-]+$/|unique:roles,nama,' . $role->id;
        }

        $data = $request->validate($rules);

        $updateData = [
            'label' => $data['label'],
            'deskripsi' => $data['deskripsi'] ?? null,
        ];

        if (!$isSystemRole && !empty($data['nama'])) {
            $updateData['nama'] = Str::slug($data['nama'], '_');
        }

        $role->update($updateData);

        $this->audit('UPDATE', 'Manajemen Role', 'Role', $role->id, "Mengubah informasi data role {$role->nama} ({$role->label})");

        // Otomatis sinkronkan ke RolePermissionSeeder.php
        SeederSyncService::syncRolePermissions();

        return back()->with('status', "Data role '{$role->label}' berhasil diperbarui.");
    }

    public function updatePermissions(Request $request, Role $role)
    {
        foreach (self::PERMISSIONS as $group) {
            foreach ($group['items'] as $key => $meta) {
                $hasAccess = $request->boolean("access_{$key}");
                $canWrite = $request->boolean("write_{$key}");
                $keysToSync = $meta['linked_keys'] ?? [$key];

                foreach ($keysToSync as $syncKey) {
                    if ($hasAccess) {
                        DB::table('role_permissions')->updateOrInsert(
                            ['role_id' => $role->id, 'perm_key' => $syncKey],
                            ['can_write' => $canWrite ? 1 : 0]
                        );
                    } else {
                        DB::table('role_permissions')
                            ->where('role_id', $role->id)
                            ->where('perm_key', $syncKey)
                            ->delete();
                    }
                }
            }
        }

        $this->audit('UPDATE', 'Manajemen Role', 'Role', $role->id, "Mengubah matriks permission role {$role->nama} ({$role->label})");

        // Otomatis sinkronkan ke hardcode RolePermissionSeeder.php
        SeederSyncService::syncRolePermissions();

        return back()->with('status', "Hak akses untuk role {$role->label} berhasil diperbarui.");
    }

    public function destroy(Role $role)
    {
        // Proteksi 1: Role admin mutlak atau role yang sedang aktif digunakan oleh user login tidak boleh dihapus
        if ($role->nama === 'admin' || (auth()->check() && auth()->user()->role_id === $role->id)) {
            return back()->withErrors([
                'role' => "Role '{$role->label}' tidak dapat dihapus karena merupakan peran administrator utama atau sedang digunakan oleh sesi login Anda saat ini."
            ]);
        }

        // Proteksi 2: Role yang masih memiliki pengguna terdaftar tidak boleh dihapus
        $activeUsersCount = $role->users()->count();
        if ($activeUsersCount > 0) {
            return back()->withErrors([
                'role' => "Role '{$role->label}' tidak dapat dihapus karena masih digunakan oleh {$activeUsersCount} pengguna aktif. Silakan alihkan atau ubah peran pengguna terkait terlebih dahulu."
            ]);
        }

        $roleId = $role->id;
        $roleNama = $role->nama;
        $roleLabel = $role->label;

        // Hapus matriks perizinan terkait dan hapus role
        DB::table('role_permissions')->where('role_id', $roleId)->delete();
        $role->delete();

        $this->audit('DELETE', 'Manajemen Role', 'Role', $roleId, "Menghapus role {$roleNama} ({$roleLabel})");

        // Otomatis sinkronkan ke RolePermissionSeeder.php
        SeederSyncService::syncRolePermissions();

        return back()->with('status', "Role '{$roleLabel}' ({$roleNama}) berhasil dihapus dari sistem.");
    }
}

