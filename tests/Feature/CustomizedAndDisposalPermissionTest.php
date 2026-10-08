<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\RoleController;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomizedAndDisposalPermissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test modul Customized dan submodulnya pada PERMISSIONS constant.
     */
    public function test_customized_module_permissions_structure()
    {
        $perms = RoleController::PERMISSIONS;

        $this->assertArrayHasKey('Customized', $perms);
        $this->assertEquals('Field Mutasi Aset', $perms['Customized']['items']['config_field_aset']['label']);
        $this->assertEquals('Field Sistem Tiket', $perms['Customized']['items']['config_ticket']['label']);

        $this->assertArrayHasKey('Pengajuan & Monitoring', $perms);
        $this->assertArrayHasKey('persetujuan_hapus_aset', $perms['Pengajuan & Monitoring']['items']);
        $this->assertEquals('Persetujuan Hapus Aset', $perms['Pengajuan & Monitoring']['items']['persetujuan_hapus_aset']['label']);
    }

    /**
     * Test hak akses persetujuan hapus aset: role admin juga patuh pada matriks hak akses.
     */
    public function test_persetujuan_hapus_aset_access_control()
    {
        $roleAdmin = Role::create(['nama' => 'admin', 'label' => 'Admin', 'deskripsi' => 'Administrator']);
        $roleUser = Role::create(['nama' => 'user', 'label' => 'User', 'deskripsi' => 'User']);

        $admin = User::factory()->create([
            'nama_lengkap' => 'Admin Test',
            'role_id'      => $roleAdmin->id,
            'is_active'    => true,
        ]);

        $user = User::factory()->create([
            'nama_lengkap' => 'User Biasa Test',
            'role_id'      => $roleUser->id,
            'is_active'    => true,
        ]);

        // 1. Awalnya admin belum memiliki permission persetujuan_hapus_aset -> Ditolak (403)
        $this->assertFalse($admin->canAccess('persetujuan_hapus_aset'));
        $respAdminDenied = $this->actingAs($admin)->get(route('disposal-aset.approval'));
        $respAdminDenied->assertStatus(403);

        // 2. Jika admin diberikan permission persetujuan_hapus_aset di matriks hak akses -> Berhasil (200)
        RolePermission::create([
            'role_id'   => $roleAdmin->id,
            'perm_key'  => 'persetujuan_hapus_aset',
            'can_write' => 1,
        ]);
        $admin->unsetRelation('role');
        $this->assertTrue($admin->fresh()->canAccess('persetujuan_hapus_aset'));
        $respAdminGranted = $this->actingAs($admin->fresh())->get(route('disposal-aset.approval'));
        $respAdminGranted->assertStatus(200);

        // 3. Jika permission persetujuan_hapus_aset di-uncheck untuk admin -> Otomatis ditolak kembali (403)
        RolePermission::where('role_id', $roleAdmin->id)->where('perm_key', 'persetujuan_hapus_aset')->delete();
        $admin->unsetRelation('role');
        $this->assertFalse($admin->fresh()->canAccess('persetujuan_hapus_aset'));
        $respAdminRevoked = $this->actingAs($admin->fresh())->get(route('disposal-aset.approval'));
        $respAdminRevoked->assertStatus(403);

        // 4. Regular user tanpa permission persetujuan_hapus_aset ditolak (403)
        $this->assertFalse($user->canAccess('persetujuan_hapus_aset'));
        $responseUser = $this->actingAs($user)->get(route('disposal-aset.approval'));
        $responseUser->assertStatus(403);

        // 5. Jika regular user diberi permission persetujuan_hapus_aset -> Diizinkan (200)
        RolePermission::create([
            'role_id'   => $roleUser->id,
            'perm_key'  => 'persetujuan_hapus_aset',
            'can_write' => 1,
        ]);
        $user->unsetRelation('role');

        $this->assertTrue($user->fresh()->canAccess('persetujuan_hapus_aset'));
        $responseUserGranted = $this->actingAs($user->fresh())->get(route('disposal-aset.approval'));
        $responseUserGranted->assertStatus(200);
    }

    /**
     * Test bahwa halaman Customized dapat diakses dengan label baru jika admin memiliki permission.
     */
    public function test_customized_pages_render_correctly()
    {
        $roleAdmin = Role::create(['nama' => 'admin', 'label' => 'Admin', 'deskripsi' => 'Administrator']);

        // Berikan permission kustomisasi form/field ke admin
        RolePermission::create(['role_id' => $roleAdmin->id, 'perm_key' => 'config_field_aset', 'can_write' => 1]);
        RolePermission::create(['role_id' => $roleAdmin->id, 'perm_key' => 'config_ticket', 'can_write' => 1]);
        RolePermission::create(['role_id' => $roleAdmin->id, 'perm_key' => 'role_mgmt', 'can_write' => 1]);

        $admin = User::factory()->create([
            'nama_lengkap' => 'Admin Test',
            'role_id'      => $roleAdmin->id,
            'is_active'    => true,
        ]);

        // Submodul 1: Field Mutasi Aset
        $respFieldAset = $this->actingAs($admin)->get(route('konfigurasi.field-aset.index'));
        $respFieldAset->assertStatus(200);
        $respFieldAset->assertSee('Field Mutasi Aset');
        $respFieldAset->assertSee('Customized');

        // Submodul 2: Field Sistem Tiket
        $respFieldTiket = $this->actingAs($admin)->get(route('konfigurasi.tiket.index'));
        $respFieldTiket->assertStatus(200);
        $respFieldTiket->assertSee('Field Sistem Tiket');
        $respFieldTiket->assertSee('Customized');

        // Halaman Peran & Hak Akses
        $respRoles = $this->actingAs($admin)->get(route('admin.roles.index'));
        $respRoles->assertStatus(200);
        $respRoles->assertSee('Customized');
        $respRoles->assertSee('Field Mutasi Aset');
        $respRoles->assertSee('Field Sistem Tiket');
        $respRoles->assertSee('Persetujuan Hapus Aset');
    }
}
