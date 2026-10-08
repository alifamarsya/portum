@extends('layouts.app')
@section('title', 'Field Mutasi Aset — ' . ($modules[$moduleKey] ?? $moduleKey))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/20">
                    @include('partials.icon', ['name' => 'sliders', 'class' => 'w-3 h-3 text-[#114E84]'])
                    Customized
                </span>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs font-semibold text-slate-600">Field Mutasi Aset</span>
            </div>
            <h1 class="text-2xl font-bold text-ink flex items-center gap-2.5">
                @include('partials.icon', ['name' => 'sliders', 'class' => 'w-6 h-6 text-[#114E84]'])
                Field Mutasi Aset — {{ $modules[$moduleKey] ?? $moduleKey }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">Kelola seluruh field (bawaan &amp; kustom) yang muncul di formulir dan tabel modul Inventarisasi Aset, Riwayat Pergerakan, dan Form Mutasi Aset.</p>
        </div>

        @if ($moduleKey === 'mutasi')
            <a href="{{ route('mutasi-aset.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-[#114E84]'])
                Pratinjau Form Mutasi
            </a>
        @elseif (in_array($moduleKey, ['aset', 'aset_history']))
            <a href="{{ route('modul.index', $moduleKey) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-[#114E84]'])
                Lihat Modul {{ $modules[$moduleKey] ?? $moduleKey }}
            </a>
        @endif
    </div>

    {{-- Module Tab Switcher --}}
    <div class="flex gap-2 flex-wrap items-center">
        @foreach ($modules as $key => $label)
            <a href="{{ route('admin.custom-fields.index', ['module' => $key]) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold border transition flex items-center gap-2
                      {{ $moduleKey === $key
                            ? 'bg-[#114E84] text-white border-[#114E84] shadow-md'
                            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:text-ink' }}">
                <span>{{ $label }}</span>
                @if ($moduleKey === $key)
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Tabel Field yang Ada --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
                <form id="reorderForm" method="POST" action="{{ route('admin.custom-fields.reorder') }}">
                    @csrf
                    <input type="hidden" name="module" value="{{ $moduleKey }}">

                    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-bold text-sm text-ink flex items-center gap-2">
                                @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-[#114E84]'])
                                Field Terdaftar ({{ $fields->count() }} field)
                            </h2>
                            <p class="text-[11px] text-slate-400 mt-0.5">Urutan menentukan posisi field di formulir (kiri ke kanan / atas ke bawah).</p>
                        </div>

                        @if ($fields->isNotEmpty())
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition border border-slate-200">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                                Simpan Urutan
                            </button>
                        @endif
                    </div>

                    @if ($fields->isEmpty())
                        <div class="py-12 text-center text-slate-400">
                            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                @include('partials.icon', ['name' => 'sliders', 'class' => 'w-6 h-6'])
                            </div>
                            <p class="font-semibold text-slate-600 text-sm">Belum Ada Dynamic Field</p>
                            <p class="text-xs mt-1">Tambahkan field baru melalui form di samping kanan.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider text-[11px] font-bold">
                                        <th class="py-3 px-3 text-center w-16">Urutan</th>
                                        <th class="py-3 px-4">Label / Field Name</th>
                                        <th class="py-3 px-4">Tipe Pengisian</th>
                                        <th class="py-3 px-3 text-center">Wajib</th>
                                        @if ($moduleKey !== 'mutasi')
                                            <th class="py-3 px-3 text-center">Di Tabel</th>
                                        @endif
                                        <th class="py-3 px-3 text-center">Status</th>
                                        <th class="py-3 px-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($fields as $field)
                                        <tr class="hover:bg-slate-50/60 transition {{ !$field->is_active ? 'opacity-50' : '' }}">
                                            <td class="py-3 px-3 text-center">
                                                <input type="number" name="orders[{{ $field->id }}]"
                                                       value="{{ $field->sort_order }}" min="0"
                                                       class="w-14 text-center font-mono py-1 px-1.5 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-brand focus:border-brand">
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="flex items-center gap-2">
                                                    <p class="font-bold text-ink">{{ $field->label }}</p>
                                                    @if ($field->is_system)
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Bawaan</span>
                                                    @else
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">Kustom</span>
                                                    @endif
                                                </div>
                                                <p class="text-[10.5px] font-mono text-slate-400">{{ $field->field_name }}</p>
                                                @if ($field->help_text)
                                                    <p class="text-[10.5px] text-slate-400 italic mt-0.5">{{ $field->help_text }}</p>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                @php
                                                    $typeColor = match($field->field_type) {
                                                        'text' => 'bg-blue-100 text-blue-800',
                                                        'select' => 'bg-amber-100 text-amber-800',
                                                        'textarea' => 'bg-slate-100 text-slate-700',
                                                        'number', 'money' => 'bg-emerald-100 text-emerald-800',
                                                        'date' => 'bg-purple-100 text-purple-800',
                                                        'file' => 'bg-teal-100 text-teal-800',
                                                        'checkbox' => 'bg-rose-100 text-rose-800',
                                                        default => 'bg-slate-100 text-slate-600',
                                                    };
                                                    $typeLabel = match($field->field_type) {
                                                        'text' => 'Text',
                                                        'select' => 'Dropdown / Select',
                                                        'textarea' => 'Textarea',
                                                        'number' => 'Number',
                                                        'money' => 'Money (Rp)',
                                                        'date' => 'Date',
                                                        'file' => 'File Upload',
                                                        'checkbox' => 'Checkbox',
                                                        default => ucfirst($field->field_type ?? 'Text'),
                                                    };
                                                @endphp
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $typeColor }}">
                                                    {{ $typeLabel }}
                                                </span>
                                                @if ($field->field_type === 'select' && !empty($field->options))
                                                    <p class="text-[10px] text-slate-400 mt-1 font-sans">
                                                        <span class="font-semibold text-slate-600">{{ count($field->options) }} opsi:</span>
                                                        {{ implode(', ', array_slice($field->options, 0, 3)) }}{{ count($field->options) > 3 ? '...' : '' }}
                                                    </p>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                @if ($field->is_required)
                                                    <span class="text-rose-500 font-bold" title="Wajib Diisi">✓</span>
                                                @else
                                                    <span class="text-slate-300" title="Opsional">—</span>
                                                @endif
                                            </td>
                                            @if ($moduleKey !== 'mutasi')
                                                <td class="py-3 px-3 text-center">
                                                    @if ($field->show_in_list)
                                                        <span class="text-emerald-600 font-bold" title="Tampil di Kolom Tabel">✓</span>
                                                    @else
                                                        <span class="text-slate-300" title="Tidak Tampil di Tabel">—</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="py-3 px-3 text-center">
                                                <button type="button"
                                                        onclick="submitToggle({{ $field->id }})"
                                                        class="px-2 py-1 rounded-lg text-[10px] font-bold transition
                                                            {{ $field->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                                    {{ $field->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </td>
                                            <td class="py-3 px-4 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button type="button"
                                                            onclick="openEditModal({{ $field->id }}, '{{ addslashes($field->label) }}', '{{ $field->field_type }}', {{ $field->is_required ? 'true' : 'false' }}, {{ $field->show_in_list ? 'true' : 'false' }}, {{ $field->sort_order }}, '{{ addslashes($field->help_text ?? '') }}', {{ $field->is_active ? 'true' : 'false' }}, {{ json_encode($field->options ?? []) }})"
                                                            class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold transition text-[11px]">
                                                        Edit
                                                    </button>
                                                    <button type="button"
                                                            onclick="submitDelete({{ $field->id }}, '{{ addslashes($field->label) }}')"
                                                            class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-semibold transition text-[11px]">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </form>

                {{-- Hidden Forms for Toggle & Delete --}}
                <form id="actionToggleForm" method="POST" action="" class="hidden">
                    @csrf
                    @method('PATCH')
                </form>
                <form id="actionDeleteForm" method="POST" action="" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            {{-- Info Box --}}
            <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-4 text-xs text-blue-900">
                <p class="font-bold text-sm text-blue-800 mb-1 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'alert-circle', 'class' => 'w-4 h-4'])
                    Panduan Dynamic Fields
                </p>
                <ul class="space-y-1.5 text-blue-800 leading-relaxed list-disc pl-4">
                    <li>Semua field aktif akan otomatis dirender di formulir pengisian modul terkait tanpa perlu mengubah file kode template Blade.</li>
                    <li><strong>Tipe Dropdown / Select:</strong> Admin dapat mengelola daftar opsi pilihan (1 baris per opsi). Opsi akan langsung menjadi pilihan dropdown di sisi pengguna.</li>
                    <li><strong>Urutan (Sort Order):</strong> Mengatur posisi urutan tampilan dari angka terkecil ke terbesar. Anda bisa mengubah angka langsung di tabel lalu klik <em>Simpan Urutan</em>.</li>
                    <li><strong>Nonaktifkan Field:</strong> Jika field tidak ingin dimunculkan lagi di form tanpa menghapus data sebelumnya, cukup ubah status menjadi <em>Nonaktif</em>.</li>
                </ul>
            </div>
        </div>

        {{-- Kolom Kanan: Form Tambah Field --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5">
                <h2 class="font-bold text-sm text-ink flex items-center gap-2 mb-4">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-[#114E84]'])
                    Tambah Field Baru
                </h2>

                <form method="POST" action="{{ route('admin.custom-fields.store') }}" class="space-y-3.5 text-xs">
                    @csrf
                    <input type="hidden" name="module_key" value="{{ $moduleKey }}">

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Field (Key / snake_case) <span class="text-rose-500">*</span></label>
                        <input type="text" name="field_name" value="{{ old('field_name') }}" required
                               placeholder="contoh: urgensi_mutasi, sk_direksi"
                               pattern="[a-z][a-z0-9_]*"
                               class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs font-mono">
                        <p class="text-[10.5px] text-slate-400 mt-0.5">Huruf kecil, angka, underscore. Digunakan sebagai key pengenal sistem.</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Label Tampilan <span class="text-rose-500">*</span></label>
                        <input type="text" name="label" value="{{ old('label') }}" required
                               placeholder="contoh: Urgensi Pengajuan / SK Mutasi"
                               class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tipe Pengisian Field <span class="text-rose-500">*</span></label>
                        <select name="field_type" id="addFieldType" required onchange="toggleOptionsField('addOptions', this.value)"
                                class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs bg-white font-medium">
                            <option value="text">Text (Input Teks Bebas Singkat)</option>
                            <option value="select">Select / Dropdown (Pilihan dari Opsi)</option>
                            <option value="textarea">Textarea (Input Teks Bebas Panjang)</option>
                            <option value="file">File Upload (Dokumen / Berkas Lampiran)</option>
                            <option value="date">Date (Tanggal)</option>
                            <option value="number">Number (Angka)</option>
                            <option value="money">Money (Mata Uang Rupiah)</option>
                            <option value="checkbox">Checkbox (Ya / Tidak)</option>
                        </select>
                    </div>

                    {{-- Dynamic Options Manager for Select --}}
                    <div id="addOptions" class="hidden bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-1.5">
                        <label class="block font-semibold text-slate-800">
                            Daftar Opsi Dropdown <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-slate-500">Tuliskan 1 opsi per baris. Opsi akan muncul secara terstruktur di formulir pengguna.</p>
                        <textarea name="options" rows="4"
                                  placeholder="Pilihan 1&#10;Pilihan 2&#10;Pilihan 3"
                                  class="w-full py-2 px-3 border border-slate-300 rounded-lg focus:ring-1 focus:ring-brand focus:border-brand text-xs font-mono bg-white"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                            <input type="checkbox" name="is_required" value="1" class="rounded text-[#114E84]">
                            <span class="font-medium text-slate-700 text-xs">Wajib Diisi</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                            <input type="checkbox" name="show_in_list" value="1" checked class="rounded text-[#114E84]">
                            <span class="font-medium text-slate-700 text-xs">Tampil di Tabel</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Urutan Tampilan</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', ($fields->max('sort_order') ?? 0) + 1) }}" min="0"
                                   class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Bantuan (Hint)</label>
                            <input type="text" name="help_text" value="{{ old('help_text') }}" placeholder="Petunjuk pengisian..."
                                   class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white font-bold text-xs transition shadow-md flex items-center justify-center gap-2">
                            @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
                            Simpan Field Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Field --}}
<div id="editFieldModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'sliders', 'class' => 'w-5 h-5 text-[#114E84]'])
                Edit Dynamic Field
            </h3>
            <button type="button" onclick="closeModal('editFieldModal')" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
        </div>

        <form id="editFieldForm" method="POST" action="" class="space-y-3.5 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Label Tampilan <span class="text-rose-500">*</span></label>
                <input type="text" id="editLabel" name="label" required
                       class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tipe Pengisian Field <span class="text-rose-500">*</span></label>
                <select id="editFieldType" name="field_type" required onchange="toggleOptionsField('editOptions', this.value)"
                        class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs bg-white font-medium">
                    <option value="text">Text (Input Teks Bebas Singkat)</option>
                    <option value="select">Select / Dropdown (Pilihan dari Opsi)</option>
                    <option value="textarea">Textarea (Input Teks Bebas Panjang)</option>
                    <option value="file">File Upload (Dokumen / Berkas Lampiran)</option>
                    <option value="date">Date (Tanggal)</option>
                    <option value="number">Number (Angka)</option>
                    <option value="money">Money (Mata Uang Rupiah)</option>
                    <option value="checkbox">Checkbox (Ya / Tidak)</option>
                </select>
            </div>

            <div id="editOptions" class="hidden bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-1.5">
                <label class="block font-semibold text-slate-800">
                    Daftar Opsi Dropdown <span class="text-rose-500">*</span>
                </label>
                <p class="text-[11px] text-slate-500">Tuliskan 1 opsi per baris. Hapus atau tambahkan baris untuk mengubah opsi.</p>
                <textarea id="editOptionsText" name="options" rows="4"
                          class="w-full py-2 px-3 border border-slate-300 rounded-lg focus:ring-1 focus:ring-brand focus:border-brand text-xs font-mono bg-white"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1">
                <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                    <input type="checkbox" id="editIsRequired" name="is_required" value="1" class="rounded text-[#114E84]">
                    <span class="font-medium text-slate-700 text-xs">Wajib Diisi</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                    <input type="checkbox" id="editShowInList" name="show_in_list" value="1" class="rounded text-[#114E84]">
                    <span class="font-medium text-slate-700 text-xs">Tampil di Tabel</span>
                </label>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Urutan Tampilan</label>
                    <input type="number" id="editSortOrder" name="sort_order" min="0"
                           class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Bantuan (Hint)</label>
                    <input type="text" id="editHelpText" name="help_text"
                           class="w-full py-2 px-3 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand text-xs">
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                <input type="checkbox" id="editIsActive" name="is_active" value="1" class="rounded text-[#114E84]">
                <span class="font-medium text-slate-700 text-xs">Tampilkan Field di Formulir (Field Aktif)</span>
            </label>

            <div class="pt-3 border-t border-slate-100 flex gap-2">
                <button type="button" onclick="closeModal('editFieldModal')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white font-bold text-xs transition shadow-md">
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
        document.getElementById('editFieldForm').action = `/admin/custom-fields/${id}`;

        document.getElementById('editLabel').value = label;
        document.getElementById('editFieldType').value = fieldType;
        document.getElementById('editIsRequired').checked = isRequired;
        document.getElementById('editShowInList').checked = showInList;
        document.getElementById('editSortOrder').value = sortOrder;
        document.getElementById('editHelpText').value = helpText;
        document.getElementById('editIsActive').checked = (isActive === true || isActive === 1 || isActive === '1');

        if (fieldType === 'select' && Array.isArray(options) && options.length > 0) {
            document.getElementById('editOptions').classList.remove('hidden');
            document.getElementById('editOptionsText').value = options.join('\n');
        } else {
            document.getElementById('editOptions').classList.add('hidden');
            document.getElementById('editOptionsText').value = '';
        }

        openModal('editFieldModal');
    }

    function submitToggle(id) {
        const form = document.getElementById('actionToggleForm');
        form.action = `/admin/custom-fields/${id}/toggle`;
        form.submit();
    }

    function submitDelete(id, label) {
        if (confirm(`Yakin hapus field '${label}'? Field ini tidak akan ditampilkan lagi pada form.`)) {
            const form = document.getElementById('actionDeleteForm');
            form.action = `/admin/custom-fields/${id}`;
            form.submit();
        }
    }
</script>
@endsection
