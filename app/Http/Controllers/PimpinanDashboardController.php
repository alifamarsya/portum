<?php

namespace App\Http\Controllers;

use App\Models\AsMutasiAset;
use App\Models\InternalDepartment;
use App\Models\Ticket;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PimpinanDashboardController extends Controller
{
    /** Status tiket yang dianggap "aktif" (belum selesai) */
    private const ACTIVE_STATUSES = [
        'Menunggu Verifikasi',
        'Dialokasikan',
        'Diverifikasi',
        'Didistribusikan',
        'Dalam Proses',
    ];

    /** Status tiket yang dianggap "selesai / ditutup" */
    private const DONE_STATUSES = [
        'Selesai',
        'Ditutup Pemohon',
        'Ditutup Otomatis (Sistem)',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();

        // Otorisasi: hanya pimpinan / kepala_divisi (dan superadmin untuk IT)
        if (!$user->isKepalaDivisi() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Halaman ini khusus untuk Pimpinan Divisi.');
        }

        /* ──────────────────────────────────────────────
         * 1. PARSE FILTER PERIODE & BAGIAN
         * ────────────────────────────────────────────── */
        $periode     = $request->get('periode', 'this_month');
        $deptFilter  = $request->get('department_id'); // opsional
        $dateFrom    = $request->get('date_from');
        $dateTo      = $request->get('date_to');

        [$startDate, $endDate] = $this->resolveDateRange($periode, $dateFrom, $dateTo);

        /* ──────────────────────────────────────────────
         * 2. BASE QUERY (filter periode + dept)
         * ────────────────────────────────────────────── */
        $base = Ticket::whereBetween('created_at', [$startDate, $endDate]);

        if ($deptFilter) {
            $base->where('department_id', $deptFilter);
        }

        /* ──────────────────────────────────────────────
         * 3. KPI CARDS
         * ────────────────────────────────────────────── */
        $totalMasuk = (clone $base)->count();

        $totalAktif = (clone $base)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->count();

        $totalSelesai = (clone $base)
            ->whereIn('status', self::DONE_STATUSES)
            ->count();

        // Overdue: tiket aktif yang sla_resolution_due_at sudah lewat
        $totalOverdue = (clone $base)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereNotNull('sla_resolution_due_at')
            ->where('sla_resolution_due_at', '<', now())
            ->count();

        // Mutasi Aset (seluruh waktu, tidak bergantung filter periode)
        $mutasiPending  = class_exists(AsMutasiAset::class)
            ? AsMutasiAset::whereNotIn('status', ['Selesai', 'Ditolak'])->count()
            : 0;
        $mutasiSelesai  = class_exists(AsMutasiAset::class)
            ? AsMutasiAset::where('status', 'Selesai')->count()
            : 0;

        /* ──────────────────────────────────────────────
         * 4. GRAFIK A — Tren Tiket Masuk (per hari / per bulan)
         * ────────────────────────────────────────────── */
        $chartTrenLabels = [];
        $chartTrenData   = [];

        $diffDays = $startDate->diffInDays($endDate);
        if ($diffDays <= 31) {
            // Granularitas harian
            $cursor = $startDate->copy()->startOfDay();
            while ($cursor->lte($endDate)) {
                $chartTrenLabels[] = $cursor->translatedFormat('d M');
                $chartTrenData[]   = (clone $base)
                    ->whereDate('created_at', $cursor->toDateString())
                    ->count();
                $cursor->addDay();
            }
        } else {
            // Granularitas bulanan
            $cursor = $startDate->copy()->startOfMonth();
            while ($cursor->lte($endDate)) {
                $chartTrenLabels[] = $cursor->translatedFormat('M Y');
                $chartTrenData[]   = (clone $base)
                    ->whereYear('created_at', $cursor->year)
                    ->whereMonth('created_at', $cursor->month)
                    ->count();
                $cursor->addMonth();
            }
        }

        /* ──────────────────────────────────────────────
         * 5. GRAFIK B — Komposisi Status Tiket (Donut)
         * ────────────────────────────────────────────── */
        $statusGroups = [
            'Menunggu Verifikasi' => 'Menunggu',
            'Dialokasikan'        => 'Dialokasikan',
            'Diverifikasi'        => 'Diverifikasi',
            'Didistribusikan'     => 'Didistribusikan',
            'Dalam Proses'        => 'Dalam Proses',
            'Selesai'             => 'Selesai',
            'Ditutup Pemohon'     => 'Ditutup',
            'Ditutup Otomatis (Sistem)' => 'Ditutup Otomatis',
            'Ditolak'             => 'Ditolak',
        ];

        $chartStatusLabels = [];
        $chartStatusData   = [];
        $chartStatusColors = [
            '#F59E0B', // amber - menunggu
            '#3B82F6', // blue - dialokasikan
            '#6366F1', // indigo - diverifikasi
            '#8B5CF6', // violet - didistribusikan
            '#7C3AED', // purple - dalam proses
            '#10B981', // emerald - selesai
            '#0D9488', // teal - ditutup pemohon
            '#64748B', // slate - ditutup otomatis
            '#EF4444', // red - ditolak
        ];

        $i = 0;
        foreach ($statusGroups as $statusKey => $statusLabel) {
            $count = (clone $base)->where('status', $statusKey)->count();
            if ($count > 0) {
                $chartStatusLabels[] = $statusLabel;
                $chartStatusData[]   = $count;
            }
            $i++;
        }

        // Tambahkan overdue sebagai kategori terpisah dalam donut
        $overdueInPeriod = (clone $base)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereNotNull('sla_resolution_due_at')
            ->where('sla_resolution_due_at', '<', now())
            ->count();
        if ($overdueInPeriod > 0) {
            $chartStatusLabels[] = 'Overdue (SLA)';
            $chartStatusData[]   = $overdueInPeriod;
            $chartStatusColors[] = '#DC2626'; // rose-600 khusus overdue
        }

        /* ──────────────────────────────────────────────
         * 6. GRAFIK C — Tiket Per Bagian (Bar Chart)
         * ────────────────────────────────────────────── */
        $departments = InternalDepartment::orderBy('id')->get();
        $chartDeptLabels  = [];
        $chartDeptAktif   = [];
        $chartDeptSelesai = [];
        $chartDeptOverdue = [];

        foreach ($departments as $dept) {
            $deptQ = (clone $base)->where('department_id', $dept->id);
            $chartDeptLabels[]  = $this->shortenDeptName($dept->name);
            $chartDeptAktif[]   = (clone $deptQ)->whereIn('status', self::ACTIVE_STATUSES)->count();
            $chartDeptSelesai[] = (clone $deptQ)->whereIn('status', self::DONE_STATUSES)->count();
            $chartDeptOverdue[] = (clone $deptQ)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->whereNotNull('sla_resolution_due_at')
                ->where('sla_resolution_due_at', '<', now())
                ->count();
        }

        /* ──────────────────────────────────────────────
         * 7. DAFTAR TIKET OVERDUE (SLA dilanggar)
         * ────────────────────────────────────────────── */
        $overdueTickets = Ticket::whereIn('status', self::ACTIVE_STATUSES)
            ->whereNotNull('sla_resolution_due_at')
            ->where('sla_resolution_due_at', '<', now())
            ->when($deptFilter, fn ($q) => $q->where('department_id', $deptFilter))
            ->with(['user', 'department', 'unitKerja', 'category', 'assignedStaff'])
            ->orderBy('sla_resolution_due_at', 'asc') // paling lama overdue duluan
            ->take(20)
            ->get()
            ->map(function ($ticket) {
                $due          = Carbon::parse($ticket->sla_resolution_due_at);
                $overdueHours = $due->diffInHours(now(), false); // positif = sudah lewat
                $ticket->overdue_hours = abs($overdueHours);
                $ticket->overdue_label = $this->formatOverdueLabel($overdueHours);
                return $ticket;
            });

        /* ──────────────────────────────────────────────
         * 8. WORKLOAD ANALYSIS — Per Bagian & Unit Kerja
         * ────────────────────────────────────────────── */
        $workload = [];
        foreach ($departments as $dept) {
            $deptQ = (clone $base)->where('department_id', $dept->id);

            $total   = (clone $deptQ)->count();
            $aktif   = (clone $deptQ)->whereIn('status', self::ACTIVE_STATUSES)->count();
            $selesai = (clone $deptQ)->whereIn('status', self::DONE_STATUSES)->count();
            $overdue = (clone $deptQ)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->whereNotNull('sla_resolution_due_at')
                ->where('sla_resolution_due_at', '<', now())
                ->count();

            // Rata-rata waktu penyelesaian (dalam jam) untuk tiket yang punya sla_resolution_time_minutes
            $avgResolutionHours = null;
            $completedWithData  = (clone $deptQ)
                ->whereIn('status', self::DONE_STATUSES)
                ->whereNotNull('sla_resolution_time_minutes')
                ->avg('sla_resolution_time_minutes');
            if ($completedWithData) {
                $avgResolutionHours = round($completedWithData / 60, 1);
            }

            // SLA Compliance Rate
            $slaOnTime  = (clone $deptQ)
                ->whereIn('status', self::DONE_STATUSES)
                ->where(fn ($q) => $q
                    ->where('sla_resolution_status', 'Tepat Waktu')
                    ->orWhereNull('sla_resolution_status')
                )
                ->count();
            $slaCompliance = $selesai > 0 ? round(($slaOnTime / $selesai) * 100) : null;

            // Unit Kerja breakdown di bawah bagian ini (jika tabel unit_kerja tersedia)
            $unitKerjas = Schema::hasTable('unit_kerja')
                ? UnitKerja::where('department_id', $dept->id)->where('is_active', true)->get()
                : collect();
            $ukBreakdown = [];
            foreach ($unitKerjas as $uk) {
                $ukQ = (clone $base)->where('unit_kerja_id', $uk->id);
                $ukTotal   = (clone $ukQ)->count();
                $ukAktif   = (clone $ukQ)->whereIn('status', self::ACTIVE_STATUSES)->count();
                $ukSelesai = (clone $ukQ)->whereIn('status', self::DONE_STATUSES)->count();
                $ukOverdue = (clone $ukQ)
                    ->whereIn('status', self::ACTIVE_STATUSES)
                    ->whereNotNull('sla_resolution_due_at')
                    ->where('sla_resolution_due_at', '<', now())
                    ->count();
                $ukBreakdown[] = [
                    'nama'    => $uk->nama,
                    'kode'    => $uk->kode,
                    'total'   => $ukTotal,
                    'aktif'   => $ukAktif,
                    'selesai' => $ukSelesai,
                    'overdue' => $ukOverdue,
                ];
            }

            $workload[] = [
                'department'        => $dept,
                'total'             => $total,
                'aktif'             => $aktif,
                'selesai'           => $selesai,
                'overdue'           => $overdue,
                'avg_resolution_h'  => $avgResolutionHours,
                'sla_compliance'    => $slaCompliance,
                'unit_kerja'        => $ukBreakdown,
            ];
        }

        /* ──────────────────────────────────────────────
         * 9. PASS TO VIEW
         * ────────────────────────────────────────────── */
        return view('pimpinan.dashboard', compact(
            // Filter state
            'periode', 'deptFilter', 'startDate', 'endDate', 'dateFrom', 'dateTo',
            'departments',
            // KPI
            'totalMasuk', 'totalAktif', 'totalSelesai', 'totalOverdue',
            'mutasiPending', 'mutasiSelesai',
            // Chart A
            'chartTrenLabels', 'chartTrenData',
            // Chart B
            'chartStatusLabels', 'chartStatusData', 'chartStatusColors',
            // Chart C
            'chartDeptLabels', 'chartDeptAktif', 'chartDeptSelesai', 'chartDeptOverdue',
            // Overdue list
            'overdueTickets',
            // Workload
            'workload',
        ));
    }

    /* ──────────────────────────────────────────────────
     * HELPER: Resolve date range dari parameter periode
     * ────────────────────────────────────────────────── */
    private function resolveDateRange(string $periode, ?string $dateFrom, ?string $dateTo): array
    {
        $now = now();

        return match ($periode) {
            'today'      => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            '7_days'     => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '30_days'    => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'custom'     => [
                $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : $now->copy()->startOfMonth(),
                $dateTo   ? Carbon::parse($dateTo)->endOfDay()     : $now->copy()->endOfDay(),
            ],
            default      => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    /* ──────────────────────────────────────────────────
     * HELPER: Singkat nama departemen untuk label chart
     * ────────────────────────────────────────────────── */
    private function shortenDeptName(string $name): string
    {
        return match (true) {
            str_contains($name, 'Umum')      => 'Umum & RT',
            str_contains($name, 'Aset')      => 'Aset & Logistik',
            str_contains($name, 'Pengadaan') => 'Pengadaan',
            default                          => \Str::limit($name, 18),
        };
    }

    /* ──────────────────────────────────────────────────
     * HELPER: Format keterlambatan ke label yang mudah dibaca
     * ────────────────────────────────────────────────── */
    private function formatOverdueLabel(int $overdueHours): string
    {
        $hours = abs($overdueHours);
        if ($hours < 1) {
            return 'Baru saja lewat';
        }
        if ($hours < 24) {
            return "{$hours} jam terlambat";
        }
        $days = intdiv($hours, 24);
        $rem  = $hours % 24;
        return $rem > 0
            ? "{$days} hari {$rem} jam terlambat"
            : "{$days} hari terlambat";
    }
}
