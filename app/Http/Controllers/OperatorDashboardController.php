<?php

namespace App\Http\Controllers;

use App\Models\AsMutasiAset;
use App\Models\InternalDepartment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OperatorDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->isOperator()) {
            abort(403, 'Halaman ini khusus untuk Operator Helpdesk.');
        }

        $now = Carbon::now();

        // 1. Stat cards – scope all tickets (Operator sees everything)
        $stats = [
            'baru_masuk'  => Ticket::where('status', 'Menunggu Verifikasi')->count(),
            'diproses'    => Ticket::whereIn('status', ['Dialokasikan', 'Didistribusikan', 'Diverifikasi', 'Dalam Proses'])->count(),
            'terlambat'   => Ticket::where('status', 'Menunggu Verifikasi')
                                ->where('sla_response_due_at', '<', $now)
                                ->count(),
            'selesai_bulan_ini' => Ticket::whereIn('status', ['Ditutup Pemohon', 'Ditutup Otomatis (Sistem)'])
                                ->whereYear('updated_at', $now->year)
                                ->whereMonth('updated_at', $now->month)
                                ->count(),
        ];

        // 2. Antrian tiket belum diverifikasi (diurutkan berdasarkan batas waktu SLA terdekat)
        $antrian = Ticket::where('status', 'Menunggu Verifikasi')
            ->with(['user'])
            ->orderBy('sla_response_due_at')
            ->orderBy('created_at')
            ->take(15)
            ->get();

        // 3. Ringkasan distribusi per departemen
        $distribusi = InternalDepartment::withCount([
            'tickets as total_aktif' => fn ($q) => $q->whereNotIn('status', ['Selesai', 'Ditolak']),
            'tickets as total_selesai' => fn ($q) => $q->where('status', 'Selesai'),
        ])->get();

        // 4. Antrean mutasi belum dicek operator (status Diajukan saja,
        // diurutkan dari yang paling lama menunggu)
        $mutasiBaru = AsMutasiAset::where('status', 'Diajukan')->count();
        $antrianMutasi = AsMutasiAset::where('status', 'Diajukan')
            ->with(['pengaju', 'aset', 'lokasiTujuan'])
            ->orderBy('created_at')
            ->take(15)
            ->get();

        return view('operator.dashboard', compact('stats', 'antrian', 'distribusi', 'mutasiBaru', 'antrianMutasi'));
    }
}
