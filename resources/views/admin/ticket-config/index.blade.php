@extends('layouts.app')
@section('title', 'Field Sistem Tiket')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    {{-- Header Modul Induk Konfigurasi --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/20">
                    @include('partials.icon', ['name' => 'sliders', 'class' => 'w-3 h-3 text-[#114E84]'])
                    Customized
                </span>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs font-semibold text-slate-600">Field Sistem Tiket</span>
            </div>
            <h1 class="text-2xl font-bold text-ink flex items-center gap-2.5">
                @include('partials.icon', ['name' => 'sliders', 'class' => 'w-6 h-6 text-[#114E84]'])
                Field Sistem Tiket
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Atur master kategori pengajuan, target SLA penyelesaian, serta visibilitas dan label seluruh field tabel dan formulir tiket tanpa hardcode.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('tickets.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-[#114E84]'])
                <span>Lihat Modul Tiket</span>
            </a>

            @if ($activeTab === 'kategori')
                <button type="button" onclick="openCreateCategoryModal()"
                        class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-4 py-2 rounded-xl shadow transition">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-white'])
                    <span>Tambah Kategori Baru</span>
                </button>
            @else
                <button type="button" onclick="openCreateFieldModal()"
                        class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-4 py-2 rounded-xl shadow transition">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-white'])
                    <span>Tambah Field Kustom</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Tab Switcher --}}
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('konfigurasi.tiket.index', ['tab' => 'kategori']) }}"
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2.5 {{ $activeTab === 'kategori' ? 'bg-[#114E84] text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:text-ink' }}">
            @include('partials.icon', ['name' => 'sliders', 'class' => 'w-4 h-4 ' . ($activeTab === 'kategori' ? 'text-amber-300' : 'text-slate-400')])
            <span>Kategori Tiket &amp; SLA</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $activeTab === 'kategori' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                {{ $categoryStats['total'] }}
            </span>
        </a>

        <a href="{{ route('konfigurasi.tiket.index', ['tab' => 'fields']) }}"
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2.5 {{ $activeTab === 'fields' ? 'bg-[#114E84] text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:text-ink' }}">
            @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 ' . ($activeTab === 'fields' ? 'text-amber-300' : 'text-slate-400')])
            <span>Field Formulir &amp; Tabel Tiket</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $activeTab === 'fields' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                {{ $fieldStats['total'] }}
            </span>
        </a>
    </div>

    @if ($activeTab === 'kategori')
        {{-- ======================================================== --}}
        {{-- TAB 1: KATEGORI TIKET & SLA                              --}}
        {{-- ======================================================== --}}
        
        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
                <span class="text-xs text-slate-400 block font-medium">Total Kategori</span>
                <span class="text-2xl font-bold text-ink font-mono mt-1 block">{{ $categoryStats['total'] }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
                <span class="text-xs text-slate-400 block font-medium">Kategori Permintaan</span>
                <span class="text-2xl font-bold text-sky-700 font-mono mt-1 block">{{ $categoryStats['permintaan'] }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
                <span class="text-xs text-slate-400 block font-medium">Kategori Permasalahan</span>
                <span class="text-2xl font-bold text-orange-600 font-mono mt-1 block">{{ $categoryStats['permasalahan'] }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
                <span class="text-xs text-slate-400 block font-medium">Kategori Aktif</span>
                <span class="text-2xl font-bold text-emerald-600 font-mono mt-1 block">{{ $categoryStats['aktif'] }}</span>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-4">
            <form method="GET" action="{{ route('konfigurasi.tiket.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="hidden" name="tab" value="kategori">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama kategori..."
                           class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84]">
                </div>
                <div>
                    <select name="jenis_pengajuan" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs focus:border-[#114E84]">
                        <option value="">-- Semua Jenis Pengajuan --</option>
                        <option value="Permintaan" {{ request('jenis_pengajuan') === 'Permintaan' ? 'selected' : '' }}>Permintaan</option>
                        <option value="Permasalahan" {{ request('jenis_pengajuan') === 'Permasalahan' ? 'selected' : '' }}>Permasalahan</option>
                    </select>
                </div>
                <div>
                    <select name="department_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs focus:border-[#114E84]">
                        <option value="">-- Semua Bagian --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold py-2 px-3 rounded-xl transition">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'jenis_pengajuan', 'department_id']))
                        <a href="{{ route('konfigurasi.tiket.index', ['tab' => 'kategori']) }}" class="text-xs text-slate-500 hover:text-slate-800 px-2 py-2">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Categories Table --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4">Nama Kategori</th>
                            <th class="py-3.5 px-4">Jenis Pengajuan</th>
                            <th class="py-3.5 px-4">Target SLA Resolusi</th>
                            <th class="py-3.5 px-4">Bagian Terkait</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($categories as $cat)
                            <tr class="hover:bg-slate-50/60 transition {{ !$cat->is_active ? 'opacity-60 bg-slate-50/30' : '' }}">
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-ink text-sm block">{{ $cat->name }}</span>
                                    <span class="text-[11px] text-slate-400">Urutan: {{ $cat->sort_order }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($cat->jenis_pengajuan === 'Permintaan')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                            Permintaan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-orange-50 text-orange-700 border border-orange-200">
                                            Permasalahan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold text-ink">{{ $cat->sla_resolution_hours }}</span> Jam Kerja
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($cat->department)
                                        <span class="inline-flex items-center gap-1 font-medium text-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#114E84]"></span>
                                            {{ $cat->department->name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">Umum / Semua Bagian</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="{{ route('konfigurasi.tiket.categories.toggle', $cat) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium transition {{ $cat->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}" title="Klik untuk toggle status">
                                            {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button"
                                                onclick="openEditCategoryModal({{ json_encode([
                                                    'id' => $cat->id,
                                                    'name' => $cat->name,
                                                    'jenis_pengajuan' => $cat->jenis_pengajuan,
                                                    'sla_resolution_hours' => $cat->sla_resolution_hours,
                                                    'department_id' => $cat->department_id,
                                                    'sort_order' => $cat->sort_order,
                                                    'is_active' => $cat->is_active,
                                                ]) }})"
                                                class="text-xs text-amber-700 hover:text-amber-800 font-semibold px-2 py-1 rounded bg-amber-50 hover:bg-amber-100 border border-amber-200 transition">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('konfigurasi.tiket.categories.destroy', $cat) }}"
                                              onsubmit="return confirm('Yakin ingin menghapus kategori \'{{ $cat->name }}\'?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-700 font-semibold px-2 py-1 rounded bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-slate-400">
                                    Tidak ada data kategori tiket yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>

    @else
        {{-- ======================================================== --}}
        {{-- TAB 2: FIELD FORMULIR & TABEL TIKET                      --}}
        {{-- ======================================================== --}}

        {{-- Stats Cards Fields --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
                <span class="text-xs text-slate-400 block font-medium">Total Field Tiket</span>
                <span class="text-2xl font-bold text-ink font-mono mt-1 block">{{ $fieldStats['total'] }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
                <span class="text-xs text-slate-400 block font-medium">Tampil di Tabel List</span>
                <span class="text-2xl font-bold text-sky-700 font-mono mt-1 block">{{ $fieldStats['di_tabel'] }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
                <span class="text-xs text-slate-400 block font-medium">Tampil di Form Input</span>
                <span class="text-2xl font-bold text-indigo-700 font-mono mt-1 block">{{ $fieldStats['di_form'] }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
                <span class="text-xs text-slate-400 block font-medium">Field Kustom</span>
                <span class="text-2xl font-bold text-emerald-600 font-mono mt-1 block">{{ $fieldStats['kustom'] }}</span>
            </div>
        </div>

        {{-- Tabel Pengaturan Field Tiket --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
            <form id="reorderTicketFieldsForm" method="POST" action="{{ route('konfigurasi.tiket.fields.reorder') }}">
                @csrf
                <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-sm text-ink flex items-center gap-2">
                            @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-[#114E84]'])
                            Daftar Field Sistem Tiket ({{ $fields->count() }} field)
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Atur urutan nomor, ubah label tampilan, serta aktifkan/nonaktifkan visibilitas field pada tabel list &amp; formulir pemohon.
                        </p>
                    </div>

                    @if ($fields->isNotEmpty())
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition border border-slate-200">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                            Simpan Urutan
                        </button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider text-[11px] font-bold">
                                <th class="py-3 px-3 text-center w-16">Urutan</th>
                                <th class="py-3 px-4">Label / Field Name</th>
                                <th class="py-3 px-4">Tipe Data</th>
                                <th class="py-3 px-3 text-center">Wajib Form</th>
                                <th class="py-3 px-3 text-center">Di Form</th>
                                <th class="py-3 px-3 text-center">Di Tabel</th>
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
                                                'text' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                'number' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'date' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                'select' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                'textarea' => 'bg-teal-50 text-teal-700 border-teal-200',
                                                'file' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                                            };
                                        @endphp
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono font-semibold border {{ $typeColor }}">
                                            {{ strtoupper($field->field_type) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($field->is_required)
                                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Wajib</span>
                                        @else
                                            <span class="text-slate-400 text-[11px]">Opsional</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <form method="POST" action="{{ route('konfigurasi.tiket.fields.toggle-form', $field) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2 py-0.5 rounded text-[10.5px] font-semibold transition {{ $field->show_in_form ? 'bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100' : 'bg-slate-100 text-slate-400 border border-slate-200 hover:bg-slate-200' }}" title="Klik untuk toggle tampilan di formulir">
                                                {{ $field->show_in_form ? 'Tampil' : 'Sembunyi' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <form method="POST" action="{{ route('konfigurasi.tiket.fields.toggle-list', $field) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2 py-0.5 rounded text-[10.5px] font-semibold transition {{ $field->show_in_list ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-400 border border-slate-200 hover:bg-slate-200' }}" title="Klik untuk toggle tampilan di tabel list">
                                                {{ $field->show_in_list ? 'Tampil' : 'Sembunyi' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <form method="POST" action="{{ route('konfigurasi.tiket.fields.toggle', $field) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2 py-0.5 rounded text-[10.5px] font-semibold transition {{ $field->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-400 border border-slate-200 hover:bg-slate-200' }}" title="Klik untuk toggle aktif">
                                                {{ $field->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button"
                                                    onclick="openEditFieldModal({{ json_encode([
                                                        'id' => $field->id,
                                                        'field_name' => $field->field_name,
                                                        'label' => $field->label,
                                                        'field_type' => $field->field_type,
                                                        'options' => $field->options ? implode("\n", $field->options) : '',
                                                        'is_required' => $field->is_required,
                                                        'show_in_form' => $field->show_in_form,
                                                        'show_in_list' => $field->show_in_list,
                                                        'sort_order' => $field->sort_order,
                                                        'help_text' => $field->help_text,
                                                        'is_active' => $field->is_active,
                                                        'is_system' => $field->is_system,
                                                    ]) }})"
                                                    class="text-xs text-amber-700 hover:text-amber-800 font-semibold px-2 py-1 rounded bg-amber-50 hover:bg-amber-100 border border-amber-200 transition">
                                                Edit
                                            </button>

                                            @if (!$field->is_system)
                                                <form method="POST" action="{{ route('konfigurasi.tiket.fields.destroy', $field) }}"
                                                      onsubmit="return confirm('Hapus field kustom \'{{ $field->label }}\'?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-700 font-semibold px-2 py-1 rounded bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    @endif
</div>

{{-- ======================================================== --}}
{{-- MODALS                                                   --}}
{{-- ======================================================== --}}

@push('modals')
{{-- Modal Tambah Kategori --}}
<div id="createCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-ink text-base">Tambah Kategori Tiket Baru</h3>
            <button type="button" onclick="closeCreateCategoryModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('konfigurasi.tiket.categories.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Perbaikan Printer &amp; Scan"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Pengajuan <span class="text-rose-500">*</span></label>
                <select name="jenis_pengajuan" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:border-[#114E84]">
                    <option value="Permintaan">Permintaan (Barang, Fasilitas, Sarana)</option>
                    <option value="Permasalahan">Permasalahan (Kerusakan, Kendala Teknis)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Target SLA Resolusi (Jam Kerja) <span class="text-rose-500">*</span></label>
                <input type="number" name="sla_resolution_hours" required min="1" max="720" value="24"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:border-[#114E84] font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Bagian Terkait</label>
                <select name="department_id" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:border-[#114E84]">
                    <option value="">-- Semua Bagian (Fleksibel) --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="create_cat_active" value="1" checked class="rounded text-[#114E84]">
                <label for="create_cat_active" class="text-xs text-slate-700 cursor-pointer">Kategori Langsung Aktif</label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateCategoryModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Kategori --}}
<div id="editCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-ink text-base">Edit Kategori Tiket</h3>
            <button type="button" onclick="closeEditCategoryModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form id="editCategoryForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="edit_cat_name" required
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Pengajuan <span class="text-rose-500">*</span></label>
                <select name="jenis_pengajuan" id="edit_cat_jenis" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:border-[#114E84]">
                    <option value="Permintaan">Permintaan (Barang, Fasilitas, Sarana)</option>
                    <option value="Permasalahan">Permasalahan (Kerusakan, Kendala Teknis)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Target SLA Resolusi (Jam Kerja) <span class="text-rose-500">*</span></label>
                <input type="number" name="sla_resolution_hours" id="edit_cat_sla" required min="1" max="720"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:border-[#114E84] font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Bagian Terkait</label>
                <select name="department_id" id="edit_cat_dept" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:border-[#114E84]">
                    <option value="">-- Semua Bagian (Fleksibel) --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="edit_cat_active" value="1" class="rounded text-[#114E84]">
                <label for="edit_cat_active" class="text-xs text-slate-700 cursor-pointer">Kategori Aktif</label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditCategoryModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Field Kustom Tiket --}}
<div id="createFieldModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-ink text-base">Tambah Field Kustom Tiket</h3>
            <button type="button" onclick="closeCreateFieldModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('konfigurasi.tiket.fields.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Label Field <span class="text-rose-500">*</span></label>
                <input type="text" name="label" required placeholder="Contoh: Lokasi / Gedung Lantai"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84]"
                       oninput="autoSlugTicketField(this.value, 'create_field_name')">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kolom (Slug unik) <span class="text-rose-500">*</span></label>
                <input type="text" name="field_name" id="create_field_name" required placeholder="lokasi_lantai"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84] font-mono text-slate-600">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Input <span class="text-rose-500">*</span></label>
                    <select name="field_type" id="create_field_type" required onchange="toggleFieldOptions('create')"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:border-[#114E84]">
                        <option value="text">Teks Singkat (text)</option>
                        <option value="number">Angka (number)</option>
                        <option value="textarea">Teks Panjang (textarea)</option>
                        <option value="select">Pilihan Dropdown (select)</option>
                        <option value="date">Tanggal (date)</option>
                        <option value="file">Unggah Berkas (file)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan</label>
                    <input type="number" name="sort_order" min="0" placeholder="Otomatis"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:border-[#114E84]">
                </div>
            </div>

            <div id="create_options_wrapper" class="hidden">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilihan Opsi (Satu baris satu pilihan)</label>
                <textarea name="options" rows="3" placeholder="Lantai 1&#10;Lantai 2&#10;Lantai 3"
                          class="w-full border border-slate-200 rounded-xl p-2.5 text-xs focus:border-[#114E84] font-mono"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Petunjuk Pengisian (Help text)</label>
                <input type="text" name="help_text" placeholder="Tuliskan petunjuk untuk pemohon jika diperlukan..."
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84]">
            </div>

            <div class="flex flex-wrap gap-4 pt-1">
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_required" value="1" class="rounded text-[#114E84]">
                    <span>Wajib Diisi</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" name="show_in_form" value="1" checked class="rounded text-[#114E84]">
                    <span>Tampil di Form Input</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" name="show_in_list" value="1" checked class="rounded text-[#114E84]">
                    <span>Tampil di Tabel List</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateFieldModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold">Simpan Field</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Field Tiket --}}
<div id="editFieldModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-ink text-base">Edit Field Tiket</h3>
                <p class="text-[11px] font-mono text-slate-400" id="edit_field_slug_badge">-</p>
            </div>
            <button type="button" onclick="closeEditFieldModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form id="editFieldForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Label Field <span class="text-rose-500">*</span></label>
                <input type="text" name="label" id="edit_field_label" required
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Input</label>
                    <select name="field_type" id="edit_field_type" onchange="toggleFieldOptions('edit')"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:border-[#114E84]">
                        <option value="text">Teks Singkat (text)</option>
                        <option value="number">Angka (number)</option>
                        <option value="textarea">Teks Panjang (textarea)</option>
                        <option value="select">Pilihan Dropdown (select)</option>
                        <option value="date">Tanggal (date)</option>
                        <option value="file">Unggah Berkas (file)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan</label>
                    <input type="number" name="sort_order" id="edit_field_order" min="0"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:border-[#114E84]">
                </div>
            </div>

            <div id="edit_options_wrapper" class="hidden">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilihan Opsi (Satu baris satu pilihan)</label>
                <textarea name="options" id="edit_field_options" rows="3"
                          class="w-full border border-slate-200 rounded-xl p-2.5 text-xs focus:border-[#114E84] font-mono"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Petunjuk Pengisian (Help text)</label>
                <input type="text" name="help_text" id="edit_field_help"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84]">
            </div>

            <div class="flex flex-wrap gap-4 pt-1">
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_required" id="edit_field_required" value="1" class="rounded text-[#114E84]">
                    <span>Wajib Diisi</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" name="show_in_form" id="edit_field_show_form" value="1" class="rounded text-[#114E84]">
                    <span>Tampil di Form Input</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" name="show_in_list" id="edit_field_show_list" value="1" class="rounded text-[#114E84]">
                    <span>Tampil di Tabel List</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_active" id="edit_field_active" value="1" class="rounded text-[#114E84]">
                    <span>Status Aktif</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditFieldModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openCreateCategoryModal() {
        document.getElementById('createCategoryModal').classList.remove('hidden');
        document.getElementById('createCategoryModal').classList.add('flex');
    }
    function closeCreateCategoryModal() {
        document.getElementById('createCategoryModal').classList.add('hidden');
        document.getElementById('createCategoryModal').classList.remove('flex');
    }
    function openEditCategoryModal(cat) {
        const form = document.getElementById('editCategoryForm');
        form.action = '/konfigurasi/tiket/categories/' + cat.id;
        document.getElementById('edit_cat_name').value = cat.name;
        document.getElementById('edit_cat_jenis').value = cat.jenis_pengajuan;
        document.getElementById('edit_cat_sla').value = cat.sla_resolution_hours;
        document.getElementById('edit_cat_dept').value = cat.department_id || '';
        document.getElementById('edit_cat_active').checked = !!cat.is_active;

        document.getElementById('editCategoryModal').classList.remove('hidden');
        document.getElementById('editCategoryModal').classList.add('flex');
    }
    function closeEditCategoryModal() {
        document.getElementById('editCategoryModal').classList.add('hidden');
        document.getElementById('editCategoryModal').classList.remove('flex');
    }

    function openCreateFieldModal() {
        document.getElementById('createFieldModal').classList.remove('hidden');
        document.getElementById('createFieldModal').classList.add('flex');
    }
    function closeCreateFieldModal() {
        document.getElementById('createFieldModal').classList.add('hidden');
        document.getElementById('createFieldModal').classList.remove('flex');
    }
    function openEditFieldModal(f) {
        const form = document.getElementById('editFieldForm');
        form.action = '/konfigurasi/tiket/fields/' + f.id;
        document.getElementById('edit_field_slug_badge').textContent = 'Field: ' + f.field_name;
        document.getElementById('edit_field_label').value = f.label;
        document.getElementById('edit_field_type').value = f.field_type;
        document.getElementById('edit_field_order').value = f.sort_order;
        document.getElementById('edit_field_help').value = f.help_text || '';
        document.getElementById('edit_field_options').value = f.options || '';
        document.getElementById('edit_field_required').checked = !!f.is_required;
        document.getElementById('edit_field_show_form').checked = !!f.show_in_form;
        document.getElementById('edit_field_show_list').checked = !!f.show_in_list;
        document.getElementById('edit_field_active').checked = !!f.is_active;

        if (f.is_system) {
            document.getElementById('edit_field_type').disabled = true;
        } else {
            document.getElementById('edit_field_type').disabled = false;
        }

        toggleFieldOptions('edit');

        document.getElementById('editFieldModal').classList.remove('hidden');
        document.getElementById('editFieldModal').classList.add('flex');
    }
    function closeEditFieldModal() {
        document.getElementById('editFieldModal').classList.add('hidden');
        document.getElementById('editFieldModal').classList.remove('flex');
    }

    function toggleFieldOptions(prefix) {
        const type = document.getElementById(prefix + '_field_type').value;
        const wrapper = document.getElementById(prefix + '_options_wrapper');
        if (type === 'select') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function autoSlugTicketField(value, targetId) {
        const target = document.getElementById(targetId);
        if (!target) return;
        target.value = value.toLowerCase()
            .trim()
            .replace(/[^a-z0-9_]/g, '_')
            .replace(/_+/g, '_');
    }
</script>
@endpush
@endsection
