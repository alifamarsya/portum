<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UmBiayaHarian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_double_approve_should_fail(): void
    {
        // 1. Buat role dengan permission write ke modul umum_rt (biaya_harian)
        $role = Role::create([
            'nama'  => 'pimpinan',
            'label' => 'Pimpinan Divisi',
        ]);

        RolePermission::create([
            'role_id'   => $role->id,
            'perm_key'  => 'umum_rt',  // perm_key sesuai config/modules.php biaya_harian
            'can_write' => true,
        ]);

        // 2. Buat 3 user: 1 maker + 2 checker
        $maker = User::factory()->create([
            'username' => 'adol',
            'role_id'  => $role->id,
        ]);

        $checker1 = User::factory()->create([
            'username' => 'pimpinan',
            'role_id'  => $role->id,
        ]);

        $checker2 = User::factory()->create([
            'username' => 'checker2',
            'role_id'  => $role->id,
        ]);

        // 3. Buat transaksi sebagai maker (status: Diajukan)
        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 100000,
            'kategori'        => 'BBM',
            'uraian'          => 'Test race condition',  // field yang benar: 'uraian' bukan 'keterangan'
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        // 4. Checker1 approve duluan — harus BERHASIL
        $resp1 = $this->actingAs($checker1)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $resp1->assertRedirect(); // 302 redirect = berhasil

        // 5. Checker2 coba approve transaksi yang SAMA — harus DITOLAK
        //    karena approval_status sudah bukan 'Diajukan' lagi
        $resp2 = $this->actingAs($checker2)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $resp2->assertStatus(403); // ApprovalPolicy menolak karena status != 'Diajukan'
    }
}
