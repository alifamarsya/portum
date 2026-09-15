<?php

namespace Tests\Feature;

use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PimpinanDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $pimpinan;
    private User $userBiasa;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles yang dibutuhkan
        $rolePimpinan  = Role::create(['nama' => 'pimpinan',  'label' => 'Pimpinan Divisi', 'deskripsi' => 'Pimpinan']);
        $roleUser      = Role::create(['nama' => 'user',      'label' => 'User',            'deskripsi' => 'User']);
        $roleOperator  = Role::create(['nama' => 'operator',  'label' => 'Operator',        'deskripsi' => 'Operator']);

        $this->pimpinan = User::factory()->create([
            'nama_lengkap' => 'Pimpinan Divisi Test',
            'role_id'      => $rolePimpinan->id,
            'department_id'=> null,
            'is_active'    => true,
        ]);

        $this->userBiasa = User::factory()->create([
            'nama_lengkap' => 'User Biasa',
            'role_id'      => $roleUser->id,
            'is_active'    => true,
        ]);

        $this->operator = User::factory()->create([
            'nama_lengkap' => 'Operator Test',
            'role_id'      => $roleOperator->id,
            'is_active'    => true,
        ]);

        // Buat departemen
        InternalDepartment::create(['id' => 1, 'name' => 'Bagian Umum & Rumah Tangga']);
        InternalDepartment::create(['id' => 2, 'name' => 'Bagian Aset/Inventaris & Logistik']);
        InternalDepartment::create(['id' => 3, 'name' => 'Bagian Pengadaan & Pemeliharaan']);
    }

    /** @test */
    public function test_pimpinan_can_access_pimpinan_dashboard(): void
    {
        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('pimpinan.dashboard');
    }

    /** @test */
    public function test_pimpinan_dashboard_contains_required_view_data(): void
    {
        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHasAll([
            'totalMasuk', 'totalAktif', 'totalSelesai', 'totalOverdue',
            'chartTrenLabels', 'chartTrenData',
            'chartStatusLabels', 'chartStatusData',
            'chartDeptLabels', 'chartDeptAktif', 'chartDeptSelesai', 'chartDeptOverdue',
            'overdueTickets',
            'workload',
            'departments',
            'mutasiPending', 'mutasiSelesai',
        ]);
    }

    /** @test */
    public function test_regular_user_cannot_access_pimpinan_dashboard(): void
    {
        $response = $this->actingAs($this->userBiasa)
            ->get(route('pimpinan.dashboard'));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_operator_cannot_access_pimpinan_dashboard(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('pimpinan.dashboard'));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_pimpinan_root_redirect_goes_to_pimpinan_dashboard(): void
    {
        $response = $this->actingAs($this->pimpinan)
            ->get(route('dashboard'));

        $response->assertRedirect(route('pimpinan.dashboard'));
    }

    /** @test */
    public function test_dashboard_period_filter_today_works(): void
    {
        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard', ['periode' => 'today']));

        $response->assertStatus(200);
        $response->assertViewHas('periode', 'today');
    }

    /** @test */
    public function test_dashboard_period_filter_7days_works(): void
    {
        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard', ['periode' => '7_days']));

        $response->assertStatus(200);
        $response->assertViewHas('periode', '7_days');
    }

    /** @test */
    public function test_dashboard_period_filter_custom_range_works(): void
    {
        $from = now()->subDays(14)->toDateString();
        $to   = now()->toDateString();

        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard', [
                'periode'   => 'custom',
                'date_from' => $from,
                'date_to'   => $to,
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('periode', 'custom');
    }

    /** @test */
    public function test_department_filter_works(): void
    {
        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard', ['department_id' => 1]));

        $response->assertStatus(200);
        $response->assertViewHas('deptFilter', '1');
    }

    /** @test */
    public function test_total_masuk_counts_tickets_in_period(): void
    {
        // Buat 3 tiket di bulan ini
        Ticket::factory()->count(3)->create([
            'user_id'      => $this->userBiasa->id,
            'department_id'=> 1,
            'status'       => 'Menunggu Verifikasi',
            'created_at'   => now(),
        ]);

        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard', ['periode' => 'this_month']));

        $response->assertStatus(200);
        $this->assertEquals(3, $response->viewData('totalMasuk'));
    }

    /** @test */
    public function test_overdue_tickets_are_detected_correctly(): void
    {
        // Buat tiket yang overdue: aktif + sla_resolution_due_at sudah lewat
        Ticket::factory()->create([
            'user_id'               => $this->userBiasa->id,
            'department_id'         => 1,
            'status'                => 'Dalam Proses',
            'sla_resolution_due_at' => now()->subHours(5),
            'created_at'            => now()->subDays(2),
        ]);

        // Buat tiket yang tidak overdue: aktif + sla_resolution_due_at masih masa depan
        Ticket::factory()->create([
            'user_id'               => $this->userBiasa->id,
            'department_id'         => 2,
            'status'                => 'Dalam Proses',
            'sla_resolution_due_at' => now()->addHours(10),
            'created_at'            => now(),
        ]);

        // Buat tiket yang sudah selesai (bukan overdue walaupun due_at lewat)
        Ticket::factory()->create([
            'user_id'               => $this->userBiasa->id,
            'department_id'         => 1,
            'status'                => 'Selesai',
            'sla_resolution_due_at' => now()->subDays(1),
            'created_at'            => now()->subDays(3),
        ]);

        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard', ['periode' => '30_days']));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->viewData('totalOverdue'));
        $this->assertEquals(1, $response->viewData('overdueTickets')->count());
    }

    /** @test */
    public function test_workload_analysis_contains_all_three_departments(): void
    {
        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard'));

        $response->assertStatus(200);
        $workload = $response->viewData('workload');

        $this->assertCount(3, $workload);
        $this->assertEquals(1, $workload[0]['department']->id);
        $this->assertEquals(2, $workload[1]['department']->id);
        $this->assertEquals(3, $workload[2]['department']->id);
    }

    /** @test */
    public function test_workload_ticket_counts_are_accurate(): void
    {
        // 2 tiket aktif di dept 1
        Ticket::factory()->count(2)->create([
            'user_id'       => $this->userBiasa->id,
            'department_id' => 1,
            'status'        => 'Dalam Proses',
            'created_at'    => now(),
        ]);

        // 1 tiket selesai di dept 1
        Ticket::factory()->create([
            'user_id'       => $this->userBiasa->id,
            'department_id' => 1,
            'status'        => 'Selesai',
            'created_at'    => now(),
        ]);

        // 3 tiket di dept 2
        Ticket::factory()->count(3)->create([
            'user_id'       => $this->userBiasa->id,
            'department_id' => 2,
            'status'        => 'Dialokasikan',
            'created_at'    => now(),
        ]);

        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard', ['periode' => 'this_month']));

        $response->assertStatus(200);
        $workload = $response->viewData('workload');

        // Dept 1: total=3, aktif=2, selesai=1
        $this->assertEquals(3, $workload[0]['total']);
        $this->assertEquals(2, $workload[0]['aktif']);
        $this->assertEquals(1, $workload[0]['selesai']);

        // Dept 2: total=3, aktif=3
        $this->assertEquals(3, $workload[1]['total']);
        $this->assertEquals(3, $workload[1]['aktif']);
    }

    /** @test */
    public function test_chart_tren_labels_are_not_empty_for_this_month(): void
    {
        $response = $this->actingAs($this->pimpinan)
            ->get(route('pimpinan.dashboard', ['periode' => 'this_month']));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->viewData('chartTrenLabels'));
    }
}
