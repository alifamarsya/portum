<?php

namespace Tests\Feature;

use App\Models\AsAset;
use App\Models\AsCustomField;
use App\Models\AsMasterLokasi;
use App\Models\AsMasterPersonel;
use App\Models\AsMutasiAset;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Database\Seeders\MasterLokasiPersonelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MutasiMasterLokasiPersonelTest extends TestCase
{
    use RefreshDatabase;

    protected User $userPemohon;
    protected User $operator;
    protected User $stafAset;
    protected User $kabagAset;
    protected AsAset $aset;

    protected function setUp(): void
    {
        parent::setUp();

        $roleUser = Role::create(['nama' => 'user', 'label' => 'User']);
        $roleOperator = Role::create(['nama' => 'operator', 'label' => 'Operator']);
        $roleStaf = Role::create(['nama' => 'uk_administrasi_aset', 'label' => 'Staf Administrasi Aset']);
        $roleKabag = Role::create(['nama' => 'kabag_aset', 'label' => 'Kepala Bagian Aset']);

        foreach ([$roleUser, $roleOperator, $roleStaf, $roleKabag] as $r) {
            RolePermission::create([
                'role_id'   => $r->id,
                'perm_key'  => 'mutasi_aset',
                'can_write' => true,
            ]);
        }

        RolePermission::create([
            'role_id'   => $roleStaf->id,
            'perm_key'  => 'custom_fields',
            'can_write' => true,
        ]);

        $this->userPemohon = User::create([
            'username'     => 'pemohon_user',
            'password'     => bcrypt('secret'),
            'nama_lengkap' => 'Dirli Pratama',
            'email'        => 'dirli@example.com',
            'jabatan'      => 'Staf Divisi',
            'role_id'      => $roleUser->id,
            'status_aktif' => 'aktif',
        ]);

        $this->operator = User::create([
            'username'     => 'operator_user',
            'password'     => bcrypt('secret'),
            'nama_lengkap' => 'Operator Sistem',
            'email'        => 'operator@example.com',
            'role_id'      => $roleOperator->id,
            'status_aktif' => 'aktif',
        ]);

        $this->stafAset = User::create([
            'username'     => 'staf_aset',
            'password'     => bcrypt('secret'),
            'nama_lengkap' => 'Admin Aset',
            'email'        => 'staf@example.com',
            'role_id'      => $roleStaf->id,
            'status_aktif' => 'aktif',
        ]);

        $this->kabagAset = User::create([
            'username'     => 'kabag_aset',
            'password'     => bcrypt('secret'),
            'nama_lengkap' => 'Kepala Bagian Aset',
            'email'        => 'kabag@example.com',
            'role_id'      => $roleKabag->id,
            'status_aktif' => 'aktif',
        ]);

        $this->aset = AsAset::create([
            'kode_aset'        => 'AST-TEST-001',
            'nama_aset'        => 'Laptop Dell Latitude 5420',
            'kategori'         => 'Elektronik & IT',
            'lokasi'           => 'Divisi Cyber',
            'penanggung_jawab' => 'Dirli',
            'status'           => 'Tersedia',
            'kondisi'          => 'Baik',
        ]);

        // Run seed for initial Divisi Cyber, Divisi IT, Divisi SKAI
        $this->seed(MasterLokasiPersonelSeeder::class);
    }

    public function test_user_can_view_mutasi_create_with_master_lokasi_data(): void
    {
        $response = $this->actingAs($this->userPemohon)->get(route('mutasi-aset.create'));

        $response->assertOk();
        $response->assertSee('Lokasi Asal');
        $response->assertSee('Nama Pemohon');
        $response->assertSee('Lokasi Tujuan');
        $response->assertSee('Penanggung Jawab Baru');
        $response->assertSee('Divisi Cyber');
        $response->assertSee('Divisi IT');
        $response->assertSee('Divisi SKAI');
    }

    public function test_user_can_submit_mutasi_with_cascading_master_and_new_pj(): void
    {
        Notification::fake();

        $lokasiAsal = AsMasterLokasi::where('nama_lokasi', 'Divisi Cyber')->first();
        $pemohon = AsMasterPersonel::where('nama_personel', 'Dirli')->first();
        $lokasiTujuan = AsMasterLokasi::where('nama_lokasi', 'Divisi IT')->first();
        $pjTujuan = AsMasterPersonel::where('nama_personel', 'Fauzhira')->first();

        $response = $this->actingAs($this->userPemohon)->post(route('mutasi-aset.store'), [
            'aset_id'             => $this->aset->id,
            'lokasi_asal_id'      => $lokasiAsal->id,
            'pemohon_personel_id' => $pemohon->id,
            'lokasi_tujuan_id'    => $lokasiTujuan->id,
            'penanggung_jawab_id' => $pjTujuan->id,
            'alasan'              => 'Pemindahan unit workstation ke Divisi IT.',
        ]);

        $response->assertSessionHasNoErrors();
        $mutasi = AsMutasiAset::where('aset_id', $this->aset->id)->latest('id')->first();
        $this->assertNotNull($mutasi);
        $response->assertRedirect(route('mutasi-aset.show', $mutasi));

        $this->assertEquals($lokasiAsal->id, $mutasi->lokasi_asal_id);
        $this->assertEquals($pemohon->id, $mutasi->pemohon_personel_id);
        $this->assertEquals($lokasiTujuan->id, $mutasi->lokasi_tujuan_id);
        $this->assertEquals($pjTujuan->id, $mutasi->penanggung_jawab_id);
        $this->assertFalse((bool) $mutasi->is_pemohon_pindah);
        $this->assertEquals('Dirli', $mutasi->nama_pemohon);
        $this->assertEquals('Divisi Cyber', $mutasi->dari_lokasi);
        $this->assertEquals('Divisi IT', $mutasi->ke_lokasi);
        $this->assertEquals('Fauzhira', $mutasi->ke_penanggung_jawab);

        // Personel master data should NOT be altered
        $this->assertEquals($lokasiAsal->id, $pemohon->fresh()->lokasi_id);
    }

    public function test_submit_pemohon_pindah_does_not_relocate_personnel_immediately(): void
    {
        Notification::fake();

        $lokasiAsal = AsMasterLokasi::where('nama_lokasi', 'Divisi Cyber')->first();
        $pemohon = AsMasterPersonel::where('nama_personel', 'Marsya')->first();
        $lokasiTujuan = AsMasterLokasi::where('nama_lokasi', 'Divisi SKAI')->first();

        $this->assertEquals($lokasiAsal->id, $pemohon->lokasi_id);

        $response = $this->actingAs($this->userPemohon)->post(route('mutasi-aset.store'), [
            'aset_id'             => $this->aset->id,
            'lokasi_asal_id'      => $lokasiAsal->id,
            'pemohon_personel_id' => $pemohon->id,
            'lokasi_tujuan_id'    => $lokasiTujuan->id,
            'penanggung_jawab_id' => '__pemohon_pindah__',
            'alasan'              => 'Mutasi personel dan aset ke unit SKAI.',
        ]);

        $response->assertSessionHasNoErrors();
        $mutasi = AsMutasiAset::where('aset_id', $this->aset->id)->latest('id')->first();
        $this->assertNotNull($mutasi);
        $response->assertRedirect(route('mutasi-aset.show', $mutasi));

        $this->assertEquals($lokasiAsal->id, $mutasi->lokasi_asal_id);
        $this->assertEquals($pemohon->id, $mutasi->pemohon_personel_id);
        $this->assertEquals($lokasiTujuan->id, $mutasi->lokasi_tujuan_id);
        $this->assertNull($mutasi->penanggung_jawab_id);
        $this->assertTrue((bool) $mutasi->is_pemohon_pindah);
        $this->assertEquals('Marsya', $mutasi->nama_pemohon);
        $this->assertEquals('Diajukan', $mutasi->status);

        // KRITIS: Setelah submit sukses, master personel BELUM boleh pindah!
        $this->assertEquals($lokasiAsal->id, $pemohon->fresh()->lokasi_id, 'Personel tidak boleh pindah saat baru diajukan.');
    }

    public function test_personnel_is_relocated_only_after_kabag_approves_mutasi(): void
    {
        Notification::fake();

        $lokasiAsal = AsMasterLokasi::where('nama_lokasi', 'Divisi Cyber')->first();
        $pemohon = AsMasterPersonel::where('nama_personel', 'Marsya')->first();
        $lokasiTujuan = AsMasterLokasi::where('nama_lokasi', 'Divisi SKAI')->first();

        // 1. Submit pengajuan
        $this->actingAs($this->userPemohon)->post(route('mutasi-aset.store'), [
            'aset_id'             => $this->aset->id,
            'lokasi_asal_id'      => $lokasiAsal->id,
            'pemohon_personel_id' => $pemohon->id,
            'lokasi_tujuan_id'    => $lokasiTujuan->id,
            'penanggung_jawab_id' => '__pemohon_pindah__',
            'alasan'              => 'Mutasi personel ke SKAI.',
        ]);

        $mutasi = AsMutasiAset::where('aset_id', $this->aset->id)->latest('id')->first();

        // 2. Operator check -> status Diproses
        $this->actingAs($this->operator)->post(route('mutasi-aset.check-operator', $mutasi), [
            'catatan_operator' => 'Data lengkap.',
        ]);
        $this->assertEquals('Diproses', $mutasi->fresh()->status);
        $this->assertEquals($lokasiAsal->id, $pemohon->fresh()->lokasi_id, 'Personel masih di lokasi asal saat Diproses.');

        // 3. Bagian Aset menyetujui mutasi (satu langkah verifikasi & approval) -> status Disetujui
        $this->actingAs($this->kabagAset)->post(route('mutasi-aset.verifikasi-aset', $mutasi), [
            'keputusan'        => 'setujui',
            'catatan_approval' => 'Disetujui untuk mutasi unit dan personel.',
        ]);

        $this->assertEquals('Disetujui', $mutasi->fresh()->status);

        // KRITIS: Sekarang personel di master HARUS sudah pindah ke Lokasi Tujuan!
        $this->assertEquals($lokasiTujuan->id, $pemohon->fresh()->lokasi_id, 'Personel harus pindah ke lokasi tujuan setelah Disetujui oleh Bagian Aset.');
    }

    public function test_personnel_remains_in_origin_if_mutasi_is_rejected_by_kabag(): void
    {
        Notification::fake();

        $lokasiAsal = AsMasterLokasi::where('nama_lokasi', 'Divisi Cyber')->first();
        $pemohon = AsMasterPersonel::where('nama_personel', 'Marsya')->first();
        $lokasiTujuan = AsMasterLokasi::where('nama_lokasi', 'Divisi SKAI')->first();

        // 1. Submit pengajuan
        $this->actingAs($this->userPemohon)->post(route('mutasi-aset.store'), [
            'aset_id'             => $this->aset->id,
            'lokasi_asal_id'      => $lokasiAsal->id,
            'pemohon_personel_id' => $pemohon->id,
            'lokasi_tujuan_id'    => $lokasiTujuan->id,
            'penanggung_jawab_id' => '__pemohon_pindah__',
            'alasan'              => 'Mutasi personel ke SKAI.',
        ]);

        $mutasi = AsMutasiAset::where('aset_id', $this->aset->id)->latest('id')->first();

        // 2. Operator check
        $this->actingAs($this->operator)->post(route('mutasi-aset.check-operator', $mutasi), [
            'catatan_operator' => 'OK.',
        ]);

        // 3. Bagian Aset rejects (satu langkah)
        $response = $this->actingAs($this->kabagAset)->post(route('mutasi-aset.verifikasi-aset', $mutasi), [
            'keputusan'        => 'tolak',
            'alasan_penolakan' => 'Formasi di SKAI sudah penuh.',
        ]);
        $response->assertSessionHasNoErrors();

        $this->assertEquals('Ditutup', $mutasi->fresh()->status);
        $this->assertEquals('Ditolak', $mutasi->fresh()->status_hasil);

        // KRITIS: Karena ditolak, personel di master TETAP di lokasi asal!
        $this->assertEquals($lokasiAsal->id, $pemohon->fresh()->lokasi_id, 'Personel harus tetap di lokasi asal jika pengajuan ditolak.');
    }

    public function test_personnel_remains_in_origin_if_mutasi_is_marked_invalid_by_staf(): void
    {
        Notification::fake();

        $lokasiAsal = AsMasterLokasi::where('nama_lokasi', 'Divisi Cyber')->first();
        $pemohon = AsMasterPersonel::where('nama_personel', 'Marsya')->first();
        $lokasiTujuan = AsMasterLokasi::where('nama_lokasi', 'Divisi SKAI')->first();

        // 1. Submit pengajuan
        $this->actingAs($this->userPemohon)->post(route('mutasi-aset.store'), [
            'aset_id'             => $this->aset->id,
            'lokasi_asal_id'      => $lokasiAsal->id,
            'pemohon_personel_id' => $pemohon->id,
            'lokasi_tujuan_id'    => $lokasiTujuan->id,
            'penanggung_jawab_id' => '__pemohon_pindah__',
            'alasan'              => 'Mutasi personel ke SKAI.',
        ]);

        $mutasi = AsMutasiAset::where('aset_id', $this->aset->id)->latest('id')->first();

        // Staf Aset marks invalid
        $this->actingAs($this->stafAset)->post(route('mutasi-aset.verify-staf', $mutasi), [
            'keputusan'          => 'tidak_valid',
            'catatan_verifikasi' => 'Data aset tidak sesuai fisik.',
        ]);

        $this->assertEquals('Ditutup', $mutasi->fresh()->status);
        $this->assertEquals('Tidak Valid', $mutasi->fresh()->status_hasil);

        // KRITIS: Personel di master TETAP di lokasi asal!
        $this->assertEquals($lokasiAsal->id, $pemohon->fresh()->lokasi_id, 'Personel harus tetap di lokasi asal jika pengajuan tidak valid.');
    }

    public function test_validation_fails_when_pemohon_does_not_belong_to_lokasi_asal(): void
    {
        $lokasiAsal = AsMasterLokasi::where('nama_lokasi', 'Divisi Cyber')->first();
        // Aulia belongs to Divisi SKAI, not Divisi Cyber
        $personelLain = AsMasterPersonel::where('nama_personel', 'Aulia')->first();
        $lokasiTujuan = AsMasterLokasi::where('nama_lokasi', 'Divisi IT')->first();

        $response = $this->actingAs($this->userPemohon)->post(route('mutasi-aset.store'), [
            'aset_id'             => $this->aset->id,
            'lokasi_asal_id'      => $lokasiAsal->id,
            'pemohon_personel_id' => $personelLain->id,
            'lokasi_tujuan_id'    => $lokasiTujuan->id,
            'penanggung_jawab_id' => '__pemohon_pindah__',
            'alasan'              => 'Tes validasi integritas lokasi.',
        ]);

        $response->assertSessionHasErrors('pemohon_personel_id');
        // Master personnel should not change when validation fails
        $this->assertEquals($personelLain->lokasi_id, $personelLain->fresh()->lokasi_id);
    }

    public function test_custom_field_select_options_does_not_block_penanggung_jawab_baru(): void
    {
        Notification::fake();

        // Simulasikan custom field ke_penanggung_jawab bertipe select dengan options terbatas
        AsCustomField::create([
            'module_key'   => 'mutasi',
            'field_name'   => 'ke_penanggung_jawab',
            'label'        => 'Penanggung Jawab Baru',
            'field_type'   => 'select',
            'options'      => ['saya', 'dia', 'kamu'],
            'is_system'    => true,
            'is_required'  => true,
            'is_active'    => true,
            'sort_order'   => 1,
        ]);

        $lokasiAsal = AsMasterLokasi::where('nama_lokasi', 'Divisi Cyber')->first();
        $pemohon = AsMasterPersonel::where('nama_personel', 'Dirli')->first();
        $lokasiTujuan = AsMasterLokasi::where('nama_lokasi', 'Divisi IT')->first();

        // Submit dengan opsi khusus __pemohon_pindah__ (nama Dirli tidak ada di ['saya','dia','kamu'])
        $response = $this->actingAs($this->userPemohon)->post(route('mutasi-aset.store'), [
            'aset_id'             => $this->aset->id,
            'lokasi_asal_id'      => $lokasiAsal->id,
            'pemohon_personel_id' => $pemohon->id,
            'lokasi_tujuan_id'    => $lokasiTujuan->id,
            'penanggung_jawab_id' => '__pemohon_pindah__',
            'alasan'              => 'Pemohon pindah ke Divisi IT.',
        ]);

        $response->assertSessionHasNoErrors();
        $mutasi = AsMutasiAset::where('aset_id', $this->aset->id)->latest('id')->first();
        $this->assertNotNull($mutasi);
        $this->assertEquals('Dirli', $mutasi->ke_penanggung_jawab);
    }

    public function test_staf_aset_can_manage_master_lokasi_and_personel(): void
    {
        // 1. View tab lokasi_personel
        $viewResponse = $this->actingAs($this->stafAset)->get(route('konfigurasi.field-aset.index', ['module' => 'lokasi_personel']));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Master Divisi &amp; Cabang', false);
        $viewResponse->assertSee('Divisi Cyber');

        // 2. Store new lokasi
        $storeLokasiResponse = $this->actingAs($this->stafAset)->post(route('konfigurasi.field-aset.lokasi.store'), [
            'nama_lokasi' => 'Divisi Sumber Daya Manusia',
            'tipe'        => 'Divisi',
            'kode_lokasi' => 'DIV-SDM',
            'sort_order'  => 4,
            'is_active'   => 1,
        ]);
        $storeLokasiResponse->assertRedirect();
        $this->assertDatabaseHas('as_master_lokasi', ['nama_lokasi' => 'Divisi Sumber Daya Manusia']);

        $sdmLokasi = AsMasterLokasi::where('nama_lokasi', 'Divisi Sumber Daya Manusia')->first();

        // 3. Store new personel in that location
        $storePersonelResponse = $this->actingAs($this->stafAset)->post(route('konfigurasi.field-aset.personel.store'), [
            'lokasi_id'     => $sdmLokasi->id,
            'nama_personel' => 'Bambang Supriyanto',
            'nip'           => '19900101',
            'jabatan'       => 'Staf SDM',
            'is_active'     => 1,
        ]);
        $storePersonelResponse->assertRedirect();
        $this->assertDatabaseHas('as_master_personel', [
            'nama_personel' => 'Bambang Supriyanto',
            'lokasi_id'     => $sdmLokasi->id,
        ]);

        $personel = AsMasterPersonel::where('nama_personel', 'Bambang Supriyanto')->first();

        // 4. Update personel
        $updateResponse = $this->actingAs($this->stafAset)->put(route('konfigurasi.field-aset.personel.update', $personel->id), [
            'lokasi_id'     => $sdmLokasi->id,
            'nama_personel' => 'Bambang Supriyanto, S.Psi',
            'nip'           => '19900101',
            'jabatan'       => 'Kepala Seksi Rekrutmen',
            'is_active'     => 1,
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('as_master_personel', [
            'nama_personel' => 'Bambang Supriyanto, S.Psi',
        ]);

        // 5. Toggle status
        $toggleResponse = $this->actingAs($this->stafAset)->patch(route('konfigurasi.field-aset.personel.toggle', $personel->id));
        $toggleResponse->assertRedirect();
        $this->assertFalse((bool) $personel->fresh()->is_active);
    }
}
