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
            'diproses' => Ticket::where('user_id', $user->id)->whereIn('status', ['Dialokasikan', 'Diverifikasi', 'Didistribusikan', 'Dalam Proses'])->count(),
            'selesai'  => Ticket::where('user_id', $user->id)->whereIn('status', ['Selesai', 'Ditutup Pemohon'])->count(),
            'ditolak'  => Ticket::where('user_id', $user->id)->where('status', 'Ditolak')->count(),
        ];

        // 2. 5 Tiket Terakhir Diajukan
        $recentTickets = Ticket::where('user_id', $user->id)
            ->with(['category', 'department'])
            ->latest()
            ->take(5)
            ->get();

        // 3. Otomatis cek dan tutup tiket yang melewati batas waktu konfirmasi 2 hari kerja
        app(\App\Services\TicketService::class)->autoCloseExpiredTickets();

        // 4. Notifikasi penting belum dibaca untuk modal popup ringkasan
        // Card konfirmasi hanya ditampilkan untuk tiket yang masih berstatus Selesai,
        // supaya card basi (tiket sudah ditutup/dikonfirmasi) tidak menawarkan tombol
        // konfirmasi yang pasti ditolak endpoint.
        $importantNotifications = $user->unreadNotifications()
            ->get()
            ->filter(function ($n) {
                $type = $n->data['type'] ?? '';
                if (!in_array($type, ['ticket_rejected', 'ticket_completed', 'ticket_auto_closed'])) {
                    return false;
                }
                if ($type === 'ticket_completed') {
                    $ticketId = $n->data['ticket_id'] ?? null;
                    if (!$ticketId) {
                        return false;
                    }
                    $ticket = \App\Models\Ticket::find($ticketId);
                    return $ticket && $ticket->status === 'Selesai';
                }
                return true;
            })
            ->values();

        return view('user.dashboard', compact('stats', 'recentTickets', 'importantNotifications'));
    }
}
