<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\AsAset;
use App\Models\AsAsetHistory;
use App\Models\AsCustomField;
use App\Models\AsMasterLokasi;
use App\Models\AsMasterPersonel;
use App\Models\AsMutasiAset;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\MutasiStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
        $dynamicFields = AsCustomField::forModule('mutasi')->get();
        $masterLokasi = AsMasterLokasi::with(['personels' => function ($q) {
            $q->where('is_active', true)->orderBy('nama_personel');
        }])
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('nama_lokasi')
        ->get();

        $masterLokasiJson = $masterLokasi->map(fn($l) => [
            'id' => $l->id,
            'nama' => $l->nama_lokasi,
            'personels' => $l->personels->map(fn($p) => [
                'id' => $p->id,
                'nama' => $p->nama_personel,
            ])->values(),
        ])->values();

        return view('mutasi.create', compact('asets', 'dynamicFields', 'masterLokasi', 'masterLokasiJson'));
    }

    /**
     * Simpan pengajuan mutasi aset baru ke database.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Cek apakah input menggunakan dropdown master lokasi & personel
        $hasMasterInput = $request->filled('lokasi_asal_id') || $request->filled('pemohon_personel_id') || $request->filled('lokasi_tujuan_id');

        $pemohonPersonel = null;
        $lokasiAsal = null;
        $lokasiTujuan = null;
        $penanggungJawab = null;
        $isPemohonPindah = false;

        if ($hasMasterInput) {
            $masterValidation = $request->validate([
                'lokasi_asal_id'      => 'required|exists:as_master_lokasi,id',
                'pemohon_personel_id' => 'required|exists:as_master_personel,id',
                'lokasi_tujuan_id'    => 'required|exists:as_master_lokasi,id',
                'penanggung_jawab_id' => 'required',
            ], [
                'lokasi_asal_id.required'      => 'Pilih Lokasi Asal pemohon.',
                'pemohon_personel_id.required' => 'Pilih Nama Pemohon.',
                'lokasi_tujuan_id.required'    => 'Pilih Lokasi Tujuan mutasi aset.',
                'penanggung_jawab_id.required' => 'Pilih Penanggung Jawab Baru di lokasi tujuan.',
            ]);

            $lokasiAsal = AsMasterLokasi::findOrFail($masterValidation['lokasi_asal_id']);
            $lokasiTujuan = AsMasterLokasi::findOrFail($masterValidation['lokasi_tujuan_id']);

            // Validasi: Pemohon harus terdaftar di lokasi asal
            $pemohonPersonel = AsMasterPersonel::where('id', $masterValidation['pemohon_personel_id'])
                ->where('lokasi_id', $lokasiAsal->id)
                ->first();

            if (!$pemohonPersonel) {
                throw ValidationException::withMessages([
                    'pemohon_personel_id' => 'Pemohon yang dipilih tidak terdaftar di ' . $lokasiAsal->nama_lokasi . '.',
                ]);
            }

            // Cek apakah memilih opsi khusus: Pemohon (Pindah ke Lokasi Tujuan)
            $isSpecialPemohonPindah = in_array($masterValidation['penanggung_jawab_id'], [
                '__pemohon_pindah__',
                'pemohon_pindah',
                'pemohon_pindah_lokasi',
            ]);

            if ($isSpecialPemohonPindah) {
                $isPemohonPindah = true;
                $penanggungJawab = null;
                $pjName = $pemohonPersonel->nama_personel;

                // CATATAN: Master personel TIDAK dipindah saat submit/store.
                // Pemindahan personel (Lokasi Asal -> Lokasi Tujuan) HANYA dieksekusi saat mutasi disetujui (approveKabag).
            } else {
                // Penanggung jawab biasa: Harus terdaftar di lokasi tujuan
                $penanggungJawab = AsMasterPersonel::where('id', $masterValidation['penanggung_jawab_id'])
                    ->where('lokasi_id', $lokasiTujuan->id)
                    ->first();

                if (!$penanggungJawab) {
                    throw ValidationException::withMessages([
                        'penanggung_jawab_id' => 'Penanggung jawab yang dipilih tidak terdaftar di ' . $lokasiTujuan->nama_lokasi . '.',
                    ]);
                }
                $pjName = $penanggungJawab->nama_personel;
            }

            // Sinkronisasi data form agar lolos validasi field dan sinkron dengan kolom legacy
            $request->merge([
                'nama_pemohon'        => $pemohonPersonel->nama_personel,
                'jabatan_pemohon'     => $request->input('jabatan_pemohon') ?: ($user->jabatan ?? 'Karyawan / Anggota Divisi'),
                'username_pemohon'    => $request->input('username_pemohon') ?: $user->username,
                'ke_lokasi'           => $lokasiTujuan->nama_lokasi,
                'ke_penanggung_jawab' => $pjName,
            ]);
        } else {
            // Fallback legacy (misal pemanggilan via test atau tanpa dropdown master)
            $request->merge([
                'nama_pemohon'     => $request->input('nama_pemohon') ?: $user->nama_lengkap,
                'jabatan_pemohon'  => $request->input('jabatan_pemohon') ?: ($user->jabatan ?? '-'),
                'username_pemohon' => $request->input('username_pemohon') ?: $user->username,
            ]);
        }

        // Ambil dynamic fields aktif untuk konteks Form Mutasi Aset
        $dynamicFields = AsCustomField::forModule('mutasi')->get();

        // Aturan validasi dasar pemohon & pemilihan aset
        $rules = [
            'nama_pemohon'     => 'required|string|max:255',
            'jabatan_pemohon'  => 'required|string|max:255',
            'username_pemohon' => 'required|string|max:100',
            'aset_id'          => 'required|exists:as_aset,id',
        ];
        $messages = [
            'nama_pemohon.required'     => 'Nama pemohon (individu) wajib diisi.',
            'jabatan_pemohon.required'  => 'Jabatan pemohon wajib diisi.',
            'username_pemohon.required' => 'Username sistem pemohon wajib diisi.',
            'aset_id.required'          => 'Pilih aset yang ingin dimutasikan.',
            'aset_id.exists'            => 'Aset yang dipilih tidak ditemukan.',
        ];

        // Validasi dinamis mengikuti definisi field aktif
        foreach ($dynamicFields as $field) {
            $fn = $field->field_name;

            // Jika form menggunakan dropdown master, ke_lokasi dan ke_penanggung_jawab sudah divalidasi oleh masterValidation
            if ($hasMasterInput && in_array($fn, ['ke_lokasi', 'ke_penanggung_jawab'])) {
                continue;
            }

            // Untuk pemanggilan legacy, validasi ke_lokasi dan ke_penanggung_jawab sebagai text string
            if (!$hasMasterInput && in_array($fn, ['ke_lokasi', 'ke_penanggung_jawab'])) {
                $rules[$fn] = ($field->is_required ? 'required' : 'nullable') . '|string|max:2000';
                continue;
            }

            $rule = $field->is_required ? 'required' : 'nullable';

            $rule .= match ($field->field_type) {
                'file' => '|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
                'date' => '|date',
                'number', 'money' => '|numeric',
                'checkbox' => '|boolean',
                'select' => !empty($field->options) ? '|string|in:' . implode(',', $field->options) : '|string|max:2000',
                'textarea' => '|string|max:5000',
                default => '|string|max:2000',
            };

            $rules[$fn] = $rule;
            $messages["{$fn}.required"] = "{$field->label} wajib diisi.";
            $messages["{$fn}.in"]       = "Pilihan {$field->label} tidak valid.";
            $messages["{$fn}.mimes"]    = "Format berkas {$field->label} harus berupa PDF, JPG, PNG, atau Word.";
            $messages["{$fn}.max"]      = "Ukuran berkas {$field->label} maksimal 10MB.";
        }

        $validated = $request->validate($rules, $messages);

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

        // 2. Pisahkan kolom fisik tabel as_mutasi_aset dan custom_fields dinamis
        $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('as_mutasi_aset');
        $physicalData = [];
        $customData = [];

        foreach ($dynamicFields as $field) {
            $fn = $field->field_name;

            if ($field->field_type === 'file') {
                if ($request->hasFile($fn)) {
                    $file = $request->file($fn);
                    $fileName = 'MUT_' . $fn . '_' . date('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('mutasi_dokumen', $fileName, 'public');

                    if (in_array($fn, $tableColumns)) {
                        $physicalData[$fn] = $filePath;
                    } else {
                        $customData[$fn] = $filePath;
                    }
                }
            } else {
                $val = $validated[$fn] ?? null;
                if (in_array($fn, $tableColumns)) {
                    $physicalData[$fn] = $val;
                } else {
                    $customData[$fn] = $val;
                }
            }
        }

        // Tentukan dari_lokasi dan ke_lokasi final
        $finalDariLokasi = $lokasiAsal ? $lokasiAsal->nama_lokasi : ($aset->lokasi ?? 'Belum ditentukan');
        $finalKeLokasi = $lokasiTujuan
            ? $lokasiTujuan->nama_lokasi
            : ($request->input('ke_lokasi') ?: ($physicalData['ke_lokasi'] ?? ($customData['ke_lokasi'] ?? 'Belum ditentukan')));
        $finalKePj = $hasMasterInput
            ? ($isPemohonPindah ? $pemohonPersonel->nama_personel : ($penanggungJawab ? $penanggungJawab->nama_personel : 'Belum ditentukan'))
            : ($request->input('ke_penanggung_jawab') ?: ($physicalData['ke_penanggung_jawab'] ?? ($customData['ke_penanggung_jawab'] ?? 'Belum ditentukan')));

        // 3. Simpan data mutasi
        $mutasi = AsMutasiAset::create([
            'no_mutasi'             => $noMutasi,
            'aset_id'               => $aset->id,
            'pengaju_id'            => $user->id,
            'maker_id'              => $user->id,
            'lokasi_asal_id'        => $lokasiAsal?->id,
            'pemohon_personel_id'   => $pemohonPersonel?->id,
            'lokasi_tujuan_id'      => $lokasiTujuan?->id,
            'penanggung_jawab_id'   => $penanggungJawab?->id,
            'is_pemohon_pindah'     => $isPemohonPindah,
            'nama_pemohon'          => $validated['nama_pemohon'],
            'jabatan_pemohon'       => $validated['jabatan_pemohon'],
            'username_pemohon'      => $validated['username_pemohon'],
            'dari_lokasi'           => $finalDariLokasi,
            'ke_lokasi'             => $finalKeLokasi,
            'dari_penanggung_jawab' => $aset->penanggung_jawab ?? 'Belum ditentukan',
            'ke_penanggung_jawab'   => $finalKePj,
            'alasan'                => $physicalData['alasan'] ?? ($customData['alasan'] ?? '-'),
            'status'                => 'Diajukan',
            'dokumen'               => $physicalData['dokumen'] ?? ($customData['dokumen'] ?? null),
            'custom_fields'         => !empty($customData) ? $customData : null,
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
     * Aksi Operator: Mengecek kelengkapan data form dan meneruskan ke Bagian Aset.
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

        $this->audit('UPDATE', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Operator {$user->nama_lengkap} telah mengecek kelengkapan mutasi {$mutasi->no_mutasi} dan meneruskannya ke Bagian Aset");

        // Kirim notifikasi ke Bagian Aset
        $bagianAsetList = User::whereHas('role', fn ($q) => $q->whereIn('nama', ['bagian_aset', 'kabag_aset', 'uk_administrasi_aset', 'uk_logistik', 'aset']))->get();
        foreach ($bagianAsetList as $petugas) {
            $petugas->notify(new MutasiStatusNotification(
                $mutasi,
                'Pengajuan Mutasi Aset Siap Diverifikasi',
                "Mutasi {$mutasi->no_mutasi} ({$mutasi->aset?->nama_aset}) telah lolos pengecekan awal Operator dan siap diverifikasi oleh Bagian Aset.",
                'info'
            ));
        }

        return back()->with('status', "Formulir pengajuan mutasi {$mutasi->no_mutasi} berhasil diproses dan diteruskan ke Bagian Aset untuk verifikasi data.");
    }

    /**
     * Aksi Bagian Aset: Verifikasi dan persetujuan pengajuan mutasi aset (Setujui / Tolak / Tidak Valid).
     * Menggabungkan alur verifikasi staf dan persetujuan kabag menjadi satu langkah tunggal.
     */
    public function verifikasiBagianAset(Request $request, AsMutasiAset $mutasi)
    {
        $user = auth()->user();

        if (!$user->isBagianAset() && !$user->isSuperAdmin()) {
            abort(403, 'Aksi verifikasi dan persetujuan pengajuan mutasi aset khusus untuk Bagian Aset.');
        }

        if (!in_array($mutasi->status, ['Diproses', 'Menunggu Approval', 'Diajukan'])) {
            return back()->withErrors(['status' => 'Pengajuan mutasi ini tidak dalam status yang dapat diproses oleh Bagian Aset.']);
        }

        $validated = $request->validate([
            'keputusan'          => 'required|in:setujui,valid,tolak,tidak_valid',
            'alasan_penolakan'   => 'required_if:keputusan,tolak|nullable|string|max:1000',
            'catatan_verifikasi' => 'required_if:keputusan,tidak_valid|nullable|string|max:1000',
            'catatan_approval'   => 'nullable|string|max:1000',
            'catatan'            => 'nullable|string|max:1000',
        ], [
            'keputusan.required'             => 'Pilih keputusan verifikasi Bagian Aset (Setujui, Tolak, atau Tidak Valid).',
            'alasan_penolakan.required_if'   => 'Alasan penolakan mutasi wajib diisi apabila pengajuan ditolak.',
            'catatan_verifikasi.required_if' => 'Catatan verifikasi wajib diisi apabila data dinyatakan tidak valid.',
        ]);

        $keputusan = $validated['keputusan'];

        // Jika opsi 'tidak_valid' dipilih
        if ($keputusan === 'tidak_valid') {
            $catatan = $validated['catatan_verifikasi'] ?? ($validated['catatan'] ?? 'Data aset dinyatakan tidak valid oleh Bagian Aset.');

            $mutasi->update([
                'verifikator_id'     => $user->id,
                'verified_at'        => now(),
                'approver_id'        => $user->id,
                'approved_at'        => now(),
                'catatan_verifikasi' => $catatan,
                'status'             => 'Ditutup',
                'status_hasil'       => 'Tidak Valid',
                'approval_status'    => 'Tidak Valid',
            ]);

            $this->audit('REJECT', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Bagian Aset {$user->nama_lengkap} menyatakan data mutasi {$mutasi->no_mutasi} TIDAK VALID. Catatan: {$catatan}");

            // Notifikasi ke pemohon
            $mutasi->pengaju?->notify(new MutasiStatusNotification(
                $mutasi,
                'Pengajuan Mutasi Aset Tidak Valid & Ditutup',
                "Pengajuan mutasi aset {$mutasi->no_mutasi} untuk {$mutasi->aset?->nama_aset} dinyatakan TIDAK VALID oleh Bagian Aset dan statusnya telah ditutup. Catatan: {$catatan}",
                'danger'
            ));

            return back()->with('status', "Pengajuan mutasi {$mutasi->no_mutasi} telah dinyatakan Tidak Valid dan ditutup.");
        }

        // Jika opsi 'tolak' dipilih
        if ($keputusan === 'tolak') {
            $alasan = $validated['alasan_penolakan'] ?? ($validated['catatan'] ?? 'Pengajuan mutasi ditolak oleh Bagian Aset.');
            $catatanAppr = $validated['catatan_approval'] ?? null;

            $mutasi->update([
                'verifikator_id'     => $user->id,
                'verified_at'        => now(),
                'approver_id'        => $user->id,
                'approved_at'        => now(),
                'alasan_penolakan'   => $alasan,
                'catatan_approval'   => $catatanAppr,
                'catatan_verifikasi' => $alasan,
                'status'             => 'Ditutup',
                'status_hasil'       => 'Ditolak',
                'approval_status'    => 'Ditolak',
            ]);

            $this->audit('REJECT', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Bagian Aset {$user->nama_lengkap} MENOLAK mutasi {$mutasi->no_mutasi}. Alasan: {$alasan}");

            // Notifikasi ke pemohon
            $mutasi->pengaju?->notify(new MutasiStatusNotification(
                $mutasi,
                'Pengajuan Mutasi Aset Ditolak oleh Bagian Aset',
                "Pengajuan mutasi aset {$mutasi->no_mutasi} untuk {$mutasi->aset?->nama_aset} DITOLAK oleh Bagian Aset. Alasan: {$alasan}",
                'danger'
            ));

            return back()->with('status', "Pengajuan mutasi {$mutasi->no_mutasi} berhasil ditolak dan status ditutup.");
        }

        // Jika opsi 'setujui' (atau 'valid') dipilih:
        $catatan = $validated['catatan_approval']
            ?? ($validated['catatan_verifikasi']
            ?? ($validated['catatan'] ?? 'Pengajuan mutasi aset disetujui oleh Bagian Aset.'));

        $mutasi->update([
            'verifikator_id'     => $user->id,
            'verified_at'        => now(),
            'catatan_verifikasi' => $catatan,
            'approver_id'        => $user->id,
            'approved_at'        => now(),
            'catatan_approval'   => $catatan,
            'status'             => 'Disetujui',
            'status_hasil'       => 'Disetujui',
            'approval_status'    => 'Disetujui',
        ]);

        // EKSEKUSI RELOKASI MASTER PERSONEL:
        // Pindahkan personel di master lokasi/personel HANYA setelah mutasi disetujui
        if ($mutasi->is_pemohon_pindah && $mutasi->pemohon_personel_id && $mutasi->lokasi_tujuan_id) {
            $pemohonPersonel = AsMasterPersonel::find($mutasi->pemohon_personel_id);
            if ($pemohonPersonel) {
                $pemohonPersonel->update([
                    'lokasi_id' => $mutasi->lokasi_tujuan_id,
                ]);
            }
        }

        // Audit Log
        $this->audit('APPROVE', 'Mutasi Aset', 'AsMutasiAset', $mutasi->id, "Bagian Aset {$user->nama_lengkap} MENYETUJUI mutasi {$mutasi->no_mutasi}. Menunggu konfirmasi penutupan dari Pengaju.");

        // Notifikasi ke pemohon untuk melakukan konfirmasi penutupan
        $mutasi->pengaju?->notify(new MutasiStatusNotification(
            $mutasi,
            'Pengajuan Mutasi Aset Disetujui Bagian Aset — Menunggu Konfirmasi Anda',
            "Pengajuan mutasi aset {$mutasi->no_mutasi} untuk {$mutasi->aset?->nama_aset} telah DISETUJUI oleh Bagian Aset. Silakan periksa penerimaan fisik aset di lokasi baru dan klik tombol Konfirmasi Ditutup untuk menyelesaikan proses mutasi.",
            'warning'
        ));

        return back()->with('status', "Pengajuan mutasi {$mutasi->no_mutasi} berhasil Disetujui oleh Bagian Aset! Status mutasi saat ini 'Disetujui' dan menunggu konfirmasi penutupan oleh Pengaju.");
    }

    /**
     * Alias backward-compatible untuk verifikasi staf lama.
     */
    public function verifyStaf(Request $request, AsMutasiAset $mutasi)
    {
        return $this->verifikasiBagianAset($request, $mutasi);
    }

    /**
     * Alias backward-compatible untuk approval kabag lama.
     */
    public function approveKabag(Request $request, AsMutasiAset $mutasi)
    {
        return $this->verifikasiBagianAset($request, $mutasi);
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

            // Jika ada opsi pemohon pindah lokasi, pastikan master personel sudah di lokasi tujuan
            if ($mutasi->is_pemohon_pindah && $mutasi->pemohon_personel_id && $mutasi->lokasi_tujuan_id) {
                $pemohonPersonel = AsMasterPersonel::find($mutasi->pemohon_personel_id);
                if ($pemohonPersonel && $pemohonPersonel->lokasi_id != $mutasi->lokasi_tujuan_id) {
                    $pemohonPersonel->update([
                        'lokasi_id' => $mutasi->lokasi_tujuan_id,
                    ]);
                }
            }

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

        // 5. Notifikasi ke Bagian Aset
        $bagianAsetUsers = User::whereHas('role', fn ($q) => $q->whereIn('nama', ['bagian_aset', 'kabag_aset', 'uk_administrasi_aset', 'uk_logistik', 'aset']))->get();
        foreach ($bagianAsetUsers as $penerima) {
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
