<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use App\Models\AsAset;
use App\Models\AsCustomField;
use App\Models\AsMasterKodeAset;
use App\Models\AsMasterLokasi;
use App\Models\AsMasterPersonel;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    use LogsAudit;

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if (
            $user->canAccess('config_field_aset') ||
            $user->isUkAdministrasiAset() ||
            $user->hasRole(['aset', 'uk_administrasi_aset', 'kabag_aset']) ||
            $user->canAccess('administrasi_aset')
        ) {
            return;
        }

        abort(403, 'Akses ini khusus untuk Staf Unit Kerja Administrasi Aset atau Administrator.');
    }

    /**
     * Daftar semua custom field berdasarkan module.
     */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $moduleKey = $request->get('module', 'aset');
        $modules = [
            'aset'            => 'Inventarisasi Aset',
            'aset_history'    => 'Riwayat Pergerakan Aset',
            'mutasi'          => 'Form Mutasi Aset',
            'lokasi_personel' => 'Master Lokasi & Personel',
            'kode_aset'       => 'Master Kode Aset',
        ];

        $fields = collect();
        $masterLokasi = collect();
        $masterKodeAset = collect();

        if ($moduleKey === 'lokasi_personel') {
            $masterLokasi = AsMasterLokasi::with(['personels' => fn($q) => $q->orderBy('nama_personel')])
                ->orderBy('sort_order')
                ->orderBy('nama_lokasi')
                ->get();
        } elseif ($moduleKey === 'kode_aset') {
            $masterKodeAset = AsMasterKodeAset::orderBy('sort_order')
                ->orderBy('kode')
                ->get();
        } else {
            $fields = AsCustomField::where('module_key', $moduleKey)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        return view('admin.custom-fields.index', compact('fields', 'modules', 'moduleKey', 'masterLokasi', 'masterKodeAset'));
    }

    /**
     * Simpan custom field baru.
     */
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'module_key'  => 'required|in:aset,aset_history,mutasi',
            'field_name'  => [
                'required', 'string', 'max:64',
                'regex:/^[a-z][a-z0-9_]*$/',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = AsCustomField::where('module_key', $request->module_key)
                        ->where('field_name', $value)
                        ->exists();
                    if ($exists) {
                        $fail("Field dengan nama '{$value}' sudah ada di modul ini.");
                    }
                },
            ],
            'label'       => 'required|string|max:100',
            'field_type'  => 'required|in:text,number,money,date,select,textarea,checkbox,file',
            'options'     => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'show_in_list'=> 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
            'help_text'   => 'nullable|string|max:255',
        ], [
            'field_name.regex' => 'Nama field hanya boleh huruf kecil, angka, dan underscore, serta dimulai dengan huruf.',
        ]);

        // Parse opsi select
        $options = null;
        if ($validated['field_type'] === 'select' && !empty($validated['options'])) {
            $options = array_values(array_filter(array_map('trim', explode("\n", $validated['options']))));
        }

        $moduleKey = $validated['module_key'];
        $requestedOrder = isset($validated['sort_order']) ? max(1, (int) $validated['sort_order']) : null;

        if ($requestedOrder !== null) {
            // Geser field yang sudah ada dengan sort_order >= requestedOrder
            AsCustomField::where('module_key', $moduleKey)
                ->where('sort_order', '>=', $requestedOrder)
                ->increment('sort_order');
            $finalOrder = $requestedOrder;
        } else {
            $finalOrder = (AsCustomField::where('module_key', $moduleKey)->max('sort_order') ?? 0) + 1;
        }

        AsCustomField::create([
            'module_key'   => $moduleKey,
            'field_name'   => $validated['field_name'],
            'label'        => $validated['label'],
            'field_type'   => $validated['field_type'],
            'options'      => $options,
            'is_required'  => $request->boolean('is_required'),
            'show_in_list' => $request->boolean('show_in_list', true),
            'sort_order'   => $finalOrder,
            'help_text'    => $validated['help_text'] ?? null,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        $this->resequenceModuleFields($moduleKey);

        $moduleLabel = $modules[$validated['module_key']] ?? $validated['module_key'];
        return redirect()->route('admin.custom-fields.index', ['module' => $validated['module_key']])
            ->with('status', "Custom field '{$validated['label']}' berhasil ditambahkan ke modul {$moduleLabel}.");
    }

    /**
     * Update custom field.
     */
    public function update(Request $request, AsCustomField $customField)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'label'       => 'required|string|max:100',
            'field_type'  => 'required|in:text,number,money,date,select,textarea,checkbox,file',
            'options'     => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'show_in_list'=> 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
            'help_text'   => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        $options = null;
        if ($validated['field_type'] === 'select') {
            if (!empty($validated['options'])) {
                $options = array_values(array_filter(array_map('trim', explode("\n", $validated['options']))));
            } else {
                $options = [];
            }
        }

        $moduleKey = $customField->module_key;
        $oldOrder  = (int) $customField->sort_order;
        $requestedOrder = isset($validated['sort_order']) ? (int) $validated['sort_order'] : $oldOrder;
        $newOrder = max(1, $requestedOrder);

        // Logika sisipkan & geser urutan (tidak menimpa nomor urut yang sama)
        if ($newOrder !== $oldOrder) {
            if ($newOrder < $oldOrder) {
                // Pindah ke posisi lebih awal (contoh 5 -> 2):
                // Field di rentang [newOrder, oldOrder - 1] digeser turun (+1)
                AsCustomField::where('module_key', $moduleKey)
                    ->where('id', '!=', $customField->id)
                    ->where('sort_order', '>=', $newOrder)
                    ->where('sort_order', '<', $oldOrder)
                    ->increment('sort_order');
            } else {
                // Pindah ke posisi lebih akhir (contoh 2 -> 5):
                // Field di rentang [oldOrder + 1, newOrder] digeser naik (-1)
                AsCustomField::where('module_key', $moduleKey)
                    ->where('id', '!=', $customField->id)
                    ->where('sort_order', '>', $oldOrder)
                    ->where('sort_order', '<=', $newOrder)
                    ->decrement('sort_order');
            }
        }

        $customField->update([
            'label'        => $validated['label'],
            'field_type'   => $validated['field_type'],
            'options'      => $options,
            'is_required'  => $request->boolean('is_required'),
            'show_in_list' => $request->boolean('show_in_list'),
            'sort_order'   => $newOrder,
            'help_text'    => $validated['help_text'] ?? null,
            'is_active'    => $request->boolean('is_active'),
        ]);

        $this->resequenceModuleFields($moduleKey);

        return redirect()->route('admin.custom-fields.index', ['module' => $customField->module_key])
            ->with('status', "Custom field '{$customField->label}' berhasil diperbarui.");
    }

    /**
     * Update urutan (sort order) beberapa field sekaligus.
     */
    public function reorder(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'orders'   => 'required|array',
            'orders.*' => 'integer|min:0',
            'module'   => 'nullable|string|in:aset,aset_history,mutasi',
        ]);

        $moduleKey = $validated['module'] ?? 'aset';
        $orders = $validated['orders'];
        asort($orders);

        $seq = 1;
        foreach (array_keys($orders) as $id) {
            AsCustomField::where('id', $id)
                ->where('module_key', $moduleKey)
                ->update(['sort_order' => $seq++]);
        }

        $this->resequenceModuleFields($moduleKey);

        return redirect()->route('admin.custom-fields.index', ['module' => $moduleKey])
            ->with('status', 'Urutan field berhasil diperbarui.');
    }

    /**
     * Hapus custom field.
     */
    public function destroy(AsCustomField $customField)
    {
        $this->authorizeAccess();

        $moduleKey = $customField->module_key;
        $label = $customField->label;
        $customField->delete();

        $this->resequenceModuleFields($moduleKey);

        return redirect()->route('admin.custom-fields.index', ['module' => $moduleKey])
            ->with('status', "Custom field '{$label}' berhasil dihapus.");
    }

    /**
     * Susun ulang seluruh field dalam modul agar memiliki urutan berturut-turut 1..N
     */
    private function resequenceModuleFields(string $moduleKey): void
    {
        $fields = AsCustomField::where('module_key', $moduleKey)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($fields as $index => $field) {
            $expectedOrder = $index + 1;
            if ($field->sort_order !== $expectedOrder) {
                $field->update(['sort_order' => $expectedOrder]);
            }
        }
    }

    /**
     * Toggle aktif/nonaktif field.
     */
    public function toggle(AsCustomField $customField)
    {
        $this->authorizeAccess();

        $customField->update(['is_active' => !$customField->is_active]);

        $status = $customField->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('status', "Field '{$customField->label}' berhasil {$status}.");
    }

    // ========================================================
    // CRUD MASTER LOKASI (DIVISI / CABANG)
    // ========================================================

    public function storeLokasi(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:150|not_regex:/,/|unique:as_master_lokasi,nama_lokasi',
            'tipe'        => 'nullable|string|in:Divisi,Cabang,Unit Kerja',
            'kode_lokasi' => 'nullable|string|max:50',
        ], [
            'nama_lokasi.required'   => 'Nama lokasi / divisi wajib diisi.',
            'nama_lokasi.not_regex'  => 'Nama lokasi / divisi tidak boleh mengandung koma, karena nama dipakai sebagai pilihan dropdown.',
            'nama_lokasi.unique'     => 'Lokasi / divisi dengan nama ini sudah terdaftar.',
        ]);

        $maxOrder = AsMasterLokasi::max('sort_order') ?? 0;

        $lokasi = AsMasterLokasi::create([
            'nama_lokasi' => trim($validated['nama_lokasi']),
            'tipe'        => $validated['tipe'] ?: 'Divisi',
            'kode_lokasi' => $validated['kode_lokasi'] ? trim($validated['kode_lokasi']) : null,
            'is_active'   => true,
            'sort_order'  => $maxOrder + 1,
        ]);

        $this->audit('CREATE', 'Master Lokasi & Personel', 'AsMasterLokasi', $lokasi->id, "Menambahkan lokasi/divisi baru: {$lokasi->nama_lokasi}");

        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'lokasi_personel'])
            ->with('status', "Lokasi/Divisi '{$lokasi->nama_lokasi}' berhasil ditambahkan.");
    }

    public function updateLokasi(Request $request, AsMasterLokasi $lokasi)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:150|not_regex:/,/|unique:as_master_lokasi,nama_lokasi,' . $lokasi->id,
            'tipe'        => 'nullable|string|in:Divisi,Cabang,Unit Kerja',
            'kode_lokasi' => 'nullable|string|max:50',
        ], [
            'nama_lokasi.required'  => 'Nama lokasi / divisi wajib diisi.',
            'nama_lokasi.not_regex' => 'Nama lokasi / divisi tidak boleh mengandung koma, karena nama dipakai sebagai pilihan dropdown.',
            'nama_lokasi.unique'    => 'Lokasi / divisi dengan nama ini sudah digunakan.',
        ]);

        $lokasi->update([
            'nama_lokasi' => trim($validated['nama_lokasi']),
            'tipe'        => $validated['tipe'] ?: 'Divisi',
            'kode_lokasi' => $validated['kode_lokasi'] ? trim($validated['kode_lokasi']) : null,
        ]);

        $this->audit('UPDATE', 'Master Lokasi & Personel', 'AsMasterLokasi', $lokasi->id, "Memperbarui lokasi/divisi: {$lokasi->nama_lokasi}");

        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'lokasi_personel'])
            ->with('status', "Data lokasi '{$lokasi->nama_lokasi}' berhasil diperbarui.");
    }

    public function toggleLokasi(AsMasterLokasi $lokasi)
    {
        $this->authorizeAccess();

        $lokasi->update(['is_active' => !$lokasi->is_active]);

        $status = $lokasi->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'lokasi_personel'])
            ->with('status', "Status lokasi '{$lokasi->nama_lokasi}' berhasil {$status}.");
    }

    public function destroyLokasi(AsMasterLokasi $lokasi)
    {
        $this->authorizeAccess();

        $nama = $lokasi->nama_lokasi;
        $lokasi->delete();

        $this->audit('DELETE', 'Master Lokasi & Personel', 'AsMasterLokasi', $lokasi->id, "Menghapus lokasi/divisi: {$nama}");

        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'lokasi_personel'])
            ->with('status', "Lokasi '{$nama}' beserta personel di dalamnya berhasil dihapus.");
    }

    // ========================================================
    // CRUD MASTER PERSONEL
    // ========================================================

    public function storePersonel(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'lokasi_id'     => 'required|exists:as_master_lokasi,id',
            'nama_personel' => 'required|string|max:150',
            'nip'           => 'nullable|string|max:50',
            'jabatan'       => 'nullable|string|max:100',
        ], [
            'lokasi_id.required'     => 'Pilih lokasi/divisi penempatan personel.',
            'lokasi_id.exists'       => 'Lokasi/divisi yang dipilih tidak ditemukan.',
            'nama_personel.required' => 'Nama personel wajib diisi.',
        ]);

        $personel = AsMasterPersonel::create([
            'lokasi_id'     => $validated['lokasi_id'],
            'nama_personel' => trim($validated['nama_personel']),
            'nip'           => $validated['nip'] ? trim($validated['nip']) : null,
            'jabatan'       => $validated['jabatan'] ? trim($validated['jabatan']) : null,
            'is_active'     => true,
        ]);

        $this->audit('CREATE', 'Master Lokasi & Personel', 'AsMasterPersonel', $personel->id, "Menambahkan personel baru: {$personel->nama_personel}");

        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'lokasi_personel'])
            ->with('status', "Personel '{$personel->nama_personel}' berhasil ditambahkan.");
    }

    public function updatePersonel(Request $request, AsMasterPersonel $personel)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'lokasi_id'     => 'required|exists:as_master_lokasi,id',
            'nama_personel' => 'required|string|max:150',
            'nip'           => 'nullable|string|max:50',
            'jabatan'       => 'nullable|string|max:100',
        ], [
            'lokasi_id.required'     => 'Pilih lokasi/divisi penempatan personel.',
            'lokasi_id.exists'       => 'Lokasi/divisi yang dipilih tidak ditemukan.',
            'nama_personel.required' => 'Nama personel wajib diisi.',
        ]);

        $personel->update([
            'lokasi_id'     => $validated['lokasi_id'],
            'nama_personel' => trim($validated['nama_personel']),
            'nip'           => $validated['nip'] ? trim($validated['nip']) : null,
            'jabatan'       => $validated['jabatan'] ? trim($validated['jabatan']) : null,
        ]);

        $this->audit('UPDATE', 'Master Lokasi & Personel', 'AsMasterPersonel', $personel->id, "Memperbarui personel: {$personel->nama_personel}");

        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'lokasi_personel'])
            ->with('status', "Data personel '{$personel->nama_personel}' berhasil diperbarui.");
    }

    public function togglePersonel(AsMasterPersonel $personel)
    {
        $this->authorizeAccess();

        $personel->update(['is_active' => !$personel->is_active]);

        $status = $personel->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'lokasi_personel'])
            ->with('status', "Status personel '{$personel->nama_personel}' berhasil {$status}.");
    }

    public function destroyPersonel(AsMasterPersonel $personel)
    {
        $this->authorizeAccess();

        $nama = $personel->nama_personel;
        $personel->delete();

        $this->audit('DELETE', 'Master Lokasi & Personel', 'AsMasterPersonel', $personel->id, "Menghapus personel: {$nama}");

        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'lokasi_personel'])
            ->with('status', "Personel '{$nama}' berhasil dihapus.");
    }

    // ========================================================
    // CRUD MASTER KODE ASET (data general produk)
    // Satu kode general dipakai banyak unit aset; kode_aset di
    // detail aset TIDAK unique, unique hanya di master ini.
    // ========================================================

    public function storeKodeAset(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'kode'      => 'required|string|max:50|regex:/^[A-Za-z0-9\-_]+$/|unique:as_master_kode_aset,kode',
            'nama'      => 'required|string|max:150',
            'jenis'     => 'required|string|max:100',
            'merek'     => 'nullable|string|max:100',
            'tipe'      => 'nullable|string|max:100',
            'kapasitas' => 'nullable|string|max:100',
        ], [
            'kode.required'  => 'Kode aset wajib diisi.',
            'kode.regex'     => 'Kode aset hanya boleh huruf, angka, strip (-), dan underscore (_), tanpa spasi atau koma.',
            'kode.unique'    => 'Kode aset ini sudah terdaftar di master.',
            'nama.required'  => 'Nama produk aset wajib diisi.',
            'jenis.required' => 'Jenis aset wajib diisi.',
        ]);

        $maxOrder = AsMasterKodeAset::max('sort_order') ?? 0;

        $kodeAset = AsMasterKodeAset::create([
            'kode'       => trim($validated['kode']),
            'nama'       => trim($validated['nama']),
            'jenis'      => trim($validated['jenis']),
            'merek'      => $validated['merek'] ? trim($validated['merek']) : null,
            'tipe'       => $validated['tipe'] ? trim($validated['tipe']) : null,
            'kapasitas'  => $validated['kapasitas'] ? trim($validated['kapasitas']) : null,
            'is_active'  => true,
            'sort_order' => $maxOrder + 1,
        ]);

        $this->audit('CREATE', 'Master Kode Aset', 'AsMasterKodeAset', $kodeAset->id, "Menambahkan master kode aset baru: {$kodeAset->kode}");

        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'kode_aset'])
            ->with('status', "Master kode aset '{$kodeAset->kode}' berhasil ditambahkan.");
    }

    public function updateKodeAset(Request $request, AsMasterKodeAset $kodeAset)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'kode'      => 'required|string|max:50|regex:/^[A-Za-z0-9\-_]+$/|unique:as_master_kode_aset,kode,' . $kodeAset->id,
            'nama'      => 'required|string|max:150',
            'jenis'     => 'required|string|max:100',
            'merek'     => 'nullable|string|max:100',
            'tipe'      => 'nullable|string|max:100',
            'kapasitas' => 'nullable|string|max:100',
        ], [
            'kode.required'  => 'Kode aset wajib diisi.',
            'kode.regex'     => 'Kode aset hanya boleh huruf, angka, strip (-), dan underscore (_), tanpa spasi atau koma.',
            'kode.unique'    => 'Kode aset ini sudah digunakan master lain.',
            'nama.required'  => 'Nama produk aset wajib diisi.',
            'jenis.required' => 'Jenis aset wajib diisi.',
        ]);

        $kodeAset->update([
            'kode'      => trim($validated['kode']),
            'nama'      => trim($validated['nama']),
            'jenis'     => trim($validated['jenis']),
            'merek'     => $validated['merek'] ? trim($validated['merek']) : null,
            'tipe'      => $validated['tipe'] ? trim($validated['tipe']) : null,
            'kapasitas' => $validated['kapasitas'] ? trim($validated['kapasitas']) : null,
        ]);

        $this->audit('UPDATE', 'Master Kode Aset', 'AsMasterKodeAset', $kodeAset->id, "Memperbarui master kode aset: {$kodeAset->kode}");

        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'kode_aset'])
            ->with('status', "Master kode aset '{$kodeAset->kode}' berhasil diperbarui.");
    }

    public function toggleKodeAset(AsMasterKodeAset $kodeAset)
    {
        $this->authorizeAccess();

        $kodeAset->update(['is_active' => !$kodeAset->is_active]);

        $status = $kodeAset->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'kode_aset'])
            ->with('status', "Status master kode aset '{$kodeAset->kode}' berhasil {$status}.");
    }

    public function destroyKodeAset(AsMasterKodeAset $kodeAset)
    {
        $this->authorizeAccess();

        if (AsAset::where('kode_aset_id', $kodeAset->id)->exists()) {
            return redirect()->route('konfigurasi.field-aset.index', ['module' => 'kode_aset'])
                ->with('error', "Master kode aset '{$kodeAset->kode}' tidak bisa dihapus karena masih dipakai oleh unit aset yang terdaftar.");
        }

        $kode = $kodeAset->kode;
        $kodeAset->delete();

        $this->audit('DELETE', 'Master Kode Aset', 'AsMasterKodeAset', $kodeAset->id, "Menghapus master kode aset: {$kode}");

        return redirect()->route('konfigurasi.field-aset.index', ['module' => 'kode_aset'])
            ->with('status', "Master kode aset '{$kode}' berhasil dihapus.");
    }
}
