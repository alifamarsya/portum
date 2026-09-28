<?php

namespace Tests\Feature;

use App\Models\AsAset;
use App\Models\AsCustomField;
use App\Models\AsMutasiAset;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DynamicFieldMutasiTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $pemohon;
    protected AsAset $aset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::whereHas('role', fn ($q) => $q->where('nama', 'admin'))->first()
            ?? User::where('role_id', 1)->first();

        $this->pemohon = User::whereHas('role', fn ($q) => $q->where('nama', 'user'))->first()
            ?? User::factory()->create(['role_id' => 4]);

        $this->aset = AsAset::first() ?? AsAset::create([
            'kode_aset'       => 'AST-TEST-001',
            'nama_aset'       => 'Laptop Lenovo ThinkPad Test',
            'kategori'        => 'Elektronik',
            'lokasi'          => 'Kantor Pusat Lt. 3',
            'penanggung_jawab'=> 'Budi Santoso',
            'kondisi'         => 'Baik',
        ]);
    }

    public function test_admin_can_add_custom_field_to_mutasi_with_select_type_and_options(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.custom-fields.store'), [
            'module_key'  => 'mutasi',
            'field_name'  => 'urgensi_mutasi',
            'label'       => 'Tingkat Urgensi',
            'field_type'  => 'select',
            'options'     => "Rendah\nSedang\nTinggi\nDarurat",
            'is_required' => 1,
            'show_in_list'=> 1,
            'sort_order'  => 5,
            'help_text'   => 'Pilih prioritas urgensi mutasi aset',
        ]);

        $response->assertRedirect(route('admin.custom-fields.index', ['module' => 'mutasi']));

        $field = AsCustomField::where('module_key', 'mutasi')
            ->where('field_name', 'urgensi_mutasi')
            ->first();

        $this->assertNotNull($field);
        $this->assertEquals('select', $field->field_type);
        $this->assertIsArray($field->options);
        $this->assertEquals(['Rendah', 'Sedang', 'Tinggi', 'Darurat'], $field->options);
        $this->assertEquals(5, $field->sort_order);
        $this->assertTrue((bool)$field->is_required);
    }

    public function test_admin_can_reorder_fields(): void
    {
        $fieldLokasi = AsCustomField::where('module_key', 'mutasi')->where('field_name', 'ke_lokasi')->first();
        $fieldPj = AsCustomField::where('module_key', 'mutasi')->where('field_name', 'ke_penanggung_jawab')->first();

        if ($fieldLokasi && $fieldPj) {
            $response = $this->actingAs($this->admin)->post(route('admin.custom-fields.reorder'), [
                'module' => 'mutasi',
                'orders' => [
                    $fieldLokasi->id => 10,
                    $fieldPj->id     => 5,
                ],
            ]);

            $response->assertRedirect(route('admin.custom-fields.index', ['module' => 'mutasi']));

            $this->assertEquals(10, $fieldLokasi->fresh()->sort_order);
            $this->assertEquals(5, $fieldPj->fresh()->sort_order);
        }
    }

    public function test_mutasi_store_validates_dynamic_fields_and_saves_custom_fields(): void
    {
        // 1. Buat field dinamis tambahan bertipe select di konteks mutasi
        AsCustomField::firstOrCreate(
            ['module_key' => 'mutasi', 'field_name' => 'alasan_kategori'],
            [
                'label'       => 'Kategori Alasan Mutasi',
                'field_type'  => 'select',
                'options'     => ['Pergantian Pegawai', 'Kebutuhan Proyek', 'Kerusakan Ruangan'],
                'is_required' => true,
                'show_in_list'=> true,
                'sort_order'  => 6,
                'is_active'   => true,
            ]
        );

        // 2. Submit form create mutasi oleh pemohon dengan field kustom terisi
        $response = $this->actingAs($this->pemohon)->post(route('mutasi-aset.store'), [
            'nama_pemohon'        => 'Ahmad Rivai',
            'jabatan_pemohon'     => 'Staf IT Cabang',
            'username_pemohon'    => $this->pemohon->username,
            'aset_id'             => $this->aset->id,
            'ke_lokasi'           => 'Kantor Cabang Manado',
            'ke_penanggung_jawab' => 'Siti Nurhaliza',
            'alasan'              => 'Pemenuhan kebutuhan workstation cabang baru.',
            'alasan_kategori'     => 'Kebutuhan Proyek',
        ]);

        $response->assertSessionHasNoErrors();

        // 3. Periksa record tersimpan di tabel as_mutasi_aset
        $mutasi = AsMutasiAset::where('aset_id', $this->aset->id)
            ->where('ke_lokasi', 'Kantor Cabang Manado')
            ->latest('id')
            ->first();

        $this->assertNotNull($mutasi);
        $this->assertEquals('Kantor Cabang Manado', $mutasi->ke_lokasi);
        $this->assertEquals('Siti Nurhaliza', $mutasi->ke_penanggung_jawab);
        $this->assertIsArray($mutasi->custom_fields);
        $this->assertEquals('Kebutuhan Proyek', $mutasi->custom_fields['alasan_kategori'] ?? null);
        // Akses via magic getter __get()
        $this->assertEquals('Kebutuhan Proyek', $mutasi->alasan_kategori);
    }
}
