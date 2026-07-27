<?php

namespace App\Http\Middleware;

use App\Models\RolePermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// RBAC per-modul, setara matriks role/permission di versi Python, tapi
// sekarang ditegakkan lewat middleware di setiap route/group, bukan dicek
// manual di tiap handler. Daftarkan di bootstrap/app.php sebagai alias
// 'permission', lalu pakai di route: ->middleware('permission:aset_logistik,write')
class CheckModulePermission
{
    public function handle(Request $request, Closure $next, string $permKey, string $mode = 'read'): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $perm = RolePermission::where('role_id', $user->role_id)
            ->where('perm_key', $permKey)
            ->first();

        if (!$perm) {
            abort(403, "Role Anda tidak memiliki akses ke modul {$permKey}.");
        }

        if ($mode === 'write' && !$perm->can_write) {
            abort(403, "Role Anda hanya bisa melihat modul {$permKey}, tidak bisa mengubah.");
        }

        return $next($request);
    }
}
