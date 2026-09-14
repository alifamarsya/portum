@extends('layouts.app')
@section('title', 'Manajemen Dynamic Fields — ' . ($modules[$moduleKey] ?? $moduleKey))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-1">Administrasi Sistem</p>
            <h1 class="text-2xl font-bold text-ink flex items-center gap-2.5">
                @include('partials.icon', ['name' => 'sliders', 'class' => 'w-6 h-6 text-[#114E84]'])
                Dynamic Fields — {{ $modules[$moduleKey] ?? $moduleKey }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">Kelola field tambahan (custom) yang muncul di form dan tabel modul Inventarisasi & Riwayat Pergerakan Aset.</p>
        </div>
    </div>

    {{-- Module Tab Switcher --}}
    <div class="flex gap-2 flex-wrap">
        @foreach ($modules as $key => $label)
            <a href="{{ route('admin.custom-fields.index', ['module' => $key]) }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold border transition
                      {{ $moduleKey === $key
                            ? 'bg-[#114E84] text-white border-[#114E84] shadow-md'
                            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Tabel Field yang Ada --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-sm text-ink flex items-center gap-2">
                        @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-[#114E84]'])
                        Field Terdaftar ({{ $fields->count() }} field)
                    </h2>
                    <span class="text-xs text-slate-400">Modul: <strong class="text-slate-700">{{ $modules[$moduleKey] ?? $moduleKey }}</strong></span>
                </div>

                @if ($fields->isEmpty())
                    <div class="py-12 text-center text-slate-400">
                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            @include('partials.icon', ['name' => 'sliders', 'class' => 'w-6 h-6'])
                        </div>
                        <p class="font-semibold text-slate-600 text-sm">Belum Ada Custom Field</p>
                        <p class="text-xs mt-1">Tambahkan field baru melalui form di samping kanan.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider text-[11px] font-bold">
                                    <th class="py-3 px-4">Label / Field Name</th>
                                    <th class="py-3 px-4">Tipe</th>
                                    <th class="py-3 px-4 text-center">Wajib</th>
                                    <th class="py-3 px-4 text-center">Di Tabel</th>
                                    <th class="py-3 px-4 text-center">Urutan</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($fields as $field)
                                    <tr class="hover:bg-slate-50/60 transition {{ !$field->is_active ? 'opacity-50' : '' }}">
                                        <td class="py-3 px-4">
                                            <p class="font-bold text-ink">{{ $field->label }}</p>
                                            <p class="text-[10.5px] font-mono text-slate-400">{{ $field->field_name }}</p>
                                            @if ($field->help_text)
                                                <p class="text-[10.5px] text-slate-400 italic mt-0.5">{{ $field->help_text }}</p>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            @php
                                                $typeColor = match($field->field_type) {
                                                    'text' => 'bg-blue-100 text-blue-800',
                                                    'number', 'money' => 'bg-emerald-100 text-emerald-800',
                                                    'date' => 'bg-purple-100 text-purple-800',
                                                    'select' => 'bg-amber-100 text-amber-800',
                                                    'textarea' => 'bg-slate-100 text-slate-700',
                                                    'checkbox' => 'bg-rose-100 text-rose-800',
                                                    default => 'bg-slate-100 text-slate-600',
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $typeColor }} uppercase">
                                                {{ $field->field_type }}
                                            </span>
                                            @if ($field->field_type === 'select' && $field->options)
                                                <p class="text-[10px] text-slate-400 mt-0.5">{{ count($field->options) }} opsi</p>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($field->is_required)
                                                <span class="text-rose-500 font-bold">✓</span>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($field->show_in_list)
                                                <span class="text-emerald-600 font-bold">✓</span>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="font-mono text-slate-500">{{ $field->sort_order }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <form method="POST" action="{{ route('admin.custom-fields.toggle', $field) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-2 py-1 rounded-lg text-[10px] font-bold transition
                                                    {{ $field->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                                    {{ $field->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button"
                                                        onclick="openEditModal({{ $field->id }}, '{{ addslashes($field->label) }}', '{{ $field->field_type }}', {{ $field->is_required ? 'true' : 'false' }}, {{ $field->show_in_list ? 'true' : 'false' }}, {{ $field->sort_order }}, '{{ addslashes($field->help_text ?? '') }}', {{ $field->is_active ? 'true' : 'false' }}, {{ json_encode($field->options ?? []) }})"
                                                        class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold transition text-[11px]">
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('admin.custom-fields.destroy', $field) }}"
                                                      onsubmit="return confirm('Yakin hapus field \'{{ $field->label }}\'? Data custom field di semua record aset yang menggunakan field ini tidak akan otomatis terhapus.')"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-semibold transition text-[11px]">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Info Box --}}
            <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-4 text-xs text-blue-900">
                <p class="font-bold text-sm text-blue-800 mb-1 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'alert-circle', 'class' => 'w-4 h-4'])
                    Panduan Dynamic Fields
                </p>
                <ul class="space-y-1 text-blue-800 leading-relaxed list-disc pl-4">
                    <li>Field yang didefinisikan di sini akan muncul otomatis di form tambah/ubah data modul terkait.</li>
                    <li><strong>Nama Field</strong> (field_name) bersifat unik per modul dan tidak dapat diubah setelah dibuat.</li>
                    <li>Data tersimpan di kolom JSON <code class="bg-blue-100 px-1 rounded">custom_fields</code> di tabel aset/riwayat pergerakan.</li>
                    <li>Menghapus field tidak menghapus data lama yang sudah tersimpan. Hanya tidak ditampilkan lagi.</li>
                    <li>Tipe <strong>money</strong> akan otomatis diformat sebagai rupiah (Rp xxx.xxx).</li>
                </ul>
            </div>
        </div>

        {{-- Kolom Kanan: Form Tambah Field --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5">
                <h2 class="font-bold text-sm text-ink flex items-center gap-2 mb-4">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-[#114E84]'])
                    Tambah Custom Field Baru
                </h2>

                <form method="POST" action="{{ route('admin.custom-fields.store') }}" class="space-y-3 text-xs">
                    @csrf
                    <input type="hidden" name="module_key" value="{{ $moduleKey }}">

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Field (snake_case) <span class="text-rose-500">*</span></label>
                        <input type="text" name="field_name" value="{{ old('field_name') }}" required
                               placeholder="contoh: nomor_seri, merk_tipe"
                               pattern="[a-z][a-z0-9_]*"
                               class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                        <p class="text-[10.5px] text-slate-400 mt-0.5">Huruf kecil, angka, underscore. Tidak dapat diubah setelah dibuat.</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Label Tampilan <span class="text-rose-500">*</span></label>
                        <input type="text" name="label" value="{{ old('label') }}" required
                               placeholder="contoh: Nomor Seri / IMEI"
                               class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tipe Field <span class="text-rose-500">*</span></label>
                        <select name="field_type" id="addFieldType" required onchange="toggleOptionsField('addOptions', this.value)"
                                class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                            <option value="text">Text (Teks Singkat)</option>
                            <option value="textarea">Textarea (Teks Panjang)</option>
                            <option value="number">Number (Angka)</option>
                            <option value="money">Money (Rupiah)</option>
                            <option value="date">Date (Tanggal)</option>
                            <option value="select">Select (Pilihan)</option>
                            <option value="checkbox">Checkbox (Ya/Tidak)</option>
                        </select>
                    </div>

                    <div id="addOptions" class="hidden">
                        <label class="block font-semibold text-slate-700 mb-1">Opsi Pilihan (1 baris = 1 opsi)</label>
                        <textarea name="options" rows="4"
                                  placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"
                                  class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs font-mono"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_required" value="1" class="rounded text-brand">
                            <span class="font-medium text-slate-700">Wajib Diisi</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="show_in_list" value="1" checked class="rounded text-brand">
                            <span class="font-medium text-slate-700">Tampil di Tabel</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Urutan</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                   class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Hint/Bantuan</label>
                            <input type="text" name="help_text" value="{{ old('help_text') }}" placeholder="Keterangan singkat..."
                                   class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white font-bold text-xs transition shadow-md">
                            Tambah Field
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Field --}}
<div id="editFieldModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'sliders', 'class' => 'w-5 h-5 text-[#114E84]'])
                Edit Custom Field
            </h3>
            <button type="button" onclick="closeModal('editFieldModal')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form id="editFieldForm" method="POST" action="" class="space-y-3 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Label Tampilan <span class="text-rose-500">*</span></label>
                <input type="text" id="editLabel" name="label" required
                       class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tipe Field <span class="text-rose-500">*</span></label>
                <select id="editFieldType" name="field_type" required onchange="toggleOptionsField('editOptions', this.value)"
                        class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                    <option value="text">Text</option>
                    <option value="textarea">Textarea</option>
                    <option value="number">Number</option>
                    <option value="money">Money</option>
                    <option value="date">Date</option>
                    <option value="select">Select</option>
                    <option value="checkbox">Checkbox</option>
                </select>
            </div>

            <div id="editOptions" class="hidden">
                <label class="block font-semibold text-slate-700 mb-1">Opsi Pilihan (1 baris = 1 opsi)</label>
                <textarea id="editOptionsText" name="options" rows="4"
                          class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs font-mono"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="editIsRequired" name="is_required" value="1" class="rounded text-brand">
                    <span class="font-medium text-slate-700">Wajib Diisi</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="editShowInList" name="show_in_list" value="1" class="rounded text-brand">
                    <span class="font-medium text-slate-700">Tampil di Tabel</span>
                </label>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Urutan</label>
                    <input type="number" id="editSortOrder" name="sort_order" min="0"
                           class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Hint</label>
                    <input type="text" id="editHelpText" name="help_text"
                           class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="editIsActive" name="is_active" value="1" class="rounded text-brand">
                <span class="font-medium text-slate-700">Field Aktif</span>
            </label>

            <div class="pt-3 border-t border-slate-100 flex gap-2">
                <button type="button" onclick="closeModal('editFieldModal')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white font-bold text-xs transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function toggleOptionsField(containerId, type) {
        const el = document.getElementById(containerId);
        if (type === 'select') {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }

    function openEditModal(id, label, fieldType, isRequired, showInList, sortOrder, helpText, isActive, options) {
        // Set form action
        document.getElementById('editFieldForm').action = `/admin/custom-fields/${id}`;

        // Set values
        document.getElementById('editLabel').value = label;
        document.getElementById('editFieldType').value = fieldType;
        document.getElementById('editIsRequired').checked = isRequired;
        document.getElementById('editShowInList').checked = showInList;
        document.getElementById('editSortOrder').value = sortOrder;
        document.getElementById('editHelpText').value = helpText;
        document.getElementById('editIsActive').checked = isActive;

        // Handle select options
        if (fieldType === 'select' && Array.isArray(options) && options.length > 0) {
            document.getElementById('editOptions').classList.remove('hidden');
            document.getElementById('editOptionsText').value = options.join('\n');
        } else {
            document.getElementById('editOptions').classList.add('hidden');
            document.getElementById('editOptionsText').value = '';
        }

        openModal('editFieldModal');
    }
</script>
@endsection
