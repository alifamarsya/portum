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

class DisposalAsetTest extends TestCase
{
    use RefreshDatabase;

    private User $stafAset;
    private User $kadiv;
    private User $userBiasa;
    private AsAset $aset;

    protected function setUp(): void
    {
        parent::setUp();

        InternalDepartment::create(['id' => 1, 'name' => 'Bagian Umum & Rumah Tangga']);
        InternalDepartment::create(['id' => 2, 'name' => 'Bagian Aset/Inventaris & Logistik']);
        InternalDepartment::create(['id' => 3, 'name' => 'Bagian Pengadaan & Pemeliharaan']);

        $roleKadiv = Role::create(['nama' => 'pimpinan', 'label' => 'Pimpinan Divisi', 'deskripsi' => 'Kadiv']);
        $roleStafAset = Role::create(['nama' => 'uk_administrasi_aset', 'label' => 'Staf Aset', 'deskripsi' => 'Staf']);
        $roleUser = Role::create(['nama' => 'user', 'label' => 'User', 'deskripsi' => 'User']);

        $this->kadiv = User::factory()->create([
            'nama_lengkap'  => 'Kepala Divisi Test',
            'role_id'       => $roleKadiv->id,
            'department_id' => null,
            'is_active'     => true,
        ]);

        $this->stafAset = User::factory()->create([
            'nama_lengkap'  => 'Staf Aset Test',
            'role_id'       => $roleStafAset->id,
            'department_id' => 2,
            'is_active'     => true,
        ]);

        $this->userBiasa = User::factory()->create([
            'nama_lengkap'  => 'User Biasa Test',
            'role_id'       => $roleUser->id,
            'department_id' => null,
            'is_active'     => true,
        ]);

        // Permissions
        RolePermission::create(['role_id' => $roleStafAset->id, 'perm_key' => 'administrasi_aset', 'can_write' => true]);
        RolePermission::create(['role_id' => $roleKadiv->id, 'perm_key' => 'administrasi_aset', 'can_write' => true]);

        // Sample Asset
        $this->aset = AsAset::create([
            'kode_aset'          => 'AST-2026-001',
            'nama_aset'          => 'Laptop Dell Latitude 5420',
            'kategori'           => 'Elektronik & IT',
            'lokasi'             => 'Divisi Umum Lt. 2',
            'tanggal_perolehan'  => '2023-01-15',
            'nilai_perolehan'    => 15000000,
            'kondisi'            => 'Rusak Berat',
            'status_penghapusan' => 'aktif',
        ]);
    }

    /** @test */
    public function test_staff_can_submit_disposal_request_and_asset_is_not_directly_deleted(): void
    {
        $response = $this->actingAs($this->stafAset)
            ->post(route('disposal-aset.ajukan.submit', $this->aset->id), [
                'alasan_penghapusan' => 'Motherboard terbakar dan biaya perbaikan melebihi nilai ekonomis aset.',
                'metode'             => 'Dimusnahkan',
                'keterangan'         => 'Sudah dicek oleh tim IT teknisi.',
            ]);

        $response->assertRedirect(route('modul.index', 'aset'));
        $response->assertSessionHas('status');

        // Disposal record dibuat
        $this->assertDatabaseHas('as_disposal_aset', [
            'aset_id'            => $this->aset->id,
            'alasan_penghapusan' => 'Motherboard terbakar dan biaya perbaikan melebihi nilai ekonomis aset.',
            'metode'             => 'Dimusnahkan',
            'status'             => 'Diajukan',
            'approval_status'    => 'Diajukan',
            'maker_id'           => $this->stafAset->id,
        ]);

        // Aset TIDAK dihapus dari database (tidak di-soft delete)
        $this->aset->refresh();
        $this->assertNull($this->aset->deleted_at);
        $this->assertEquals('menunggu_persetujuan', $this->aset->status_penghapusan);
    }

    /** @test */
    public function test_alasan_penghapusan_is_required_when_submitting(): void
    {
        $response = $this->actingAs($this->stafAset)
            ->post(route('disposal-aset.ajukan.submit', $this->aset->id), [
                'alasan_penghapusan' => '', // kosong
                'metode'             => 'Dimusnahkan',
            ]);

        $response->assertSessionHasErrors('alasan_penghapusan');
        $this->assertDatabaseCount('as_disposal_aset', 0);
    }

