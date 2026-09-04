<?php

namespace App\Http\Controllers;

use App\Models\AsAset;
use App\Models\AsPks;
use App\Models\AuditLog;
use App\Models\FactBiayaBulanan;
use App\Models\FactPengadaan;
use App\Models\PgReminder;
use App\Models\UmBiayaHarian;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()?->isUser()) {
            return redirect()->route('user.dashboard');
        }

        if (auth()->user()?->isOperator()) {
            return redirect()->route('operator.dashboard');
        }

        $menunggu = UmBiayaHarian::where('approval_status', 'Diajukan')->count();
        $reminderAktif = PgReminder::where('status', 'Aktif')
            ->whereDate('tanggal_jatuh_tempo', '<=', now()->addDays(90))
            ->count();
        $pksJatuhTempo = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
            ->whereDate('jatuh_tempo', '<=', now()->addDays(90))
            ->count();

        $totalAset = AsAset::count();
        $totalPengadaan = (float) FactPengadaan::sum('total_nilai');

        // Total biaya operational 6 bulan (dari fact table via UmBiayaHarian)
        $totalBiaya6Bln = (float) UmBiayaHarian::where('approval_status', 'Disetujui')
            ->whereDate('tanggal', '>=', now()->subMonths(6)->startOfMonth())
            ->sum('jumlah');

        $activities = AuditLog::latest('id')->take(6)->get();
        $lastLog = $activities->first();

        // PKS akan jatuh tempo (tampil di dashboard)
        $pksNearDue = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
            ->whereDate('jatuh_tempo', '<=', now()->addDays(90))
            ->orderBy('jatuh_tempo')
            ->take(4)
            ->get();

        // Data chart 6 bulan terakhir per kategori
        $chartLabels = [];
        $chartValues = [];       // Total semua kategori
        $chartBbm = [];          // BBM saja
        $chartPerawatan = [];    // Perawatan
        $chartRt = [];           // Rumah Tangga

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $chartLabels[] = $month->translatedFormat('M');

            $monthData = UmBiayaHarian::whereYear('tanggal', $month->year)
                ->whereMonth('tanggal', $month->month)
                ->where('approval_status', 'Disetujui')
                ->selectRaw('kategori, sum(jumlah) as total')
                ->groupBy('kategori')
                ->pluck('total', 'kategori');

            $chartValues[]    = (float) $monthData->sum();
            $chartBbm[]       = (float) ($monthData['BBM'] ?? 0);
            $chartPerawatan[] = (float) ($monthData['Perawatan'] ?? 0);
            $chartRt[]        = (float) ($monthData['Rumah Tangga'] ?? 0);
        }

        return view('dashboard', compact(
            'menunggu',
            'reminderAktif',
            'pksJatuhTempo',
            'totalAset',
            'totalPengadaan',
            'totalBiaya6Bln',
            'activities',
            'lastLog',
            'pksNearDue',
            'chartLabels',
            'chartValues',
            'chartBbm',
            'chartPerawatan',
            'chartRt'
        ));
    }
}
