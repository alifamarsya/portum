<?php

namespace Tests\Feature;

use App\Models\RefAkun;
use App\Models\RisalahRapat;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UmBiayaHarian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalAndReferenceAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create([
            'nama'  => 'superadmin',
            'label' => 'Super Administrator',
        ]);

        $perms = [
            'umum_rt', 'dokumen_arsip', 'administrasi_aset',
            'logistik_pelaporan', 'pengadaan', 'pemeliharaan_pengawasan',
            'ref_akun', 'risalah', 'dashboard'
        ];

        foreach ($perms as $perm) {
            RolePermission::create([
                'role_id'   => $roleAdmin->id,
                'perm_key'  => $perm,
                'can_write' => true,
            ]);
        }

        $this->adminUser = User::factory()->create([
            'username' => 'admin_test',
            'role_id'  => $roleAdmin->id,
            'is_active' => true,
        ]);
    }

    /** Test 1: Buku Panduan route is 404 and does not exist in sidebar */
    public function test_buku_panduan_is_completely_removed(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/panduan');
        $response->assertStatus(404);

        $dashResp = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $dashResp->assertDontSee('Buku Panduan');
        $dashResp->assertDontSee('panduan.index');
    }

    /** Test 2: All operational modules have maker_checker false, no file fields, and all fields req true */
    public function test_operational_modules_configuration(): void
    {
        $modules = config('modules');
        $opPerms = [
            'umum_rt', 'dokumen_arsip', 'administrasi_aset',
            'logistik_pelaporan', 'pengadaan', 'pemeliharaan_pengawasan'
        ];

        $techFields = ['maker_id', 'checker_id', 'approval_status', 'approved_at', 'catatan_approval', 'dibuat_oleh'];

        foreach ($modules as $key => $cfg) {
            if (in_array($cfg['perm'] ?? '', $opPerms)) {
                // Must not use maker_checker
                $this->assertFalse((bool) ($cfg['maker_checker'] ?? false), "Module $key must have maker_checker false");

                // Must not have file fields
                foreach ($cfg['fields'] as $fName => $fMeta) {
                    $this->assertNotEquals('file', $fMeta['type'] ?? '', "Module $key field $fName must not have type file");

                    // If not technical or auto_user, must be req => true
                    if (!in_array($fName, $techFields) && ($fMeta['type'] ?? '') !== 'auto_user') {
                        $this->assertTrue((bool) ($fMeta['req'] ?? false), "Module $key field $fName must be required");
                    }
                }
            }
        }
    }

    /** Test 3: Reference modules (ref_akun and risalah) require all fields */
    public function test_reference_modules_require_all_fields(): void
    {
        // 1. ref_akun in config
        $refAkunCfg = config('modules.ref_akun');
        $this->assertNotNull($refAkunCfg);
        foreach ($refAkunCfg['fields'] as $fName => $fMeta) {
            $this->assertTrue((bool) ($fMeta['req'] ?? false), "ref_akun field $fName must be required");
        }

        // Test ref_akun rejection when field is empty
        $respRefEmpty = $this->actingAs($this->adminUser)->post('/modul/ref_akun', [
            'nama_beban' => 'Biaya Test',
        ]);
        $respRefEmpty->assertSessionHasErrors(['rekening_debet', 'contoh_keterangan']);

        // Test ref_akun valid submission
        $respRefValid = $this->actingAs($this->adminUser)->post('/modul/ref_akun', [
            'nama_beban' => 'Biaya Test COA',
            'rekening_debet' => '5.1.09.99',
            'contoh_keterangan' => 'Keterangan lengkap akun',
        ]);
        $respRefValid->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ref_akun', ['nama_beban' => 'Biaya Test COA']);

        // 2. Risalah Rapat rejection when fields are missing
        $respRisalahEmpty = $this->actingAs($this->adminUser)->post(route('risalah.store'), [
            'judul' => 'Rapat Koordinasi',
        ]);
        $respRisalahEmpty->assertSessionHasErrors(['nomor', 'tanggal', 'waktu', 'tempat', 'pemimpin', 'peserta', 'agenda', 'pembahasan', 'keputusan', 'tindak_lanjut']);

        // Test Risalah Rapat valid submission
        $respRisalahValid = $this->actingAs($this->adminUser)->post(route('risalah.store'), [
            'nomor' => 'RIS-001/2026',
            'judul' => 'Rapat Koordinasi Anggaran',
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00 - 11:30 WITA',
            'tempat' => 'Ruang Rapat Lantai 3',
            'pemimpin' => 'Direktur Utama',
            'peserta' => 'Seluruh Kepala Divisi',
            'agenda' => 'Pembahasan RKAP 2026',
            'pembahasan' => 'Penyesuaian target dan realisasi kuartal I',
            'keputusan' => 'Pagu anggaran disetujui',
            'tindak_lanjut' => 'Divisi terkait segera menyusun detail',
        ]);
        $respRisalahValid->assertSessionHasNoErrors();
        $this->assertDatabaseHas('risalah_rapat', ['nomor' => 'RIS-001/2026']);
    }

    /** Test 4: Operational module form view does not display file upload and has required indicators */
    public function test_operational_form_view_has_asterisks_and_no_file_inputs(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/modul/kendaraan/tambah');
        $response->assertStatus(200);
        $response->assertSee('*');
        $response->assertDontSee('placeholder="Nama file (upload asli menyusul)"');
    }

    /** Test 5: Status field is completely removed from operational forms (create & edit) */
    public function test_status_field_is_removed_from_operational_forms(): void
    {
        // 1. Biaya Harian form must NOT have status field
        $respBiaya = $this->actingAs($this->adminUser)->get('/modul/biaya_harian/tambah');
        $respBiaya->assertStatus(200);
        $respBiaya->assertDontSee('name="status"', false);
        $respBiaya->assertDontSee('name="approval_status"', false);

        // 2. Memo Sewa Cabang form must NOT have status_persetujuan
        $respMemo = $this->actingAs($this->adminUser)->get('/modul/memo_sewa_cabang/tambah');
        $respMemo->assertStatus(200);
        $respMemo->assertDontSee('name="status_persetujuan"', false);
        $respMemo->assertDontSee('Status Persetujuan');

        // 3. Mutasi Aset, Disposal Aset, Perencanaan Kebutuhan
        $respMutasi = $this->actingAs($this->adminUser)->get('/modul/mutasi_aset/tambah');
        $respMutasi->assertStatus(200);
        $respMutasi->assertDontSee('name="status"', false);

        $respDisposal = $this->actingAs($this->adminUser)->get('/modul/disposal_aset/tambah');
        $respDisposal->assertStatus(200);
        $respDisposal->assertDontSee('name="status"', false);

        $respRencana = $this->actingAs($this->adminUser)->get('/modul/perencanaan_kebutuhan/tambah');
        $respRencana->assertStatus(200);
        $respRencana->assertDontSee('name="status"', false);

        // 4. Storing without status succeeds and automatically populates default approved/booked status
        $respStoreBiaya = $this->actingAs($this->adminUser)->post('/modul/biaya_harian', [
            'tanggal'         => now()->toDateString(),
            'kategori'        => 'BBM',
            'kendaraan'       => 'DN 1234 XY',
            'nama_beban'      => 'BBM Operasional',
            'rekening_debet'  => '5.1.01.01',
            'rekening_kredit' => '1.1.01.01',
            'uraian'          => 'Operasional kantor harian',
            'jumlah'          => 120000,
            'no_nota'         => 'NOTA-TEST-99',
            'keterangan'      => 'BBM tanpa input status',
        ]);
        $respStoreBiaya->assertSessionHasNoErrors();
        $this->assertDatabaseHas('um_biaya_harian', [
            'no_nota'         => 'NOTA-TEST-99',
            'approval_status' => 'Disetujui',
            'status'          => 'Dibukukan',
        ]);
    }

    /** Test 6: No maker-checker approval/reject buttons on operational module lists */
    public function test_no_approval_buttons_in_operational_tables(): void
    {
        $resp = $this->actingAs($this->adminUser)->get('/modul/biaya_harian');
        $resp->assertStatus(200);
        $resp->assertDontSee('Setujui');
        $resp->assertDontSee('Tolak');
    }

    /** Test 7: Audit all 36 operational module create forms to ensure NO status field is displayed */
    public function test_all_operational_forms_do_not_contain_status_field(): void
    {
        $modules = config('modules');
        $opPerms = [
            'umum_rt', 'dokumen_arsip', 'administrasi_aset',
            'logistik_pelaporan', 'pengadaan', 'pemeliharaan_pengawasan'
        ];

        foreach ($modules as $key => $cfg) {
            if (!in_array($cfg['perm'] ?? '', $opPerms)) {
                continue;
            }

            $resp = $this->actingAs($this->adminUser)->get("/modul/{$key}/tambah");
            $resp->assertStatus(200);
            $resp->assertDontSee('name="status"', false);
            $resp->assertDontSee('name="status_persetujuan"', false);
            $resp->assertDontSee('name="status_arsip"', false);
            $resp->assertDontSee('name="status_pembayaran"', false);
        }
    }

    /** Test 8: Storing operational data without status uses correct business default */
    public function test_store_kendaraan_without_status_uses_business_default(): void
    {
        $resp = $this->actingAs($this->adminUser)->post('/modul/kendaraan', [
            'no_polisi'  => 'DN 9999 ZZ',
            'jenis'      => 'Minibus',
            'merk'       => 'Toyota Innova',
            'tahun'      => '2023',
            'peruntukan' => 'Operasional Direksi',
            'driver'     => 'Budi Santoso',
            'keterangan' => 'Unit kendaraan operasional baru',
        ]);
        $resp->assertSessionHasNoErrors();
        $this->assertDatabaseHas('um_kendaraan', [
            'no_polisi' => 'DN 9999 ZZ',
            'status'    => 'Aktif',
        ]);
    }

    /** Test 9: All 36 operational module tables do NOT contain Status column in table header */
    public function test_all_operational_tables_do_not_contain_status_column(): void
    {
        $modules = config('modules');
        $opPerms = [
            'umum_rt', 'dokumen_arsip', 'administrasi_aset',
            'logistik_pelaporan', 'pengadaan', 'pemeliharaan_pengawasan'
        ];

        foreach ($modules as $key => $cfg) {
            if (!in_array($cfg['perm'] ?? '', $opPerms)) {
                continue;
            }

            $resp = $this->actingAs($this->adminUser)->get("/modul/{$key}");
            $resp->assertStatus(200);

            // Ensure no Status th header is rendered
            $resp->assertDontSee('>Status</th>', false);
            $resp->assertDontSee('>Status Persetujuan</th>', false);
            $resp->assertDontSee('>Status Arsip</th>', false);
            $resp->assertDontSee('>Status Pembayaran</th>', false);
        }
    }

    /** Test 10: Non-operational reference table (ref_akun) works normally */
    public function test_non_operational_reference_table_works_normally(): void
    {
        $resp = $this->actingAs($this->adminUser)->get('/modul/ref_akun');
        $resp->assertStatus(200);
        $resp->assertSee('Nama Beban');
        $resp->assertSee('Rekening Debet');
        $resp->assertSee('Contoh Keterangan');
    }
}
