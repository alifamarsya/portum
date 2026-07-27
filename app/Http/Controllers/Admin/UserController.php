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
        $items = User::with('role')->orderBy('username')->get();
        $roles = Role::orderBy('label')->get();
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

        $plain = Str::random(12);
        $data['password'] = bcrypt($plain);
        $data['must_change_pwd'] = true;
        $data['is_active'] = true;

        $user = User::create($data);
        $this->audit('CREATE', 'Manajemen User', 'User', $user->id, "Menambah user {$user->username}");

        return back()->with('status', "User {$user->username} dibuat. Password sementara: {$plain}");
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
        $id = $user->id;
        $username = $user->username;
        $user->delete();
        $this->audit('DELETE', 'Manajemen User', 'User', $id, "Menghapus user {$username}");

        return back()->with('status', 'User dihapus.');
    }
}
