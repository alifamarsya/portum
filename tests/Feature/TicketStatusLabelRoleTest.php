<?php

namespace Tests\Feature;

use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketHistory;
use App\Models\User;
use App\Notifications\TicketAllocatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketStatusLabelRoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $pemohon;
    protected User $operator;
    protected User $kabagUmum;
    protected User $stafUmum;
    protected TicketCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Department
        InternalDepartment::firstOrCreate(['id' => 1], [
            'name' => 'Bagian Umum & Rumah Tangga',
            'code' => 'UMUM',
        ]);

        // 2. Roles
        $roleUser = Role::firstOrCreate(['nama' => 'user'], ['label' => 'User']);
        $roleOperator = Role::firstOrCreate(['nama' => 'operator'], ['label' => 'Operator']);
        $roleKabagUmum = Role::firstOrCreate(['nama' => 'bagian_umum'], ['label' => 'Bagian Umum & Rumah Tangga']);
        $roleStafUmum = Role::firstOrCreate(['nama' => 'uk_umum_rt'], ['label' => 'Unit Kerja Umum & RT']);

        // 3. Users
        $this->pemohon = User::factory()->create([
            'username' => 'pemohon_label_test',
            'nama_lengkap' => 'Pemohon Label Test',
            'role_id' => $roleUser->id,
        ]);

        $this->operator = User::factory()->create([
            'username' => 'operator_label_test',
            'nama_lengkap' => 'Operator Label Test',
            'role_id' => $roleOperator->id,
        ]);

        $this->kabagUmum = User::factory()->create([
            'username' => 'kabag_umum_label_test',
            'nama_lengkap' => 'Kabag Umum Label Test',
            'role_id' => $roleKabagUmum->id,
            'department_id' => 1,
        ]);

        $this->stafUmum = User::factory()->create([
            'username' => 'staf_umum_label_test',
            'nama_lengkap' => 'Staf Umum Label Test',
            'role_id' => $roleStafUmum->id,
            'department_id' => 1,
        ]);

        // 4. Category
        $this->category = TicketCategory::firstOrCreate(['name' => 'ATK Test'], [
            'department_id' => 1,
            'jenis_pengajuan' => 'Permintaan',
            'sla_resolution_hours' => 24,
            'is_active' => true,
        ]);
    }

    /**
     * Test mapping status labels for Pemohon (User).
     */
    public function test_pemohon_status_labels(): void
    {
        $this->assertEquals('Diajukan', Ticket::mapStatusLabel('Menunggu Verifikasi', $this->pemohon));
        $this->assertEquals('Sedang Ditangani', Ticket::mapStatusLabel('Dialokasikan', $this->pemohon));
        $this->assertEquals('Sedang Ditangani', Ticket::mapStatusLabel('Diverifikasi', $this->pemohon));
        $this->assertEquals('Sedang Ditangani', Ticket::mapStatusLabel('Didistribusikan', $this->pemohon));
        $this->assertEquals('Sedang Dikerjakan', Ticket::mapStatusLabel('Dalam Proses', $this->pemohon));
        $this->assertEquals('Selesai (Menunggu Konfirmasi)', Ticket::mapStatusLabel('Selesai', $this->pemohon));
        $this->assertEquals('Ditutup (Dikonfirmasi)', Ticket::mapStatusLabel('Ditutup Pemohon', $this->pemohon));
        $this->assertEquals('Ditutup Otomatis (Kedaluwarsa)', Ticket::mapStatusLabel('Ditutup Otomatis (Sistem)', $this->pemohon));
        $this->assertEquals('Tidak Dapat Diproses', Ticket::mapStatusLabel('Ditolak', $this->pemohon));
    }

    /**
     * Test mapping status labels for Operator.
     */
    public function test_operator_status_labels(): void
    {
        $this->assertEquals('Menunggu Verifikasi', Ticket::mapStatusLabel('Menunggu Verifikasi', $this->operator));
        $this->assertEquals('Diterima Operator', Ticket::mapStatusLabel('Dialokasikan', $this->operator));
        $this->assertEquals('Diterima Operator', Ticket::mapStatusLabel('Diverifikasi', $this->operator));
        $this->assertEquals('Sedang Ditangani', Ticket::mapStatusLabel('Didistribusikan', $this->operator));
        $this->assertEquals('Sedang Dikerjakan', Ticket::mapStatusLabel('Dalam Proses', $this->operator));
        $this->assertEquals('Selesai (Menunggu Konfirmasi)', Ticket::mapStatusLabel('Selesai', $this->operator));
        $this->assertEquals('Ditutup (Dikonfirmasi)', Ticket::mapStatusLabel('Ditutup Pemohon', $this->operator));
        $this->assertEquals('Ditolak', Ticket::mapStatusLabel('Ditolak', $this->operator));
    }

    /**
     * Test mapping status labels for Kabag and Staf Unit Kerja.
     */
    public function test_kabag_and_staff_status_labels(): void
    {
        // Kabag
        $this->assertEquals('Sedang Ditindaklanjuti Kabag', Ticket::mapStatusLabel('Dialokasikan', $this->kabagUmum));
        $this->assertEquals('Sedang Ditindaklanjuti Kabag', Ticket::mapStatusLabel('Diverifikasi', $this->kabagUmum));
        $this->assertEquals('Sedang Ditindaklanjuti', Ticket::mapStatusLabel('Didistribusikan', $this->kabagUmum));
        $this->assertEquals('Sedang Dikerjakan', Ticket::mapStatusLabel('Dalam Proses', $this->kabagUmum));

        // Staf
        $this->assertEquals('Sedang Ditindaklanjuti Kabag', Ticket::mapStatusLabel('Dialokasikan', $this->stafUmum));
        $this->assertEquals('Sedang Ditindaklanjuti', Ticket::mapStatusLabel('Didistribusikan', $this->stafUmum));
        $this->assertEquals('Sedang Dikerjakan', Ticket::mapStatusLabel('Dalam Proses', $this->stafUmum));
    }

    /**
     * Test ticket history status labels and display notes.
     */
    public function test_ticket_history_status_labels(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TIK-TEST-001',
            'user_id' => $this->pemohon->id,
            'department_id' => 1,
            'category_id' => $this->category->id,
            'title' => 'Permintaan Pengadaan Kursi Rapat',
            'description' => 'Deskripsi kebutuhan pengadaan kursi rapat di aula kantor lantai 2 sebanyak 10 unit dengan spesifikasi standar ergonomis.',
            'status' => 'Dialokasikan',
            'priority' => 'Sedang',
        ]);

        $history = TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->operator->id,
            'old_status' => 'Menunggu Verifikasi',
            'new_status' => 'Dialokasikan',
            'notes' => 'Tiket dialokasikan ke Bagian Umum & Rumah Tangga',
        ]);

        // Pemohon view
        $this->assertEquals('Sedang Ditangani', $history->getRoleStatusLabel($this->pemohon));
        $this->assertStringNotContainsString('dialokasikan', strtolower($history->display_notes));
        $this->assertStringContainsString('diteruskan ke', $history->display_notes);

        // Operator view
        $this->assertEquals('Diterima Operator', $history->getRoleStatusLabel($this->operator));

        // Kabag view
        $this->assertEquals('Sedang Ditindaklanjuti Kabag', $history->getRoleStatusLabel($this->kabagUmum));
    }

    /**
     * Test ticket notification message content uses user-friendly terminology.
     */
    public function test_ticket_allocated_notification_content(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TIK-NOTIF-001',
            'user_id' => $this->pemohon->id,
            'department_id' => 1,
            'category_id' => $this->category->id,
            'title' => 'Permintaan Komputer Laptop Baru',
            'description' => 'Permintaan unit laptop untuk keperluan operasional tim pelaporan keuangan akhir tahun.',
            'status' => 'Dialokasikan',
            'priority' => 'Tinggi',
        ]);

        $notification = new TicketAllocatedNotification($ticket, 'Bagian Umum & Rumah Tangga');
        $data = $notification->toDatabase($this->pemohon);

        $this->assertEquals('Tiket TIK-NOTIF-001 Sedang Ditangani', $data['title']);
        $this->assertStringNotContainsString('dialokasikan', strtolower($data['message']));
        $this->assertStringContainsString('diteruskan ke Bagian Umum & Rumah Tangga', $data['message']);
    }

    /**
     * Test ticket views render role-based labels correctly.
     */
    public function test_ticket_views_render_role_based_labels(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TIK-VIEW-001',
            'user_id' => $this->pemohon->id,
            'department_id' => 1,
            'category_id' => $this->category->id,
            'title' => 'Permintaan Pemeliharaan AC Ruang Rapat',
            'description' => 'AC di ruang rapat lantai 3 tidak dingin dan mengeluarkan air menetes sejak 2 hari lalu.',
            'status' => 'Dialokasikan',
            'priority' => 'Sedang',
        ]);

        // Acting as Pemohon: sees 'Sedang Ditangani', not 'Dialokasikan'
        $responsePemohon = $this->actingAs($this->pemohon)->get(route('tickets.show', $ticket));
        $responsePemohon->assertStatus(200);
        $responsePemohon->assertSee('Sedang Ditangani');
        $responsePemohon->assertDontSee('Dialokasikan');

        // Acting as Operator: sees 'Diterima Operator'
        $responseOperator = $this->actingAs($this->operator)->get(route('tickets.show', $ticket));
        $responseOperator->assertStatus(200);
        $responseOperator->assertSee('Diterima Operator');

        // Acting as Kabag: sees 'Sedang Ditindaklanjuti Kabag'
        $responseKabag = $this->actingAs($this->kabagUmum)->get(route('tickets.show', $ticket));
        $responseKabag->assertStatus(200);
        $responseKabag->assertSee('Sedang Ditindaklanjuti Kabag');
    }
}
