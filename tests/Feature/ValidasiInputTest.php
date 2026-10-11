<?php

namespace Tests\Feature;

use App\Models\RolePermission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidasiInputTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

protected function setUp(): void
{
    parent::setUp();

    $role = Role::create([
        'nama' => 'umum_rt',
        'label' => 'Staf Umum & Rumah Tangga',
    ]);

    RolePermission::create([
        'role_id' => $role->id,
        'perm_key' => 'umum_rt',
        'can_write' => true,
    ]);

    $this->user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
}
    public function test_biaya_harian_dengan_jumlah_negatif_ditolak(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal' => now()->toDateString(),
            'kategori' => 'BBM',
            'nama_beban' => 'Isi bensin',
            'jumlah' => -50000,
            'uraian' => 'Test input negatif',
        ]);

        $response->assertSessionHasErrors('jumlah');
    }

    public function test_biaya_harian_dengan_field_wajib_kosong_ditolak(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal' => now()->toDateString(),
            'jumlah' => 50000,
        ]);

        $response->assertSessionHasErrors('kategori');
    }

    public function test_biaya_harian_dengan_kategori_di_luar_opsi_ditolak(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal' => now()->toDateString(),
            'kategori' => 'Kategori-Tidak-Terdaftar-XYZ',
            'nama_beban' => 'Test',
            'jumlah' => 10000,
        ]);

        $response->assertSessionHasErrors('kategori');
    }

    public function test_biaya_harian_dengan_tanggal_tidak_valid_ditolak(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal' => 'bukan-tanggal',
            'kategori' => 'BBM',
            'jumlah' => 10000,
        ]);

        $response->assertSessionHasErrors('tanggal');
    }

    public function test_biaya_harian_dengan_hanya_spasi_ditolak(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal'         => now()->toDateString(),
            'kategori'        => 'BBM',
            'kendaraan'       => '   ',
            'nama_beban'      => '   ',
            'rekening_debet'  => '   ',
            'rekening_kredit' => '   ',
            'uraian'          => '   ',
            'jumlah'          => 75000,
            'no_nota'         => '   ',
            'status'          => 'Lunas',
            'keterangan'      => '   ',
        ]);

        $response->assertSessionHasErrors(['kendaraan', 'uraian', 'rekening_debet']);
    }

    public function test_biaya_harian_dengan_input_valid_berhasil_tersimpan(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal'         => now()->toDateString(),
            'kategori'        => 'BBM',
            'kendaraan'       => 'DN 1001 SB',
            'nama_beban'      => 'Beban BBM Operasional',
            'rekening_debet'  => '5.1.01.01',
            'rekening_kredit' => '1.1.01.01',
            'uraian'          => 'Perjalanan dinas ke cabang',
            'jumlah'          => 75000,
            'no_nota'         => 'NOTA-001',
            'status'          => 'Dibukukan',
            'keterangan'      => 'Pengisian bensin operasional',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('um_biaya_harian', ['jumlah' => 75000, 'kategori' => 'BBM', 'approval_status' => 'Disetujui']);
    }
}