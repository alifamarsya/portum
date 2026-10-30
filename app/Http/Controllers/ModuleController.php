<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\AsCustomField;
use App\Models\AsMasterKodeAset;
use App\Models\AsMasterLokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use App\Services\AmortisasiCalculator;

// Mesin CRUD generik untuk 20 modul transaksional Portum, setara "RES"
// di portum.py -- satu controller melayani semua modul lewat {key} di
// route, konfigurasinya diambil dari config/modules.php. Menjaga arsitektur
// aslinya (config-driven), tapi sekarang di atas Eloquent + Laravel RBAC.
class ModuleController extends Controller
{
    use LogsAudit;
    public function __construct(private AmortisasiCalculator $amortisasiCalculator)
    {
    }
    private function config(string $key): array
    {
        $cfg = config("modules.$key");
        abort_if(!$cfg, 404, "Modul '$key' tidak ditemukan.");

        // Jika modul mendukung dynamic fields (aset, aset_history), muat konfigurasi field langsung dari DB
        if (in_array($key, ['aset', 'aset_history'])) {
            $dbFields = AsCustomField::where('module_key', $key)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($dbFields->isNotEmpty()) {
                $fields = [];
                foreach ($dbFields as $df) {
                    if ($df->field_type === 'file') {
                        continue;
                    }
                    $fieldConf = [
                        'label' => $df->label,
                        'type'  => $df->field_type,
                        'list'  => (bool) $df->show_in_list,
                        'req'   => true,
                        'opts'  => $df->options ?? [],
                        'help'  => $df->help_text,
                    ];
                    if ($df->field_type === 'money') {
                        $fieldConf['fmt'] = 'money';
                    } elseif ($df->field_type === 'date') {
                        $fieldConf['fmt'] = 'date';
                    } elseif ($df->field_type === 'select') {
                        $fieldConf['fmt'] = 'badge';
                    }
                    $fields[$df->field_name] = $fieldConf;
                }
                $cfg['fields'] = $fields;
            }
        }

        // Dropdown dinamis khusus modul aset: opsi kode & lokasi selalu
        // diambil dari master yang aktif (bukan dari opsi statis config/DB),
        // agar validasi in: generik otomatis membatasi ke nilai aktif.
        if ($key === 'aset') {
            if (isset($cfg['fields']['kode_aset'])) {
                $cfg['fields']['kode_aset']['type'] = 'select';
                $cfg['fields']['kode_aset']['opts'] = AsMasterKodeAset::active()
                    ->orderBy('sort_order')->orderBy('kode')->pluck('kode')->all();
            }
            if (isset($cfg['fields']['lokasi'])) {
                $cfg['fields']['lokasi']['type'] = 'select';
                $cfg['fields']['lokasi']['opts'] = AsMasterLokasi::active()
                    ->orderBy('sort_order')->orderBy('nama_lokasi')->pluck('nama_lokasi')->all();
            }
        }

        return $cfg;
    }

    private function authorizeModule(string $key, string $mode = 'read'): array
    {
        $cfg = $this->config($key);
        $user = auth()->user();

        $perm = \App\Models\RolePermission::where('role_id', $user->role_id)
            ->where('perm_key', $cfg['perm'])
            ->first();

        abort_if(!$perm, 403, "Role Anda tidak memiliki akses ke modul {$cfg['modul']}.");
        abort_if($mode === 'write' && !$perm->can_write, 403, "Role Anda hanya bisa melihat modul {$cfg['modul']}.");

        return $cfg;
    }

