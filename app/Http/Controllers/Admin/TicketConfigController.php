<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use App\Models\InternalDepartment;
use App\Models\TicketCategory;
use App\Models\TicketField;
use Illuminate\Http\Request;

class TicketConfigController extends Controller
{
    use LogsAudit;

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if (
            $user->canAccess('config_ticket') ||
            $user->isOperator() ||
            $user->canAccess('ticketing')
        ) {
            return;
        }

        abort(403, 'Akses ini khusus untuk Operator Helpdesk atau Administrator.');
    }

    /**
     * Tampilkan halaman Konfigurasi Sistem Tiket (Tab Kategori & Tab Fields).
     */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $activeTab = $request->get('tab', 'kategori');
        if (!in_array($activeTab, ['kategori', 'fields'])) {
            $activeTab = 'kategori';
        }

        // Data untuk Tab Kategori
        $categoryQuery = TicketCategory::with('department')
            ->orderBy('department_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('jenis_pengajuan')) {
            $categoryQuery->where('jenis_pengajuan', $request->jenis_pengajuan);
        }
        if ($request->filled('department_id')) {
            $categoryQuery->where('department_id', $request->department_id);
        }
        if ($request->filled('search')) {
            $categoryQuery->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $categories = $categoryQuery->paginate(20)->withQueryString();
        $departments = InternalDepartment::all();

        $categoryStats = [
            'total'        => TicketCategory::count(),
            'permasalahan' => TicketCategory::where('jenis_pengajuan', 'Permasalahan')->count(),
            'permintaan'   => TicketCategory::where('jenis_pengajuan', 'Permintaan')->count(),
            'aktif'        => TicketCategory::where('is_active', true)->count(),
        ];

        // Data untuk Tab Fields
        $fields = TicketField::orderBy('sort_order')->orderBy('id')->get();
        $fieldStats = [
            'total'     => $fields->count(),
            'aktif'     => $fields->where('is_active', true)->count(),
            'di_tabel'  => $fields->where('show_in_list', true)->count(),
            'di_form'   => $fields->where('show_in_form', true)->count(),
            'kustom'    => $fields->where('is_system', false)->count(),
        ];

        return view('admin.ticket-config.index', compact(
            'activeTab',
            'categories',
            'departments',
            'categoryStats',
            'fields',
            'fieldStats'
        ));
    }

    // ==========================================
    // AKSI CRUD KATEGORI TIKET
    // ==========================================

    public function storeCategory(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'jenis_pengajuan'      => 'required|in:Permintaan,Permasalahan',
            'sla_resolution_hours' => 'required|integer|min:1|max:720',
            'department_id'        => 'nullable|exists:internal_departments,id',
            'sort_order'           => 'nullable|integer|min:0|max:999',
            'is_active'            => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['default_sla_hours'] = $validated['sla_resolution_hours'];
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = TicketCategory::create($validated);

        $this->audit(
            'CREATE',
            'Kategori Tiket',
            'TicketCategory',
            $category->id,
            "Menambahkan kategori tiket baru '{$category->name}' ({$category->jenis_pengajuan}, SLA: {$category->sla_resolution_hours} jam)"
        );

        return redirect()->route('konfigurasi.tiket.index', ['tab' => 'kategori'])
            ->with('status', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    public function updateCategory(Request $request, TicketCategory $category)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'jenis_pengajuan'      => 'required|in:Permintaan,Permasalahan',
            'sla_resolution_hours' => 'required|integer|min:1|max:720',
            'department_id'        => 'nullable|exists:internal_departments,id',
            'sort_order'           => 'nullable|integer|min:0|max:999',
            'is_active'            => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['default_sla_hours'] = $validated['sla_resolution_hours'];
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category->update($validated);

        $this->audit(
            'UPDATE',
            'Kategori Tiket',
            'TicketCategory',
            $category->id,
            "Memperbarui kategori tiket '{$category->name}' ({$category->jenis_pengajuan}, SLA: {$category->sla_resolution_hours} jam)"
        );

        return redirect()->route('konfigurasi.tiket.index', ['tab' => 'kategori'])
            ->with('status', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    public function destroyCategory(TicketCategory $category)
    {
        $this->authorizeAccess();

        $name = $category->name;
        $category->delete();

        $this->audit('DELETE', 'Kategori Tiket', 'TicketCategory', $category->id, "Menghapus kategori tiket '{$name}'");

        return redirect()->route('konfigurasi.tiket.index', ['tab' => 'kategori'])
            ->with('status', "Kategori '{$name}' berhasil dihapus.");
    }

    public function toggleCategory(TicketCategory $category)
    {
        $this->authorizeAccess();

        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->audit('UPDATE', 'Kategori Tiket', 'TicketCategory', $category->id, "Mengubah status kategori '{$category->name}' menjadi {$status}");

        return back()->with('status', "Status kategori '{$category->name}' berhasil {$status}.");
    }

    // ==========================================
    // AKSI PENGELOLAAN FIELD FORM & TABEL TIKET
    // ==========================================

    public function storeField(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'field_name'  => [
                'required', 'string', 'max:64',
                'regex:/^[a-z][a-z0-9_]*$/',
                'unique:ticket_fields,field_name',
            ],
            'label'        => 'required|string|max:100',
            'field_type'   => 'required|in:text,number,select,textarea,file,date',
            'options'      => 'nullable|string',
            'is_required'  => 'nullable|boolean',
            'show_in_form' => 'nullable|boolean',
            'show_in_list' => 'nullable|boolean',
            'sort_order'   => 'nullable|integer|min:0',
            'help_text'    => 'nullable|string|max:255',
        ], [
            'field_name.regex' => 'Nama field hanya boleh huruf kecil, angka, dan underscore, serta dimulai dengan huruf.',
        ]);

        $options = null;
        if ($validated['field_type'] === 'select' && !empty($validated['options'])) {
            $options = array_values(array_filter(array_map('trim', explode("\n", $validated['options']))));
        }

        $hasManualOrder = isset($validated['sort_order']) && $validated['sort_order'] !== null && $validated['sort_order'] !== '';
        if ($hasManualOrder) {
            $finalOrder = max(1, (int) $validated['sort_order']);
            // Geser field yang sudah ada dengan urutan >= finalOrder agar slot nomor urut tersedia
            TicketField::where('sort_order', '>=', $finalOrder)->increment('sort_order');
        } else {
            $finalOrder = ((TicketField::max('sort_order') ?? 0) + 1);
        }

        $field = TicketField::create([
            'field_name'   => $validated['field_name'],
            'label'        => $validated['label'],
            'field_type'   => $validated['field_type'],
            'options'      => $options,
            'is_required'  => $request->boolean('is_required'),
            'show_in_form' => $request->has('show_in_form'),
            'show_in_list' => $request->has('show_in_list'),
            'sort_order'   => $finalOrder,
            'help_text'    => $validated['help_text'] ?? null,
            'is_active'    => true,
            'is_system'    => false,
        ]);

        $this->resequenceFields();

        $this->audit('CREATE', 'Konfigurasi Field Tiket', 'TicketField', $field->id, "Menambahkan field tiket baru '{$field->label}' ({$field->field_name})");

        return redirect()->route('konfigurasi.tiket.index', ['tab' => 'fields'])
            ->with('status', "Field tiket '{$field->label}' berhasil ditambahkan.");
    }

    public function updateField(Request $request, TicketField $field)
    {
        $this->authorizeAccess();

        $rules = [
            'label'        => 'required|string|max:100',
            'options'      => 'nullable|string',
            'is_required'  => 'nullable|boolean',
            'show_in_form' => 'nullable|boolean',
            'show_in_list' => 'nullable|boolean',
            'sort_order'   => 'nullable|integer|min:0',
            'help_text'    => 'nullable|string|max:255',
            'is_active'    => 'nullable|boolean',
        ];

        // Jika field bawaan sistem (is_system), field_type tidak wajib di-submit dari browser
        if (!$field->is_system) {
            $rules['field_type'] = 'required|in:text,number,select,textarea,file,date';
        } else {
            $rules['field_type'] = 'nullable|in:text,number,select,textarea,file,date';
        }

        $validated = $request->validate($rules);

        $fieldType = $field->is_system
            ? $field->field_type
            : ($validated['field_type'] ?? $field->field_type);

        $options = null;
        if ($fieldType === 'select') {
            if (!empty($validated['options'])) {
                $options = array_values(array_filter(array_map('trim', explode("\n", $validated['options']))));
            } else {
                $options = [];
            }
        }

        $oldOrder = (int) $field->sort_order;
        $hasManualOrder = isset($validated['sort_order']) && $validated['sort_order'] !== null && $validated['sort_order'] !== '';
        $newOrder = $hasManualOrder ? max(1, (int) $validated['sort_order']) : $oldOrder;

        if ($newOrder !== $oldOrder) {
            if ($newOrder < $oldOrder) {
                TicketField::where('id', '!=', $field->id)
                    ->where('sort_order', '>=', $newOrder)
                    ->where('sort_order', '<', $oldOrder)
                    ->increment('sort_order');
            } else {
                TicketField::where('id', '!=', $field->id)
                    ->where('sort_order', '>', $oldOrder)
                    ->where('sort_order', '<=', $newOrder)
                    ->decrement('sort_order');
            }
        }

        $field->update([
            'label'        => $validated['label'],
            'field_type'   => $fieldType,
            'options'      => $field->is_system ? $field->options : $options,
            'is_required'  => $request->boolean('is_required'),
            'show_in_form' => $request->has('show_in_form'),
            'show_in_list' => $request->has('show_in_list'),
            'sort_order'   => $newOrder,
            'help_text'    => $validated['help_text'] ?? null,
            'is_active'    => $request->has('is_active'),
        ]);

        $this->resequenceFields();

        $this->audit('UPDATE', 'Konfigurasi Field Tiket', 'TicketField', $field->id, "Memperbarui konfigurasi field tiket '{$field->label}'");

        return redirect()->route('konfigurasi.tiket.index', ['tab' => 'fields'])
            ->with('status', "Field tiket '{$field->label}' berhasil diperbarui.");
    }

    public function destroyField(TicketField $field)
    {
        $this->authorizeAccess();

        if ($field->is_system) {
            return back()->withErrors(['field' => "Field bawaan sistem '{$field->label}' tidak dapat dihapus."]);
        }

        $label = $field->label;
        $id = $field->id;
        $field->delete();

        $this->resequenceFields();

        $this->audit('DELETE', 'Konfigurasi Field Tiket', 'TicketField', $id, "Menghapus field tiket kustom '{$label}'");

        return redirect()->route('konfigurasi.tiket.index', ['tab' => 'fields'])
            ->with('status', "Field '{$label}' berhasil dihapus.");
    }

    public function reorderFields(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'orders'   => 'required|array',
            'orders.*' => 'integer|min:0',
        ]);

        $orders = $validated['orders'];
        asort($orders);

        $seq = 1;
        foreach (array_keys($orders) as $id) {
            TicketField::where('id', $id)->update(['sort_order' => $seq++]);
        }

        $this->resequenceFields();

        $this->audit('UPDATE', 'Konfigurasi Field Tiket', 'TicketField', 0, "Menyusun ulang urutan field tiket");

        return redirect()->route('konfigurasi.tiket.index', ['tab' => 'fields'])
            ->with('status', 'Urutan field tiket berhasil diperbarui.');
    }

    public function toggleField(TicketField $field)
    {
        $this->authorizeAccess();

        $field->update(['is_active' => !$field->is_active]);
        $status = $field->is_active ? 'diaktifkan' : 'dinonaktifkan';

        $this->audit('UPDATE', 'Konfigurasi Field Tiket', 'TicketField', $field->id, "Mengubah status field '{$field->label}' menjadi {$status}");

        return back()->with('status', "Field '{$field->label}' berhasil {$status}.");
    }

    public function toggleFieldList(TicketField $field)
    {
        $this->authorizeAccess();

        $field->update(['show_in_list' => !$field->show_in_list]);
        $status = $field->show_in_list ? 'ditampilkan di tabel' : 'disembunyikan dari tabel';

        $this->audit('UPDATE', 'Konfigurasi Field Tiket', 'TicketField', $field->id, "Mengubah visibilitas tabel field '{$field->label}' menjadi {$status}");

        return back()->with('status', "Field '{$field->label}' {$status}.");
    }

    public function toggleFieldForm(TicketField $field)
    {
        $this->authorizeAccess();

        $field->update(['show_in_form' => !$field->show_in_form]);
        $status = $field->show_in_form ? 'ditampilkan di form' : 'disembunyikan dari form';

        $this->audit('UPDATE', 'Konfigurasi Field Tiket', 'TicketField', $field->id, "Mengubah visibilitas form field '{$field->label}' menjadi {$status}");

        return back()->with('status', "Field '{$field->label}' {$status}.");
    }

    private function resequenceFields(): void
    {
        $fields = TicketField::orderBy('sort_order')->orderBy('id')->get();
        foreach ($fields as $index => $field) {
            $expected = $index + 1;
            if ($field->sort_order !== $expected) {
                $field->update(['sort_order' => $expected]);
            }
        }
    }
}
