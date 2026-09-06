<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use LogsAudit;

    public function index()
    {
        $items = User::with('role')->orderBy('role_id')->orderBy('username')->get();
        $roles = Role::withCount('users')->orderBy('id')->get();
        return view('admin.users.index', compact('items', 'roles'));
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

        // Aturan sistem: Hanya role 'user' yang dapat memiliki banyak user. Role lainnya hanya 1 user.
        $role = Role::findOrFail($request->role_id);
        if ($role->nama !== 'user' && $role->users()->count() >= 1) {
            return back()->withErrors([
                'role_id' => "Role {$role->label} hanya dapat memiliki 1 akun user. Hanya role User (Pemohon Layanan) yang dapat memiliki banyak akun."
            ])->withInput();
        }

        $plain = Str::random(12);
        $data['password'] = bcrypt($plain);
        $data['must_change_pwd'] = true;
        $data['is_active'] = true;

        $user = User::create($data);
        $this->audit('CREATE', 'Manajemen User', 'User', $user->id, "Menambah user {$user->username} pada role {$role->label}");

        return back()->with('status', "User {$user->username} berhasil dibuat. Password sementara: {$plain}");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'jabatan' => 'nullable|string|max:255',
            'bagian' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        // Aturan sistem: Hanya role 'user' yang dapat memiliki banyak user.
        $role = Role::findOrFail($request->role_id);
        if ($role->nama !== 'user' && $role->users()->where('id', '!=', $user->id)->count() >= 1) {
            return back()->withErrors([
                'role_id' => "Role {$role->label} sudah memiliki 1 akun user. Hanya role User (Pemohon Layanan) yang dapat memiliki banyak akun."
            ])->withInput();
        }

        $user->update($data);
        $this->audit('UPDATE', 'Manajemen User', 'User', $user->id, "Mengubah data user {$user->username}");

        return back()->with('status', 'User diperbarui.');
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
        // Proteksi: Role tunggal tidak boleh dihapus agar sistem tidak kehilangan akun penanggung jawab
        if ($user->role?->nama !== 'user') {
            return back()->withErrors([
                'error' => "Akun {$user->username} ({$user->role?->label}) tidak dapat dihapus karena role ini harus memiliki 1 akun penanggung jawab. Anda hanya dapat mengubah informasinya."
            ]);
        }

        $id = $user->id;
        $username = $user->username;
        $user->delete();
        $this->audit('DELETE', 'Manajemen User', 'User', $id, "Menghapus user {$username}");

        return back()->with('status', "User {$username} berhasil dihapus.");
    }
}
