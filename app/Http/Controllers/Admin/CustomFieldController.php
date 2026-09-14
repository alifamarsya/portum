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
            'module_key'  => 'required|in:aset,aset_history',
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
            'field_type'  => 'required|in:text,number,money,date,select,textarea,checkbox',
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

        AsCustomField::create([
            'module_key'   => $validated['module_key'],
            'field_name'   => $validated['field_name'],
            'label'        => $validated['label'],
            'field_type'   => $validated['field_type'],
            'options'      => $options,
            'is_required'  => (bool) ($validated['is_required'] ?? false),
            'show_in_list' => (bool) ($validated['show_in_list'] ?? true),
            'sort_order'   => (int) ($validated['sort_order'] ?? 0),
            'help_text'    => $validated['help_text'] ?? null,
            'is_active'    => true,
        ]);

        return redirect()->route('admin.custom-fields.index', ['module' => $validated['module_key']])
            ->with('status', "Custom field '{$validated['label']}' berhasil ditambahkan ke modul {$validated['module_key']}.");
    }

    /**
     * Update custom field.
     */
    public function update(Request $request, AsCustomField $customField)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'label'       => 'required|string|max:100',
            'field_type'  => 'required|in:text,number,money,date,select,textarea,checkbox',
            'options'     => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'show_in_list'=> 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
            'help_text'   => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        $options = $customField->options;
        if ($validated['field_type'] === 'select' && !empty($validated['options'])) {
            $options = array_values(array_filter(array_map('trim', explode("\n", $validated['options']))));
        }

        $customField->update([
            'label'        => $validated['label'],
            'field_type'   => $validated['field_type'],
            'options'      => $options,
            'is_required'  => (bool) ($validated['is_required'] ?? false),
            'show_in_list' => (bool) ($validated['show_in_list'] ?? true),
            'sort_order'   => (int) ($validated['sort_order'] ?? 0),
            'help_text'    => $validated['help_text'] ?? null,
            'is_active'    => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('admin.custom-fields.index', ['module' => $customField->module_key])
            ->with('status', "Custom field '{$customField->label}' berhasil diperbarui.");
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

        return redirect()->route('admin.custom-fields.index', ['module' => $moduleKey])
            ->with('status', "Custom field '{$label}' berhasil dihapus.");
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
