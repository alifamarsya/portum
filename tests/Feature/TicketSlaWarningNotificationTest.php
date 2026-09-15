<?php

namespace Tests\Feature;

use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketSlaWarningNotification;
use App\Services\TicketSlaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TicketSlaWarningNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $stafUser;
    private User $requesterUser;
    private User $kabagUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup departemen
        InternalDepartment::create([
            'id'   => 1,
            'name' => 'Bagian Umum & Rumah Tangga',
        ]);

        // 2. Setup roles
        $roleStaf = Role::create([
            'id'    => 13,
            'nama'  => 'uk_umum_rt',
            'label' => 'Staf Unit Kerja Umum & RT',
        ]);

        $roleKabag = Role::create([
            'id'    => 10,
            'nama'  => 'kabag_umum',
            'label' => 'Kepala Bagian Umum',
        ]);

        $roleUser = Role::create([
            'id'    => 6,
            'nama'  => 'user',
            'label' => 'User Pemohon',
        ]);

        // 3. Setup users
        $this->stafUser = User::create([
            'username'      => 'staf_test',
            'nama_lengkap'  => 'Staf Pelaksana',
            'email'         => 'staf@banksulteng.co.id',
            'password'      => bcrypt('password'),
            'role_id'       => $roleStaf->id,
            'department_id' => 1,
            'is_active'     => true,
        ]);

        $this->kabagUser = User::create([
            'username'      => 'kabag_test',
            'nama_lengkap'  => 'Kepala Bagian',
            'email'         => 'kabag@banksulteng.co.id',
            'password'      => bcrypt('password'),
            'role_id'       => $roleKabag->id,
            'department_id' => 1,
            'is_active'     => true,
        ]);

        $this->requesterUser = User::create([
            'username'      => 'requester_test',
            'nama_lengkap'  => 'Pemohon Layanan',
            'email'         => 'pemohon@banksulteng.co.id',
            'password'      => bcrypt('password'),
            'role_id'       => $roleUser->id,
            'is_active'     => true,
        ]);
    }

    /**
     * Uji notifikasi peringatan SLA dikirimkan ketika sisa waktu resolusi <= 2 jam (<= 120 menit).
     */
    public function test_sends_sla_warning_notification_when_resolution_time_is_nearly_due(): void
    {
        // Mock waktu hari kerja jam 14:00 (Senin)
        Carbon::setTestNow(Carbon::create(2026, 9, 14, 14, 0, 0));

        // Mulai jam 12:30 hari Senin (telah berjalan 90 menit)
        $startAt = Carbon::create(2026, 9, 14, 12, 30, 0);
        // Target 3 jam (180 menit), sehingga batas akhir jam 15:30 (sisa 90 menit = 1 jam 30 mnt, <= 120 mnt)
        $dueAt = Carbon::create(2026, 9, 14, 15, 30, 0);

        $ticket = Ticket::create([
            'ticket_number'           => 'REQ-20260914-0001',
            'user_id'                 => $this->requesterUser->id,
            'department_id'           => 1,
            'assigned_to'             => $this->stafUser->id,
            'disposed_by'             => $this->kabagUser->id,
            'priority'                => 'Sedang',
            'status'                  => 'Dalam Proses',
            'description'             => 'AC ruang staf bocor',
            'sla_resolution_hours'    => 3,
            'sla_resolution_start_at' => $startAt,
            'sla_resolution_due_at'   => $dueAt,
            'sla_resolution_status'   => 'Berjalan',
        ]);

        $slaService = app(TicketSlaService::class);
        $result = $slaService->checkAndNotifyStaffSlaWarning();

        $this->assertEquals(1, $result['notified_count']);
        $this->assertEquals(1, $this->stafUser->unreadNotifications()->count());

        $notification = $this->stafUser->unreadNotifications()->first();
        $this->assertEquals('sla_warning', $notification->data['type']);
        $this->assertEquals($ticket->id, $notification->data['ticket_id']);
        $this->assertEquals('Peringatan SLA – Tiket hampir melebihi batas waktu', $notification->data['title']);
        $this->assertStringContainsString('REQ-20260914-0001', $notification->data['message']);
        $this->assertStringContainsString('kurang dari 2 jam', $notification->data['message']);
        $this->assertEquals(route('tickets.show', $ticket), $notification->data['action_url']);
    }

    /**
     * Uji anti-spam: notifikasi hanya dikirim 1 kali per tiket dan tidak duplikat saat scheduler berjalan lagi.
     */
    public function test_does_not_send_duplicate_notification_for_same_ticket(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 14, 14, 0, 0));

        // Mulai jam 13:00 (berjalan 60 menit), target 2 jam (120 menit), batas akhir 15:00 (sisa 60 menit)
        $startAt = Carbon::create(2026, 9, 14, 13, 0, 0);
        $dueAt = Carbon::create(2026, 9, 14, 15, 0, 0);

        $ticket = Ticket::create([
            'ticket_number'           => 'REQ-20260914-0002',
            'user_id'                 => $this->requesterUser->id,
            'department_id'           => 1,
            'assigned_to'             => $this->stafUser->id,
            'disposed_by'             => $this->kabagUser->id,
            'priority'                => 'Sedang',
            'status'                  => 'Didistribusikan',
            'description'             => 'Pintu ruangan macet',
            'sla_resolution_hours'    => 2,
            'sla_resolution_start_at' => $startAt,
            'sla_resolution_due_at'   => $dueAt,
            'sla_resolution_status'   => 'Berjalan',
        ]);

        $slaService = app(TicketSlaService::class);

        // Eksekusi pertama: harus terkirim 1 notifikasi
        $res1 = $slaService->checkAndNotifyStaffSlaWarning();
        $this->assertEquals(1, $res1['notified_count']);
        $this->assertEquals(1, $this->stafUser->notifications()->count());

        // Eksekusi kedua (simulasi cron berjalan lagi): tidak boleh kirim duplikat
        $res2 = $slaService->checkAndNotifyStaffSlaWarning();
        $this->assertEquals(0, $res2['notified_count']);
        $this->assertEquals(1, $res2['skipped_already_notified']);
        $this->assertEquals(1, $this->stafUser->notifications()->count());
    }

    /**
     * Uji tiket yang sudah overdue / melewati batas tidak dikirimkan peringatan hampir habis.
     */
    public function test_does_not_send_warning_if_ticket_is_already_overdue(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 14, 16, 0, 0));

        // Batas waktu jam 14:00 (sudah lewat 2 jam yang lalu)
        $dueAt = Carbon::create(2026, 9, 14, 14, 0, 0);

        Ticket::create([
            'ticket_number'           => 'REQ-20260914-0003',
            'user_id'                 => $this->requesterUser->id,
            'department_id'           => 1,
            'assigned_to'             => $this->stafUser->id,
            'disposed_by'             => $this->kabagUser->id,
            'priority'                => 'Sedang',
            'status'                  => 'Dalam Proses',
            'description'             => 'Kelistrikan padam',
            'sla_resolution_hours'    => 48,
            'sla_resolution_start_at' => Carbon::create(2026, 9, 8, 8, 0, 0),
            'sla_resolution_due_at'   => $dueAt,
            'sla_resolution_status'   => 'Terlambat',
        ]);

        $slaService = app(TicketSlaService::class);
        $result = $slaService->checkAndNotifyStaffSlaWarning();

        $this->assertEquals(0, $result['notified_count']);
        $this->assertEquals(0, $this->stafUser->notifications()->count());
    }

    /**
     * Uji tiket dengan sisa waktu > 2 jam (misal 5 jam) tidak memicu notifikasi peringatan.
     */
    public function test_does_not_send_warning_if_remaining_time_is_more_than_two_hours(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 14, 9, 0, 0));

        // Batas waktu jam 16:00 (sisa 7 jam kerja)
        $dueAt = Carbon::create(2026, 9, 14, 16, 0, 0);

        Ticket::create([
            'ticket_number'           => 'REQ-20260914-0004',
            'user_id'                 => $this->requesterUser->id,
            'department_id'           => 1,
            'assigned_to'             => $this->stafUser->id,
            'disposed_by'             => $this->kabagUser->id,
            'priority'                => 'Sedang',
            'status'                  => 'Dalam Proses',
            'description'             => 'Pembersihan karpet',
            'sla_resolution_hours'    => 48,
            'sla_resolution_start_at' => Carbon::create(2026, 9, 14, 8, 0, 0),
            'sla_resolution_due_at'   => $dueAt,
            'sla_resolution_status'   => 'Berjalan',
        ]);

        $slaService = app(TicketSlaService::class);
        $result = $slaService->checkAndNotifyStaffSlaWarning();

        $this->assertEquals(0, $result['notified_count']);
        $this->assertEquals(0, $this->stafUser->notifications()->count());
    }

    /**
     * Uji batasan role: Tiket yang ditugaskan kepada selain role staf (misal Kabag atau User) tidak dikirim notifikasi.
     */
    public function test_does_not_send_warning_if_assigned_to_non_staff_role(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 14, 14, 0, 0));

        $startAt = Carbon::create(2026, 9, 14, 13, 0, 0);
        $dueAt = Carbon::create(2026, 9, 14, 15, 0, 0);

        // Ditugaskan kepada Kabag (bukan staf)
        Ticket::create([
            'ticket_number'           => 'REQ-20260914-0005',
            'user_id'                 => $this->requesterUser->id,
            'department_id'           => 1,
            'assigned_to'             => $this->kabagUser->id,
            'disposed_by'             => $this->kabagUser->id,
            'priority'                => 'Sedang',
            'status'                  => 'Dalam Proses',
            'description'             => 'Pengecekan fasilitas',
            'sla_resolution_hours'    => 2,
            'sla_resolution_start_at' => $startAt,
            'sla_resolution_due_at'   => $dueAt,
            'sla_resolution_status'   => 'Berjalan',
        ]);

        $slaService = app(TicketSlaService::class);
        $result = $slaService->checkAndNotifyStaffSlaWarning();

        $this->assertEquals(0, $result['notified_count']);
        $this->assertEquals(1, $result['skipped_not_staff']);
        $this->assertEquals(0, $this->kabagUser->notifications()->count());
    }

    /**
     * Uji artisan command tickets:check-sla-warning berjalan sukses.
     */
    public function test_artisan_command_tickets_check_sla_warning_runs_successfully(): void
    {
        $this->artisan('tickets:check-sla-warning')
            ->expectsOutputToContain('=== PEMANTAUAN SLA PERINGATAN STAF')
            ->assertExitCode(0);
    }
}

