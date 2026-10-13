<?php

namespace Tests\Feature;

use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketField;
use App\Models\User;
use Database\Seeders\TicketFieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicFieldTiketTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $pemohon;
    protected TicketCategory $category;
    protected InternalDepartment $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = InternalDepartment::create([
            'id'   => 1,
            'name' => 'Bagian Umum & Rumah Tangga',
            'code' => 'UMUM',
        ]);

        $roleAdmin = Role::firstOrCreate(
            ['nama' => 'admin'],
            ['label' => 'Administrator', 'deskripsi' => 'Administrator Sistem']
        );

        $roleUser = Role::firstOrCreate(
            ['nama' => 'user'],
            ['label' => 'User', 'deskripsi' => 'User Pemohon']
        );

        RolePermission::firstOrCreate(
            ['role_id' => $roleAdmin->id, 'perm_key' => 'config_ticket'],
            ['can_write' => true]
        );
        RolePermission::firstOrCreate(
            ['role_id' => $roleAdmin->id, 'perm_key' => 'ticketing'],
            ['can_write' => true]
        );
        RolePermission::firstOrCreate(
            ['role_id' => $roleUser->id, 'perm_key' => 'ticketing'],
            ['can_write' => true]
        );

        $this->admin = User::factory()->create([
            'username'  => 'admin_test',
            'role_id'   => $roleAdmin->id,
            'is_active' => true,
        ]);

        $this->pemohon = User::factory()->create([
            'username'  => 'pemohon_test',
            'role_id'   => $roleUser->id,
            'is_active' => true,
        ]);

        $this->category = TicketCategory::create([
            'name'                 => 'Permintaan Sarana & Prasarana',
            'jenis_pengajuan'      => 'Permintaan',
            'department_id'        => 1,
            'sla_resolution_hours' => 24,
            'is_active'            => true,
        ]);

        // Seed field standar sistem tiket
        $this->seed(TicketFieldSeeder::class);
    }

    /**
     * 1. Konfigurasi Dynamic Field Tiket:
     * - Penambahan custom field baru dengan tipe select, opsi pilihan, dan urutan.
     * - Pengelolaan status aktif dan visibilitas field (form dan tabel).
     */
    public function test_admin_can_configure_ticket_custom_fields_with_select_options_and_visibilities(): void
    {
        // 1a. Admin menambahkan custom field baru dengan tipe select dan opsi
        $response = $this->actingAs($this->admin)->post(route('konfigurasi.tiket.fields.store'), [
            'field_name'   => 'kategori_kendala',
            'label'        => 'Kategori Kendala Layanan',
            'field_type'   => 'select',
            'options'      => "Perangkat Keras\nJaringan & Internet\nAplikasi Perbankan",
            'is_required'  => 1,
            'show_in_form' => 1,
            'show_in_list' => 1,
            'sort_order'   => 15,
            'help_text'    => 'Pilih kelompok kendala teknis layanan',
        ]);

        $response->assertRedirect(route('konfigurasi.tiket.index', ['tab' => 'fields']));

        $field = TicketField::where('field_name', 'kategori_kendala')->first();

        $this->assertNotNull($field);
        $this->assertEquals('select', $field->field_type);
        $this->assertIsArray($field->options);
        $this->assertEquals(['Perangkat Keras', 'Jaringan & Internet', 'Aplikasi Perbankan'], $field->options);
        $this->assertEquals(11, $field->sort_order);
        $this->assertTrue((bool) $field->is_required);
        $this->assertTrue((bool) $field->show_in_form);
        $this->assertTrue((bool) $field->show_in_list);
        $this->assertFalse((bool) $field->is_system);

        // 1b. Admin menguji toggle status aktif dan visibilitas field
        // Toggle Status Aktif (Aktif -> Nonaktif)
        $this->actingAs($this->admin)->patch(route('konfigurasi.tiket.fields.toggle', $field));
        $this->assertFalse($field->fresh()->is_active);

        // Toggle Visibilitas Form (Tampil di Form -> Sembunyi dari Form)
        $this->actingAs($this->admin)->patch(route('konfigurasi.tiket.fields.toggle-form', $field));
        $this->assertFalse($field->fresh()->show_in_form);

        // Toggle Visibilitas Tabel (Tampil di List -> Sembunyi dari List)
        $this->actingAs($this->admin)->patch(route('konfigurasi.tiket.fields.toggle-list', $field));
        $this->assertFalse($field->fresh()->show_in_list);
    }

    /**
     * 2. Form dan Validasi Dynamic Field Tiket:
     * - Field pada form pembuatan tiket ditampilkan sesuai konfigurasi (aktif & show_in_form).
     * - Rendering elemen input, opsi dropdown, dan penanda field wajib.
     * - Validasi standar pengajuan tiket tetap berjalan menolak input tidak valid.
     */
    public function test_ticket_create_form_displays_fields_correctly_and_validates_requirements(): void
    {
        // 2a. Siapkan konfigurasi field aktif, select, number, date, nonaktif, dan tersembunyi
        TicketField::create([
            'field_name'   => 'lokasi_gedung',
            'label'        => 'Lokasi Gedung & Lantai',
            'field_type'   => 'text',
            'is_required'  => true,
            'is_active'    => true,
            'show_in_form' => true,
            'is_system'    => false,
            'sort_order'   => 17,
        ]);

        TicketField::create([
            'field_name'   => 'tipe_perangkat',
            'label'        => 'Tipe Perangkat Kerja',
            'field_type'   => 'select',
            'options'      => ['Laptop', 'Desktop PC', 'Mesin ATM'],
            'is_required'  => true,
            'show_in_form' => true,
            'is_active'    => true,
            'is_system'    => false,
            'sort_order'   => 18,
        ]);

        TicketField::create([
            'field_name'   => 'jumlah_unit',
            'label'        => 'Jumlah Unit Dibutuhkan',
            'field_type'   => 'number',
            'is_required'  => false,
            'show_in_form' => true,
            'is_active'    => true,
            'is_system'    => false,
            'sort_order'   => 19,
        ]);

        TicketField::create([
            'field_name'   => 'target_tanggal',
            'label'        => 'Target Tanggal Selesai',
            'field_type'   => 'date',
            'is_required'  => false,
            'show_in_form' => true,
            'is_active'    => true,
            'is_system'    => false,
            'sort_order'   => 20,
        ]);

        TicketField::create([
            'field_name'   => 'field_nonaktif',
            'label'        => 'Label Field Nonaktif',
            'field_type'   => 'text',
            'is_active'    => false,
            'show_in_form' => true,
            'is_system'    => false,
            'sort_order'   => 21,
        ]);

        TicketField::create([
            'field_name'   => 'field_tersembunyi_form',
            'label'        => 'Label Khusus Internal List',
            'field_type'   => 'text',
            'is_active'    => true,
            'show_in_form' => false,
            'show_in_list' => true,
            'is_system'    => false,
            'sort_order'   => 22,
        ]);

        // 2b. Buka form pembuatan tiket dan verifikasi tampilan sesuai konfigurasi
        $response = $this->actingAs($this->pemohon)->get(route('tickets.create'));
        $response->assertStatus(200);

        // Verifikasi field aktif dan komponennya tampil
        $response->assertSee('Lokasi Gedung & Lantai');
        $response->assertSee('Tipe Perangkat Kerja');
        $response->assertSee('name="tipe_perangkat"', false);
        $response->assertSee('Desktop PC');
        $response->assertSee('Mesin ATM');
        $response->assertSee('type="number"', false);
        $response->assertSee('name="jumlah_unit"', false);
        $response->assertSee('type="date"', false);
        $response->assertSee('name="target_tanggal"', false);

        // Verifikasi field nonaktif dan field internal tersembunyi tidak tampil
        $response->assertDontSee('Label Field Nonaktif');
        $response->assertDontSee('Label Khusus Internal List');

        // 2c. Uji validasi standar pengajuan tiket (menolak deskripsi di bawah 100 karakter atau tanpa kategori)
        $respInvalid = $this->actingAs($this->pemohon)->post(route('tickets.store'), [
            'jenis_pengajuan' => 'Permintaan',
            'description'     => 'Deskripsi pendek di bawah seratus karakter.',
            'lokasi_gedung'   => 'Lantai 1',
        ]);

        $respInvalid->assertSessionHasErrors(['category_id', 'description']);
        $this->assertEquals(0, Ticket::count());
    }

    /**
     * 3. Penyimpanan dan Penampilan Nilai Dynamic Field Tiket:
     * - Nilai field dinamis aktif tersimpan dengan benar di kolom tickets.custom_fields.
     * - Field nonaktif atau tidak terdaftar diabaikan dan tidak ikut tersimpan.
     * - Nilai tersimpan ditampilkan kembali pada detail tiket (show) dan tabel daftar tiket (index).
     */
    public function test_ticket_store_saves_custom_fields_and_displays_on_show_and_index(): void
    {
        // 3a. Buat field aktif untuk form dan tabel, serta field nonaktif
        TicketField::create([
            'field_name'   => 'lokasi_penempatan',
            'label'        => 'Lokasi Penempatan',
            'field_type'   => 'text',
            'is_required'  => true,
            'show_in_form' => true,
            'show_in_list' => true,
            'is_active'    => true,
            'is_system'    => false,
            'sort_order'   => 23,
        ]);

        TicketField::create([
            'field_name'   => 'spesifikasi_teknis',
            'label'        => 'Spesifikasi Teknis Kebutuhan',
            'field_type'   => 'text',
            'is_required'  => false,
            'show_in_form' => true,
            'show_in_list' => true,
            'is_active'    => true,
            'is_system'    => false,
            'sort_order'   => 24,
        ]);

        TicketField::create([
            'field_name'   => 'field_nonaktif',
            'label'        => 'Field Nonaktif',
            'field_type'   => 'text',
            'is_active'    => false,
            'show_in_form' => true,
            'is_system'    => false,
            'sort_order'   => 25,
        ]);

        // 3b. Submit tiket dengan nilai field valid, ditambah upaya injeksi field nonaktif dan sembarangan
        $response = $this->actingAs($this->pemohon)->post(route('tickets.store'), [
            'jenis_pengajuan'    => 'Permintaan',
            'category_id'        => $this->category->id,
            'description'        => 'Uraian pengajuan kebutuhan fasilitas operasional kantor secara lengkap dan jelas lebih dari batas minimal seratus karakter.',
            'lokasi_penempatan'  => 'Gedung Kantor Wilayah Lantai 2',
            'spesifikasi_teknis' => 'RAM 16GB SSD 512GB Core i7',
            'field_nonaktif'     => 'injeksi_field_nonaktif',
            'field_sembarangan'  => 'injeksi_field_tak_terdaftar',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 3c. Verifikasi record dan kolom custom_fields di database
        $ticket = Ticket::where('user_id', $this->pemohon->id)->latest('id')->first();
        $this->assertNotNull($ticket);
        $this->assertIsArray($ticket->custom_fields);
        $this->assertEquals('Gedung Kantor Wilayah Lantai 2', $ticket->custom_fields['lokasi_penempatan']);
        $this->assertEquals('RAM 16GB SSD 512GB Core i7', $ticket->custom_fields['spesifikasi_teknis']);

        // Pastikan field nonaktif dan field tak terdaftar diabaikan
        $this->assertArrayNotHasKey('field_nonaktif', $ticket->custom_fields);
        $this->assertArrayNotHasKey('field_sembarangan', $ticket->custom_fields);

        // 3d. Verifikasi penampilan nilai pada halaman detail tiket (show)
        $respShow = $this->actingAs($this->pemohon)->get(route('tickets.show', $ticket));
        $respShow->assertStatus(200);
        $respShow->assertSee('Lokasi Penempatan');
        $respShow->assertSee('Gedung Kantor Wilayah Lantai 2');
        $respShow->assertSee('Spesifikasi Teknis Kebutuhan');
        $respShow->assertSee('RAM 16GB SSD 512GB Core i7');

        // 3e. Verifikasi penampilan nilai pada tabel daftar tiket (index)
        $respIndex = $this->actingAs($this->pemohon)->get(route('tickets.index'));
        $respIndex->assertStatus(200);
        $respIndex->assertSee('Lokasi Penempatan');
        $respIndex->assertSee('Gedung Kantor Wilayah Lantai 2');
        $respIndex->assertSee('Spesifikasi Teknis Kebutuhan');
        $respIndex->assertSee('RAM 16GB SSD 512GB Core i7');
    }
}
