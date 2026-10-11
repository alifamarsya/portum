<?php

namespace Tests\Feature;

use App\Models\AsMutasiAset;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\TicketField;
use App\Models\User;
use Database\Seeders\TicketFieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketFieldConfigAndMutasiStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['nama' => 'admin', 'label' => 'Super Admin', 'deskripsi' => 'Administrator']);
        $roleUser = Role::create(['nama' => 'user', 'label' => 'User Pemohon', 'deskripsi' => 'User']);

        RolePermission::create(['role_id' => $roleAdmin->id, 'perm_key' => 'config_ticket']);
        RolePermission::create(['role_id' => $roleAdmin->id, 'perm_key' => 'ticketing']);

        $this->admin = User::factory()->create([
            'nama_lengkap' => 'Admin Portum',
            'role_id'      => $roleAdmin->id,
            'is_active'    => true,
        ]);

        $this->user = User::factory()->create([
            'nama_lengkap' => 'User Pemohon',
            'role_id'      => $roleUser->id,
            'is_active'    => true,
        ]);

        // Seed default ticket fields
        $this->seed(TicketFieldSeeder::class);
    }

    /**
     * Test pembuatan field tiket kustom dengan urutan otomatis (max + 1).
     */
    public function test_can_create_custom_ticket_field_with_auto_sort_order()
    {
        $maxOrder = TicketField::max('sort_order');

        $response = $this->actingAs($this->admin)
            ->post(route('konfigurasi.tiket.fields.store'), [
                'label'        => 'Lokasi Kerusakan',
                'field_name'   => 'lokasi_kerusakan',
                'field_type'   => 'text',
                'sort_order'   => '',
                'help_text'    => 'Contoh: Gedung A Lantai 2',
                'show_in_form' => '1',
                'show_in_list' => '1',
            ]);

        $response->assertRedirect(route('konfigurasi.tiket.index', ['tab' => 'fields']));
        $response->assertSessionHas('status');

        $field = TicketField::where('field_name', 'lokasi_kerusakan')->first();
        $this->assertNotNull($field);
        $this->assertEquals('Lokasi Kerusakan', $field->label);
        $this->assertEquals($maxOrder + 1, $field->sort_order);
        $this->assertTrue($field->show_in_form);
        $this->assertTrue($field->show_in_list);
        $this->assertFalse($field->is_system);
    }

    /**
     * Test pembuatan field tiket kustom dengan urutan manual (memverifikasi pergeseran slot urutan).
     */
    public function test_can_create_custom_ticket_field_with_manual_sort_order_shifting()
    {
        // Field nomor 2 sebelumnya adalah user_id
        $oldFieldAt2 = TicketField::where('sort_order', 2)->first();
        $this->assertNotNull($oldFieldAt2);

        $response = $this->actingAs($this->admin)
            ->post(route('konfigurasi.tiket.fields.store'), [
                'label'        => 'Nomor Inventaris',
                'field_name'   => 'nomor_inventaris',
                'field_type'   => 'text',
                'sort_order'   => 2,
                'show_in_form' => '1',
                'show_in_list' => '1',
            ]);

        $response->assertRedirect();
        
        $newField = TicketField::where('field_name', 'nomor_inventaris')->first();
        $this->assertNotNull($newField);
        $this->assertEquals(2, $newField->sort_order);

        // Field lama harus bergeser ke 3
        $oldFieldAt2->refresh();
        $this->assertEquals(3, $oldFieldAt2->sort_order);
    }

    /**
     * Test update field tiket kustom dan field bawaan (is_system).
     */
    public function test_can_update_custom_and_system_ticket_field()
    {
        $customField = TicketField::create([
            'label'        => 'Perangkat',
            'field_name'   => 'perangkat',
            'field_type'   => 'select',
            'options'      => ['Laptop', 'PC'],
            'sort_order'   => 20,
            'is_system'    => false,
            'is_active'    => true,
            'show_in_form' => true,
            'show_in_list' => true,
        ]);

        // Update custom field
        $respCustom = $this->actingAs($this->admin)
            ->put(route('konfigurasi.tiket.fields.update', $customField), [
                'label'        => 'Perangkat Kerja',
                'field_type'   => 'select',
                'options'      => "Laptop\nPC\nPrinter",
                'sort_order'   => 20,
                'is_active'    => '1',
                'show_in_form' => '1',
            ]);

        $respCustom->assertRedirect();
        $customField->refresh();
        $this->assertEquals('Perangkat Kerja', $customField->label);
        $this->assertEquals(['Laptop', 'PC', 'Printer'], $customField->options);

        // Update system field (field_type tidak dikirim / null karena disabled di UI)
        $systemField = TicketField::where('field_name', 'description')->first();
        $this->assertNotNull($systemField);
        $this->assertTrue($systemField->is_system);

        $respSystem = $this->actingAs($this->admin)
            ->put(route('konfigurasi.tiket.fields.update', $systemField), [
                'label'        => 'Uraian Kendala Layanan',
                'sort_order'   => $systemField->sort_order,
                'help_text'    => 'Uraikan permasalahan secara detail',
                'is_active'    => '1',
                'show_in_form' => '1',
            ]);

        $respSystem->assertRedirect();
        $systemField->refresh();
        $this->assertEquals('Uraian Kendala Layanan', $systemField->label);
        $this->assertEquals('textarea', $systemField->field_type);
    }

    /**
     * Test reorder urutan field melalui tabel.
     */
    public function test_can_reorder_fields()
    {
        $fields = TicketField::orderBy('sort_order')->take(3)->get();
        $this->assertCount(3, $fields);

        // Reverse urutan 3 field pertama
        $orders = [
            $fields[0]->id => 3,
            $fields[1]->id => 2,
            $fields[2]->id => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('konfigurasi.tiket.fields.reorder'), [
                'orders' => $orders,
            ]);

        $response->assertRedirect();

        $this->assertEquals(1, TicketField::find($fields[2]->id)->sort_order);
        $this->assertEquals(2, TicketField::find($fields[1]->id)->sort_order);
        $this->assertEquals(3, TicketField::find($fields[0]->id)->sort_order);
    }

    /**
     * Test verifikasi label status Mutasi Aset ringkas dan bersih tanpa tanda kurung.
     */
    public function test_mutasi_aset_status_badge_labels_are_concise_without_parentheses()
    {
        $m1 = new AsMutasiAset(['status' => 'Diajukan']);
        $this->assertEquals('Diajukan', $m1->status_badge['label']);
        $this->assertStringNotContainsString('(', $m1->status_badge['label']);

        $m2 = new AsMutasiAset(['status' => 'Diproses']);
        $this->assertEquals('Diproses', $m2->status_badge['label']);
        $this->assertStringNotContainsString('(', $m2->status_badge['label']);

        $m3 = new AsMutasiAset(['status' => 'Menunggu Approval']);
        $this->assertEquals('Menunggu Approval', $m3->status_badge['label']);
        $this->assertStringNotContainsString('(', $m3->status_badge['label']);

        $m4 = new AsMutasiAset(['status' => 'Disetujui']);
        $this->assertEquals('Disetujui', $m4->status_badge['label']);
        $this->assertStringNotContainsString('(', $m4->status_badge['label']);

        $m5 = new AsMutasiAset(['status' => 'Ditutup', 'status_hasil' => 'Disetujui']);
        $this->assertEquals('Selesai', $m5->status_badge['label']);
        $this->assertStringNotContainsString('(', $m5->status_badge['label']);

        $m6 = new AsMutasiAset(['status' => 'Ditutup', 'status_hasil' => 'Ditolak']);
        $this->assertEquals('Ditolak', $m6->status_badge['label']);
        $this->assertStringNotContainsString('(', $m6->status_badge['label']);

        $m7 = new AsMutasiAset(['status' => 'Ditutup', 'status_hasil' => null]);
        $this->assertEquals('Ditutup', $m7->status_badge['label']);
        $this->assertStringNotContainsString('(', $m7->status_badge['label']);
    }
}
