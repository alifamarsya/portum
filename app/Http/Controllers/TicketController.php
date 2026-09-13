<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\InternalDepartment;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'diverifikasi' => (clone $baseQuery)->whereIn('status', ['Diverifikasi', 'Dialokasikan'])->count(),
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

        // Filter by jenis_pengajuan if provided
        if ($request->filled('jenis_pengajuan')) {
            $query->where('jenis_pengajuan', $request->jenis_pengajuan);
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
        if (!auth()->user()->isUser()) {
            abort(403, 'Hanya role User (Pemohon Layanan) yang berwenang membuat tiket baru. Role lainnya hanya dapat memantau dan memproses tiket.');
        }

        $categories = TicketCategory::all();
        return view('tickets.create', compact('categories'));
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isUser()) {
            abort(403, 'Hanya role User (Pemohon Layanan) yang berwenang membuat tiket baru.');
        }

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
        $userDeptId = $user->effectiveDepartmentId();
        if (!is_null($userDeptId) && !$user->isSuperAdmin() && !$user->isOperator() && !$user->isKepalaDivisi() && $ticket->department_id !== $userDeptId) {
            abort(403, 'Tiket ini tidak ditugaskan ke bagian Anda.');
        }

        if ($user->isUser() && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk melihat tiket ini.');
        }

        $ticket->load([
            'user',
            'category',
            'department',
            'assignedStaff',
            'disposedBy',
            'histories' => function ($q) {
                $q->with('user')->latest();
            }
        ]);

        $departments = InternalDepartment::all();

        // Daftar staf aktif di bagian ini untuk dipilih oleh Kabag saat mendisposisikan tiket
        $departmentStaff = collect();
        if ($ticket->department_id) {
            $departmentStaff = User::where(function ($q) use ($ticket) {
                    $q->where('department_id', $ticket->department_id);
                    if ($ticket->department_id == 1) {
                        // Sertakan role lama (umum_rt) dan role baru (uk_umum_rt, uk_dokumen)
                        $q->orWhereHas('role', fn($r) => $r->whereIn('nama', ['umum_rt', 'uk_umum_rt', 'uk_dokumen']));
                    } elseif ($ticket->department_id == 2) {
                        $q->orWhereHas('role', fn($r) => $r->whereIn('nama', ['aset', 'uk_administrasi_aset', 'uk_logistik']));
                    } elseif ($ticket->department_id == 3) {
                        $q->orWhereHas('role', fn($r) => $r->whereIn('nama', ['pengadaan', 'uk_pengadaan', 'uk_pemeliharaan']));
                    }
                })
                ->where('is_active', true)
                ->whereDoesntHave('role', fn($q) => $q->whereIn('nama', ['kabag_umum', 'kabag_aset', 'kabag_pengadaan']))
                ->orderBy('nama_lengkap')
                ->get();
        }

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

        $unitKerjaList = UnitKerja::where('is_active', true)->get();

        return view('tickets.show', compact('ticket', 'departments', 'departmentStaff', 'unitKerjaList'));
    }

    /**
     * Disposisi tiket oleh Kepala Bagian (Kabag) kepada staf tertentu di timnya
     * setelah pengecekan kesesuaian RBB / pagu anggaran.
     */
    public function dispose(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        $isAuthorizedKabag = ($user->isKabag() && $user->effectiveDepartmentId() == $ticket->department_id) || $user->isSuperAdmin();
        if (!$isAuthorizedKabag) {
            abort(403, 'Hanya Kepala Bagian penanggung jawab yang berwenang mendisposisikan tiket ini.');
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'disposition_notes' => 'required|string|min:5|max:1000',
        ]);

        $staff = \App\Models\User::findOrFail($validated['assigned_to']);
        if ($staff->effectiveDepartmentId() != $ticket->department_id) {
            return back()->withErrors(['assigned_to' => 'Staf yang dipilih bukan anggota bagian ini.'])->withInput();
        }

        $this->ticketService->disposeTicket($ticket, $staff, $validated['disposition_notes'], $user);

        $this->audit(
            'DISPOSE',
            'Ticketing',
            'Ticket',
            $ticket->id,
            "Kepala Bagian {$user->nama_lengkap} mendisposisikan tiket {$ticket->ticket_number} ke staf {$staff->nama_lengkap} ({$staff->username}). Catatan RBB/Anggaran: {$validated['disposition_notes']}"
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Tiket {$ticket->ticket_number} berhasil disetujui & didisposisikan kepada staf {$staff->nama_lengkap}.");
    }

    /**
     * Penolakan tiket oleh Kepala Bagian atau Operator (misal: tidak sesuai RBB / pagu habis).
     */
    public function reject(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        $isAuthorized = ($user->isKabag() && $user->effectiveDepartmentId() == $ticket->department_id) 
            || $user->isOperator() 
            || $user->isSuperAdmin();

        if (!$isAuthorized) {
            abort(403, 'Anda tidak berwenang menolak tiket ini.');
        }

        $validated = $request->validate([
            'notes' => 'required|string|min:5|max:1000',
        ]);

        $oldStatus = $ticket->status;
        $this->ticketService->updateTicket($ticket, [
            'status' => 'Ditolak',
            'notes' => "Tiket ditolak oleh {$user->nama_lengkap} ({$user->role?->label}). Alasan: {$validated['notes']}",
        ], $user);

        $this->audit(
            'REJECT',
            'Ticketing',
            'Ticket',
            $ticket->id,
            "Menolak tiket {$ticket->ticket_number}. Alasan: {$validated['notes']}"
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Tiket {$ticket->ticket_number} telah ditolak.");
    }

    /**
     * Show the form for editing/updating ticket status or assignment.
     */
    public function edit(Ticket $ticket)
    {
        $user = auth()->user();
        $isOperator = $user->isOperator() || $user->isSuperAdmin();
        $isInternal = !is_null($user->effectiveDepartmentId());

        if (!$isOperator && !$isInternal) {
            abort(403, 'Hanya Operator dan Staf/Kepala Bagian Internal yang berwenang mengubah tiket.');
        }

        if ($isInternal && !$isOperator && $ticket->department_id !== $user->effectiveDepartmentId()) {
            abort(403, 'Tiket ini tidak ditugaskan ke bagian Anda.');
        }

        $categories = TicketCategory::all();
        $departments = InternalDepartment::all();

        $ticket->load(['user', 'category', 'department', 'assignedStaff', 'disposedBy', 'histories' => fn($q) => $q->with('user')->latest()]);

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
        $isInternal = !is_null($user->effectiveDepartmentId());

        // Authorization check
        if (!$isOperator && !$isInternal) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah tiket.');
        }

        if ($isInternal && !$isOperator && $ticket->department_id !== $user->effectiveDepartmentId()) {
            abort(403, 'Tiket ini tidak ditugaskan ke bagian Anda.');
        }

        $rules = [
            'status' => 'required|string|in:Menunggu Verifikasi,Diverifikasi,Didistribusikan,Dialokasikan,Dalam Proses,Selesai,Ditolak,Ditutup Pemohon',
            'notes' => 'nullable|string|max:1000',
        ];

        // Operator verifies and allocates directly to department: status is automatically set to 'Dialokasikan'
        if ($isOperator) {
            $request->merge(['status' => 'Dialokasikan']);
            $rules['department_id']    = 'required|exists:internal_departments,id';
            $rules['unit_kerja_id']    = 'nullable|exists:unit_kerja,id';
            $rules['category_id']      = 'nullable|exists:ticket_categories,id';
            $rules['priority']         = 'nullable|in:Rendah,Normal,Sedang,Tinggi,Kritis,Darurat';
            $rules['jenis_pengajuan']  = 'nullable|in:Permintaan,Permasalahan';
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

    /**
     * View ticket attachment in browser.
     */
    public function viewAttachment(Ticket $ticket)
    {
        $user = auth()->user();

        // Check view authorization
        $userDeptId = $user->effectiveDepartmentId();
        if (!is_null($userDeptId) && !$user->isSuperAdmin() && !$user->isOperator() && !$user->isKepalaDivisi() && $ticket->department_id !== $userDeptId) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isUser() && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak berwenang mengakses lampiran tiket ini.');
        }

        if (!$ticket->attachment_path || !Storage::disk('public')->exists($ticket->attachment_path)) {
            abort(404, 'Berkas lampiran tidak ditemukan pada server.');
        }

        $fullPath = Storage::disk('public')->path($ticket->attachment_path);
        return response()->file($fullPath);
    }

    /**
     * Download ticket attachment directly.
     */
    public function downloadAttachment(Ticket $ticket)
    {
        $user = auth()->user();

        // Check view authorization
        $userDeptId = $user->effectiveDepartmentId();
        if (!is_null($userDeptId) && !$user->isSuperAdmin() && !$user->isOperator() && !$user->isKepalaDivisi() && $ticket->department_id !== $userDeptId) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isUser() && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak berwenang mengunduh lampiran tiket ini.');
        }

        if (!$ticket->attachment_path || !Storage::disk('public')->exists($ticket->attachment_path)) {
            abort(404, 'Berkas lampiran tidak ditemukan pada server.');
        }

        $extension = pathinfo($ticket->attachment_path, PATHINFO_EXTENSION);
        $cleanFileName = 'Lampiran-' . $ticket->ticket_number . ($extension ? '.' . $extension : '');

        return Storage::disk('public')->download($ticket->attachment_path, $cleanFileName);
    }
}
