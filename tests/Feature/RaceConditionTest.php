<?php

namespace Tests\Feature;

use App\Models\AsAset;
use App\Models\AsDisposalAset;
use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_double_approve_should_fail(): void
    {
        InternalDepartment::create(['id' => 1, 'name' => 'Bagian Umum & Rumah Tangga']);
        InternalDepartment::create(['id' => 2, 'name' => 'Bagian Aset/Inventaris & Logistik']);

        $roleKadiv = Role::create([
            'nama'  => 'pimpinan',
            'label' => 'Pimpinan Divisi',
        ]);

        $roleStaf = Role::create([
            'nama'  => 'uk_administrasi_aset',
            'label' => 'Staf Aset',
        ]);

        RolePermission::create([
            'role_id'   => $roleKadiv->id,
            'perm_key'  => 'administrasi_aset',
            'can_write' => true,
        ]);

        $kadiv1 = User::factory()->create([
            'username' => 'kadiv1',
            'role_id'  => $roleKadiv->id,
        ]);

        $kadiv2 = User::factory()->create([
            'username' => 'kadiv2',
            'role_id'  => $roleKadiv->id,
        ]);

        $staf = User::factory()->create([
            'username' => 'staf_aset',
            'role_id'  => $roleStaf->id,
        ]);

        $aset = AsAset::create([
            'kode_aset'       => 'AST-RACE-001',
            'nama_aset'       => 'Laptop Lenovo ThinkPad',
            'kategori'        => 'Elektronik',
            'status'          => 'Aktif',
            'nilai_perolehan' => 15000000,
            'status_penghapusan' => 'menunggu_persetujuan',
        ]);

        $disposal = AsDisposalAset::create([
            'no_disposal'        => 'DSP-RACE-001',
            'aset_id'            => $aset->id,
            'tanggal_pengajuan'  => now()->toDateString(),
            'alasan_penghapusan' => 'Rusak berat',
            'metode'             => 'Dimusnahkan',
            'nilai_buku_terakhir'=> 0,
            'status'             => 'Diajukan',
            'approval_status'    => 'Diajukan',
            'maker_id'           => $staf->id,
        ]);

        // Kadiv 1 approve duluan — harus BERHASIL
        $resp1 = $this->actingAs($kadiv1)
            ->post(route('disposal-aset.approve', $disposal->id));

        $resp1->assertRedirect();
        $this->assertEquals('Disetujui', $disposal->fresh()->approval_status);

        // Kadiv 2 mencoba approve pengajuan yang SAMA — harus DITOLAK karena sudah diproses
        $resp2 = $this->actingAs($kadiv2)
            ->post(route('disposal-aset.approve', $disposal->id));

        $resp2->assertSessionHas('error', 'Pengajuan ini sudah diproses sebelumnya.');
    }
}
