<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UmBiayaHarian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakerCheckerTest extends TestCase
{
    use RefreshDatabase;

    // Helper: setup role + permission + 2 user (maker & checker) sekali pakai
    private function setupUsers(): array
    {
        $role = Role::create([
            'nama'  => 'pimpinan',
            'label' => 'Pimpinan Divisi',
        ]);

        // Checker PERLU permission can_write agar lolos authorizeModule()
        RolePermission::create([
            'role_id'  => $role->id,
            'perm_key' => 'umum_rt',
            'can_write' => true,
        ]);

        $maker = User::factory()->create([
            'username' => 'adol',
            'role_id'  => $role->id,
        ]);

        $checker = User::factory()->create([
            'username' => 'pimpinan',
            'role_id'  => $role->id,
        ]);

        return [$maker, $checker];
    }

    /** Test 1: Maker TIDAK BISA self-approve */
    public function test_maker_cannot_approve_own_transaction(): void
    {
        [$maker] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 50000,
            'kategori'        => 'BBM',
            'uraian'          => 'Test self-approve',
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        // Maker mencoba approve transaksinya sendiri → harus 403
        $response = $this->actingAs($maker)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $response->assertStatus(403);

        // Status di database harus tetap 'Diajukan'
        $this->assertDatabaseHas('um_biaya_harian', [
            'id'              => $transaksi->id,
            'approval_status' => 'Diajukan',
        ]);
    }

    /** Test 2: Checker YANG BERBEDA bisa approve */
    public function test_different_checker_can_approve(): void
    {
        [$maker, $checker] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 75000,
            'kategori'        => 'Perawatan',
            'uraian'          => 'Test normal approve',
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        $response = $this->actingAs($checker)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        // Harus redirect (berhasil)
        $response->assertRedirect();

        // Status di database harus berubah ke Disetujui
        $this->assertDatabaseHas('um_biaya_harian', [
            'id'              => $transaksi->id,
            'approval_status' => 'Disetujui',
            'checker_id'      => $checker->id,
        ]);
    }

    /** Test 3: Transaksi yang sudah disetujui tidak bisa disetujui lagi */
    public function test_already_approved_cannot_be_approved_again(): void
    {
        [$maker, $checker] = $this->setupUsers();

        // Langsung buat transaksi dengan status sudah 'Disetujui'
        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 200000,
            'kategori'        => 'Rumah Tangga',
            'uraian'          => 'Sudah disetujui',
            'maker_id'        => $maker->id,
            'approval_status' => 'Disetujui',
            'checker_id'      => $checker->id,
        ]);

        // Checker coba approve lagi → harus 403
        $response = $this->actingAs($checker)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $response->assertStatus(403);
    }
}
