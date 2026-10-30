<?php

namespace App\Http\Controllers;

use App\Models\AsAset;
use App\Models\AsMutasiAset;
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

        if (!$user->isBagian() && !$user->isKabag() && !$user->isSuperAdmin()) {
            abort(403, 'Akses dashboard ini khusus untuk tim Bagian operasional.');
        }

        $deptId = $user->effectiveDepartmentId() ?? 1;
        $department = InternalDepartment::find($deptId);

        // Tiket di bagian ini
        $baseQuery = Ticket::where('department_id', $deptId);

        $stats = [
            'total'            => (clone $baseQuery)->count(),
            'perlu_disposisi'  => (clone $baseQuery)->whereIn('status', ['Dialokasikan', 'Diverifikasi'])->count(),
            'selesai'          => (clone $baseQuery)->whereIn('status', ['Selesai', 'Ditutup Pemohon', 'Ditutup Otomatis (Sistem)'])->count(),
            'ditolak'          => (clone $baseQuery)->where('status', 'Ditolak')->count(),
        ];

        // Antrean tiket yang membutuhkan tindakan dari Bagian (Terima / Tolak)
        $antreanDisposisi = (clone $baseQuery)
            ->whereIn('status', ['Dialokasikan', 'Diverifikasi'])
            ->with(['user', 'category'])
            ->latest()
            ->take(8)
            ->get();

        // Rekan tim di bagian yang sama
        $staffMembers = User::where('department_id', $deptId)
            ->where('is_active', true)
            ->whereDoesntHave('role', fn($q) => $q->whereIn('nama', ['admin', 'superadmin', 'operator', 'user', 'pimpinan']))
            ->withCount([
                'assignedTickets as active_tickets_count' => function ($q) {
                    $q->whereIn('status', ['Didistribusikan', 'Dalam Proses']);
                },
                'assignedTickets as completed_tickets_count' => function ($q) {
                    $q->whereIn('status', ['Selesai', 'Ditutup Pemohon', 'Ditutup Otomatis (Sistem)']);
                }
            ])
            ->orderBy('nama_lengkap')
            ->get();

        // Data spesifik per bagian
        $deptMetrics = [];
        $roleNama = strtolower($user->role?->nama ?? '');

        if (in_array($roleNama, ['bagian_umum', 'kabag_umum']) || $deptId === 1) {
            $deptMetrics['label'] = 'Ringkasan Bagian Umum';
            $deptMetrics['biaya_bulan_ini'] = UmBiayaHarian::whereYear('tanggal', now()->year)
                ->whereMonth('tanggal', now()->month)
                ->sum('jumlah');
            $deptMetrics['total_transaksi'] = UmBiayaHarian::whereYear('tanggal', now()->year)
                ->whereMonth('tanggal', now()->month)
                ->count();
        } elseif (in_array($roleNama, ['bagian_aset', 'kabag_aset']) || $deptId === 2) {
            $deptMetrics['label'] = 'Ringkasan Bagian Aset';
            $deptMetrics['total_aset'] = AsAset::count();
            $deptMetrics['pks_jatuh_tempo'] = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
                ->whereDate('jatuh_tempo', '<=', now()->addDays(90))
                ->count();
        } elseif (in_array($roleNama, ['bagian_pengadaan', 'kabag_pengadaan']) || $deptId === 3) {
            $deptMetrics['label'] = 'Ringkasan Bagian Pengadaan';
            $deptMetrics['total_pengadaan'] = (float) FactPengadaan::sum('total_nilai');
            $deptMetrics['reminder_vendor'] = PgReminder::where('status', 'Aktif')
                ->whereDate('tanggal_jatuh_tempo', '<=', now()->addDays(90))
                ->count();
        }

        // Antrean mutasi khusus Kabag/Staf Bagian Aset (verifikasi Bagian Aset).
        // Hanya ditampilkan untuk bagian aset agar dashboard bagian lain tetap bersih.
        // Scope ketat: hanya status 'Diproses' yang memang giliran verifikasi
        // Bagian Aset. 'Diajukan' = antrean operator, 'Menunggu Approval' =
        // antrean Kadiv; keduanya bukan giliran Bagian Aset.
        $showMutasiAset = in_array($roleNama, ['bagian_aset', 'kabag_aset']) || $deptId === 2;
        $mutasiMenungguVerifikasi = 0;
        $antreanMutasiVerifikasi = collect();
        if ($showMutasiAset) {
            $mutasiMenungguVerifikasi = AsMutasiAset::where('status', 'Diproses')->count();
            $antreanMutasiVerifikasi = AsMutasiAset::where('status', 'Diproses')
                ->with(['pengaju', 'aset', 'lokasiTujuan'])
                ->oldest()
                ->take(8)
                ->get();
        }

        return view('kabag.dashboard', compact(
            'department',
            'stats',
            'showMutasiAset',
            'mutasiMenungguVerifikasi',
            'antreanMutasiVerifikasi',
            'antreanDisposisi',
            'staffMembers',
            'deptMetrics'
        ));
    }
}
