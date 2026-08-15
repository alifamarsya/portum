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

    const PERM_KEYS = [
        'dashboard', 'analytics_dw', 'umum_rt', 'aset_logistik', 'pengadaan', 'risalah',
        'panduan', 'user_mgmt', 'role_mgmt', 'audit_log', 'ref_akun',
    ];

    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        return view('admin.roles.index', ['roles' => $roles, 'permKeys' => self::PERM_KEYS]);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        foreach (self::PERM_KEYS as $key) {
            RolePermission::updateOrCreate(
                ['role_id' => $role->id, 'perm_key' => $key],
                ['can_write' => $request->boolean("write_{$key}")]
            );
        }
        $this->audit('UPDATE', 'Manajemen Role', 'Role', $role->id, "Mengubah matriks permission role {$role->nama}");

        return back()->with('status', "Permission role {$role->label} diperbarui.");
    }
}
