<?php

namespace App\Http\Controllers;

use App\Models\AsPks;
use App\Models\AuditLog;
use App\Models\PgReminder;
use App\Models\UmBiayaHarian;

class DashboardController extends Controller
{
    public function index()
    {
        $menunggu = UmBiayaHarian::where('approval_status', 'Diajukan')->count();
        $reminderAktif = PgReminder::where('status', 'Aktif')
            ->whereDate('tanggal_jatuh_tempo', '<=', now()->addDays(90))
            ->count();
        $pksJatuhTempo = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
            ->whereDate('jatuh_tempo', '<=', now()->addDays(90))
            ->count();
        $lastLog = AuditLog::latest('id')->first();

        return view('dashboard', compact('menunggu', 'reminderAktif', 'pksJatuhTempo', 'lastLog'));
    }
}