    public function index(Request $request, string $key)
    {
        $cfg = $this->authorizeModule($key, 'read');
        $model = $cfg['model'];

        $modelInstance = new $model;
        $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing($modelInstance->getTable());

        $items = $model::query()
            ->when($request->q, function ($q) use ($cfg, $request, $tableColumns) {
                $q->where(function ($qq) use ($cfg, $request, $tableColumns) {
                    foreach (array_keys($cfg['fields']) as $field) {
                        if (in_array($field, $tableColumns)) {
                            $qq->orWhere($field, 'like', '%' . $request->q . '%');
                        } else {
                            $qq->orWhere("custom_fields->{$field}", 'like', '%' . $request->q . '%');
                        }
                    }
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('modules.index', compact('cfg', 'items', 'key'));
    }

    public function create(string $key)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $viewData = ['cfg' => $cfg, 'key' => $key, 'item' => null];
        if ($key === 'aset') {
            $viewData['masterKodeAset'] = AsMasterKodeAset::active()
                ->orderBy('sort_order')->orderBy('kode')->get();
        }
        return view('modules.form', $viewData);
    }

    public function store(Request $request, string $key)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $data = $this->validated($request, $cfg);
        $data = $this->hitungAmortisasiJikaPerlu($key, $data);
        $data = $this->resolveAsetMasterRefs($key, $data);
        $this->validateAsetTahun($key, $data);

        // Pisahkan kolom SQL asli dan custom JSON fields untuk modul aset
        if (in_array($key, ['aset', 'aset_history'])) {
            $data = $this->splitModelAndCustomFields($cfg['model'], $data);
        }

        if ($cfg['maker_checker']) {
            $data['maker_id'] = auth()->id();
            $data['approval_status'] = 'Diajukan';
        } else {
            $statusDefaults = [
                'kendaraan'               => ['status' => 'Aktif'],
                'biaya_harian'            => ['status' => 'Dibukukan'],
                'pemeliharaan_gedung'     => ['status' => 'Dijadwalkan'],
                'checklist_kebersihan'    => ['status' => 'Belum Dilakukan'],
                'k3_insiden'              => ['status' => 'Open'],
                'invoice_sewa'            => ['status' => 'Belum Bayar'],
                'pks'                     => ['status' => 'Aktif'],
                'memo_sewa_cabang'        => ['status_persetujuan' => 'Disetujui'],
                'temuan'                  => ['status' => 'Open'],
                'mutasi_aset'             => ['status' => 'Disetujui'],
                'disposal_aset'           => ['status' => 'Disetujui'],
                'rekonsiliasi_aset'       => ['status' => 'Proses'],
                'penerimaan_barang'       => ['status' => 'Diterima'],
                'distribusi_barang'       => ['status' => 'Disiapkan'],
                'pembayaran_tagihan'      => ['status' => 'Belum Bayar'],
                'memo_internal'           => ['status' => 'Masuk'],
                'penawaran'               => ['status' => 'Diterima'],
                'draft_dokumen'           => ['status' => 'Draft'],
                'spk'                     => ['status' => 'Berjalan'],
                'reminder'                => ['status' => 'Aktif'],
                'surat_masuk'             => ['status_arsip' => 'Aktif'],
                'surat_keluar'            => ['status_arsip' => 'Aktif'],
                'memo_masuk'              => ['status_arsip' => 'Aktif'],
                'memo_keluar'             => ['status_arsip' => 'Aktif'],
                'arsip_dokumen'           => ['status_arsip' => 'Aktif'],
                'dokumen_legalitas'       => ['status' => 'Aktif'],
                'perencanaan_kebutuhan'   => ['status' => 'Disetujui'],
                'jadwal_pemeliharaan'     => ['status' => 'Direncanakan'],
                'monitoring_kondisi'      => ['status' => 'Selesai'],
                'tindak_lanjut_perbaikan' => ['status' => 'Dilaporkan'],
            ];

            if (isset($statusDefaults[$key])) {
                foreach ($statusDefaults[$key] as $stCol => $stVal) {
                    if (!isset($data[$stCol])) {
                        $data[$stCol] = $stVal;
                    }
                }
            }

            $modelInstance = new $cfg['model'];
            $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing($modelInstance->getTable());
            if (in_array('approval_status', $tableColumns) && !isset($data['approval_status'])) {
                $data['approval_status'] = 'Disetujui';
            }
            if (in_array('status_persetujuan', $tableColumns) && !isset($data['status_persetujuan'])) {
                $data['status_persetujuan'] = 'Disetujui';
            }
        }
        if (array_key_exists('dibuat_oleh', $cfg['fields'])) {
            $data['dibuat_oleh'] = auth()->user()->nama_lengkap;
        }

        $item = $cfg['model']::create($data);
        $this->audit('CREATE', $cfg['modul'], $cfg['judul'], $item->id, 'Menambahkan data baru');

        return redirect()->route('modul.index', $key)->with('status', "{$cfg['judul']} berhasil ditambahkan.");
    }

    public function edit(string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $item = $cfg['model']::findOrFail($id);

        $viewData = ['cfg' => $cfg, 'key' => $key, 'item' => $item];
        if ($key === 'aset') {
            $viewData['masterKodeAset'] = AsMasterKodeAset::active()
                ->orderBy('sort_order')->orderBy('kode')->get();
        }
        return view('modules.form', $viewData);
    }

    public function update(Request $request, string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $item = $cfg['model']::findOrFail($id);
        $data = $this->validated($request, $cfg);
        $data = $this->hitungAmortisasiJikaPerlu($key, $data);
        $data = $this->resolveAsetMasterRefs($key, $data);
        $this->validateAsetTahun($key, $data);

        // Pisahkan kolom SQL asli dan custom JSON fields untuk modul aset
        if (in_array($key, ['aset', 'aset_history'])) {
            $data = $this->splitModelAndCustomFields($cfg['model'], $data, $item);
        }

        $item->update($data);
        $this->audit('UPDATE', $cfg['modul'], $cfg['judul'], $item->id, 'Mengubah data');

        return redirect()->route('modul.index', $key)->with('status', "{$cfg['judul']} berhasil diperbarui.");
    }

    public function destroy(string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $item = $cfg['model']::findOrFail($id);

        if ($key === 'aset') {
            return redirect()->route('disposal-aset.ajukan', $id)
                ->with('error', 'Penghapusan inventaris aset wajib melalui pengajuan dan persetujuan Kepala Divisi.');
        }

        $item->delete();
        $this->audit('DELETE', $cfg['modul'], $cfg['judul'], $id, 'Menghapus data');

        return redirect()->route('modul.index', $key)->with('status', "{$cfg['judul']} berhasil dihapus.");
    }

    // --- Alur Maker-Checker (CPMK Blockchain) ---

    public function approve(string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        abort_unless($cfg['maker_checker'], 404);
        $item = $cfg['model']::findOrFail($id);

        Gate::authorize('approve', $item);

        $item->update([
            'checker_id' => auth()->id(),
            'approval_status' => 'Disetujui',
            'approved_at' => now(),
        ]);
        $this->audit('APPROVE', $cfg['modul'], $cfg['judul'], $item->id, 'Menyetujui transaksi (checker)');

        return back()->with('status', 'Disetujui.');
    }

    public function reject(Request $request, string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        abort_unless($cfg['maker_checker'], 404);
        $item = $cfg['model']::findOrFail($id);

        Gate::authorize('reject', $item);

        $item->update([
            'checker_id' => auth()->id(),
            'approval_status' => 'Ditolak',
            'approved_at' => now(),
            'catatan_approval' => $request->input('catatan'),
        ]);
        $this->audit('REJECT', $cfg['modul'], $cfg['judul'], $item->id, 'Menolak transaksi (checker): ' . $request->input('catatan'));

        return back()->with('status', 'Ditolak.');
    }

    private function validated(Request $request, array $cfg): array
    {
        $rules = [];
        $attributes = [];
        $techFields = ['maker_id', 'checker_id', 'approval_status', 'approved_at', 'catatan_approval', 'dibuat_oleh'];
        $opPerms = ['umum_rt', 'dokumen_arsip', 'administrasi_aset', 'logistik_pelaporan', 'pengadaan', 'pemeliharaan_pengawasan'];
        $isOperational = in_array($cfg['perm'] ?? '', $opPerms);

        foreach ($cfg['fields'] as $field => $meta) {
            $isStatusField = $isOperational && (
                in_array($field, ['status', 'status_persetujuan', 'status_arsip', 'status_pembayaran'])
                || stripos($field, 'status') !== false
                || stripos($meta['label'] ?? '', 'status') !== false
            );

            if ($isStatusField || in_array($field, $techFields) || ($meta['type'] ?? '') === 'auto_user' || ($meta['type'] ?? '') === 'file') {
                continue;
            }
            $type = $meta['type'] ?? 'text';
            $isRequired = (bool) ($meta['req'] ?? false);
            $rule = $isRequired ? 'required' : 'nullable';
            $rule .= match ($type) {
                'date' => '|date',
                'number', 'money' => '|numeric|min:0',
                'checkbox' => '|boolean',
                'select' => !empty($meta['opts']) ? '|string|in:' . implode(',', $meta['opts']) : '|string|max:2000',
                default => '|string|max:2000',
            };
            $rules[$field] = $rule;
            $attributes[$field] = $meta['label'] ?? $field;
        }

        $messages = [
            'required' => ':attribute wajib diisi.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal bernilai :min.',
            'in' => 'Pilihan :attribute tidak valid.',
            'boolean' => ':attribute harus bernilai valid.',
        ];

        return $request->validate($rules, $messages, $attributes);
    }

    private function hitungAmortisasiJikaPerlu(string $key, array $data): array
    {
        if ($key !== 'amortisasi') {
            return $data;
        }

        if (empty($data['nilai_per_bulan']) && !empty($data['nilai_perolehan']) && !empty($data['umur_bulan'])) {
            $data['nilai_per_bulan'] = $this->amortisasiCalculator->hitungNilaiPerBulan(
                (float) $data['nilai_perolehan'],
                (int) $data['umur_bulan']
            );
        }

        if (!empty($data['tanggal_mulai']) && !empty($data['nilai_per_bulan'])) {
            $bulanBerjalan = $this->amortisasiCalculator->hitungBulanBerjalan(new \DateTime($data['tanggal_mulai']));
            $data['akumulasi'] = $this->amortisasiCalculator->hitungAkumulasi((float) $data['nilai_per_bulan'], $bulanBerjalan);
            $data['nilai_buku'] = $this->amortisasiCalculator->hitungNilaiBuku((float) $data['nilai_perolehan'], $data['akumulasi']);
        }

        return $data;
    }

    /**
     * Resolusi FK master untuk modul aset: kode_aset (string dari dropdown)
     * -> kode_aset_id, lokasi (string nama master) -> lokasi_id. Bila tidak
     * ketemu di master, FK di-null-kan (string tetap tersimpan apa adanya).
     */
    private function resolveAsetMasterRefs(string $key, array $data): array
    {
        if ($key !== 'aset') {
            return $data;
        }

        if (array_key_exists('kode_aset', $data)) {
            $master = $data['kode_aset'] !== null && $data['kode_aset'] !== ''
                ? AsMasterKodeAset::where('kode', $data['kode_aset'])->first()
                : null;
            $data['kode_aset_id'] = $master?->id;
        }

        if (array_key_exists('lokasi', $data)) {
            $lokasi = $data['lokasi'] !== null && $data['lokasi'] !== ''
                ? AsMasterLokasi::where('nama_lokasi', $data['lokasi'])->first()
                : null;
            $data['lokasi_id'] = $lokasi?->id;
        }

        return $data;
    }

    /**
     * Validasi tahun khusus modul aset. Aturan generik hanya numeric|min:0,
     * jadi rentang tahun ditegakkan di sini: bilangan bulat 1900 s.d.
     * tahun berjalan + 1. Nilai kosong dilewati (aturan required generik
     * yang menentukan wajib/tidaknya).
     */
    private function validateAsetTahun(string $key, array $data): void
    {
        if ($key !== 'aset') {
            return;
        }

        $maxTahun = (int) date('Y') + 1;
        $errors = [];
        foreach (['tahun_pembelian' => 'Tahun Pembelian', 'tahun_penggunaan' => 'Tahun Penggunaan'] as $field => $label) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                continue;
            }
            $value = $data[$field];
            if (!is_numeric($value) || (float) $value != (int) $value) {
                $errors[$field] = "{$label} harus berupa tahun (bilangan bulat).";
                continue;
            }
            $tahun = (int) $value;
            if ($tahun < 1900 || $tahun > $maxTahun) {
                $errors[$field] = "{$label} harus antara 1900 dan {$maxTahun}.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Pisahkan data request antara kolom fisik tabel dan kolom JSON 'custom_fields'.
     */
    private function splitModelAndCustomFields(string $modelClass, array $data, $existingItem = null): array
    {
        $modelInstance = new $modelClass;
        $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing($modelInstance->getTable());

        $directData = [];
        $customData = ($existingItem && is_array($existingItem->custom_fields)) ? $existingItem->custom_fields : [];

        foreach ($data as $fieldName => $value) {
            if (in_array($fieldName, $tableColumns)) {
                $directData[$fieldName] = $value;
            } else {
                $customData[$fieldName] = $value;
            }
        }

        $directData['custom_fields'] = $customData;
        return $directData;
    }
}
