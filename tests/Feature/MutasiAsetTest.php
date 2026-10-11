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
    protected User $kadiv;
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
        $roleKadiv = Role::create(['nama' => 'pimpinan', 'label' => 'Pimpinan Divisi']);

        foreach ([$roleUser, $roleOperator, $roleKabagAset, $roleStafAset, $roleKadiv] as $r) {
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

        $this->kadiv = User::create([
            'username'     => 'pimpinan_divisi',
            'password'     => bcrypt('secret'),
            'nama_lengkap' => 'Pimpinan Divisi Umum',
            'email'        => 'pimpinan@banksulteng.co.id',
            'jabatan'      => 'Kepala Divisi',
            'bagian'       => 'Divisi Umum',
            'role_id'      => $roleKadiv->id,
            'department_id'=> null,
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

    public function test_bagian_aset_can_verify_invalid(): void
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

        $response = $this->actingAs($this->stafAset)->post(route('mutasi-aset.verifikasi-aset', $mutasi), [
            'keputusan'          => 'tidak_valid',
            'catatan_verifikasi' => 'Aset ini masih dalam status sewa aktif dan tidak dapat dimutasikan.',
        ]);

        $response->assertRedirect();
        $mutasi->refresh();
        $this->assertEquals('Ditutup', $mutasi->status);
        $this->assertEquals('Tidak Valid', $mutasi->status_hasil);
        $this->assertEquals($this->stafAset->id, $mutasi->verifikator_id);
    }

    public function test_bagian_aset_can_reject(): void
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
            'status'                => 'Diproses',
        ]);

        $response = $this->actingAs($this->kabagAset)->post(route('mutasi-aset.verifikasi-aset', $mutasi), [
            'keputusan'        => 'tolak',
            'alasan_penolakan' => 'Anggaran relokasi dan logistik belum tersedia untuk semester ini.',
            'catatan_approval' => 'Ditolak sementara.',
        ]);

        $response->assertRedirect();
        $mutasi->refresh();
        $this->assertEquals('Ditutup', $mutasi->status);
        $this->assertEquals('Ditolak', $mutasi->status_hasil);
        $this->assertEquals($this->kabagAset->id, $mutasi->verifikator_id);
    }

    public function test_5_stage_mutation_flow_from_submission_to_confirmation(): void
    {
        Notification::fake();

        $tujuanLokasi = 'KC Donggala Lt. 1';
        $tujuanPj = 'Agus Setiawan (Pimpinan Cabang)';

        // 1. Pengajuan
        $mutasi = AsMutasiAset::create([
            'no_mutasi'             => 'MUT-' . date('Ymd') . '-9995',
            'aset_id'               => $this->aset->id,
            'pengaju_id'            => $this->user->id,
            'dari_lokasi'           => $this->aset->lokasi,
            'ke_lokasi'             => $tujuanLokasi,
            'dari_penanggung_jawab' => $this->aset->penanggung_jawab,
            'ke_penanggung_jawab'   => $tujuanPj,
            'alasan'                => 'Relokasi server utama ke KC Donggala',
            'status'                => 'Diajukan',
        ]);

        // 2. Cek Operator -> status Diproses
        $opResponse = $this->actingAs($this->operator)->post(route('mutasi-aset.check-operator', $mutasi), [
            'catatan_operator' => 'Data lengkap.',
        ]);
        $opResponse->assertRedirect();
        $this->assertEquals('Diproses', $mutasi->fresh()->status);

        // 3. Verifikasi Bagian Aset -> status Menunggu Approval (antrian Kadiv)
        $verifyResponse = $this->actingAs($this->stafAset)->post(route('mutasi-aset.verifikasi-aset', $mutasi), [
            'keputusan'          => 'setujui',
            'catatan_verifikasi' => 'Fisik aset layak dan siap dimutasikan.',
        ]);
        $verifyResponse->assertRedirect();
        $mutasi->refresh();
        $this->assertEquals('Menunggu Approval', $mutasi->status);
        $this->assertEquals($this->stafAset->id, $mutasi->verifikator_id);

        // Pengaju belum bisa menutup pengajuan sebelum approval Kadiv
        $earlyConfirmResponse = $this->actingAs($this->user)->post(route('mutasi-aset.konfirmasi-pengaju', $mutasi), [
            'catatan_konfirmasi' => 'Aset diterima lebih awal.',
        ]);
        $earlyConfirmResponse->assertSessionHasErrors('status');
        $this->assertEquals('Menunggu Approval', $mutasi->fresh()->status);

        // Non-kadiv / staf tidak boleh mengeksekusi approval tahap 4
        $unauthKadivResponse = $this->actingAs($this->stafAset)->post(route('mutasi-aset.approval-kadiv', $mutasi), [
            'keputusan' => 'setujui',
        ]);
        $unauthKadivResponse->assertStatus(403);

        // 4. Approval Pimpinan / Kadiv -> status Disetujui
        $kadivResponse = $this->actingAs($this->kadiv)->post(route('mutasi-aset.approval-kadiv', $mutasi), [
            'keputusan'        => 'setujui',
            'catatan_approval' => 'Disetujui untuk peningkatan operasional KC Donggala.',
        ]);
        $kadivResponse->assertRedirect();
        $mutasi->refresh();
        $this->assertEquals('Disetujui', $mutasi->status);
        $this->assertEquals('Disetujui', $mutasi->status_hasil);
        $this->assertEquals($this->kadiv->id, $mutasi->approver_id);

        // 5. Konfirmasi Ditutup oleh Pengaju -> status Ditutup & master aset terupdate
        $confirmResponse = $this->actingAs($this->user)->post(route('mutasi-aset.konfirmasi-pengaju', $mutasi), [
            'catatan_konfirmasi' => 'Aset telah diterima dengan baik.',
        ]);
        $confirmResponse->assertRedirect();

        $mutasi->refresh();
        $this->assertEquals('Ditutup', $mutasi->status);
        $this->assertEquals('Disetujui', $mutasi->status_hasil);

        // Verifikasi tabel as_aset terupdate otomatis
        $this->aset->refresh();
        $this->assertEquals($tujuanLokasi, $this->aset->lokasi);
        $this->assertEquals($tujuanPj, $this->aset->penanggung_jawab);

        // Verifikasi tabel as_aset_histories mencatat mutasi
        $history = AsAsetHistory::where('aset_id', $this->aset->id)
            ->where('field_changed', 'Mutasi Aset Selesai')
            ->latest('id')
            ->first();

        $this->assertNotNull($history);
        $this->assertStringContainsString($tujuanLokasi, $history->new_value);
        $this->assertStringContainsString($mutasi->no_mutasi, $history->keterangan);
    }

    public function test_kadiv_can_reject_mutation_in_stage_4(): void
    {
        Notification::fake();

        $mutasi = AsMutasiAset::create([
            'no_mutasi'             => 'MUT-' . date('Ymd') . '-9996',
            'aset_id'               => $this->aset->id,
            'pengaju_id'            => $this->user->id,
            'dari_lokasi'           => $this->aset->lokasi,
            'ke_lokasi'             => 'KC Buol',
            'dari_penanggung_jawab' => $this->aset->penanggung_jawab,
            'ke_penanggung_jawab'   => 'Farhan',
            'alasan'                => 'Pemindahan server ke Buol',
            'status'                => 'Menunggu Approval',
            'verifikator_id'        => $this->stafAset->id,
            'verified_at'           => now(),
        ]);

        $oldLokasi = $this->aset->lokasi;
        $oldPj = $this->aset->penanggung_jawab;

        $response = $this->actingAs($this->kadiv)->post(route('mutasi-aset.approval-kadiv', $mutasi), [
            'keputusan'        => 'tolak',
            'alasan_penolakan' => 'Ditolak Pimpinan: Unit ini masih krusial di Kantor Pusat.',
        ]);

        $response->assertRedirect();
        $mutasi->refresh();
        $this->assertEquals('Ditutup', $mutasi->status);
        $this->assertEquals('Ditolak', $mutasi->status_hasil);
        $this->assertEquals($this->kadiv->id, $mutasi->approver_id);
        $this->assertEquals('Ditolak Pimpinan: Unit ini masih krusial di Kantor Pusat.', $mutasi->alasan_penolakan);

        // Master aset TIDAK berubah
        $this->aset->refresh();
        $this->assertEquals($oldLokasi, $this->aset->lokasi);
        $this->assertEquals($oldPj, $this->aset->penanggung_jawab);
    }
}
