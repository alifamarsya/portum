<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. 4 Stat Cards untuk User
        $stats = [
            'total'   => Ticket::where('user_id', $user->id)->count(),
            'menunggu' => Ticket::where('user_id', $user->id)->where('status', 'Menunggu Verifikasi')->count(),
            'diproses' => Ticket::where('user_id', $user->id)->whereIn('status', ['Diverifikasi', 'Didistribusikan', 'Dalam Proses'])->count(),
            'selesai'  => Ticket::where('user_id', $user->id)->whereIn('status', ['Selesai', 'Ditutup Pemohon'])->count(),
            'ditolak'  => Ticket::where('user_id', $user->id)->where('status', 'Ditolak')->count(),
        ];

        // 2. 5 Tiket Terakhir Diajukan
        $recentTickets = Ticket::where('user_id', $user->id)
            ->with(['category', 'department'])
            ->latest()
            ->take(5)
            ->get();

        return view('user.dashboard', compact('stats', 'recentTickets'));
    }
}
