<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\AsAset;
use App\Models\AsDisposalAset;
use App\Models\User;
use App\Notifications\DisposalStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DisposalAsetController extends Controller
{
    use LogsAudit;

    /**
     * Memeriksa apakah user berhak mengajukan penghapusan aset (Staff / Kabag Aset / Admin).
     */
    private function authorizeMaker(): void
    {
        $user = auth()->user();
        $isAuthorized = $user->isSuperAdmin()
            || $user->isUkAdministrasiAset()
            || $user->isBagianAset()
            || $user->isKabagAset()
            || $user->canAccess('administrasi_aset');

        abort_if(!$isAuthorized, 403, 'Anda tidak memiliki hak akses untuk mengajukan penghapusan aset.');
    }

    /**
     * Memeriksa apakah user berhak menyetujui penghapusan aset (Kadiv / role dengan izin persetujuan_hapus_aset / SuperAdmin).
     */
    private function authorizeKadiv(): void
    {
        $user = auth()->user();
        $isAuthorized = $user && (
            $user->canAccess('persetujuan_hapus_aset')
            || $user->isKepalaDivisi()
            || $user->hasRole(['pimpinan', 'kepala_divisi'])
        );

        abort_if(!$isAuthorized, 403, 'Anda tidak memiliki hak akses untuk persetujuan penghapusan aset.');
    }

    /**
     * Memeriksa akses untuk melihat riwayat aset terhapus.
     */
    private function authorizeViewer(): void
    {
        $user = auth()->user();
        $isAuthorized = $user->isSuperAdmin()
            || $user->isKepalaDivisi()
            || $user->hasRole(['pimpinan', 'kepala_divisi'])
            || $user->isUkAdministrasiAset()
            || $user->isBagianAset()
            || $user->isKabagAset()
            || $user->canAccess('administrasi_aset');

        abort_if(!$isAuthorized, 403, 'Anda tidak memiliki hak akses untuk melihat riwayat aset terhapus.');
    }

    /**
     * Tampilkan form / modal konfirmasi pengajuan penghapusan aset.
     */
    public function ajukanForm(AsAset $aset)
    {
        $this->authorizeMaker();

        if ($aset->status_penghapusan === 'menunggu_persetujuan') {
            return redirect()->route('modul.index', 'aset')
                ->with('error', "Aset '{$aset->nama_aset}' sedang dalam antrian pengajuan penghapusan ke Kepala Divisi.");
        }

        if ($aset->trashed() || $aset->status_penghapusan === 'terhapus') {
            return redirect()->route('modul.index', 'aset')
                ->with('error', "Aset '{$aset->nama_aset}' sudah dihapus sebelumnya.");
        }

        return view('disposal.ajukan', compact('aset'));
    }

    /**
     * Simpan pengajuan penghapusan aset oleh Staff / Kabag.
     */
    public function ajukanSubmit(Request $request, AsAset $aset)
    {
        $this->authorizeMaker();

        if ($aset->status_penghapusan === 'menunggu_persetujuan') {
            return redirect()->route('modul.index', 'aset')
                ->with('error', "Aset '{$aset->nama_aset}' sudah dalam antrian pengajuan penghapusan.");
        }

        $validated = $request->validate([
            'alasan_penghapusan' => 'required|string|min:5|max:2000',
            'metode'             => 'nullable|string|in:Dihibahkan,Dijual,Dimusnahkan,Dihapusbukukan',
            'keterangan'         => 'nullable|string|max:2000',
            'dokumen'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'alasan_penghapusan.required' => 'Alasan penghapusan wajib diisi.',
            'alasan_penghapusan.min'      => 'Alasan penghapusan minimal 5 karakter.',
            'dokumen.max'                 => 'Ukuran dokumen maksimal 5 MB.',
        ]);

        $dokumenPath = null;
        if ($request->hasFile('dokumen')) {
            $dokumenPath = $request->file('dokumen')->store('disposal_dokumen', 'public');
        }

        // Generate No. Disposal unik (DSP-YYYYMMDD-XXXX)
        $todayCode = date('Ymd');
        $countToday = AsDisposalAset::whereDate('created_at', today())->count() + 1;
        $noDisposal = 'DSP-' . $todayCode . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        // Buat record pengajuan disposal
        $disposal = AsDisposalAset::create([
            'no_disposal'         => $noDisposal,
            'aset_id'             => $aset->id,
            'tanggal_pengajuan'   => now()->toDateString(),
            'alasan_penghapusan'  => $validated['alasan_penghapusan'],
            'metode'              => $validated['metode'] ?: 'Dimusnahkan',
            'nilai_buku_terakhir' => $aset->nilai_perolehan ?? 0,
            'status'              => 'Diajukan',
            'approval_status'     => 'Diajukan',
            'maker_id'            => auth()->id(),
            'keterangan'          => $validated['keterangan'] ?? null,
            'dokumen'             => $dokumenPath,
        ]);

        // Tandai aset bahwa sedang menunggu persetujuan Kadiv (aset TIDAK langsung dihapus!)
        $aset->update([
            'status_penghapusan' => 'menunggu_persetujuan',
        ]);

        // Audit log
        $this->audit(
            'CREATE',
            'Administrasi Aset & Inventaris',
            'Penghapusan Aset (Disposal)',
            $disposal->id,
            "Mengajukan penghapusan aset: {$aset->nama_aset} (No. {$noDisposal}) - Alasan: {$validated['alasan_penghapusan']}"
        );

        // Kirim notifikasi ke Kepala Divisi
        $kadivUsers = User::whereHas('role', fn ($q) => $q->whereIn('nama', ['pimpinan', 'kepala_divisi']))
            ->orWhere(fn ($q) => $q->whereNull('department_id')->where('role_id', 2))
            ->get();

        foreach ($kadivUsers as $kadiv) {
            $kadiv->notify(new DisposalStatusNotification(
                $disposal,
                'Pengajuan Penghapusan Aset Baru',
                auth()->user()->nama_lengkap . " mengajukan penghapusan aset: {$aset->nama_aset}.",
                'warning',
                route('disposal-aset.approval')
            ));
        }

        return redirect()->route('modul.index', 'aset')
            ->with('status', "Pengajuan penghapusan aset '{$aset->nama_aset}' berhasil dikirim. Menunggu persetujuan Kepala Divisi.");
    }

    /**
     * Halaman Antrian Approval Penghapusan Aset khusus Kepala Divisi.
     */
    public function approvalQueue(Request $request)
    {
        $this->authorizeKadiv();

        $query = AsDisposalAset::with(['aset' => fn ($q) => $q->withTrashed(), 'maker'])
            ->where('approval_status', 'Diajukan');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($sub) use ($search) {
                $sub->where('no_disposal', 'like', "%{$search}%")
                    ->orWhere('alasan_penghapusan', 'like', "%{$search}%")
                    ->orWhereHas('aset', fn ($a) => $a->withTrashed()->where('nama_aset', 'like', "%{$search}%")->orWhere('kode_aset', 'like', "%{$search}%"))
                    ->orWhereHas('maker', fn ($m) => $m->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        $items = $query->latest('id')->paginate(15)->withQueryString();

        return view('disposal.approval', compact('items'));
    }

    /**
     * Kadiv menyetujui pengajuan penghapusan aset.
     * Hanya saat disetujui, aset ditandai sebagai terhapus (Soft Delete).
     */
    public function approve(Request $request, AsDisposalAset $disposal)
    {
        $this->authorizeKadiv();

        if ($disposal->approval_status !== 'Diajukan') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $disposal->update([
            'status'           => 'Disetujui',
            'approval_status'  => 'Disetujui',
            'checker_id'       => auth()->id(),
            'approved_at'      => now(),
            'catatan_approval' => $request->input('catatan'),
        ]);

        // Ambil aset terkait dan tandai terhapus (soft delete)
        $aset = $disposal->aset()->withTrashed()->first();
        if ($aset) {
            $aset->status_penghapusan = 'terhapus';
            $aset->save();
            $aset->delete(); // Soft delete: sets deleted_at = now()
        }

        // Audit log
        $this->audit(
            'APPROVE',
            'Administrasi Aset & Inventaris',
            'Penghapusan Aset (Disposal)',
            $disposal->id,
            "Kepala Divisi menyetujui penghapusan aset: {$aset?->nama_aset} (No. {$disposal->no_disposal})"
        );

        // Notifikasi ke pemohon (maker)
        if ($disposal->maker) {
            $disposal->maker->notify(new DisposalStatusNotification(
                $disposal,
                'Penghapusan Aset Disetujui',
                "Pengajuan penghapusan aset '{$aset?->nama_aset}' telah DISETUJUI oleh Kepala Divisi.",
                'success',
                route('disposal-aset.riwayat')
            ));
        }

        return back()->with('status', "Pengajuan penghapusan aset '{$aset?->nama_aset}' telah disetujui. Aset telah dipindahkan ke Riwayat Aset Terhapus.");
    }

    /**
     * Kadiv menolak pengajuan penghapusan aset.
     * Aset tetap aktif di inventaris dan alasan penolakan dicatat.
     */
    public function reject(Request $request, AsDisposalAset $disposal)
    {
        $this->authorizeKadiv();

        if ($disposal->approval_status !== 'Diajukan') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|min:5|max:2000',
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'alasan_penolakan.min'      => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $disposal->update([
            'status'           => 'Ditolak',
            'approval_status'  => 'Ditolak',
            'checker_id'       => auth()->id(),
            'approved_at'      => now(),
            'alasan_penolakan' => $validated['alasan_penolakan'],
            'catatan_approval' => $validated['alasan_penolakan'],
        ]);

        // Aset tetap aktif, kembalikan status_penghapusan menjadi 'aktif'
        $aset = $disposal->aset()->withTrashed()->first();
        if ($aset) {
            $aset->status_penghapusan = 'aktif';
            $aset->save();
        }

        // Audit log
        $this->audit(
            'REJECT',
            'Administrasi Aset & Inventaris',
            'Penghapusan Aset (Disposal)',
            $disposal->id,
            "Kepala Divisi menolak penghapusan aset: {$aset?->nama_aset}. Alasan: {$validated['alasan_penolakan']}"
        );

        // Notifikasi ke pemohon (maker)
        if ($disposal->maker) {
            $disposal->maker->notify(new DisposalStatusNotification(
                $disposal,
                'Penghapusan Aset Ditolak',
                "Pengajuan penghapusan aset '{$aset?->nama_aset}' DITOLAK oleh Kepala Divisi. Alasan: {$validated['alasan_penolakan']}",
                'danger',
                route('modul.index', 'aset')
            ));
        }

        return back()->with('status', "Pengajuan penghapusan aset '{$aset?->nama_aset}' ditolak. Aset tetap aktif di inventaris.");
    }

    /**
     * Menu Riwayat Aset Terhapus (Audit Trail aset yang sudah disetujui & terhapus).
     * Staff & role terkait dapat melihat aset yang sudah dihapus beserta meta penghapusan.
     */
    public function riwayatTerhapus(Request $request)
    {
        $this->authorizeViewer();

        $query = AsDisposalAset::with(['aset' => fn ($q) => $q->withTrashed(), 'maker', 'checker'])
            ->where('approval_status', 'Disetujui');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($sub) use ($search) {
                $sub->where('no_disposal', 'like', "%{$search}%")
                    ->orWhere('alasan_penghapusan', 'like', "%{$search}%")
                    ->orWhere('metode', 'like', "%{$search}%")
                    ->orWhereHas('aset', fn ($a) => $a->withTrashed()->where('nama_aset', 'like', "%{$search}%")->orWhere('kode_aset', 'like', "%{$search}%")->orWhere('kategori', 'like', "%{$search}%"))
                    ->orWhereHas('maker', fn ($m) => $m->where('nama_lengkap', 'like', "%{$search}%"))
                    ->orWhereHas('checker', fn ($c) => $c->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('metode')) {
            $query->where('metode', $request->metode);
        }

        $items = $query->latest('approved_at')->paginate(15)->withQueryString();

        return view('disposal.riwayat', compact('items'));
    }

    /**
     * Tampilkan detail dokumen pendukung disposal jika ada.
     */
    public function downloadDokumen(AsDisposalAset $disposal)
    {
        $this->authorizeViewer();

        abort_unless($disposal->dokumen, 404, 'Dokumen pendukung tidak ditemukan.');

        if (!Storage::disk('public')->exists($disposal->dokumen)) {
            abort(404, 'File dokumen tidak ditemukan pada penyimpanan.');
        }

        return Storage::disk('public')->download($disposal->dokumen);
    }
}
