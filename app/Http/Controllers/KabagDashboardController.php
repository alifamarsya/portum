<?php

namespace App\Http\Controllers;

use App\Models\AsAset;
use App\Models\AsPks;
use App\Models\FactPengadaan;
use App\Models\InternalDepartment;
use App\Models\PgReminder;
use App\Models\Ticket;
use App\Models\UmBiayaHarian;
use App\Models\User;
use Illuminate\Http\Request;

class KabagDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->isKabag() && !$user->isSuperAdmin()) {
            abort(403, 'Akses dashboard ini khusus untuk Kepala Bagian operasional.');
        }

        $deptId = $user->effectiveDepartmentId() ?? 1;
        $department = InternalDepartment::find($deptId);

        // Tiket di bagian ini
        $baseQuery = Ticket::where('department_id', $deptId);

        $stats = [
            'total'            => (clone $baseQuery)->count(),
            'perlu_disposisi'  => (clone $baseQuery)->where(function ($q) {
                $q->whereIn('status', ['Dialokasikan', 'Diverifikasi', 'Didistribusikan'])->whereNull('assigned_to');
            })->count(),
            'sedang_dikerjakan'=> (clone $baseQuery)->whereIn('status', ['Didistribusikan', 'Dalam Proses'])->whereNotNull('assigned_to')->count(),
            'selesai'          => (clone $baseQuery)->whereIn('status', ['Selesai', 'Ditutup Pemohon'])->count(),
            'ditolak'          => (clone $baseQuery)->where('status', 'Ditolak')->count(),
        ];

        // Antrean tiket yang membutuhkan verifikasi RBB & disposisi dari Kabag
        $antreanDisposisi = (clone $baseQuery)
            ->where(function ($q) {
                $q->whereIn('status', ['Dialokasikan', 'Diverifikasi', 'Didistribusikan'])->whereNull('assigned_to');
            })
            ->with(['user', 'category'])
            ->latest()
            ->take(8)
            ->get();

        // Tiket yang sedang ditangani staf
        $tiketBerjalan = (clone $baseQuery)
            ->whereIn('status', ['Didistribusikan', 'Dalam Proses'])
            ->whereNotNull('assigned_to')
            ->with(['user', 'assignedStaff', 'category'])
            ->latest()
            ->take(6)
            ->get();

        // Daftar staf tim di bawah Kabag
        $staffMembers = User::where(function ($q) use ($deptId) {
                $q->where('department_id', $deptId);
                if ($deptId == 1) {
                    $q->orWhereHas('role', fn($r) => $r->whereIn('nama', ['umum_rt', 'uk_umum_rt', 'uk_dokumen']));
                } elseif ($deptId == 2) {
                    $q->orWhereHas('role', fn($r) => $r->whereIn('nama', ['aset', 'uk_administrasi_aset', 'uk_logistik']));
                } elseif ($deptId == 3) {
                    $q->orWhereHas('role', fn($r) => $r->whereIn('nama', ['pengadaan', 'uk_pengadaan', 'uk_pemeliharaan']));
                }
            })
            ->where('is_active', true)
            ->whereDoesntHave('role', fn($q) => $q->whereIn('nama', ['kabag_umum', 'kabag_aset', 'kabag_pengadaan']))
            ->withCount([
                'assignedTickets as active_tickets_count' => function ($q) {
                    $q->whereIn('status', ['Didistribusikan', 'Dalam Proses']);
                },
                'assignedTickets as completed_tickets_count' => function ($q) {
                    $q->whereIn('status', ['Selesai', 'Ditutup Pemohon']);
                }
            ])
            ->orderBy('nama_lengkap')
            ->get();

        // Data spesifik per bagian
        $deptMetrics = [];
        $roleNama = strtolower($user->role?->nama ?? '');

        if ($roleNama === 'kabag_umum' || $deptId === 1) {
            $deptMetrics['label'] = 'Ringkasan Umum & Rumah Tangga';
            $deptMetrics['biaya_bulan_ini'] = UmBiayaHarian::whereYear('tanggal', now()->year)
                ->whereMonth('tanggal', now()->month)
                ->where('approval_status', 'Disetujui')
                ->sum('jumlah');
            $deptMetrics['menunggu_approval'] = UmBiayaHarian::where('approval_status', 'Diajukan')->count();
        } elseif ($roleNama === 'kabag_aset' || $deptId === 2) {
            $deptMetrics['label'] = 'Ringkasan Aset & Logistik';
            $deptMetrics['total_aset'] = AsAset::count();
            $deptMetrics['pks_jatuh_tempo'] = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
                ->whereDate('jatuh_tempo', '<=', now()->addDays(90))
                ->count();
        } elseif ($roleNama === 'kabag_pengadaan' || $deptId === 3) {
            $deptMetrics['label'] = 'Ringkasan Pengadaan & Pemeliharaan';
            $deptMetrics['total_pengadaan'] = (float) FactPengadaan::sum('total_nilai');
            $deptMetrics['reminder_vendor'] = PgReminder::where('status', 'Aktif')
                ->whereDate('tanggal_jatuh_tempo', '<=', now()->addDays(90))
                ->count();
        }

        return view('kabag.dashboard', compact(
            'department',
            'stats',
            'antreanDisposisi',
            'tiketBerjalan',
            'staffMembers',
            'deptMetrics'
        ));
    }
}
