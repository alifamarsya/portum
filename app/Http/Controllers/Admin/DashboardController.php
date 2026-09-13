<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses khusus Administrator.');
        }

        // 1. Statistik Pengguna
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;
        $mustChangePwdUsers = User::where('must_change_pwd', true)->count();

        // Distribusi pengguna per role
        $rolesWithUserCount = Role::withCount('users')->get();

        // 2. Statistik Modul Administrasi
        $totalRoles = Role::count();
        $totalPermissions = RolePermission::count();

        // 3. Statistik Audit Log & Integritas Hash
        $totalAuditLogs = AuditLog::count();
        $todayAuditLogs = AuditLog::whereDate('created_at', Carbon::today())->count();
        $recentAuditLogs = AuditLog::with('user')->latest('id')->take(6)->get();

        // Cek cepat integritas rantai hash pada 50 log terakhir
        $recentLogsForCheck = AuditLog::orderBy('id', 'asc')->take(50)->get();
        $hasTamperedLogs = false;
        $expectedPrev = null;
        foreach ($recentLogsForCheck as $log) {
            $expectedHash = hash('sha256', $log->prev_hash . '|' . $log->aksi . '|' . $log->modul . '|'
                . $log->entitas . '|' . $log->entitas_id . '|' . $log->keterangan . '|' . $log->created_at);

            if (!hash_equals($expectedHash, $log->hash) || ($log->prev_hash !== $expectedPrev && $expectedPrev !== null)) {
                $hasTamperedLogs = true;
                break;
            }
            $expectedPrev = $log->hash;
        }

        // 4. Statistik Sistem Tiket
        $totalTickets = Ticket::count();
        $pendingTickets = Ticket::where('status', 'Menunggu Verifikasi')->count();
        $processTickets = Ticket::whereIn('status', ['Dialokasikan', 'Diverifikasi', 'Didistribusikan', 'Dalam Proses'])->count();
        $completedTickets = Ticket::whereIn('status', ['Selesai', 'Ditutup Pemohon'])->count();
        $rejectedTickets = Ticket::where('status', 'Ditolak')->count();

        // Kategori tiket dengan jumlahnya
        $categoriesWithCount = TicketCategory::withCount('tickets')->get();

        // 5. Informasi Server / Lingkungan Sistem
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'app_env' => config('app.env'),
            'db_driver' => config('database.default'),
            'server_time' => now()->format('H:i:s T'),
        ];

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'mustChangePwdUsers',
            'rolesWithUserCount',
            'totalRoles',
            'totalPermissions',
            'totalAuditLogs',
            'todayAuditLogs',
            'recentAuditLogs',
            'hasTamperedLogs',
            'totalTickets',
            'pendingTickets',
            'processTickets',
            'completedTickets',
            'rejectedTickets',
            'categoriesWithCount',
            'systemInfo'
        ));
    }
}
