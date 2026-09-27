<?php

namespace Tests\Feature;

use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketRefactorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $operator;
    protected User $bagianUmum;
    protected TicketCategory $categoryPermintaan;
    protected TicketCategory $categoryPermasalahan;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Setup Department
        InternalDepartment::firstOrCreate(['id' => 1], [
            'name' => 'Bagian Umum & Rumah Tangga',
            'code' => 'UMUM',
        ]);

        // Setup Roles
        $roleUser = Role::firstOrCreate(['nama' => 'user'], ['label' => 'User']);
        $roleOperator = Role::firstOrCreate(['nama' => 'operator'], ['label' => 'Operator']);
        $roleBagianUmum = Role::firstOrCreate(['nama' => 'bagian_umum'], ['label' => 'Bagian Umum & Rumah Tangga']);

        // Setup Users
        $this->user = User::factory()->create([
            'username' => 'pemohon_test',
            'nama_lengkap' => 'Pemohon Test',
            'role_id' => $roleUser->id,
        ]);

        $this->operator = User::factory()->create([
            'username' => 'operator_test',
            'nama_lengkap' => 'Operator Test',
            'role_id' => $roleOperator->id,
        ]);

        $this->bagianUmum = User::factory()->create([
            'username' => 'bagian_umum_test',
            'nama_lengkap' => 'Staf Bagian Umum',
            'role_id' => $roleBagianUmum->id,
            'department_id' => 1,
        ]);

        $this->categoryPermintaan = TicketCategory::create([
            'name' => 'Permintaan ATK & Perlengkapan Kerja',
            'jenis_pengajuan' => 'Permintaan',
            'department_id' => 1,
            'sla_resolution_hours' => 24,
            'is_active' => true,
        ]);

        $this->categoryPermasalahan = TicketCategory::create([
            'name' => 'Kerusakan AC & Kelistrikan',
            'jenis_pengajuan' => 'Permasalahan',
            'department_id' => 1,
            'sla_resolution_hours' => 12,
            'is_active' => true,
        ]);
    }

    /**
     * Test User can view ticket create page without parse or syntax errors
     */
    public function test_user_can_view_ticket_create_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('tickets.create'));
        $response->assertStatus(200);
        $response->assertSee('Buat Tiket Layanan Baru');
        $response->assertSee('Permintaan ATK & Perlengkapan Kerja', false);
    }

    /**
     * 1. Test Dynamic Category API filter by jenis_pengajuan
     */
    public function test_api_returns_categories_filtered_by_jenis_pengajuan(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('api.ticket-categories', ['jenis' => 'Permintaan']));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals('Permintaan', $item['jenis_pengajuan']);
            $this->assertArrayHasKey('sla_resolution_hours', $item);
        }
    }

    /**
     * 2. Test File Upload Validation: Rejects non-PDF
     */
    public function test_ticket_creation_rejects_non_pdf_files(): void
    {
        $nonPdfFile = UploadedFile::fake()->create('dokumen.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->user)
            ->post(route('tickets.store'), [
                'title' => 'Uji Coba Validasi Non PDF',
                'jenis_pengajuan' => 'Permintaan',
                'category_id' => $this->categoryPermintaan->id,
                'description' => 'Mencoba mengunggah file non PDF yang seharusnya ditolak oleh sistem.',
                'attachments' => [$nonPdfFile],
            ]);

        $response->assertSessionHasErrors(['attachments.0']);
    }

    /**
     * 3. Test File Upload: Accepts Multiple PDF files up to 100MB
     */
    public function test_ticket_creation_accepts_multiple_pdf_files(): void
    {
        $pdf1 = UploadedFile::fake()->create('lampiran_1.pdf', 1024, 'application/pdf');
        $pdf2 = UploadedFile::fake()->create('lampiran_2.pdf', 2048, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('tickets.store'), [
                'title' => 'Pengadaan ATK Multi PDF',
                'jenis_pengajuan' => 'Permintaan',
                'category_id' => $this->categoryPermintaan->id,
                'description' => 'Deskripsi pengajuan dengan multi lampiran PDF.',
                'attachments' => [$pdf1, $pdf2],
            ]);

        $response->assertRedirect();
        
        $ticket = Ticket::where('title', 'Pengadaan ATK Multi PDF')->latest()->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('Menunggu Verifikasi', $ticket->status);
        $this->assertEquals(24, $ticket->sla_resolution_hours);

        // Cek ticket_attachments
        $attachments = TicketAttachment::where('ticket_id', $ticket->id)->get();
        $this->assertCount(2, $attachments);
        $this->assertEquals('lampiran_1.pdf', $attachments[0]->file_name);
        $this->assertEquals('lampiran_2.pdf', $attachments[1]->file_name);
    }

    /**
     * 4. Test Streamlined Workflow End-to-End:
     * User creates -> Operator allocates -> Bagian accepts -> Bagian updates progress -> Bagian completes -> User closes
     */
    public function test_full_streamlined_ticketing_workflow(): void
    {
        $ticketService = app(TicketService::class);

        // Step 1: User creates ticket
        $ticket = $ticketService->createTicket([
            'title' => 'AC Ruang Kerja Mati Total',
            'jenis_pengajuan' => 'Permasalahan',
            'category_id' => $this->categoryPermasalahan->id,
            'description' => 'AC tiba-tiba mati dan mengeluarkan bau hangus.',
        ], $this->user);

        $this->assertEquals('Menunggu Verifikasi', $ticket->status);
        $this->assertEquals(12, $ticket->sla_resolution_hours);

        // Step 2: Operator allocates to Bagian Umum
        $ticket = $ticketService->allocateTicket($ticket, 1, 'Dialokasikan ke Bagian Umum untuk penanganan teknis.', $this->operator);
        $this->assertEquals('Dialokasikan', $ticket->status);
        $this->assertEquals(1, $ticket->department_id);

        // Step 3: Bagian Umum accepts ticket (status -> Dalam Proses, SLA starts)
        $responseAccept = $this->actingAs($this->bagianUmum)
            ->post(route('tickets.accept', $ticket), [
                'notes' => 'Tiket diterima oleh tim Bagian Umum dan teknisi segera diberangkatkan.',
            ]);
        $responseAccept->assertRedirect();

        $ticket->refresh();
        $this->assertEquals('Dalam Proses', $ticket->status);
        $this->assertNotNull($ticket->sla_resolution_start_at);
        $this->assertEquals($this->bagianUmum->id, $ticket->assigned_to);

        // Step 4: Bagian Umum adds progress notes
        $responseProgress = $this->actingAs($this->bagianUmum)
            ->post(route('tickets.add-progress', $ticket), [
                'notes' => 'Teknisi telah membongkar unit AC dan mengganti kapasitor blower.',
            ]);
        $responseProgress->assertRedirect();

        $ticket->refresh();
        $this->assertEquals('Dalam Proses', $ticket->status);

        // Step 5: Bagian Umum completes ticket (status -> Selesai, confirmation_deadline set to 2 working days)
        $responseComplete = $this->actingAs($this->bagianUmum)
            ->post(route('tickets.complete', $ticket), [
                'notes' => 'Perbaikan selesai dan unit AC telah diuji coba dingin normal.',
            ]);
        $responseComplete->assertRedirect();

        $ticket->refresh();
        $this->assertEquals('Selesai', $ticket->status);
        $this->assertNotNull($ticket->confirmation_deadline);

        // Step 6: Requester confirms and closes ticket
        $responseClose = $this->actingAs($this->user)
            ->post(route('tickets.confirm-close', $ticket), [
                'rating' => 5,
                'feedback' => 'Penanganan sangat cepat dan memuaskan. Terima kasih!',
            ]);
        $responseClose->assertRedirect();

        $ticket->refresh();
        $this->assertEquals('Ditutup Pemohon', $ticket->status);
        $this->assertEquals(5, $ticket->rating);
        $this->assertNotNull($ticket->closed_at);
    }

    /**
     * 5. Test Auto-Close Expired Tickets (2 working days deadline passed)
     */
    public function test_auto_close_expired_tickets(): void
    {
        $ticketService = app(TicketService::class);

        $ticket = $ticketService->createTicket([
            'title' => 'Pembersihan Ruang Arsip',
            'jenis_pengajuan' => 'Permintaan',
            'category_id' => $this->categoryPermintaan->id,
            'description' => 'Mohon bantuan pembersihan ruang arsip.',
        ], $this->user);

        $ticketService->allocateTicket($ticket, 1, 'Alokasi ke bagian umum', $this->operator);
        $ticketService->acceptTicket($ticket, $this->bagianUmum);
        $ticketService->completeTicket($ticket, $this->bagianUmum);

        $ticket->refresh();
        $this->assertEquals('Selesai', $ticket->status);

        // Simulasikan deadline sudah lewat (misal 3 hari yang lalu)
        $ticket->update(['confirmation_deadline' => now()->subDay()]);

        $closedCount = $ticketService->autoCloseExpiredTickets();
        $this->assertGreaterThanOrEqual(1, $closedCount);

        $ticket->refresh();
        $this->assertEquals('Ditutup Otomatis (Sistem)', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }
}
