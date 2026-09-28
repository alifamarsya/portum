<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsCustomField;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if (
            $user->isSuperAdmin() ||
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
            'aset'         => 'Inventarisasi Aset',
            'aset_history' => 'Riwayat Pergerakan Aset',
            'mutasi'       => 'Form Mutasi Aset',
        ];

        $fields = AsCustomField::where('module_key', $moduleKey)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.custom-fields.index', compact('fields', 'modules', 'moduleKey'));
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
}
