<?php

namespace App\Services;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;

class SeederSyncService
{
    /**
     * Sinkronisasi data Role & Permission dari database ke file RolePermissionSeeder.php
     */
    public static function syncRolePermissions(): void
    {
        $roles = Role::orderBy('id')->get();
        $perms = RolePermission::orderBy('role_id')->orderBy('perm_key')->get();

        $rolesCode = "";
        foreach ($roles as $r) {
            $desc = addslashes($r->deskripsi ?? '');
            $label = addslashes($r->label ?? '');
            $rolesCode .= "            ['id' => {$r->id}, 'nama' => '{$r->nama}', 'label' => '{$label}', 'deskripsi' => '{$desc}'],\n";
        }

        $permsCode = "";
        foreach ($perms as $p) {
            $write = $p->can_write ? 1 : 0;
            $permsCode .= "            [{$p->role_id}, '{$p->perm_key}', {$write}],\n";
        }

        $content = <<<PHP
<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    // File ini otomatis disinkronisasi saat Admin melakukan perubahan hak akses lewat website
    public function run(): void
    {
        \$roles = [
{$rolesCode}        ];

        foreach (\$roles as \$r) {
            Role::updateOrCreate(['id' => \$r['id']], \$r);
        }

        \$perms = [
{$permsCode}        ];

        DB::table('role_permissions')->truncate();
        foreach (\$perms as [\$roleId, \$key, \$write]) {
            DB::table('role_permissions')->insert([
                'role_id' => \$roleId,
                'perm_key' => \$key,
                'can_write' => \$write,
            ]);
        }
    }
}

PHP;

        file_put_contents(database_path('seeders/RolePermissionSeeder.php'), $content);
    }

    /**
     * Sinkronisasi data User dari database ke file UserSeeder.php
     */
    public static function syncUsers(): void
    {
        $users = User::with('role')->orderBy('role_id')->orderBy('id')->get();

        $singleUsersCode = "";
        $multiUsersCode = "";

        foreach ($users as $u) {
            $email = addslashes($u->email ?? '');
            $nama = addslashes($u->nama_lengkap ?? '');
            $jabatan = addslashes($u->jabatan ?? '');
            $bagian = addslashes($u->bagian ?? '');
            $dept = $u->department_id ? $u->department_id : 'null';

            $line = "            ['username' => '{$u->username}', 'nama_lengkap' => '{$nama}', 'email' => '{$email}', 'jabatan' => '{$jabatan}', 'bagian' => '{$bagian}', 'role_id' => {$u->role_id}, 'department_id' => {$dept}],\n";

            if ($u->role?->nama === 'user') {
                $multiUsersCode .= $line;
            } else {
                $singleUsersCode .= $line;
            }
        }

        $content = <<<PHP
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    // File ini otomatis disinkronisasi saat Admin melakukan perubahan pengguna lewat website
    public function run(): void
    {
        // Role Akun Tunggal
        \$singleRoleUsers = [
{$singleUsersCode}        ];

        // Role Multi-User (Pemohon Layanan Cabang & Divisi)
        \$multiRoleUsers = [
{$multiUsersCode}        ];

        foreach (array_merge(\$singleRoleUsers, \$multiRoleUsers) as \$u) {
            \$plain = \$u['username'] . '2026';
            \$existing = User::where('username', \$u['username'])->first();
            if (\$existing) {
                // Hanya update data profil, JANGAN timpa password & must_change_pwd
                \$existing->update([...\$u, 'is_active' => true]);
            } else {
                // User baru: set password default + wajib ganti
                User::create([...\$u, 'password' => Hash::make(\$plain), 'must_change_pwd' => true, 'is_active' => true]);
            }
        }
    }
}

PHP;

        file_put_contents(database_path('seeders/UserSeeder.php'), $content);
    }
}
