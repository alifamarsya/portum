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

    private function setupUsers(): array
    {
        $role = Role::create([
            'nama'  => 'umum_rt',
            'label' => 'Staf Umum & Rumah Tangga',
        ]);

        RolePermission::create([
            'role_id'   => $role->id,
            'perm_key'  => 'umum_rt',
            'can_write' => true,
        ]);

        $staf = User::factory()->create([
            'username' => 'staf_urt',
            'role_id'  => $role->id,
        ]);

        $pimpinan = User::factory()->create([
            'username' => 'pimpinan_urt',
            'role_id'  => $role->id,
        ]);

        return [$staf, $pimpinan];
    }

    /** Test 1: Endpoint maker-checker approve pada modul operasional mengembalikan 404 karena maker-checker dinonaktifkan */
    public function test_operasional_module_endpoint_setujui_returns_404(): void
    {
        [$staf, $pimpinan] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 50000,
            'kategori'        => 'BBM',
            'uraian'          => 'Beli bensin dinas',
            'kendaraan'       => 'DN 1001 SB',
            'nama_beban'      => 'Beban BBM Operasional',
            'rekening_debet'  => '5.1.01.01',
            'rekening_kredit' => '1.1.01.01',
            'no_nota'         => 'NOTA-001',
            'status'          => 'Dibukukan',
            'keterangan'      => 'Operasional',
            'approval_status' => 'Disetujui',
        ]);

        $response = $this->actingAs($pimpinan)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        // Karena maker-checker sudah dimatikan, endpoint harus 404
        $response->assertStatus(404);
    }

    /** Test 2: Endpoint maker-checker reject pada modul operasional mengembalikan 404 */
    public function test_operasional_module_endpoint_tolak_returns_404(): void
    {
        [$staf, $pimpinan] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 75000,
            'kategori'        => 'Perawatan',
            'uraian'          => 'Servis mobil',
            'kendaraan'       => 'DN 1001 SB',
            'nama_beban'      => 'Beban Perawatan',
            'rekening_debet'  => '5.1.01.01',
            'rekening_kredit' => '1.1.01.01',
            'no_nota'         => 'NOTA-002',
            'status'          => 'Dibukukan',
            'keterangan'      => 'Servis',
            'approval_status' => 'Disetujui',
        ]);

        $response = $this->actingAs($pimpinan)
            ->post("/modul/biaya_harian/{$transaksi->id}/tolak");

        $response->assertStatus(404);
    }

    /** Test 3: Data yang disimpan langsung aktif (Disetujui) dan tidak tertahan status Diajukan */
    public function test_transaksi_operasional_langsung_aktif_tanpa_tertahan_approval(): void
    {
        [$staf] = $this->setupUsers();

        $response = $this->actingAs($staf)->post('/modul/biaya_harian', [
            'tanggal'         => now()->toDateString(),
            'kategori'        => 'BBM',
            'kendaraan'       => 'DN 1001 SB',
            'nama_beban'      => 'Beban BBM Operasional',
            'rekening_debet'  => '5.1.01.01',
            'rekening_kredit' => '1.1.01.01',
            'uraian'          => 'Isi BBM tanpa maker checker',
            'jumlah'          => 120000,
            'no_nota'         => 'NOTA-099',
            'status'          => 'Dibukukan',
            'keterangan'      => 'Perjalanan dinas',
        ]);

        $response->assertRedirect('/modul/biaya_harian');
        $this->assertDatabaseHas('um_biaya_harian', [
            'uraian'          => 'Isi BBM tanpa maker checker',
            'approval_status' => 'Disetujui',
        ]);
        $this->assertDatabaseMissing('um_biaya_harian', [
            'uraian'          => 'Isi BBM tanpa maker checker',
            'approval_status' => 'Diajukan',
        ]);
    }

    /** Test 4: Halaman index modul operasional tidak menampilkan tombol Setujui dan Tolak */
    public function test_halaman_index_operasional_tidak_menampilkan_tombol_approval(): void
    {
        [$staf] = $this->setupUsers();

        UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 50000,
            'kategori'        => 'BBM',
            'uraian'          => 'Bensin kantor',
            'kendaraan'       => 'DN 1001 SB',
            'nama_beban'      => 'Beban BBM Operasional',
            'rekening_debet'  => '5.1.01.01',
            'rekening_kredit' => '1.1.01.01',
            'no_nota'         => 'NOTA-100',
            'status'          => 'Dibukukan',
            'keterangan'      => 'Operasional',
            'approval_status' => 'Disetujui',
        ]);

        $response = $this->actingAs($staf)->get('/modul/biaya_harian');

        $response->assertStatus(200);
        $response->assertDontSee('Setujui');
        $response->assertDontSee('Tolak');
    }
}
