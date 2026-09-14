<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\AsAset;
use App\Models\AsAsetHistory;
use App\Models\AsMutasiAset;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\MutasiStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MutasiAsetController extends Controller
{
    use LogsAudit;

    /**
     * Tampilkan daftar pengajuan mutasi aset dengan filter, pencarian, dan statistik.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = AsMutasiAset::with(['aset', 'pengaju', 'operator', 'verifikator', 'approver'])
            ->latest();

        // Scoping data berdasarkan peran
        if ($user->isUser()) {
            $query->where('pengaju_id', $user->id);
        }

        // Filter status
        if ($request->filled('status')) {
            if ($request->status === 'Ditutup') {
                $query->where('status', 'Ditutup')->where('status_hasil', 'Disetujui');
            } elseif ($request->status === 'Disetujui') {
                $query->where('status', 'Disetujui');
            } elseif ($request->status === 'Ditolak') {
                $query->where('status', 'Ditutup')->where('status_hasil', 'Ditolak');
            } elseif ($request->status === 'Tidak Valid') {
                $query->where('status', 'Ditutup')->where('status_hasil', 'Tidak Valid');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('no_mutasi', 'like', "%{$search}%")
                    ->orWhere('nama_pemohon', 'like', "%{$search}%")
                    ->orWhere('jabatan_pemohon', 'like', "%{$search}%")
                    ->orWhere('username_pemohon', 'like', "%{$search}%")
                    ->orWhere('alasan', 'like', "%{$search}%")
                    ->orWhere('ke_lokasi', 'like', "%{$search}%")
                    ->orWhere('ke_penanggung_jawab', 'like', "%{$search}%")
                    ->orWhereHas('aset', function ($aq) use ($search) {
                        $aq->where('nama_aset', 'like', "%{$search}%")
                            ->orWhere('kode_aset', 'like', "%{$search}%");
                    })
                    ->orWhereHas('pengaju', function ($uq) use ($search) {
                        $uq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        // Filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('created_at', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_selesai);
        }

        $mutasiList = $query->paginate(15)->withQueryString();

        // Hitung statistik (disesuaikan jika role user biasa)
        $baseStatsQuery = AsMutasiAset::query();
        if ($user->isUser()) {
            $baseStatsQuery->where('pengaju_id', $user->id);
        }

        $stats = [
            'total'             => (clone $baseStatsQuery)->count(),
            'diajukan'          => (clone $baseStatsQuery)->where('status', 'Diajukan')->count(),
            'diproses'          => (clone $baseStatsQuery)->where('status', 'Diproses')->count(),
            'menunggu_approval' => (clone $baseStatsQuery)->where('status', 'Menunggu Approval')->count(),
            'disetujui'         => (clone $baseStatsQuery)->where('status', 'Disetujui')->count(),
            'ditutup'           => (clone $baseStatsQuery)->where('status', 'Ditutup')->where('status_hasil', 'Disetujui')->count(),
            'ditolak'           => (clone $baseStatsQuery)->where('status', 'Ditutup')->whereIn('status_hasil', ['Ditolak', 'Tidak Valid'])->count(),
        ];

        return view('mutasi.index', compact('mutasiList', 'stats', 'user'));
    }

    /**
     * Tampilkan form permohonan mutasi aset baru.
     * Hanya role 'user' yang diizinkan membuat pengajuan.
     */
    public function create()
    {
        $user = auth()->user();

        if (!$user->isUser() && !$user->isSuperAdmin()) {
            abort(403, 'Hanya User / Pemohon yang berhak mengajukan permintaan Mutasi Aset. Silakan hubungi staf yang memiliki akun pemohon.');
        }

        $asets = AsAset::orderBy('nama_aset')->get();

        return view('mutasi.create', compact('asets'));
    }

    /**
     * Simpan pengajuan mutasi aset baru ke database.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Hanya role 'user' / pemohon yang boleh membuat pengajuan mutasi aset
        if (!$user->isUser() && !$user->isSuperAdmin()) {
            abort(403, 'Hanya User / Pemohon yang berhak mengajukan Mutasi Aset.');
        }

        $validated = $request->validate([
            'nama_pemohon'        => 'required|string|max:255',
            'jabatan_pemohon'     => 'required|string|max:255',
            'username_pemohon'    => 'required|string|max:100',
            'aset_id'             => 'required|exists:as_aset,id',
            'ke_lokasi'           => 'required|string|max:255',
            'ke_penanggung_jawab' => 'required|string|max:255',
            'alasan'              => 'required|string|max:2000',
            'dokumen'             => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ], [
            'nama_pemohon.required'        => 'Nama pemohon (individu) wajib diisi.',
            'jabatan_pemohon.required'     => 'Jabatan pemohon wajib diisi.',
            'username_pemohon.required'    => 'Username sistem pemohon wajib diisi.',
            'aset_id.required'             => 'Pilih aset yang ingin dimutasikan.',
            'aset_id.exists'               => 'Aset yang dipilih tidak ditemukan.',
            'ke_lokasi.required'           => 'Lokasi tujuan pemindahan wajib diisi.',
            'ke_penanggung_jawab.required' => 'Penanggung jawab baru wajib diisi.',
            'alasan.required'              => 'Alasan pemindahan aset wajib diisi.',
            'dokumen.max'                  => 'Ukuran file dokumen maksimal 10MB.',
            'dokumen.mimes'                => 'Format dokumen harus berupa PDF, JPG, PNG, atau Word.',
        ]);

        $aset = AsAset::findOrFail($validated['aset_id']);


        // 1. Generate nomor mutasi otomatis MUT-YYYYMMDD-XXXX
        $datePrefix = 'MUT-' . date('Ymd') . '-';
        $lastMutasi = AsMutasiAset::where('no_mutasi', 'like', "{$datePrefix}%")
            ->orderByDesc('id')
            ->first();

        $nextSeq = 1;
        if ($lastMutasi && preg_match('/MUT-\d{8}-(\d{4})/', $lastMutasi->no_mutasi, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        }
        $noMutasi = $datePrefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);

        // 2. Upload file dokumen pendukung jika ada
        $dokumenPath = null;
        if ($request->hasFile('dokumen')) {
            $file = $request->file('dokumen');
            $fileName = 'MUT_' . date('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dokumenPath = $file->storeAs('mutasi_dokumen', $fileName, 'public');
        }

        // 3. Simpan data mutasi
        $mutasi = AsMutasiAset::create([
            'no_mutasi'             => $noMutasi,
            'aset_id'               => $aset->id,
            'pengaju_id'            => $user->id,
            'maker_id'              => $user->id,
            'nama_pemohon'          => $validated['nama_pemohon'],
            'jabatan_pemohon'       => $validated['jabatan_pemohon'],
            'username_pemohon'      => $validated['username_pemohon'],
            'dari_lokasi'           => $aset->lokasi ?? 'Belum ditentukan',
            'ke_lokasi'             => $validated['ke_lokasi'],
            'dari_penanggung_jawab' => $aset->penanggung_jawab ?? 'Belum ditentukan',
            'ke_penanggung_jawab'   => $validated['ke_penanggung_jawab'],
            'alasan'                => $validated['alasan'],
            'status'                => 'Diajukan',
            'dokumen'               => $dokumenPath,
        ]);

        // 4. Catat Audit Log
        $this->audit('CREATE', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Pengajuan mutasi aset {$aset->nama_aset} ({$noMutasi}) dari {$mutasi->dari_lokasi} ke {$mutasi->ke_lokasi}");

        // 5. Kirim notifikasi konfirmasi ke Pengaju
        $user->notify(new MutasiStatusNotification(
            $mutasi,
            'Pengajuan Mutasi Aset Berhasil Terkirim',
            "Pengajuan mutasi aset untuk {$aset->nama_aset} dengan nomor {$mutasi->no_mutasi} telah berhasil diajukan dan sedang menunggu pengecekan kelengkapan data oleh Operator.",
            'success'
        ));

        // 6. Kirim notifikasi ke seluruh Operator
        $operators = User::whereHas('role', fn ($q) => $q->where('nama', 'operator'))->get();
        foreach ($operators as $op) {
            $op->notify(new MutasiStatusNotification(
                $mutasi,
                'Pengajuan Mutasi Aset Baru Menunggu Pengecekan',
                "Pengajuan mutasi baru {$mutasi->no_mutasi} ({$aset->nama_aset}) diajukan oleh {$user->nama_lengkap}. Silakan lakukan pengecekan kelengkapan data form.",
                'info'
            ));
        }

        return redirect()->route('mutasi-aset.show', $mutasi)
            ->with('status', "Pengajuan mutasi aset {$noMutasi} berhasil dibuat dan saat ini berstatus Diajukan.");
    }

    /**
     * Tampilkan detail pengajuan mutasi aset beserta progress timeline dan panel aksi peran.
     */
    public function show(AsMutasiAset $mutasi)
    {
        $user = auth()->user();

        // Scoping akses user biasa: hanya boleh melihat pengajuan miliknya
        if ($user->isUser() && $mutasi->pengaju_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat rincian pengajuan mutasi aset ini.');
        }

        $mutasi->load(['aset', 'pengaju.unitKerja', 'operator', 'verifikator', 'approver']);

        // Riwayat audit terkait aset ini
        $riwayatAset = AsAsetHistory::where('aset_id', $mutasi->aset_id)
            ->with('user')
            ->latest('changed_at')
            ->take(10)
            ->get();

        return view('mutasi.show', compact('mutasi', 'user', 'riwayatAset'));
    }

    /**
     * Lihat / Preview Dokumen Lampiran.
     */
    public function viewDokumen(AsMutasiAset $mutasi)
    {
        if (!$mutasi->dokumen || !Storage::disk('public')->exists($mutasi->dokumen)) {
            abort(404, 'Dokumen lampiran tidak ditemukan.');
        }

        return response()->file(Storage::disk('public')->path($mutasi->dokumen));
    }

    /**
     * Unduh Dokumen Lampiran.
     */
    public function downloadDokumen(AsMutasiAset $mutasi)
    {
        if (!$mutasi->dokumen || !Storage::disk('public')->exists($mutasi->dokumen)) {
            abort(404, 'Dokumen lampiran tidak ditemukan.');
        }

        $ext = pathinfo($mutasi->dokumen, PATHINFO_EXTENSION);
        $downloadName = 'Dokumen_' . $mutasi->no_mutasi . '.' . $ext;

        return response()->download(Storage::disk('public')->path($mutasi->dokumen), $downloadName);
    }

    /**
     * Aksi Operator: Mengecek kelengkapan data form dan meneruskan ke Staf Aset.
     */
    public function checkOperator(Request $request, AsMutasiAset $mutasi)
    {
        $user = auth()->user();

        if (!$user->isOperator() && !$user->isSuperAdmin()) {
            abort(403, 'Aksi pengecekan formulir khusus untuk Operator Helpdesk.');
        }

        if ($mutasi->status !== 'Diajukan') {
            return back()->withErrors(['status' => 'Pengajuan mutasi ini tidak dalam status Diajukan.']);
        }

        $validated = $request->validate([
            'catatan_operator' => 'nullable|string|max:1000',
        ]);

        $mutasi->update([
            'operator_id'         => $user->id,
            'operator_checked_at' => now(),
            'catatan_operator'    => $validated['catatan_operator'] ?? 'Data formulir pengajuan mutasi telah dicek dan dinyatakan lengkap.',
            'status'              => 'Diproses',
        ]);

        $this->audit('UPDATE', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Operator {$user->nama_lengkap} telah mengecek kelengkapan mutasi {$mutasi->no_mutasi} dan meneruskannya ke Staf Aset");

        // Kirim notifikasi ke Staf Administrasi Aset
        $stafAsetList = User::whereHas('role', fn ($q) => $q->whereIn('nama', ['uk_administrasi_aset', 'aset']))->get();
        foreach ($stafAsetList as $staf) {
            $staf->notify(new MutasiStatusNotification(
                $mutasi,
                'Pengajuan Mutasi Aset Siap Diverifikasi',
                "Mutasi {$mutasi->no_mutasi} ({$mutasi->aset?->nama_aset}) telah lolos pengecekan awal Operator dan siap diverifikasi keabsahan data asetnya.",
                'info'
            ));
        }

        return back()->with('status', "Formulir pengajuan mutasi {$mutasi->no_mutasi} berhasil diproses dan diteruskan ke Staf Administrasi Aset untuk verifikasi data.");
    }

    /**
     * Aksi Staf Administrasi Aset: Memverifikasi keabsahan data aset (Valid / Tidak Valid).
     */
    public function verifyStaf(Request $request, AsMutasiAset $mutasi)
    {
        $user = auth()->user();

        if (!$user->isUkAdministrasiAset() && !$user->hasRole('aset') && !$user->isSuperAdmin()) {
            abort(403, 'Aksi verifikasi data aset khusus untuk Staf Unit Kerja Administrasi Aset.');
        }

        if (!in_array($mutasi->status, ['Diajukan', 'Diproses'])) {
            return back()->withErrors(['status' => 'Pengajuan mutasi ini tidak dalam status yang dapat diverifikasi oleh Staf Aset.']);
        }

        $validated = $request->validate([
            'keputusan'          => 'required|in:valid,tidak_valid',
            'catatan_verifikasi' => 'required|string|max:1000',
        ], [
            'keputusan.required'          => 'Tentukan keputusan verifikasi data aset (Valid atau Tidak Valid).',
            'catatan_verifikasi.required' => 'Catatan verifikasi data aset wajib diisi.',
        ]);

        if ($validated['keputusan'] === 'tidak_valid') {
            // Keputusan Data Tidak Valid: Langsung Ditutup dan kirim notifikasi ke pemohon
            $mutasi->update([
                'verifikator_id'     => $user->id,
                'verified_at'        => now(),
                'catatan_verifikasi' => $validated['catatan_verifikasi'],
                'status'             => 'Ditutup',
                'status_hasil'       => 'Tidak Valid',
            ]);

            $this->audit('REJECT', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Staf Aset {$user->nama_lengkap} menyatakan data mutasi {$mutasi->no_mutasi} TIDAK VALID. Alasan: {$validated['catatan_verifikasi']}");

            // Notifikasi ke pemohon
            $mutasi->pengaju?->notify(new MutasiStatusNotification(
                $mutasi,
                'Pengajuan Mutasi Aset Tidak Valid & Ditutup',
                "Pengajuan mutasi aset {$mutasi->no_mutasi} untuk {$mutasi->aset?->nama_aset} dinyatakan TIDAK VALID oleh Staf Administrasi Aset dan statusnya telah ditutup. Catatan verifikasi: {$validated['catatan_verifikasi']}",
                'danger'
            ));

            return back()->with('status', "Pengajuan mutasi {$mutasi->no_mutasi} telah dinyatakan Tidak Valid dan ditutup.");
        }

        // Keputusan Data Valid: Lanjut ke Kabag Aset (Menunggu Approval)
        $mutasi->update([
            'verifikator_id'     => $user->id,
            'verified_at'        => now(),
            'catatan_verifikasi' => $validated['catatan_verifikasi'],
            'status'             => 'Menunggu Approval',
        ]);

        $this->audit('APPROVE', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Staf Aset {$user->nama_lengkap} memverifikasi VALID data mutasi {$mutasi->no_mutasi} dan meneruskan ke Kabag Aset");

        // Notifikasi ke Kabag Aset
        $kabagAsetList = User::whereHas('role', fn ($q) => $q->where('nama', 'kabag_aset'))->get();
        foreach ($kabagAsetList as $kabag) {
            $kabag->notify(new MutasiStatusNotification(
                $mutasi,
                'Pengajuan Mutasi Aset Menunggu Persetujuan Anda',
                "Mutasi aset {$mutasi->no_mutasi} ({$mutasi->aset?->nama_aset}) telah diverifikasi valid oleh Staf Aset dan menunggu keputusan persetujuan dari Anda.",
                'warning'
            ));
        }

        return back()->with('status', "Data aset mutasi {$mutasi->no_mutasi} berhasil diverifikasi Valid dan diteruskan ke Kepala Bagian Aset untuk persetujuan akhir.");
    }

    /**
     * Aksi Kepala Bagian Aset: Menyetujui (Approve) atau Menolak (Reject) pengajuan mutasi.
     */
    public function approveKabag(Request $request, AsMutasiAset $mutasi)
    {
        $user = auth()->user();

        if (!$user->isKabagAset() && !$user->hasRole('kabag_aset') && !$user->isSuperAdmin()) {
            abort(403, 'Aksi persetujuan pengajuan mutasi aset khusus untuk Kepala Bagian Aset/Inventaris & Logistik.');
        }

        if ($mutasi->status !== 'Menunggu Approval') {
            return back()->withErrors(['status' => 'Pengajuan mutasi ini tidak dalam status Menunggu Approval.']);
        }

        $validated = $request->validate([
            'keputusan'        => 'required|in:setujui,tolak',
            'alasan_penolakan' => 'required_if:keputusan,tolak|nullable|string|max:1000',
            'catatan_approval' => 'nullable|string|max:1000',
        ], [
            'keputusan.required'        => 'Pilih keputusan persetujuan (Setujui atau Tolak).',
            'alasan_penolakan.required_if' => 'Alasan penolakan mutasi wajib diisi apabila pengajuan ditolak.',
        ]);

        if ($validated['keputusan'] === 'tolak') {
            // Ditolak oleh Kabag Aset
            $mutasi->update([
                'approver_id'      => $user->id,
                'approved_at'      => now(),
                'catatan_approval' => $validated['catatan_approval'],
                'alasan_penolakan' => $validated['alasan_penolakan'],
                'status'           => 'Ditutup',
                'status_hasil'     => 'Ditolak',
                'approval_status'  => 'Ditolak',
            ]);

            $this->audit('REJECT', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Kabag Aset {$user->nama_lengkap} MENOLAK mutasi {$mutasi->no_mutasi}. Alasan: {$validated['alasan_penolakan']}");

            // Notifikasi ke pemohon
            $mutasi->pengaju?->notify(new MutasiStatusNotification(
                $mutasi,
                'Pengajuan Mutasi Aset Ditolak oleh Kabag Aset',
                "Pengajuan mutasi aset {$mutasi->no_mutasi} untuk {$mutasi->aset?->nama_aset} DITOLAK oleh Kepala Bagian Aset. Alasan: {$validated['alasan_penolakan']}",
                'danger'
            ));

            return back()->with('status', "Pengajuan mutasi {$mutasi->no_mutasi} berhasil ditolak dan status ditutup.");
        }

        // Disetujui oleh Kabag Aset:
        // Status menjadi 'Disetujui' (belum 'Ditutup'). Menunggu konfirmasi penutupan oleh Pengaju.
        $mutasi->update([
            'approver_id'      => $user->id,
            'approved_at'      => now(),
            'catatan_approval' => $validated['catatan_approval'] ?? 'Pengajuan mutasi aset disetujui.',
            'status'           => 'Disetujui',
            'status_hasil'     => 'Disetujui',
            'approval_status'  => 'Disetujui',
        ]);

        // Audit Log Kabag
        $this->audit('APPROVE', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Kabag Aset {$user->nama_lengkap} MENYETUJUI mutasi {$mutasi->no_mutasi}. Menunggu konfirmasi penutupan dari Pengaju.");

        // Notifikasi ke pemohon untuk melakukan konfirmasi penutupan
        $mutasi->pengaju?->notify(new MutasiStatusNotification(
            $mutasi,
            'Pengajuan Mutasi Aset Disetujui Kabag — Menunggu Konfirmasi Anda',
            "Pengajuan mutasi aset {$mutasi->no_mutasi} untuk {$mutasi->aset?->nama_aset} telah DISETUJUI oleh Kepala Bagian Aset. Silakan periksa penerimaan fisik aset di lokasi baru dan klik tombol Konfirmasi Ditutup untuk menyelesaikan proses mutasi.",
            'warning'
        ));

        return back()->with('status', "Pengajuan mutasi {$mutasi->no_mutasi} berhasil Disetujui! Status mutasi saat ini 'Disetujui' dan menunggu konfirmasi penutupan (Ditutup) oleh Pengaju.");
    }

    /**
     * Aksi Pengaju (User Pembuat Pengajuan): Konfirmasi akhir & penutupan mutasi aset (Status Ditutup).
     * Saat pengaju menutup pengajuan:
     * 1. Data lokasi & penanggung jawab di master as_aset diperbarui
     * 2. Riwayat mutasi dicatat di as_aset_histories
     * 3. Status mutasi resmi berubah menjadi 'Ditutup'
     */
    public function konfirmasiPengaju(Request $request, AsMutasiAset $mutasi)
    {
        $user = auth()->user();

        // Hanya pembuat pengajuan atau superadmin yang berhak menutup tiket mutasi ini
        if ($mutasi->pengaju_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403, 'Aksi penutupan mutasi aset ini hanya dapat dilakukan oleh Pengaju yang membuat permohonan.');
        }

        if ($mutasi->status !== 'Disetujui') {
            return back()->withErrors(['status' => 'Pengajuan mutasi belum dalam status Disetujui atau sudah ditutup sebelumnya.']);
        }

        $validated = $request->validate([
            'catatan_konfirmasi' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($mutasi, $validated, $user) {
            $aset = $mutasi->aset;
            $oldLokasi = $aset->lokasi;
            $oldPj = $aset->penanggung_jawab;

            // 1. Update master aset (lokasi & penanggung jawab baru)
            $aset->update([
                'lokasi'           => $mutasi->ke_lokasi,
                'penanggung_jawab' => $mutasi->ke_penanggung_jawab,
            ]);

            // 2. Catat riwayat audit eksplisit ke as_aset_histories
            AsAsetHistory::create([
                'aset_id'       => $aset->id,
                'user_id'       => $user->id,
                'field_changed' => 'Mutasi Aset Selesai',
                'old_value'     => "Lokasi: {$oldLokasi} | PJ: {$oldPj}",
                'new_value'     => "Lokasi: {$mutasi->ke_lokasi} | PJ: {$mutasi->ke_penanggung_jawab}",
                'keterangan'    => "Mutasi No. {$mutasi->no_mutasi} dikonfirmasi & ditutup oleh pengaju ({$user->nama_lengkap}). Catatan: " . ($validated['catatan_konfirmasi'] ?? 'Fisik aset telah diterima di lokasi tujuan.'),
                'changed_at'    => now(),
            ]);

            // 3. Update pengajuan mutasi menjadi Ditutup
            $mutasi->update([
                'status'             => 'Ditutup',
                'status_hasil'       => 'Disetujui',
                'catatan_konfirmasi' => $validated['catatan_konfirmasi'] ?? 'Fisik aset telah diterima dan proses mutasi dikonfirmasi selesai.',
                'confirmed_at'       => now(),
            ]);

            // 4. Audit Log
            $this->audit('APPROVE', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Pengaju {$user->nama_lengkap} telah mengonfirmasi dan MENUTUP mutasi {$mutasi->no_mutasi}. Lokasi aset {$aset->nama_aset} resmi diperbarui ke {$mutasi->ke_lokasi}");
        });

        // 5. Notifikasi ke Staf Aset dan Kabag Aset
        $stafAndKabag = User::whereHas('role', fn ($q) => $q->whereIn('nama', ['uk_administrasi_aset', 'aset', 'kabag_aset']))->get();
        foreach ($stafAndKabag as $penerima) {
            $penerima->notify(new MutasiStatusNotification(
                $mutasi,
                'Pengajuan Mutasi Aset Telah Ditutup oleh Pengaju',
                "Pengajuan mutasi aset {$mutasi->no_mutasi} ({$mutasi->aset?->nama_aset}) telah dikonfirmasi dan statusnya resmi DITUTUP oleh pemohon ({$user->nama_lengkap}). Data lokasi aset kini aktif di {$mutasi->ke_lokasi}.",
                'success'
            ));
        }

        return back()->with('status', "Pengajuan mutasi {$mutasi->no_mutasi} telah berhasil Dikonfirmasi & Ditutup! Data lokasi dan penanggung jawab aset telah resmi diperbarui di database inventaris.");
    }
}
