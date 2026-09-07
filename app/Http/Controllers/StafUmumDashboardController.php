<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\UmBiayaHarian;
use App\Models\UmKendaraan;
use App\Models\UmPermintaanCabang;
use Illuminate\Http\Request;

class StafUmumDashboardController extends Controller
{
    /**
     * Tampilan Dashboard khusus Staf Bagian Umum & Rumah Tangga.
     * Tiket difilter secara personal khusus untuk staf yang sedang login (assigned_to = auth()->id()).
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user->hasRole('umum_rt') && !$user->isSuperAdmin()) {
            abort(403, 'Akses dashboard ini khusus untuk Staf Bagian Umum & Rumah Tangga.');
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

        // 4. Metrik Operasional Modul Umum & RT
        // A. Biaya Harian
        $totalBiayaBulanIni = (float) UmBiayaHarian::whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->where('approval_status', 'Disetujui')
            ->sum('jumlah');

        $biayaMenungguApproval = UmBiayaHarian::where('approval_status', 'Diajukan')->count();

        $biayaPerKategori = UmBiayaHarian::whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->where('approval_status', 'Disetujui')
            ->selectRaw('kategori, sum(jumlah) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori')
            ->toArray();

        $transaksiBiayaTerbaru = UmBiayaHarian::with('maker')
            ->latest('tanggal')
            ->latest('id')
            ->take(5)
            ->get();

        // B. Kendaraan & Driver
        $totalKendaraan = UmKendaraan::count();
        $kendaraanAktif = UmKendaraan::where('status', 'Aktif')->count();
        $kendaraanServis = UmKendaraan::where('status', 'Servis')->count();
        $kendaraanList = UmKendaraan::latest()->take(5)->get();

        // C. Permintaan Cabang
        $permintaanPending = UmPermintaanCabang::whereIn('status', ['Diajukan', 'Diproses'])->count();
        $permintaanTerbaru = UmPermintaanCabang::latest('tanggal')->latest('id')->take(4)->get();

        return view('staf.umum.dashboard', compact(
            'user',
            'ticketStats',
            'antreanTiket',
            'tiketSelesai',
            'totalBiayaBulanIni',
            'biayaMenungguApproval',
            'biayaPerKategori',
            'transaksiBiayaTerbaru',
            'totalKendaraan',
            'kendaraanAktif',
            'kendaraanServis',
            'kendaraanList',
            'permintaanPending',
            'permintaanTerbaru'
        ));
    }
}
