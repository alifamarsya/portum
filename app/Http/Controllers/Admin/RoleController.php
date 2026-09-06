<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use LogsAudit;

    const PERMISSIONS = [
        'Layanan & Monitoring' => [
            'dashboard' => ['label' => 'Dashboard', 'desc' => 'Akses halaman dashboard pemantauan utama sistem'],
            'ticketing' => ['label' => 'Sistem Tiket', 'desc' => 'Akses modul tiket layanan (pemohon, operator, atau unit kerja)'],
            'analytics_dw' => ['label' => 'Analitik DW', 'desc' => 'Akses laporan analitik biaya operasional & data warehouse'],
        ],
        'Modul Operasional' => [
            'umum_rt' => ['label' => 'Umum & Rumah Tangga', 'desc' => 'Pengelolaan kendaraan, biaya BBM/RT, dan permintaan ATK cabang'],
            'aset_logistik' => ['label' => 'Aset & Logistik', 'desc' => 'Pengelolaan inventaris, invoice sewa, amortisasi, dan PKS'],
            'pengadaan' => ['label' => 'Pengadaan & Pemeliharaan', 'desc' => 'Pengelolaan memo internal, penawaran vendor, SPK, dan reminder'],
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
        ]);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        foreach (self::allKeys() as $key) {
            $hasAccess = $request->boolean("access_{$key}");
            $canWrite = $request->boolean("write_{$key}");

            if ($hasAccess) {
                RolePermission::updateOrCreate(
                    ['role_id' => $role->id, 'perm_key' => $key],
                    ['can_write' => $canWrite ? 1 : 0]
                );
            } else {
                RolePermission::where('role_id', $role->id)->where('perm_key', $key)->delete();
            }
        }

        $this->audit('UPDATE', 'Manajemen Role', 'Role', $role->id, "Mengubah matriks permission role {$role->nama} ({$role->label})");

        return back()->with('status', "Hak akses untuk role {$role->label} berhasil diperbarui.");
    }
}
