<?php

namespace App\Http\Controllers;

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
            'diproses'    => Ticket::whereIn('status', ['Didistribusikan', 'Diverifikasi', 'Dalam Proses'])->count(),
            'terlambat'   => Ticket::whereNotIn('status', ['Selesai', 'Ditolak'])
                                ->whereHas('category', function ($q) use ($now) {
                                    $q->whereRaw('tickets.created_at < NOW() - INTERVAL ticket_categories.default_sla_hours HOUR');
                                })->count(),
            'selesai_bulan_ini' => Ticket::where('status', 'Selesai')
                                ->whereYear('updated_at', $now->year)
                                ->whereMonth('updated_at', $now->month)
                                ->count(),
        ];

        // 2. Antrian tiket belum diverifikasi (terbaru dulu)
        $antrian = Ticket::where('status', 'Menunggu Verifikasi')
            ->with(['user', 'category'])
            ->oldest()
            ->take(10)
            ->get();

        // 3. Ringkasan distribusi per departemen
        $distribusi = InternalDepartment::withCount([
            'tickets as total_aktif' => fn ($q) => $q->whereNotIn('status', ['Selesai', 'Ditolak']),
            'tickets as total_selesai' => fn ($q) => $q->where('status', 'Selesai'),
        ])->get();

        return view('operator.dashboard', compact('stats', 'antrian', 'distribusi'));
    }
}
