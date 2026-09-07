<?php

namespace App\Http\Controllers;

use App\Models\AsAset;
use App\Models\AsMemoSewaCabang;
use App\Models\AsPks;
use App\Models\Ticket;
use Illuminate\Http\Request;

class StafAsetDashboardController extends Controller
{
    /**
     * Tampilan Dashboard khusus Staf Bagian Aset/Inventaris & Logistik.
     * Tiket difilter secara personal khusus untuk staf yang sedang login (assigned_to = auth()->id()).
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user->hasRole('aset') && !$user->isSuperAdmin()) {
            abort(403, 'Akses dashboard ini khusus untuk Staf Bagian Aset/Inventaris & Logistik.');
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

        // 4. Metrik Operasional Modul Aset & Logistik
        // A. Inventarisasi Aset
        $totalAset = AsAset::count();
        $totalNilaiPerolehan = (float) AsAset::sum('nilai_perolehan');
        $asetBaik = AsAset::where('kondisi', 'Baik')->count();
        $asetRusakRingan = AsAset::where('kondisi', 'Rusak Ringan')->count();
        $asetRusakBerat = AsAset::where('kondisi', 'Rusak Berat')->count();
        $asetTerbaru = AsAset::latest('tanggal_perolehan')->latest('id')->take(5)->get();

        // B. PKS & Jatuh Tempo
        $pksAktif = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])->count();
        $pksJatuhTempo = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
            ->whereDate('jatuh_tempo', '<=', now()->addDays(90))
            ->count();
        $pksNearDue = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
            ->whereDate('jatuh_tempo', '<=', now()->addDays(90))
            ->orderBy('jatuh_tempo')
            ->take(5)
            ->get();

        // C. Memo Sewa Cabang
        $memoSewaPending = AsMemoSewaCabang::whereIn('status_persetujuan', ['Diajukan', 'Persetujuan Prinsip'])->count();
        $memoSewaTerbaru = AsMemoSewaCabang::latest('tanggal')->latest('id')->take(4)->get();

        return view('staf.aset.dashboard', compact(
            'user',
            'ticketStats',
            'antreanTiket',
            'tiketSelesai',
            'totalAset',
            'totalNilaiPerolehan',
            'asetBaik',
            'asetRusakRingan',
            'asetRusakBerat',
            'asetTerbaru',
            'pksAktif',
            'pksJatuhTempo',
            'pksNearDue',
            'memoSewaPending',
            'memoSewaTerbaru'
        ));
    }
}
