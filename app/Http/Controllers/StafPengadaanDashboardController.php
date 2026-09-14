<?php

namespace App\Http\Controllers;

use App\Models\FactPengadaan;
use App\Models\PgDraftDokumen;
use App\Models\PgMemoInternal;
use App\Models\PgNegosiasi;
use App\Models\PgPenawaran;
use App\Models\PgReminder;
use App\Models\PgSpk;
use App\Models\PmJadwalPemeliharaan;
use App\Models\PmMonitoringKondisi;
use App\Models\PmPengawasanPenggunaan;
use App\Models\PmPerencanaanKebutuhan;
use App\Models\PmTindakLanjutPerbaikan;
use App\Models\Ticket;
use Illuminate\Http\Request;

class StafPengadaanDashboardController extends Controller
{
    /**
     * Tampilan Dashboard khusus Staf Bagian Pengadaan & Pemeliharaan Aset & Inventaris.
     * Mencakup staf Unit Kerja Pengadaan (uk_pengadaan) dan Pemeliharaan (uk_pemeliharaan).
     * Tiket difilter secara personal khusus untuk staf yang sedang login (assigned_to = auth()->id()).
     */
    public function index()
    {
        $user = auth()->user();

        $isAuthorized = $user->hasRole(['uk_pengadaan', 'uk_pemeliharaan', 'pengadaan'])
            || $user->isSuperAdmin();

        if (!$isAuthorized) {
            abort(403, 'Akses dashboard ini khusus untuk Staf Bagian Pengadaan & Pemeliharaan.');
        }

        // 1. Metrik Tiket Personal Staf Ini (strictly scoped to assigned_to = $user->id)
        $ticketStats = [
            'tugas_aktif'       => Ticket::where('assigned_to', $user->id)->whereIn('status', ['Didistribusikan', 'Dalam Proses'])->count(),
            'sedang_dikerjakan' => Ticket::where('assigned_to', $user->id)->where('status', 'Dalam Proses')->count(),
            'selesai'           => Ticket::where('assigned_to', $user->id)->whereIn('status', ['Selesai', 'Ditutup Pemohon'])->count(),
            'total_tugas'       => Ticket::where('assigned_to', $user->id)->count(),
        ];

        // 2. Daftar Antrean Tiket Aktif Milik Staf Ini (prioritas darurat/kritis di atas)
        $antreanTiket = Ticket::where('assigned_to', $user->id)
            ->whereIn('status', ['Didistribusikan', 'Dalam Proses'])
            ->with(['user', 'category', 'disposedBy'])
            ->orderByRaw("FIELD(priority, 'Darurat', 'Kritis', 'Tinggi', 'Normal', 'Sedang', 'Rendah')")
            ->latest()
            ->get();

        // 3. Tiket Selesai Terakhir yang Ditangani Staf Ini
        $tiketSelesai = Ticket::where('assigned_to', $user->id)
            ->whereIn('status', ['Selesai', 'Ditutup Pemohon'])
            ->with(['user', 'category'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        // 4. Metrik Operasional Unit Kerja Pengadaan
        $totalPengadaan = (float) FactPengadaan::sum('total_nilai');
        $totalSpk = PgSpk::count();
        $spkAktif = PgSpk::whereIn('status', ['Dikerjakan', 'Draft', 'Diterbitkan'])->count();
        $spkTerbaru = PgSpk::latest('tanggal_terbit')->latest('id')->take(5)->get();

        $reminderAktif = PgReminder::where('status', 'Aktif')
            ->whereDate('tanggal_jatuh_tempo', '<=', now()->addDays(90))
            ->count();
        $reminderList = PgReminder::where('status', 'Aktif')
            ->orderBy('tanggal_jatuh_tempo')
            ->take(5)
            ->get();

        $penawaranTotal = PgPenawaran::count();
        $perencanaanTotal = PmPerencanaanKebutuhan::count();
        $memoTotal = PgMemoInternal::count();

        // 5. Metrik Operasional Unit Kerja Pemeliharaan & Pengawasan
        $jadwalTotal = PmJadwalPemeliharaan::count();
        $jadwalDirencanakan = PmJadwalPemeliharaan::where('status', 'Direncanakan')->count();
        $jadwalDikerjakan = PmJadwalPemeliharaan::where('status', 'Dikerjakan')->count();
        $jadwalTerbaru = PmJadwalPemeliharaan::with('aset')->latest()->take(5)->get();

        $monitoringTotal = PmMonitoringKondisi::count();
        $monitoringTerbaru = PmMonitoringKondisi::with('aset')->latest('tanggal_inspeksi')->latest('id')->take(5)->get();

        $pengawasanTotal = PmPengawasanPenggunaan::count();
        $tindakLanjutTotal = PmTindakLanjutPerbaikan::count();
        $tindakLanjutAktif = PmTindakLanjutPerbaikan::whereIn('status', ['Direncanakan', 'Dikerjakan'])->count();
        $tindakLanjutTerbaru = PmTindakLanjutPerbaikan::with('aset')->latest('tanggal_laporan')->latest('id')->take(5)->get();

        return view('staf.pengadaan.dashboard', compact(
            'user',
            'ticketStats',
            'antreanTiket',
            'tiketSelesai',
            'totalPengadaan',
            'totalSpk',
            'spkAktif',
            'spkTerbaru',
            'reminderAktif',
            'reminderList',
            'penawaranTotal',
            'perencanaanTotal',
            'memoTotal',
            'jadwalTotal',
            'jadwalDirencanakan',
            'jadwalDikerjakan',
            'jadwalTerbaru',
            'monitoringTotal',
            'monitoringTerbaru',
            'pengawasanTotal',
            'tindakLanjutTotal',
            'tindakLanjutAktif',
            'tindakLanjutTerbaru'
        ));
    }
}
