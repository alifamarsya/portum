<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\InternalDepartment;
use App\Models\Ticket;
use App\Models\TicketAttachment;
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

        // Overview counts for current user's visible tickets
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'menunggu' => (clone $baseQuery)->where('status', 'Menunggu Verifikasi')->count(),
            'diverifikasi' => (clone $baseQuery)->whereIn('status', ['Diverifikasi', 'Dialokasikan'])->count(),
            'dalam_proses' => (clone $baseQuery)->whereIn('status', ['Didistribusikan', 'Dalam Proses'])->count(),
            'selesai' => (clone $baseQuery)->whereIn('status', ['Selesai', 'Ditutup Pemohon', 'Ditutup Otomatis (Sistem)'])->count(),
            'ditolak' => (clone $baseQuery)->where('status', 'Ditolak')->count(),
        ];

        $query = (clone $baseQuery)->with(['user', 'category', 'department', 'attachments'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('nama_lengkap', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->filled('jenis_pengajuan')) {
            $query->where('jenis_pengajuan', $request->jenis_pengajuan);
        }

        $tickets = $query->paginate(15)->withQueryString();
        $categories = TicketCategory::active()->orderBy('name')->get();
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

        $categories = TicketCategory::active()->with('department')->orderBy('sort_order')->orderBy('name')->get();

        return view('tickets.create', compact('categories'));
    }

    /**
     * Store a newly created ticket in storage with multiple PDF attachments.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isUser()) {
            abort(403, 'Hanya role User (Pemohon Layanan) yang berwenang membuat tiket baru.');
        }

        $validated = $request->validate([
            'title'           => 'nullable|string|max:255',
            'jenis_pengajuan' => 'required|in:Permintaan,Permasalahan',
            'category_id'     => 'required|exists:ticket_categories,id',
            'description'     => 'required|string|min:100',
            'attachments'     => 'nullable|array',
            'attachments.*'   => 'file|mimes:pdf|max:102400', // Hanya PDF, max 100MB per file
        ], [
            'description.required' => 'Deskripsi atau uraian lengkap permohonan wajib diisi.',
            'description.min'      => 'Deskripsi / uraian lengkap minimal 100 karakter agar informasi kendala atau kebutuhan layanan jelas.',
            'attachments.*.mimes'  => 'Semua lampiran wajib berupa file dokumen PDF (.pdf).',
            'attachments.*.max'    => 'Ukuran tiap file lampiran tidak boleh melebihi 100 MB.',
            'category_id.required' => 'Silakan pilih Kategori Tiket yang sesuai.',
        ]);

        // Cek total ukuran attachment jika diunggah
        $files = $request->file('attachments') ?? [];
        if (!is_array($files) && $files) {
            $files = [$files];
        }

        $ticket = $this->ticketService->createTicket(
            $validated,
            auth()->user(),
            $files
        );

        $this->audit(
            'CREATE',
            'Ticketing',
            'Ticket',
            $ticket->id,
            "Membuat tiket baru {$ticket->ticket_number} (Kategori: {$ticket->category?->name})"
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Tiket {$ticket->ticket_number} berhasil dibuat dan sedang menunggu verifikasi Operator Helpdesk.");
    }

    /**
     * Display the specified ticket with details, attachments, and history timeline.
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
            'attachments',
            'histories' => function ($q) {
                $q->with('user')->latest();
            }
        ]);

        $departments = InternalDepartment::all();

        // Staf / rekan kerja di bagian ini
        $departmentStaff = collect();
        if ($ticket->department_id) {
            $departmentStaff = User::where('department_id', $ticket->department_id)
                ->where('is_active', true)
                ->orderBy('nama_lengkap')
                ->get();
        }

        // Audit: Pemohon membuka detail tiket miliknya
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
     * Aksi Role Bagian: Menerima Tiket (Accept).
     * Mengubah status menjadi 'Dalam Proses' dan memulai timer SLA Resolusi.
     */
    public function accept(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        $isAuthorized = ($user->isBagian() && $user->effectiveDepartmentId() == $ticket->department_id)
            || $user->isSuperAdmin();

        if (!$isAuthorized) {
            abort(403, 'Hanya anggota Bagian penanggung jawab yang berwenang menerima tiket ini.');
        }

        if ($ticket->status !== 'Dialokasikan' && $ticket->status !== 'Diverifikasi' && $ticket->status !== 'Didistribusikan') {
            return back()->with('error', 'Tiket ini tidak dalam status menunggu penerimaan.');
        }

        $notes = $request->input('notes');
        $this->ticketService->acceptTicket($ticket, $user, $notes);

        $this->audit(
            'ACCEPT',
            'Ticketing',
            'Ticket',
            $ticket->id,
            "Bagian {$user->role?->label} ({$user->nama_lengkap}) menerima tiket {$ticket->ticket_number} untuk langsung diproses."
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Tiket {$ticket->ticket_number} berhasil diterima. Status kini 'Dalam Proses' dan timer SLA resolusi telah berjalan.");
    }

    /**
     * Aksi Role Bagian: Menambahkan update progres berkala.
     */
    public function addProgress(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        $isAuthorized = ($user->isBagian() && $user->effectiveDepartmentId() == $ticket->department_id)
            || $user->isOperator()
            || $user->isSuperAdmin();

        if (!$isAuthorized) {
            abort(403, 'Anda tidak berwenang menambahkan catatan progres pada tiket ini.');
        }

        $validated = $request->validate([
            'notes' => 'required|string|min:3|max:1000',
        ], [
            'notes.required' => 'Uraian progres pengerjaan wajib diisi.',
            'notes.min'      => 'Uraian progres minimal 3 karakter.',
        ]);

        $this->ticketService->addProgress($ticket, $user, $validated['notes']);

        $this->audit(
            'UPDATE_PROGRESS',
            'Ticketing',
            'Ticket',
            $ticket->id,
            "Menambahkan catatan progres pengerjaan pada tiket {$ticket->ticket_number}: {$validated['notes']}"
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Catatan progres pengerjaan berhasil ditambahkan ke riwayat tiket.");
    }

    /**
     * Aksi Role Bagian: Konfirmasi Selesai Pengerjaan Tiket.
     * Mengubah status menjadi 'Selesai', mencatat SLA resolusi, dan menyalakan batas waktu 2 hari konfirmasi pemohon.
     */
    public function complete(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        $isAuthorized = ($user->isBagian() && $user->effectiveDepartmentId() == $ticket->department_id)
            || $user->isSuperAdmin();

        if (!$isAuthorized) {
            abort(403, 'Hanya anggota Bagian penanggung jawab yang berwenang mengonfirmasi penyelesaian tiket ini.');
        }

        $notes = $request->input('notes');
        $this->ticketService->completeTicket($ticket, $user, $notes);

        $this->audit(
            'COMPLETE',
            'Ticketing',
            'Ticket',
            $ticket->id,
            "Bagian {$user->nama_lengkap} mengonfirmasi pekerjaan tiket {$ticket->ticket_number} telah selesai."
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Tiket {$ticket->ticket_number} telah ditandai Selesai. Sistem kini menunggu konfirmasi dari pemohon layanan.");
    }

    /**
     * Penolakan tiket oleh Bagian atau Operator.
     */
    public function reject(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        $isAuthorized = ($user->isBagian() && $user->effectiveDepartmentId() == $ticket->department_id)
            || $user->isOperator()
            || $user->isSuperAdmin();

        if (!$isAuthorized) {
            abort(403, 'Anda tidak berwenang menolak tiket ini.');
        }

        $validated = $request->validate([
            'notes' => 'required|string|min:5|max:1000',
        ], [
            'notes.required' => 'Alasan penolakan tiket wajib diisi.',
            'notes.min'      => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $this->ticketService->rejectTicket($ticket, $user, $validated['notes']);

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
     * Show the form for editing/updating ticket status or assignment (Operator).
     */
    public function edit(Ticket $ticket)
    {
        $user = auth()->user();
        $isOperator = $user->isOperator() || $user->isSuperAdmin();
        $isInternal = !is_null($user->effectiveDepartmentId());

        if (!$isOperator && !$isInternal) {
            abort(403, 'Hanya Operator dan Bagian Internal yang berwenang mengubah tiket.');
        }

        if ($isInternal && !$isOperator && $ticket->department_id !== $user->effectiveDepartmentId()) {
            abort(403, 'Tiket ini tidak ditugaskan ke bagian Anda.');
        }

        $categories = TicketCategory::active()->orderBy('name')->get();
        $departments = InternalDepartment::all();

        $ticket->load(['user', 'category', 'department', 'attachments', 'histories' => fn($q) => $q->with('user')->latest()]);

        return view('tickets.edit', compact('ticket', 'categories', 'departments'));
    }

    /**
     * Update the specified ticket in storage (Operator Alokasi / Verifikasi).
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = auth()->user();
        $isOperator = $user->isOperator() || $user->isSuperAdmin();
        $isInternal = !is_null($user->effectiveDepartmentId());

        if (!$isOperator && !$isInternal) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah tiket.');
        }

        if ($isInternal && !$isOperator && $ticket->department_id !== $user->effectiveDepartmentId()) {
            abort(403, 'Tiket ini tidak ditugaskan ke bagian Anda.');
        }

        $rules = [
            'status' => 'required|string|in:Menunggu Verifikasi,Diverifikasi,Didistribusikan,Dialokasikan,Dalam Proses,Selesai,Ditolak,Ditutup Pemohon',
            'notes'  => 'nullable|string|max:1000',
        ];

        // Operator verifikasi dan langsung alokasikan ke Bagian: status menjadi 'Diverifikasi'
        if ($isOperator) {
            $request->merge(['status' => 'Diverifikasi']);
            $rules['department_id']    = 'required|exists:internal_departments,id';
            $rules['category_id']      = 'nullable|exists:ticket_categories,id';
            $rules['jenis_pengajuan']  = 'nullable|in:Permintaan,Permasalahan';
            $rules['priority']         = 'nullable|in:Rendah,Sedang,Tinggi,Kritis';
        }

        $validated = $request->validate($rules);

        // Sinkronisasi SLA dan auto-derive priority jika kategori diubah
        if (!empty($validated['category_id'])) {
            $cat = TicketCategory::find($validated['category_id']);
            if ($cat) {
                $validated['sla_resolution_hours'] = $cat->sla_resolution_hours;

                // Auto-derive priority dari SLA jam jika tidak dipilih secara eksplisit
                if (empty($validated['priority'])) {
                    $slaHours = $cat->sla_resolution_hours;
                    $validated['priority'] = match (true) {
                        $slaHours <= 8  => 'Kritis',
                        $slaHours <= 12 => 'Tinggi',
                        $slaHours <= 48 => 'Sedang',
                        default         => 'Rendah',
                    };
                }
            }
        }

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
     * Disposisi tiket (kompatibilitas).
     */
    public function dispose(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        $isAuthorized = ($user->isBagian() && $user->effectiveDepartmentId() == $ticket->department_id) || $user->isSuperAdmin();
        if (!$isAuthorized) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'disposition_notes' => 'required|string|min:5|max:1000',
        ]);

        $staff = User::findOrFail($validated['assigned_to']);
        $this->ticketService->disposeTicket($ticket, $staff, $validated['disposition_notes'], $user);

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Tiket {$ticket->ticket_number} berhasil didisposisikan kepada {$staff->nama_lengkap}.");
    }

    /**
     * Konfirmasi penutupan tiket oleh pemohon saat status 'Selesai'.
     */
    public function confirmClose(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        if ($ticket->user_id !== $user->id) {
            abort(403, 'Anda bukan pemilik tiket ini.');
        }

        if ($ticket->status !== 'Selesai') {
            return back()->with('error', 'Tiket hanya dapat dikonfirmasi jika sudah berstatus Selesai dari bagian yang menangani.');
        }

        $validated = $request->validate([
            'rating'   => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $this->ticketService->updateTicket($ticket, [
            'status'   => 'Ditutup Pemohon',
            'notes'    => 'Pemohon mengonfirmasi bahwa kendala telah terselesaikan dengan baik dan menutup tiket ini.',
            'closed_at'=> now(),
        ], $user);

        // Simpan rating dan feedback jika diisi
        $ticket->update(array_filter([
            'rating'   => $validated['rating'] ?? null,
            'feedback' => $validated['feedback'] ?? null,
        ], fn ($v) => !is_null($v)));

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
     * Pemohon melaporkan kendala belum tuntas pada tiket berstatus Selesai/Tertutup.
     */
    public function reportIncomplete(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        if ($ticket->user_id !== $user->id) {
            abort(403, 'Anda bukan pemilik tiket ini.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        \App\Models\TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'old_status'=> $ticket->status,
            'new_status'=> $ticket->status,
            'notes'     => "Pemohon melaporkan bahwa pekerjaan belum selesai: \"{$validated['reason']}\". Pemohon diarahkan untuk membuat tiket baru sesuai SOP.",
        ]);

        $this->audit(
            'REPORT_INCOMPLETE',
            'Ticketing',
            'Ticket',
            $ticket->id,
            "Pemohon menyatakan pekerjaan belum selesai pada tiket {$ticket->ticket_number}: {$validated['reason']}"
        );

        $newTicketDescription = "[Tindak Lanjut dari Tiket {$ticket->ticket_number}]\n\nKendala yang masih belum terselesaikan:\n{$validated['reason']}\n\nUraian tiket sebelumnya:\n{$ticket->description}";

        return redirect()->route('tickets.create', [
            'jenis_pengajuan' => $ticket->jenis_pengajuan ?? 'Permasalahan',
            'category_id'     => $ticket->category_id,
            'description'     => $newTicketDescription,
        ])->with('status', "Tiket {$ticket->ticket_number} tercatat membutuhkan tindak lanjut. Silakan lengkapi dan kirim formulir tiket pengajuan baru di bawah ini.");
    }

    /**
     * View specific attachment file.
     */
    public function viewAttachmentFile(TicketAttachment $attachment)
    {
        $ticket = $attachment->ticket;
        $user = auth()->user();

        $userDeptId = $user->effectiveDepartmentId();
        if (!is_null($userDeptId) && !$user->isSuperAdmin() && !$user->isOperator() && !$user->isKepalaDivisi() && $ticket->department_id !== $userDeptId) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isUser() && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak berwenang mengakses lampiran ini.');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'Berkas lampiran tidak ditemukan pada server.');
        }

        return response()->file(Storage::disk('public')->path($attachment->file_path));
    }

    /**
     * Download specific attachment file.
     */
    public function downloadAttachmentFile(TicketAttachment $attachment)
    {
        $ticket = $attachment->ticket;
        $user = auth()->user();

        $userDeptId = $user->effectiveDepartmentId();
        if (!is_null($userDeptId) && !$user->isSuperAdmin() && !$user->isOperator() && !$user->isKepalaDivisi() && $ticket->department_id !== $userDeptId) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isUser() && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak berwenang mengunduh lampiran ini.');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'Berkas lampiran tidak ditemukan pada server.');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * View legacy ticket attachment in browser.
     */
    public function viewAttachment(Ticket $ticket)
    {
        $firstAttachment = $ticket->attachments()->first();
        if ($firstAttachment) {
            return $this->viewAttachmentFile($firstAttachment);
        }

        if (!$ticket->attachment_path || !Storage::disk('public')->exists($ticket->attachment_path)) {
            abort(404, 'Berkas lampiran tidak ditemukan pada server.');
        }

        return response()->file(Storage::disk('public')->path($ticket->attachment_path));
    }

    /**
     * Download legacy ticket attachment directly.
     */
    public function downloadAttachment(Ticket $ticket)
    {
        $firstAttachment = $ticket->attachments()->first();
        if ($firstAttachment) {
            return $this->downloadAttachmentFile($firstAttachment);
        }

        if (!$ticket->attachment_path || !Storage::disk('public')->exists($ticket->attachment_path)) {
            abort(404, 'Berkas lampiran tidak ditemukan pada server.');
        }

        $extension = pathinfo($ticket->attachment_path, PATHINFO_EXTENSION);
        $cleanFileName = 'Lampiran-' . $ticket->ticket_number . ($extension ? '.' . $extension : '');

        return Storage::disk('public')->download($ticket->attachment_path, $cleanFileName);
    }
}