    /** @test */
    public function test_direct_delete_on_aset_module_is_blocked(): void
    {
        // Mencoba delete langsung via ModuleController::destroy('aset', $id)
        $response = $this->actingAs($this->stafAset)
            ->delete(route('modul.destroy', ['key' => 'aset', 'id' => $this->aset->id]));

        $response->assertRedirect(route('disposal-aset.ajukan', $this->aset->id));
        $response->assertSessionHas('error');

        // Pastikan aset TIDAK terhapus
        $this->assertDatabaseHas('as_aset', [
            'id'         => $this->aset->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function test_non_kadiv_cannot_approve_or_reject_disposal(): void
    {
        // Buat pengajuan disposal
        $disposal = AsDisposalAset::create([
            'no_disposal'        => 'DSP-20260929-0001',
            'aset_id'            => $this->aset->id,
            'tanggal_pengajuan'  => now()->toDateString(),
            'alasan_penghapusan' => 'Rusak berat.',
            'metode'             => 'Dimusnahkan',
            'nilai_buku_terakhir'=> 15000000,
            'status'             => 'Diajukan',
            'approval_status'    => 'Diajukan',
            'maker_id'           => $this->stafAset->id,
        ]);

        // Staf mencoba approve
        $response = $this->actingAs($this->stafAset)
            ->post(route('disposal-aset.approve', $disposal->id));
        $response->assertStatus(403);

        // User biasa mencoba reject
        $response2 = $this->actingAs($this->userBiasa)
            ->post(route('disposal-aset.reject', $disposal->id), [
                'alasan_penolakan' => 'Tidak setuju.',
            ]);
        $response2->assertStatus(403);

        // Status disposal tetap Diajukan
        $this->assertEquals('Diajukan', $disposal->fresh()->approval_status);
    }

    /** @test */
    public function test_kadiv_can_reject_disposal_and_asset_remains_active(): void
    {
        $this->aset->update(['status_penghapusan' => 'menunggu_persetujuan']);

        $disposal = AsDisposalAset::create([
            'no_disposal'        => 'DSP-20260929-0002',
            'aset_id'            => $this->aset->id,
            'tanggal_pengajuan'  => now()->toDateString(),
            'alasan_penghapusan' => 'Rusak berat.',
            'metode'             => 'Dimusnahkan',
            'nilai_buku_terakhir'=> 15000000,
            'status'             => 'Diajukan',
            'approval_status'    => 'Diajukan',
            'maker_id'           => $this->stafAset->id,
        ]);

        $response = $this->actingAs($this->kadiv)
            ->post(route('disposal-aset.reject', $disposal->id), [
                'alasan_penolakan' => 'Aset masih bisa diperbaiki oleh vendor rekanan.',
            ]);

        $response->assertSessionHas('status');

        $disposal->refresh();
        $this->assertEquals('Ditolak', $disposal->status);
        $this->assertEquals('Ditolak', $disposal->approval_status);
        $this->assertEquals($this->kadiv->id, $disposal->checker_id);
        $this->assertEquals('Aset masih bisa diperbaiki oleh vendor rekanan.', $disposal->alasan_penolakan);

        // Aset tetap aktif dan tidak dihapus
        $this->aset->refresh();
        $this->assertEquals('aktif', $this->aset->status_penghapusan);
        $this->assertNull($this->aset->deleted_at);
    }

    /** @test */
    public function test_kadiv_approval_soft_deletes_asset_and_moves_to_riwayat_terhapus(): void
    {
        $this->aset->update(['status_penghapusan' => 'menunggu_persetujuan']);

        $disposal = AsDisposalAset::create([
            'no_disposal'        => 'DSP-20260929-0003',
            'aset_id'            => $this->aset->id,
            'tanggal_pengajuan'  => now()->toDateString(),
            'alasan_penghapusan' => 'Kerusakan fisik parah akibat banjir.',
            'metode'             => 'Dihapusbukukan',
            'nilai_buku_terakhir'=> 15000000,
            'status'             => 'Diajukan',
            'approval_status'    => 'Diajukan',
            'maker_id'           => $this->stafAset->id,
        ]);

        $response = $this->actingAs($this->kadiv)
            ->post(route('disposal-aset.approve', $disposal->id), [
                'catatan' => 'Disetujui untuk dihapusbukukan.',
            ]);

        $response->assertSessionHas('status');

        // Status disposal Disetujui
        $disposal->refresh();
        $this->assertEquals('Disetujui', $disposal->status);
        $this->assertEquals('Disetujui', $disposal->approval_status);
        $this->assertEquals($this->kadiv->id, $disposal->checker_id);
        $this->assertNotNull($disposal->approved_at);

        // Aset ditandai terhapus dan di-soft delete
        $this->aset->refresh();
        $this->assertEquals('terhapus', $this->aset->status_penghapusan);
        $this->assertNotNull($this->aset->deleted_at);

        // Aset tidak muncul pada query inventaris aktif AsAset::query()
        $activeAset = AsAset::where('id', $this->aset->id)->first();
        $this->assertNull($activeAset);

        // Tapi data aset tetap ada dengan withTrashed()
        $trashedAset = AsAset::withTrashed()->find($this->aset->id);
        $this->assertNotNull($trashedAset);
        $this->assertEquals('Laptop Dell Latitude 5420', $trashedAset->nama_aset);

        // Riwayat Aset Terhapus menampilkan data lengkap + meta
        $riwayatResponse = $this->actingAs($this->stafAset)
            ->get(route('disposal-aset.riwayat'));

        $riwayatResponse->assertStatus(200);
        $riwayatResponse->assertSee('DSP-20260929-0003');
        $riwayatResponse->assertSee('Laptop Dell Latitude 5420');
        $riwayatResponse->assertSee('Staf Aset Test');
        $riwayatResponse->assertSee('Kepala Divisi Test');
        $riwayatResponse->assertSee('Kerusakan fisik parah akibat banjir.');
    }
}
