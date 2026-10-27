<?php

namespace App\Http\Controllers;

use App\Models\AsDisposalAset;
use App\Models\AsMutasiAset;
use App\Models\DimKategori;
use App\Models\DimVendor;
use App\Models\DimWaktu;
use App\Models\FactAmortisasiAset;
use App\Models\FactBiayaBulanan;
use App\Models\FactPengadaan;
use App\Models\InternalDepartment;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\UnitKerja;
use App\Services\TicketSlaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PimpinanDashboardController extends Controller
{
    /** Status tiket yang dianggap "aktif" (sedang dalam proses / antrian) */
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
         * 2. BASE TICKET QUERY (filter periode + dept)
         * ────────────────────────────────────────────── */
        $base = Ticket::whereBetween('created_at', [$startDate, $endDate]);
        if ($deptFilter) {
            $base->where('department_id', $deptFilter);
        }

        // Summary Keseluruhan Tiket
        $totalMasuk = (clone $base)->count();
        $totalAktif = (clone $base)->whereIn('status', self::ACTIVE_STATUSES)->count();
        $totalSelesai = (clone $base)->whereIn('status', self::DONE_STATUSES)->count();

        // Total Overdue Global (Resolution SLA) — KUMULATIF dalam periode:
        // tiket yang pernah overdue tetap terhitung meski sudah ditutup.
        $totalOverdue = $this->everOverdueResolution(clone $base)->count();

        // Tiket mendekati deadline SLA resolusi — aturan SAMA dengan
        // TicketSlaService::checkAndNotifyStaffSlaWarning(): timer sudah
        // berjalan, belum selesai, belum overdue, sisa > 0 dan <= 120 menit
        // (SLA_WARNING_THRESHOLD_MINUTES). Global (tanpa filter periode /
        // bagian), persis seperti service-nya.
        $nearDeadlineCount = 0;
        $slaService = app(TicketSlaService::class);
        $warningCandidates = Ticket::whereIn('status', ['Didistribusikan', 'Dalam Proses'])
            ->whereNotNull('assigned_to')
            ->where(function ($q) {
                $q->whereNotNull('sla_resolution_start_at')
                  ->orWhereNotNull('disposed_at');
            })
            ->get();
        foreach ($warningCandidates as $candidate) {
            $slaInfo = $slaService->getSlaResolutionInfo($candidate);
            if ($slaInfo['is_started']
                && !$slaInfo['is_resolved']
                && !$slaInfo['is_overdue']
                && $slaInfo['remaining_minutes'] > 0
                && $slaInfo['remaining_minutes'] <= TicketSlaService::SLA_WARNING_THRESHOLD_MINUTES) {
                $nearDeadlineCount++;
            }
        }

        /* ──────────────────────────────────────────────
         * 3. MONITORING BEBAN KERJA & OVERDUE PER BAGIAN
         *    (3 Bagian Utama Divisi Umum)
         * ────────────────────────────────────────────── */
        $departments = InternalDepartment::orderBy('id')->get();
        $deptCards = [];
        $workload = [];
        $chartDeptLabels  = [];
        $chartDeptAktif   = [];
        $chartDeptSelesai = [];
        $chartDeptOverdue = [];

        $totalOverdueResolutionGlobal = 0;
        $totalOverdueResponseGlobal = 0;

        foreach ($departments as $dept) {
            $deptQ = (clone $base)->where('department_id', $dept->id);

            $totalDept     = (clone $deptQ)->count();
            $aktifDept     = (clone $deptQ)->whereIn('status', self::ACTIVE_STATUSES)->count();
            $selesaiDept   = (clone $deptQ)->whereIn('status', self::DONE_STATUSES)->count();

            // Overdue Resolution — KUMULATIF dalam periode (lihat
            // everOverdueResolution()): tiket yang pernah overdue tetap
            // terhitung meski sudah Selesai/Ditutup.
            $overdueRes = $this->everOverdueResolution(clone $deptQ)->count();

            // Overdue Response (Tiket belum diverifikasi dan batas respon terlewati, ATAU respon terlambat)
            $overdueResp = (clone $deptQ)
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('verified_at')
                            ->whereNotNull('sla_response_due_at')
                            ->where('sla_response_due_at', '<', now());
                    })->orWhere('sla_response_status', 'Terlambat');
                })
                ->count();

            $totalOverdueDept = $overdueRes + $overdueResp;
            $totalOverdueResolutionGlobal += $overdueRes;
            $totalOverdueResponseGlobal += $overdueResp;

            $chartDeptLabels[]  = $this->shortenDeptName($dept->name);
            $chartDeptAktif[]   = $aktifDept;
            $chartDeptSelesai[] = $selesaiDept;
            $chartDeptOverdue[] = $overdueRes;

            // Unit Kerja breakdown di bawah bagian ini
            $unitKerjas = Schema::hasTable('unit_kerja')
                ? UnitKerja::where('department_id', $dept->id)->where('is_active', true)->get()
                : collect();
            $hasUkColumn = Schema::hasColumn('tickets', 'unit_kerja_id');
            $hasUkCol = Schema::hasColumn('tickets', 'unit_kerja_id');
            $hasStaffUkCol = Schema::hasColumn('users', 'unit_kerja_id');
            $ukBreakdown = [];
            foreach ($unitKerjas as $uk) {
                $ukQ = (clone $base)->where(function ($q) use ($uk, $hasUkColumn) {
                    if ($hasUkColumn) {
                        $q->where('unit_kerja_id', $uk->id);
                    } else {
                        $q->whereHas('assignedStaff', fn ($sq) => $sq->where('unit_kerja_id', $uk->id));
                    }
                });
                if ($hasUkCol) {
                    $ukQ = (clone $base)->where('unit_kerja_id', $uk->id);
                } elseif ($hasStaffUkCol) {
                    $ukQ = (clone $base)->whereHas('assignedStaff', fn ($u) => $u->where('unit_kerja_id', $uk->id));
                } else {
                    $ukQ = (clone $base)->whereRaw('1 = 0');
                }
                $ukTotal   = (clone $ukQ)->count();
                $ukAktif   = (clone $ukQ)->whereIn('status', self::ACTIVE_STATUSES)->count();
                $ukSelesai = (clone $ukQ)->whereIn('status', self::DONE_STATUSES)->count();
                // Overdue per unit kerja memakai definisi kumulatif yang sama
                // dengan kartu bagian (lihat everOverdueResolution()).
                $ukOverdue = $this->everOverdueResolution(clone $ukQ)->count();
                $ukBreakdown[] = [
                    'nama'    => $uk->nama,
                    'kode'    => $uk->kode,
                    'total'   => $ukTotal,
                    'aktif'   => $ukAktif,
                    'selesai' => $ukSelesai,
                    'overdue' => $ukOverdue,
                ];
            }

            // Penilaian Beban Kerja & Kecukupan Staf:
            // - Kritis: Overdue >= 3 ATAU aktif >= 15
            // - Perhatian: Overdue 1-2 ATAU aktif >= 8
            // - Normal: Overdue 0 dan beban aktif terkendali
            if ($overdueRes >= 3 || $totalOverdueDept >= 4) {
                $statusBeban = 'Kritis';
                $statusBadgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
                $statusIcon = 'alert-triangle';
                $statusDesc = 'Tinggi overdue! Indikasi kekurangan staf atau kendala proses.';
            } elseif ($overdueRes >= 1 || $aktifDept >= 8) {
                $statusBeban = 'Perhatian';
                $statusBadgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
                $statusIcon = 'clock';
                $statusDesc = 'Beban kerja tinggi, mendekati kapasitas maksimum tim.';
            } else {
                $statusBeban = 'Terkendali';
                $statusBadgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                $statusIcon = 'check-circle';
                $statusDesc = 'Kapasitas staf mencukupi, SLA terjaga dengan baik.';
            }

            // Persentase penyelesaian
            $completionRate = $totalDept > 0 ? round(($selesaiDept / $totalDept) * 100) : 0;

            // Rata-rata jam penyelesaian
            $avgMinutes = (clone $deptQ)
                ->whereIn('status', self::DONE_STATUSES)
                ->whereNotNull('sla_resolution_time_minutes')
                ->avg('sla_resolution_time_minutes');
            $avgHours = $avgMinutes ? round($avgMinutes / 60, 1) : null;

            $deptCards[] = [
                'id'              => $dept->id,
                'name'            => $dept->name,
                'short_name'      => $this->shortenDeptName($dept->name),
                'total'           => $totalDept,
                'aktif'           => $aktifDept,
                'selesai'         => $selesaiDept,
                'overdue_res'     => $overdueRes,
                'overdue_resp'    => $overdueResp,
                'total_overdue'   => $totalOverdueDept,
                'completion_rate' => $completionRate,
                'avg_hours'       => $avgHours,
                'status_beban'    => $statusBeban,
                'badge_class'     => $statusBadgeClass,
                'status_icon'     => $statusIcon,
                'status_desc'     => $statusDesc,
                'unit_kerja'      => $ukBreakdown,
            ];

            $workload[] = [
                'department'        => $dept,
                'total'             => $totalDept,
                'aktif'             => $aktifDept,
                'selesai'           => $selesaiDept,
                'overdue'           => $overdueRes,
                'avg_resolution_h'  => $avgHours,
                'sla_compliance'    => $completionRate,
                'unit_kerja'        => $ukBreakdown,
            ];
        }

        /* ──────────────────────────────────────────────
         * 4. DAFTAR TIKET OVERDUE (ACTIONABLE ESCALATION)
         * ────────────────────────────────────────────── */
        $overdueTickets = (clone $base)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereNotNull('sla_resolution_due_at')
            ->where('sla_resolution_due_at', '<', now())
            ->with(['user', 'department', 'unitKerja', 'category', 'assignedStaff'])
            ->orderBy('sla_resolution_due_at', 'asc')
            ->take(10)
            ->get()
            ->map(function ($ticket) {
                $due          = Carbon::parse($ticket->sla_resolution_due_at);
                $overdueHours = $due->diffInHours(now(), false);
                $ticket->overdue_hours = abs($overdueHours);
                $ticket->overdue_label = $this->formatOverdueLabel($overdueHours);
                return $ticket;
            });

        /* ──────────────────────────────────────────────
         * 5. MONITORING MUTASI ASET / PERGERAKAN ASET
         * ────────────────────────────────────────────── */
        $mutasiStats = [
            'total'     => 0,
            'diajukan'  => 0,
            'diproses'  => 0,
            'disetujui' => 0,
            'selesai'   => 0,
            'ditolak'   => 0,
        ];
        $recentMutations = collect();

        if (Schema::hasTable('as_mutasi_aset')) {
            $mutasiStats['total']     = AsMutasiAset::count();
            $mutasiStats['diajukan']  = AsMutasiAset::where('status', 'Diajukan')->count();
            $mutasiStats['diproses']  = AsMutasiAset::whereIn('status', ['Diproses', 'Menunggu Approval'])->count();
            $mutasiStats['disetujui'] = AsMutasiAset::where('status', 'Disetujui')->count();
            $mutasiStats['selesai']   = AsMutasiAset::whereIn('status', ['Selesai', 'Ditutup'])->count();
            $mutasiStats['ditolak']   = AsMutasiAset::whereIn('status', ['Ditolak', 'Tidak Valid'])->count();

            $recentMutations = AsMutasiAset::with(['aset', 'maker'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }
        $mutasiPending = $mutasiStats['diajukan'] + $mutasiStats['diproses'];
        $mutasiSelesai = $mutasiStats['selesai'];

        // Antrean approval disposal — kriteria SAMA dengan
        // DisposalAsetController::approvalQueue().
        $disposalPending = Schema::hasTable('as_disposal_aset')
            ? AsDisposalAset::where('approval_status', 'Diajukan')->count()
            : 0;

        /* ──────────────────────────────────────────────
         * 6. GRAFIK & ANALITIK DATA WAREHOUSE (CPMK DW)
         * ────────────────────────────────────────────── */
        // Summary KPI Finansial DW
        $dwTotalBiaya = Schema::hasTable('fact_biaya_bulanan')
            ? (float) FactBiayaBulanan::sum('total_biaya')
            : 0;

        $dwTotalPengadaan = Schema::hasTable('fact_pengadaan')
            ? (float) FactPengadaan::sum('total_nilai')
            : 0;

        $latestWaktuId = Schema::hasTable('fact_amortisasi_aset')
            ? FactAmortisasiAset::max('dim_waktu_id')
            : null;

        $dwTotalNilaiBuku = ($latestWaktuId && Schema::hasTable('fact_amortisasi_aset'))
            ? (float) FactAmortisasiAset::where('dim_waktu_id', $latestWaktuId)->sum('nilai_buku')
            : 0;

        // Data Chart DW 1: Tren Pengeluaran Biaya Operasional per Bulan
        $dwBiayaLabels = [];
        $dwBiayaData = [];
        if (Schema::hasTable('fact_biaya_bulanan') && Schema::hasTable('dim_waktu')) {
            $dwBiayaRows = FactBiayaBulanan::join('dim_waktu', 'fact_biaya_bulanan.dim_waktu_id', '=', 'dim_waktu.id')
                ->selectRaw('dim_waktu.nama_bulan, dim_waktu.tahun, dim_waktu.bulan, sum(fact_biaya_bulanan.total_biaya) as total')
                ->groupBy('dim_waktu.tahun', 'dim_waktu.bulan', 'dim_waktu.nama_bulan')
                ->orderBy('dim_waktu.tahun')
                ->orderBy('dim_waktu.bulan')
                ->take(6)
                ->get();

            foreach ($dwBiayaRows as $row) {
                $dwBiayaLabels[] = substr($row->nama_bulan, 0, 3) . ' ' . $row->tahun;
                $dwBiayaData[]   = (float) $row->total;
            }
        }

        // Data Chart DW 2: Realisasi Pengadaan per Vendor Rekanan (Doughnut)
        $dwVendorLabels = [];
        $dwVendorData = [];
        if (Schema::hasTable('fact_pengadaan') && Schema::hasTable('dim_vendor')) {
            $dwVendorRows = FactPengadaan::join('dim_vendor', 'fact_pengadaan.dim_vendor_id', '=', 'dim_vendor.id')
                ->selectRaw('dim_vendor.nama_vendor, sum(fact_pengadaan.total_nilai) as total')
                ->groupBy('dim_vendor.nama_vendor')
                ->orderByDesc('total')
                ->take(5)
                ->get();

            foreach ($dwVendorRows as $row) {
                $dwVendorLabels[] = \Str::limit($row->nama_vendor ?: 'Lainnya', 20);
                $dwVendorData[]   = (float) $row->total;
            }
        }

        // Data Chart 3: Tren Tiket Masuk vs Selesai (Periode Aktif)
        //
        // DEFINISI "SELESAI" (dikoreksi — sebelumnya seri ini memakai
        // created_at, sehingga tiket yang dibuat di satu bulan tapi selesai
        // di bulan lain tercatat selesai di bulan pembuatannya):
        // - Tanggal selesai sebuah tiket = created_at dari riwayat
        //   ticket_histories PERTAMA kali new_status masuk ke DONE_STATUSES.
        // - Fallback: bila tidak ada riwayat penyelesaian, pakai
        //   tickets.updated_at.
        // - Satu tiket dihitung tepat sekali, pada bucket tanggal selesainya.
        // - Seri ini TIDAK memakai filter created_at dari $base (tiket boleh
        //   dibuat di luar periode asal selesai di dalam periode); filter
        //   bagian (department_id) tetap berlaku; populasi dibatasi pada
        //   tiket yang saat ini berstatus DONE (sama seperti sebelumnya).
        // Seri Masuk tidak berubah: tetap memakai created_at dari $base.
        $chartTrenLabels = [];
        $chartTrenMasuk  = [];
        $chartTrenSelesai = [];

        $resolvedDates = $this->resolveCompletedDates($deptFilter);
        $selesaiPerHari = [];
        $selesaiPerBulan = [];
        foreach ($resolvedDates as $resolvedDate) {
            $keyHari = $resolvedDate->format('Y-m-d');
            $keyBulan = $resolvedDate->format('Y-m');
            $selesaiPerHari[$keyHari] = ($selesaiPerHari[$keyHari] ?? 0) + 1;
            $selesaiPerBulan[$keyBulan] = ($selesaiPerBulan[$keyBulan] ?? 0) + 1;
        }

        $diffDays = $startDate->diffInDays($endDate);
        if ($diffDays <= 31) {
            $cursor = $startDate->copy()->startOfDay();
            while ($cursor->lte($endDate)) {
                $chartTrenLabels[] = $cursor->translatedFormat('d M');
                $chartTrenMasuk[]   = (clone $base)->whereDate('created_at', $cursor->toDateString())->count();
                $chartTrenSelesai[] = $selesaiPerHari[$cursor->format('Y-m-d')] ?? 0;
                $cursor->addDay();
            }
        } else {
            $cursor = $startDate->copy()->startOfMonth();
            while ($cursor->lte($endDate)) {
                $chartTrenLabels[] = $cursor->translatedFormat('M Y');
                $chartTrenMasuk[]   = (clone $base)
                    ->whereYear('created_at', $cursor->year)
                    ->whereMonth('created_at', $cursor->month)
                    ->count();
                $chartTrenSelesai[] = $selesaiPerBulan[$cursor->format('Y-m')] ?? 0;
                $cursor->addMonth();
            }
        }

        $chartTrenData = $chartTrenMasuk;
        $chartStatusLabels = ['Menunggu', 'Dalam Proses', 'Selesai', 'Ditolak'];
        $chartStatusData = [
            (clone $base)->whereIn('status', ['Menunggu Verifikasi', 'Dialokasikan'])->count(),
            (clone $base)->whereIn('status', ['Dalam Proses', 'Menunggu Approval'])->count(),
            (clone $base)->whereIn('status', self::DONE_STATUSES)->count(),
            (clone $base)->whereIn('status', ['Ditolak', 'Dibatalkan'])->count(),
        ];

        return view('pimpinan.dashboard', compact(
            'periode', 'deptFilter', 'startDate', 'endDate', 'dateFrom', 'dateTo',
            'departments',
            'totalMasuk', 'totalAktif', 'totalSelesai', 'totalOverdue',
            'totalOverdueResolutionGlobal', 'totalOverdueResponseGlobal',
            'deptCards',
            'workload',
            'overdueTickets',
            'mutasiPending', 'mutasiSelesai',
            'mutasiStats', 'recentMutations',
            'disposalPending', 'nearDeadlineCount',
            'dwTotalBiaya', 'dwTotalPengadaan', 'dwTotalNilaiBuku',
            'dwBiayaLabels', 'dwBiayaData',
            'dwVendorLabels', 'dwVendorData',
            'chartTrenLabels', 'chartTrenMasuk', 'chartTrenSelesai', 'chartTrenData',
            'chartStatusLabels', 'chartStatusData',
            'chartDeptLabels', 'chartDeptAktif', 'chartDeptSelesai', 'chartDeptOverdue'
        ));
    }

    /**
     * Terapkan filter "pernah overdue resolusi" (KUMULATIF dalam periode).
     *
     * Sebuah tiket terhitung overdue resolusi bila:
     * - sla_resolution_status = 'Terlambat' (flag sticky: di-set oleh job
     *   terjadwal saat tiket aktif melewati deadline, atau oleh
     *   recordResolution() saat tiket diselesaikan terlambat; tidak pernah
     *   dikembalikan), ATAU
     * - status masih aktif DAN sla_resolution_due_at terisi DAN sudah lewat
     *   (menutup jeda sebelum job terjadwal berikutnya berjalan).
     *
     * Karena mengandalkan flag, angka TIDAK turun saat tiket ditutup —
     * tiket yang pernah overdue tetap terhitung dalam periodenya.
     */
    private function everOverdueResolution($query)
    {
        return $query->where(function ($q) {
            $q->where('sla_resolution_status', 'Terlambat')
                ->orWhere(function ($sub) {
                    $sub->whereIn('status', self::ACTIVE_STATUSES)
                        ->whereNotNull('sla_resolution_due_at')
                        ->where('sla_resolution_due_at', '<', now());
                });
        });
    }

    /**
     * Unduh rekap dashboard pimpinan sebagai CSV.
     * Menghormati filter periode + department_id yang sama dengan index().
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        if (!$user->isKepalaDivisi() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Halaman ini khusus untuk Pimpinan Divisi.');
        }

        $periode    = $request->get('periode', 'this_month');
        $deptFilter = $request->get('department_id');
        $dateFrom   = $request->get('date_from');
        $dateTo     = $request->get('date_to');

        [$startDate, $endDate] = $this->resolveDateRange($periode, $dateFrom, $dateTo);

        $base = Ticket::whereBetween('created_at', [$startDate, $endDate]);
        if ($deptFilter) {
            $base->where('department_id', $deptFilter);
        }

        $totalMasuk   = (clone $base)->count();
        $totalAktif   = (clone $base)->whereIn('status', self::ACTIVE_STATUSES)->count();
        $totalSelesai = (clone $base)->whereIn('status', self::DONE_STATUSES)->count();
        $totalOverdue = $this->everOverdueResolution(clone $base)->count();

        $deptRows = [];
        foreach (InternalDepartment::orderBy('id')->get() as $dept) {
            if ($deptFilter && (int) $deptFilter !== (int) $dept->id) {
                continue;
            }
            $deptQ = (clone $base)->where('department_id', $dept->id);
            $deptRows[] = [
                $dept->name,
                (clone $deptQ)->count(),
                (clone $deptQ)->whereIn('status', self::ACTIVE_STATUSES)->count(),
                (clone $deptQ)->whereIn('status', self::DONE_STATUSES)->count(),
                $this->everOverdueResolution(clone $deptQ)->count(),
            ];
        }

        // Daftar tiket yang PERNAH overdue (kumulatif, tanpa batas 10
        // seperti di halaman). Diurutkan batas SLA menaik, NULL terakhir.
        $overdueTickets = $this->everOverdueResolution(clone $base)
            ->with(['department', 'user'])
            ->orderByRaw('sla_resolution_due_at IS NULL, sla_resolution_due_at ASC')
            ->get();

        $filename = 'rekap-dashboard-pimpinan-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use (
            $periode, $startDate, $endDate,
            $totalMasuk, $totalAktif, $totalSelesai, $totalOverdue,
            $deptRows, $overdueTickets
        ) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 agar dibuka dengan benar di Excel.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Rekap Dashboard Pimpinan Divisi']);
            fputcsv($out, ['Periode', $periode, $startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            fputcsv($out, []);
            fputcsv($out, ['Ringkasan KPI']);
            fputcsv($out, ['Total Tiket Masuk', $totalMasuk]);
            fputcsv($out, ['Tiket Aktif Berjalan', $totalAktif]);
            fputcsv($out, ['Tiket Selesai', $totalSelesai]);
            fputcsv($out, ['Overdue SLA (Resolusi)', $totalOverdue]);
            fputcsv($out, []);
            fputcsv($out, ['Per Bagian']);
            fputcsv($out, ['Bagian', 'Total', 'Aktif', 'Selesai', 'Overdue Resolusi']);
            foreach ($deptRows as $row) {
                fputcsv($out, $row);
            }
            fputcsv($out, []);
            fputcsv($out, ['Daftar Tiket Overdue']);
            fputcsv($out, ['Nomor Tiket', 'Bagian', 'Pemohon', 'Batas SLA Resolusi', 'Status saat ini', 'Keterlambatan (jam)']);
            foreach ($overdueTickets as $ticket) {
                // due_at bisa NULL untuk tiket yang ditandai Terlambat lewat
                // recordResolution tanpa due_at tersimpan — tampilkan '-'
                // dan jangan sampai error.
                $due = $ticket->sla_resolution_due_at
                    ? Carbon::parse($ticket->sla_resolution_due_at)
                    : null;
                if (! $due) {
                    $batas = '-';
                    $late = '-';
                } else {
                    $batas = $due->format('Y-m-d H:i');
                    if (in_array($ticket->status, self::ACTIVE_STATUSES, true)) {
                        $late = abs($due->diffInHours(now(), false));
                    } else {
                        $end = $ticket->resolved_at ?? $ticket->completed_at ?? $ticket->updated_at;
                        $late = abs($due->diffInHours(Carbon::parse($end), false));
                    }
                }
                fputcsv($out, [
                    $ticket->ticket_number,
                    $ticket->department?->name ?? '-',
                    $ticket->user?->nama_lengkap ?? '-',
                    $batas,
                    $ticket->status,
                    $late,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Peta ticket_id => tanggal selesai (Carbon, awal hari) untuk tiket
     * yang saat ini berstatus DONE.
     *
     * Tanggal selesai = created_at riwayat ticket_histories pertama kali
     * new_status masuk ke DONE_STATUSES; fallback tickets.updated_at bila
     * tidak ada riwayat penyelesaian. Filter bagian (department_id) tetap
     * berlaku; filter periode TIDAK dipakai di sini — pemanggil yang
     * mem-bucket tanggal ke dalam rentang periode.
     */
    private function resolveCompletedDates(?string $deptFilter): array
    {
        $doneQuery = fn ($q) => $q->whereIn('status', self::DONE_STATUSES)
            ->when($deptFilter, fn ($qq) => $qq->where('department_id', $deptFilter));

        $resolved = [];
        $historyRows = TicketHistory::select('ticket_id', DB::raw('MIN(created_at) as resolved_at'))
            ->whereIn('new_status', self::DONE_STATUSES)
            ->whereHas('ticket', $doneQuery)
            ->groupBy('ticket_id')
            ->get();
        foreach ($historyRows as $row) {
            $resolved[$row->ticket_id] = Carbon::parse($row->resolved_at)->startOfDay();
        }

        $fallbackTickets = Ticket::whereNotIn('id', array_keys($resolved))
            ->where($doneQuery)
            ->select('id', 'updated_at')
            ->get();
        foreach ($fallbackTickets as $ticket) {
            $resolved[$ticket->id] = Carbon::parse($ticket->updated_at)->startOfDay();
        }

        return $resolved;
    }

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

    private function shortenDeptName(string $name): string
    {
        return match (true) {
            str_contains($name, 'Umum')      => 'Umum & Rumah Tangga',
            str_contains($name, 'Aset')      => 'Aset / Inventaris & Logistik',
            str_contains($name, 'Pengadaan') => 'Pengadaan & Pemeliharaan',
            default                          => \Str::limit($name, 25),
        };
    }

    private function formatOverdueLabel(int $overdueHours): string
    {
        $hours = abs($overdueHours);
        if ($hours < 1) return 'Baru lewat';
        if ($hours < 24) return "{$hours} jam terlambat";
        $days = intdiv($hours, 24);
        $rem  = $hours % 24;
        return $rem > 0 ? "{$days}h {$rem}j terlambat" : "{$days} hari terlambat";
    }
}
