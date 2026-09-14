<?php

namespace Tests\Feature;

use App\Models\AsAset;
use App\Models\AsAsetHistory;
use App\Models\AsMutasiAset;
use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MutasiAsetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $operator;
    protected User $stafAset;
    protected User $kabagAset;
    protected AsAset $aset;
    protected InternalDepartment $dept;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = InternalDepartment::create([
            'name' => 'Bagian Aset/Inventaris & Logistik',
        ]);

        $roleUser = Role::create(['nama' => 'user', 'label' => 'User']);
        $roleOperator = Role::create(['nama' => 'operator', 'label' => 'Operator']);
        $roleKabagAset = Role::create(['nama' => 'kabag_aset', 'label' => 'Kepala Bagian Aset/Inventaris & Logistik']);
        $roleStafAset = Role::create(['nama' => 'uk_administrasi_aset', 'label' => 'Staf Administrasi Aset']);

        foreach ([$roleUser, $roleOperator, $roleKabagAset, $roleStafAset] as $r) {
            RolePermission::create([
                'role_id'   => $r->id,
                'perm_key'  => 'mutasi_aset',
                'can_write' => true,
            ]);
            RolePermission::create([
                'role_id'   => $r->id,
                'perm_key'  => 'dashboard',
                'can_write' => true,
            ]);
        }

        $this->user = User::create([
            'username'     => 'cabang_tawaeli',
            'password'     => bcrypt('secret'),
            'nama_lengkap' => 'Staf Cabang Tawaeli',
            'email'        => 'cabang.palu@banksulteng.co.id',
            'jabatan'      => 'Customer Service',
            'bagian'       => 'Kantor Cabang Palu',
            'role_id'      => $roleUser->id,
            'is_active'    => true,
        ]);

        $this->operator = User::create([
            'username'     => 'operator_helpdesk',
            'password'     => bcrypt('secret'),
            'nama_lengkap' => 'Operator Helpdesk',
            'email'        => 'operator@banksulteng.co.id',
            'jabatan'      => 'Operator',
            'bagian'       => 'Divisi Umum',
            'role_id'      => $roleOperator->id,
            'is_active'    => true,
        ]);

        $this->stafAset = User::create([
            'username'     => 'staf_aset_dirli',
            'password'     => bcrypt('secret'),
            'nama_lengkap' => 'Dirli Staf Aset',
            'email'        => 'aset@banksulteng.co.id',
            'jabatan'      => 'Staf Administrasi Aset',
            'bagian'       => 'Bagian Aset/Inventaris & Logistik',
            'role_id'      => $roleStafAset->id,
            'department_id'=> $this->dept->id,
            'is_active'    => true,
        ]);

        $this->kabagAset = User::create([
            'username'     => 'kabag_aset_user',
            'password'     => bcrypt('secret'),
            'nama_lengkap' => 'Kabag Aset',
            'email'        => 'kabagaset@banksulteng.co.id',
            'jabatan'      => 'Kepala Bagian Aset',
            'bagian'       => 'Bagian Aset/Inventaris & Logistik',
            'role_id'      => $roleKabagAset->id,
            'department_id'=> $this->dept->id,
            'is_active'    => true,
        ]);

        $this->aset = AsAset::create([
            'kode_aset'        => 'AST-TEST-001',
            'nama_aset'        => 'Komputer Server Uji Coba',
            'kategori'         => 'Peralatan Kantor',
            'lokasi'           => 'Kantor Pusat Lt. 3',
            'penanggung_jawab' => 'Divisi TI',
            'nilai_perolehan'  => 50000000,
            'kondisi'          => 'Baik',
        ]);
    }

    public function test_user_can_view_mutasi_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('mutasi-aset.index'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Mutasi Aset');
    }

    public function test_user_can_submit_mutation_request(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->user)->post(route('mutasi-aset.store'), [
            'aset_id'             => $this->aset->id,
            'ke_lokasi'           => 'KC Palu Barat',
            'ke_penanggung_jawab' => 'Rahmat Hidayat (Pimpinan Cabang)',
            'alasan'              => 'Pemindahan unit server untuk backup operasional cabang.',
        ]);

        $response->assertRedirect();

        $mutasi = AsMutasiAset::where('aset_id', $this->aset->id)->latest('id')->first();
        $this->assertNotNull($mutasi);
        $this->assertStringStartsWith('MUT-' . date('Ymd') . '-', $mutasi->no_mutasi);
        $this->assertEquals('Diajukan', $mutasi->status);
        $this->assertEquals('KC Palu Barat', $mutasi->ke_lokasi);
        $this->assertEquals('Rahmat Hidayat (Pimpinan Cabang)', $mutasi->ke_penanggung_jawab);
        $this->assertEquals($this->user->id, $mutasi->pengaju_id);
    }

    public function test_operator_can_check_form(): void
    {
        $mutasi = AsMutasiAset::create([
            'no_mutasi'             => 'MUT-' . date('Ymd') . '-9991',
            'aset_id'               => $this->aset->id,
            'pengaju_id'            => $this->user->id,
            'dari_lokasi'           => $this->aset->lokasi,
            'ke_lokasi'             => 'KC Luwuk',
            'dari_penanggung_jawab' => $this->aset->penanggung_jawab,
            'ke_penanggung_jawab'   => 'Budi Hartono',
            'alasan'                => 'Kebutuhan cabang baru',
            'status'                => 'Diajukan',
        ]);

        $response = $this->actingAs($this->operator)->post(route('mutasi-aset.check-operator', $mutasi), [
            'catatan_operator' => 'Dokumen dan isian form lengkap.',
        ]);

        $response->assertRedirect();
        $mutasi->refresh();
        $this->assertEquals('Diproses', $mutasi->status);
        $this->assertEquals($this->operator->id, $mutasi->operator_id);
        $this->assertNotNull($mutasi->operator_checked_at);
    }

    public function test_staf_aset_can_verify_invalid(): void
    {
        Notification::fake();

        $mutasi = AsMutasiAset::create([
            'no_mutasi'             => 'MUT-' . date('Ymd') . '-9992',
            'aset_id'               => $this->aset->id,
            'pengaju_id'            => $this->user->id,
            'dari_lokasi'           => $this->aset->lokasi,
            'ke_lokasi'             => 'KC Toli-Toli',
            'dari_penanggung_jawab' => $this->aset->penanggung_jawab,
            'ke_penanggung_jawab'   => 'Hendra',
            'alasan'                => 'Uji coba invalid',
            'status'                => 'Diproses',
        ]);

        $response = $this->actingAs($this->stafAset)->post(route('mutasi-aset.verify-staf', $mutasi), [
            'keputusan'          => 'tidak_valid',
            'catatan_verifikasi' => 'Aset ini masih dalam status sewa aktif dan tidak dapat dimutasikan.',
        ]);

        $response->assertRedirect();
        $mutasi->refresh();
        $this->assertEquals('Ditutup', $mutasi->status);
        $this->assertEquals('Tidak Valid', $mutasi->status_hasil);
        $this->assertEquals($this->stafAset->id, $mutasi->verifikator_id);
    }

    public function test_staf_aset_can_verify_valid(): void
    {
        Notification::fake();

        $mutasi = AsMutasiAset::create([
            'no_mutasi'             => 'MUT-' . date('Ymd') . '-9993',
            'aset_id'               => $this->aset->id,
            'pengaju_id'            => $this->user->id,
            'dari_lokasi'           => $this->aset->lokasi,
            'ke_lokasi'             => 'KC Parigi',
            'dari_penanggung_jawab' => $this->aset->penanggung_jawab,
            'ke_penanggung_jawab'   => 'Siti Aminah',
            'alasan'                => 'Kebutuhan operasional KC Parigi',
            'status'                => 'Diproses',
        ]);

        $response = $this->actingAs($this->stafAset)->post(route('mutasi-aset.verify-staf', $mutasi), [
            'keputusan'          => 'valid',
            'catatan_verifikasi' => 'Fisik dan data aset telah diverifikasi sesuai dan siap dimutasi.',
        ]);

        $response->assertRedirect();
        $mutasi->refresh();
        $this->assertEquals('Menunggu Approval', $mutasi->status);
        $this->assertEquals($this->stafAset->id, $mutasi->verifikator_id);
    }

    public function test_kabag_aset_can_reject(): void
    {
        Notification::fake();

        $mutasi = AsMutasiAset::create([
            'no_mutasi'             => 'MUT-' . date('Ymd') . '-9994',
            'aset_id'               => $this->aset->id,
            'pengaju_id'            => $this->user->id,
            'dari_lokasi'           => $this->aset->lokasi,
            'ke_lokasi'             => 'KC Buol',
            'dari_penanggung_jawab' => $this->aset->penanggung_jawab,
            'ke_penanggung_jawab'   => 'Farhan',
            'alasan'                => 'Mutasi aset',
            'status'                => 'Menunggu Approval',
        ]);

        $response = $this->actingAs($this->kabagAset)->post(route('mutasi-aset.approve-kabag', $mutasi), [
            'keputusan'        => 'tolak',
            'alasan_penolakan' => 'Anggaran relokasi dan logistik belum tersedia untuk semester ini.',
            'catatan_approval' => 'Ditolak sementara.',
        ]);

        $response->assertRedirect();
        $mutasi->refresh();
        $this->assertEquals('Ditutup', $mutasi->status);
        $this->assertEquals('Ditolak', $mutasi->status_hasil);
        $this->assertEquals($this->kabagAset->id, $mutasi->approver_id);
    }

    public function test_kabag_aset_can_approve_and_update_asset_and_record_history(): void
    {
        Notification::fake();

        $tujuanLokasi = 'KC Donggala Lt. 1';
        $tujuanPj = 'Agus Setiawan (Pimpinan Cabang)';

        $mutasi = AsMutasiAset::create([
            'no_mutasi'             => 'MUT-' . date('Ymd') . '-9995',
            'aset_id'               => $this->aset->id,
            'pengaju_id'            => $this->user->id,
            'dari_lokasi'           => $this->aset->lokasi,
            'ke_lokasi'             => $tujuanLokasi,
            'dari_penanggung_jawab' => $this->aset->penanggung_jawab,
            'ke_penanggung_jawab'   => $tujuanPj,
            'alasan'                => 'Relokasi server utama ke KC Donggala',
            'status'                => 'Menunggu Approval',
        ]);

        $response = $this->actingAs($this->kabagAset)->post(route('mutasi-aset.approve-kabag', $mutasi), [
            'keputusan'        => 'setujui',
            'catatan_approval' => 'Disetujui. Harap koordinasikan pengiriman dengan bagian logistik.',
        ]);

        $response->assertRedirect();
        $mutasi->refresh();
        $this->assertEquals('Ditutup', $mutasi->status);
        $this->assertEquals('Disetujui', $mutasi->status_hasil);
        $this->assertEquals($this->kabagAset->id, $mutasi->approver_id);

        // Verifikasi tabel as_aset terupdate otomatis
        $this->aset->refresh();
        $this->assertEquals($tujuanLokasi, $this->aset->lokasi);
        $this->assertEquals($tujuanPj, $this->aset->penanggung_jawab);

        // Verifikasi tabel as_aset_histories mencatat mutasi
        $history = AsAsetHistory::where('aset_id', $this->aset->id)
            ->where('field_changed', 'Mutasi Aset')
            ->latest('id')
            ->first();

        $this->assertNotNull($history);
        $this->assertStringContainsString($tujuanLokasi, $history->new_value);
        $this->assertStringContainsString($mutasi->no_mutasi, $history->keterangan);
    }
}
