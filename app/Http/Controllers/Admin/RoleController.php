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
            'dashboard' => ['label' => 'Dashboard', 'desc' => 'Akses halaman dashboard pemantauan utama sistem'],
            'ticketing' => ['label' => 'Sistem Tiket', 'desc' => 'Akses modul tiket pengajuan (pemohon, operator, atau unit kerja)'],
        ],
        'Modul Operasional' => [
            'umum_rt' => [
                'bagian' => 'Bagian Umum & Rumah Tangga',
                'label' => 'UK Umum & Rumah Tangga',
                'desc' => 'Pengelolaan kendaraan operasional, biaya BBM/RT, fasilitas kantor, pemeliharaan gedung, kebersihan, dan K3',
                'submodules' => ['Kendaraan & Driver', 'Biaya BBM & Perawatan RT', 'Fasilitas Kantor', 'Pemeliharaan Gedung', 'Kebersihan & Keamanan', 'Catatan K3 & Lingkungan'],
            ],
            'dokumen_arsip' => [
                'bagian' => 'Bagian Umum & Rumah Tangga',
                'label' => 'UK Dokumen & Kearsipan',
                'desc' => 'Pengelolaan surat masuk/keluar, memo internal/eksternal, master arsip dokumen fisik & digital, dan legalitas',
                'submodules' => ['Surat Masuk', 'Surat Keluar', 'Memo Masuk', 'Memo Keluar', 'Master Arsip Dokumen', 'Dokumen Legalitas'],
            ],
            'administrasi_aset' => [
                'bagian' => 'Bagian Aset/Inventaris & Logistik',
                'label' => 'UK Administrasi Aset & Inventaris',
                'desc' => 'Inventarisasi aset, amortisasi, riwayat pergerakan aset, mutasi aset, disposal, rekonsiliasi, dan temuan audit',
                'submodules' => ['Inventarisasi Aset', 'Amortisasi Aset', 'Riwayat Pergerakan Aset', 'Mutasi Aset', 'Penghapusan Aset (Disposal)', 'Rekonsiliasi & Reklasifikasi', 'Tindak Lanjut Temuan'],
            ],
            'logistik_pelaporan' => [
                'bagian' => 'Bagian Aset/Inventaris & Logistik',
                'label' => 'UK Logistik & Pelaporan',
                'desc' => 'Tagihan invoice sewa, PKS & reminder jatuh tempo, memo sewa cabang, penerimaan/distribusi barang, dan pembayaran tagihan',
                'submodules' => ['Tagihan / Invoice Sewa', 'PKS & Jatuh Tempo', 'Memo Sewa Cabang', 'Penerimaan Barang / Jasa', 'Distribusi Barang / Jasa', 'Administrasi Pembayaran Tagihan'],
            ],
            'pengadaan' => [
                'bagian' => 'Bagian Pengadaan & Pemeliharaan',
                'label' => 'Pengadaan & Pemeliharaan',
                'desc' => 'Memo internal pengadaan, penawaran vendor, negosiasi harga, draft dokumen SPK, penerbitan SPK, dan reminder pengerjaan',
                'submodules' => ['Memo Internal', 'Penawaran Vendor', 'Negosiasi Harga', 'Draft Dokumen SPK', 'Surat Perintah Kerja (SPK)', 'Reminder & Monitoring'],
            ],
        ],
        'Dokumentasi & Referensi' => [
            'risalah' => ['label' => 'Risalah Rapat', 'desc' => 'Notulensi agenda rapat, daftar hadir, dan tindak lanjut keputusan'],
            'panduan' => ['label' => 'Buku Panduan & SOP', 'desc' => 'Dokumentasi pedoman teknis dan panduan operasional perbankan'],
            'ref_akun' => ['label' => 'Referensi Akun (COA)', 'desc' => 'Master data rekening debet dan akun beban biaya'],
        ],
        'Administrasi Sistem' => [
            'user_mgmt' => ['label' => 'Manajemen User', 'desc' => 'Pengelolaan data pengguna, reset kata sandi, dan status aktif'],
            'role_mgmt' => ['label' => 'Manajemen Role', 'desc' => 'Konfigurasi matriks izin modul dan peran jabatan (RBAC)'],
            'audit_log' => ['label' => 'Audit Log & Hash', 'desc' => 'Jejak audit digital seluruh aktivitas dan integritas SHA-256'],
        ],
    ];

    public static function allKeys(): array
    {
        $keys = [];
        foreach (self::PERMISSIONS as $group) {
            foreach (array_keys($group) as $k) {
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
        foreach (self::allKeys() as $key) {
            $hasAccess = $request->boolean("access_{$key}");
            $canWrite = $request->boolean("write_{$key}");

            if ($hasAccess) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $role->id, 'perm_key' => $key],
                    ['can_write' => $canWrite ? 1 : 0]
                );
            } else {
                DB::table('role_permissions')
                    ->where('role_id', $role->id)
                    ->where('perm_key', $key)
                    ->delete();
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

