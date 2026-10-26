<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserDefaultPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_default_password_helpers()
    {
        $this->assertEquals('operator2026', User::defaultPasswordFor('operator'));

        $user = new User(['username' => 'staf 1']);
        $this->assertEquals('staf 12026', $user->getDefaultPassword());
    }

    public function test_admin_can_reset_user_password_to_default_format()
    {
        $adminRole = Role::create(['nama' => 'admin', 'label' => 'Admin']);
        RolePermission::create([
            'role_id' => $adminRole->id,
            'perm_key' => 'user_mgmt',
            'can_write' => true,
        ]);

        $admin = User::create([
            'username' => 'admin_test',
            'nama_lengkap' => 'Admin Test',
            'password' => Hash::make('secret'),
            'role_id' => $adminRole->id,
            'is_active' => true,
            'must_change_pwd' => false,
        ]);

        $targetUser = User::create([
            'username' => 'user_target',
            'nama_lengkap' => 'Target User',
            'password' => Hash::make('custompassword123'),
            'role_id' => $adminRole->id,
            'is_active' => true,
            'must_change_pwd' => false,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.reset-password', $targetUser));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('credential_modal');
        $response->assertSessionHas('status');

        $sessionData = session('credential_modal');
        $this->assertEquals('user_target', $sessionData['username']);
        $this->assertEquals('user_target2026', $sessionData['password']);
        $this->assertTrue($sessionData['is_reset']);

        $targetUser->refresh();
        $this->assertTrue($targetUser->must_change_pwd);
        $this->assertTrue(Hash::check('user_target2026', $targetUser->password));
    }

    public function test_admin_can_create_user_with_default_password()
    {
        $adminRole = Role::create(['nama' => 'admin', 'label' => 'Admin']);
        $userRole = Role::create(['nama' => 'user', 'label' => 'Pemohon Tiket']);

        $admin = User::create([
            'username' => 'admin_test2',
            'nama_lengkap' => 'Admin Test 2',
            'password' => Hash::make('secret'),
            'role_id' => $adminRole->id,
            'is_active' => true,
            'must_change_pwd' => false,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'role_id' => $userRole->id,
                'username' => 'staf_cabang_baru',
                'nama_lengkap' => 'Staf Baru',
                'email' => 'baru@banksulteng.co.id',
                'jabatan' => 'Staff Cabang',
                'bagian' => 'Cabang Baru',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('credential_modal');

        $newUser = User::where('username', 'staf_cabang_baru')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->must_change_pwd);
        $this->assertTrue(Hash::check('staf_cabang_baru2026', $newUser->password));
    }
}
