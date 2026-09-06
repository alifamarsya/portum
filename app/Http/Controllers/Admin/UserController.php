<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\SeederSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use LogsAudit;

    const MUTLAK_ROLES = ['admin', 'pimpinan', 'kepala_bagian', 'kepala_divisi'];

    public function index()
    {
        $items = User::with('role')->orderBy('role_id')->orderBy('username')->get();
        $roles = Role::withCount('users')->orderBy('id')->get();
        // Dropdown untuk form tambah user: hilangkan role yang mutlak (admin, pimpinan divisi, kepala bagian)
        $creatableRoles = $roles->filter(function ($r) {
            return !in_array($r->nama, self::MUTLAK_ROLES);
        });

        return view('admin.users.index', compact('items', 'roles', 'creatableRoles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'jabatan' => 'nullable|string|max:255',
            'bagian' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);

        // Aturan: Role mutlak (admin, pimpinan divisi, kepala bagian) tidak boleh ditambah user baru
        if (in_array($role->nama, self::MUTLAK_ROLES)) {
            return back()->withErrors([
                'role_id' => "Role {$role->label} bersifat mutlak dan tidak dapat ditambah akun pengguna baru."
            ])->withInput();
        }

        // Otomatis tentukan department_id untuk peran staf bagian dan kabag
        $departmentId = match ($role->nama) {
            'umum_rt', 'kabag_umum' => 1,
            'aset', 'kabag_aset' => 2,
            'pengadaan', 'kabag_pengadaan' => 3,
            default => null,
        };
        $data['department_id'] = $departmentId;

        if (empty($data['bagian']) && $departmentId) {
            $data['bagian'] = match ($departmentId) {
                1 => 'Bagian Umum & Rumah Tangga',
                2 => 'Bagian Aset/Inventaris & Logistik',
                3 => 'Bagian Pengadaan & Pemeliharaan Aset & Inventaris',
                default => null,
            };
        }

        $plain = Str::random(12);
        $data['password'] = bcrypt($plain);
        $data['must_change_pwd'] = true;
        $data['is_active'] = true;

        $user = User::create($data);
        $this->audit('CREATE', 'Manajemen User', 'User', $user->id, "Menambah user {$user->username} pada role {$role->label}");

        // Otomatis sinkronkan ke hardcode UserSeeder.php
        SeederSyncService::syncUsers();

        return back()->with('status', "User {$user->username} berhasil dibuat. Password sementara: {$plain}");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => 'required|string|max:100|unique:users,username,' . $user->id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'jabatan' => 'nullable|string|max:255',
            'bagian' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $role = Role::findOrFail($request->role_id);
        
        // Jika dipindah ke role mutlak yang sudah terisi oleh user lain, cegah
        if (in_array($role->nama, self::MUTLAK_ROLES) && $user->role_id != $role->id && $role->users()->count() >= 1) {
            return back()->withErrors([
                'role_id' => "Role {$role->label} bersifat mutlak dan sudah memiliki 1 akun penanggung jawab."
            ])->withInput();
        }

        // Sinkronkan department_id dengan role yang dipilih
        $departmentId = match ($role->nama) {
            'umum_rt', 'kabag_umum' => 1,
            'aset', 'kabag_aset' => 2,
            'pengadaan', 'kabag_pengadaan' => 3,
            default => null,
        };
        $data['department_id'] = $departmentId;

        if (empty($data['bagian']) && $departmentId) {
            $data['bagian'] = match ($departmentId) {
                1 => 'Bagian Umum & Rumah Tangga',
                2 => 'Bagian Aset/Inventaris & Logistik',
                3 => 'Bagian Pengadaan & Pemeliharaan Aset & Inventaris',
                default => null,
            };
        }

        $user->update($data);
        $this->audit('UPDATE', 'Manajemen User', 'User', $user->id, "Mengubah data user {$user->username}");

        // Otomatis sinkronkan ke hardcode UserSeeder.php
        SeederSyncService::syncUsers();

        return back()->with('status', "User {$user->username} berhasil diperbarui.");
    }

    public function resetPassword(User $user)
    {
        $plain = Str::random(12);
        $user->update(['password' => bcrypt($plain), 'must_change_pwd' => true]);
        $this->audit('UPDATE', 'Manajemen User', 'User', $user->id, "Reset password user {$user->username}");

        return back()->with('status', "Password {$user->username} direset. Password sementara: {$plain}");
    }

    public function destroy(User $user)
    {
        // Proteksi: Role mutlak tidak boleh dihapus
        if (in_array($user->role?->nama, self::MUTLAK_ROLES)) {
            return back()->withErrors([
                'error' => "Akun {$user->username} ({$user->role?->label}) bersifat mutlak dan tidak dapat dihapus."
            ]);
        }

        $id = $user->id;
        $username = $user->username;
        $user->delete();
        $this->audit('DELETE', 'Manajemen User', 'User', $id, "Menghapus user {$username}");

        // Otomatis sinkronkan ke hardcode UserSeeder.php
        SeederSyncService::syncUsers();

        return back()->with('status', "User {$username} berhasil dihapus.");
    }
}
