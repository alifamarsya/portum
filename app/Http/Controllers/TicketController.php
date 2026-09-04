<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\InternalDepartment;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use LogsAudit;

    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of tickets based on user role.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $baseQuery = Ticket::visibleTo($user);

        // Calculate overview counts for current user's visible tickets
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'menunggu' => (clone $baseQuery)->where('status', 'Menunggu Verifikasi')->count(),
            'diverifikasi' => (clone $baseQuery)->where('status', 'Diverifikasi')->count(),
            'dalam_proses' => (clone $baseQuery)->whereIn('status', ['Didistribusikan', 'Dalam Proses'])->count(),
            'selesai' => (clone $baseQuery)->whereIn('status', ['Selesai', 'Ditutup Pemohon'])->count(),
            'ditolak' => (clone $baseQuery)->where('status', 'Ditolak')->count(),
        ];

        $query = (clone $baseQuery)->with(['user', 'category', 'department'])->latest();

        // Filter by status if provided in request
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category if provided in request
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by department if provided in request
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Search by ticket number or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('nama_lengkap', 'LIKE', "%{$search}%"));
            });
        }

        $tickets = $query->paginate(15)->withQueryString();
        $categories = TicketCategory::all();
        $departments = InternalDepartment::all();

        return view('tickets.index', compact('tickets', 'categories', 'departments', 'stats'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        $categories = TicketCategory::all();
        return view('tickets.create', compact('categories'));
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:ticket_categories,id',
            'priority' => 'required|in:Rendah,Normal,Sedang,Tinggi,Darurat',
            'description' => 'required|string|min:10',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,zip|max:10240',
        ]);

        $ticket = $this->ticketService->createTicket(
            $validated,
            auth()->user(),
            $request->file('attachment')
        );

        $this->audit(
            'CREATE',
            'Ticketing',
            'Ticket',
            $ticket->id,
            "Membuat tiket baru {$ticket->ticket_number}"
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Tiket {$ticket->ticket_number} berhasil dibuat dan sedang menunggu verifikasi.");
    }

    /**
     * Display the specified ticket with history timeline.
     */
    public function show(Ticket $ticket)
    {
        $user = auth()->user();

        // Check view authorization
        if (!is_null($user->department_id) && $ticket->department_id !== $user->department_id) {
            abort(403, 'Tiket ini tidak ditugaskan ke bagian Anda.');
        }

        if ($user->hasRole('user') && is_null($user->department_id) && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk melihat tiket ini.');
        }

        $ticket->load([
            'user',
            'category',
            'department',
            'histories' => function ($q) {
                $q->with('user')->latest();
            }
        ]);

        $departments = InternalDepartment::all();

        // Audit: Pemohon membuka detail tiket miliknya — catat sebagai aksi VIEW
        if ($user->isUser() && $ticket->user_id === $user->id) {
            $this->audit(
                'VIEW',
                'Ticketing',
                'Ticket',
                $ticket->id,
                "Pemohon membuka detail tiket {$ticket->ticket_number}"
            );
        }

        return view('tickets.show', compact('ticket', 'departments'));
    }

    /**
     * Show the form for editing/updating ticket status or assignment.
     */
    public function edit(Ticket $ticket)
    {
        $user = auth()->user();
        $isOperator = $user->isOperator() || $user->isSuperAdmin();
        $isInternal = !is_null($user->department_id);

        if (!$isOperator && !$isInternal) {
            abort(403, 'Hanya Operator dan Staf Bagian Internal yang berwenang mengubah tiket.');
        }

        if ($isInternal && !$isOperator && $ticket->department_id !== $user->department_id) {
            abort(403, 'Tiket ini tidak ditugaskan ke bagian Anda.');
        }

        $categories = TicketCategory::all();
        $departments = InternalDepartment::all();

        $ticket->load(['user', 'category', 'department', 'histories' => fn($q) => $q->with('user')->latest()]);

        return view('tickets.edit', compact('ticket', 'categories', 'departments'));
    }

    /**
     * Update the specified ticket in storage.
     * Records changes to TicketHistories on status/department updates.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = auth()->user();
        $isOperator = $user->isOperator() || $user->isSuperAdmin();
        $isInternal = !is_null($user->department_id);

        // Authorization check
        if (!$isOperator && !$isInternal) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah tiket.');
        }

        if ($isInternal && !$isOperator && $ticket->department_id !== $user->department_id) {
            abort(403, 'Tiket ini tidak ditugaskan ke bagian Anda.');
        }

        $rules = [
            'status' => 'required|string|in:Menunggu Verifikasi,Diverifikasi,Didistribusikan,Dalam Proses,Selesai,Ditolak,Ditutup Pemohon',
            'notes' => 'nullable|string|max:1000',
        ];

        // Only operator or superadmin can re-assign department, category & change priority
        if ($isOperator) {
            $rules['department_id'] = 'nullable|exists:internal_departments,id';
            $rules['category_id']   = 'nullable|exists:ticket_categories,id';
            $rules['priority']      = 'nullable|in:Rendah,Normal,Sedang,Tinggi,Kritis,Darurat';
        }

        $validated = $request->validate($rules);

        $oldStatus = $ticket->status;
        $this->ticketService->updateTicket($ticket, $validated, $user);

        $this->audit(
            'UPDATE',
            'Ticketing',
            'Ticket',
            $ticket->id,
            "Memperbarui tiket {$ticket->ticket_number} (Status: {$oldStatus} -> {$ticket->status})"
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Tiket {$ticket->ticket_number} berhasil diperbarui.");
    }

    /**
     * Allow the ticket requester (pemohon) to confirm closure when status is 'Selesai'.
     * This records the confirmation as 'Ditutup Pemohon' in histories and audit log.
     */
    public function confirmClose(Ticket $ticket)
    {
        $user = auth()->user();

        // Hanya pemohon pemilik tiket yang bisa konfirmasi
        if ($ticket->user_id !== $user->id) {
            abort(403, 'Anda bukan pemilik tiket ini.');
        }

        // Hanya bisa konfirmasi jika status sudah Selesai dari pihak internal
        if ($ticket->status !== 'Selesai') {
            return back()->with('error', 'Tiket hanya dapat dikonfirmasi jika sudah berstatus Selesai dari bagian yang menangani.');
        }

        // Update status dan rekam ke TicketHistories
        $this->ticketService->updateTicket($ticket, [
            'status' => 'Ditutup Pemohon',
            'notes'  => 'Pemohon mengkonfirmasi bahwa kendala telah terselesaikan dan menutup tiket ini.',
        ], $user);

        // Audit Log — masuk ke Hash-Chain
        $this->audit(
            'CONFIRM_CLOSE',
            'Ticketing',
            'Ticket',
            $ticket->id,
            "Pemohon mengkonfirmasi penutupan tiket {$ticket->ticket_number} — masalah telah terselesaikan."
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('status', 'Terima kasih! Tiket berhasil dikonfirmasi sebagai selesai dan telah ditutup.');
    }
}
