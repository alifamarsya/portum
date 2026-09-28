@extends('layouts.app')
@section('title', 'Buat Pengajuan Mutasi Aset')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Breadcrumb & Header --}}
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('mutasi-aset.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#114E84] transition mb-1">
                    @include('partials.icon', ['name' => 'chevron-down', 'class' => 'w-3.5 h-3.5 rotate-90'])
                    Kembali ke Daftar Mutasi
                </a>
                <h1 class="text-2xl font-bold text-ink flex items-center gap-2">
                    <span>Formulir Pengajuan Mutasi Aset</span>
                </h1>
            </div>
        </div>

        {{-- Info Alert Alur --}}
        <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-4 mb-6 text-xs text-blue-900 flex items-start gap-3">
            <div class="w-7 h-7 rounded-lg bg-blue-100 text-[#114E84] flex items-center justify-center flex-shrink-0 mt-0.5">
                @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4'])
            </div>
            <div>
                <p class="font-bold text-sm text-[#114E84] mb-0.5">Alur Proses Mutasi Aset</p>
                <p class="text-blue-800 leading-relaxed">
                    Pengajuan: <strong>Diajukan</strong> &rarr; <strong>Pengecekan Operator</strong> &rarr; <strong>Verifikasi Staf Aset</strong> &rarr; <strong>Approval Kabag Aset</strong> &rarr; <strong>Disetujui</strong> &rarr; <strong>Konfirmasi Ditutup oleh Pengaju</strong>. Setelah dikonfirmasi oleh pengaju, data aset resmi diperbarui.
                </p>
            </div>
        </div>

        {{-- Main Form Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
            <form method="POST" action="{{ route('mutasi-aset.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                {{-- 1. INFORMASI PEMOHON (INDIVIDU & AKUN) --}}
                <div class="space-y-4">
                    <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#114E84] text-white flex items-center justify-center text-xs">1</span>
                            Data Pemohon (Individu yang Meminta)
                        </h2>
                        <span class="text-[11px] text-slate-400">1 akun mewakili unit kerja / cabang</span>
                    </div>

                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3 text-xs text-blue-800">
                        Satu akun user login digunakan per divisi/cabang. Silakan isi nama dan jabatan individu yang sebenarnya meminta mutasi aset inventaris ini.
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="nama_pemohon" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Pemohon <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="nama_pemohon" name="nama_pemohon"
                                   value="{{ old('nama_pemohon', auth()->user()->nama_lengkap) }}" required
                                   placeholder="Nama orang yang meminta"
                                   class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <span class="text-[11px] text-slate-400 mt-1 block">Nama individu yang sebenarnya meminta.</span>
                        </div>

                        <div>
                            <label for="jabatan_pemohon" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jabatan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="jabatan_pemohon" name="jabatan_pemohon"
                                   value="{{ old('jabatan_pemohon', auth()->user()->jabatan ?? auth()->user()->unitKerja?->nama ?? '') }}" required
                                   placeholder="Contoh: Staf Operasional / Teller / CS"
                                   class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <span class="text-[11px] text-slate-400 mt-1 block">Jabatan dari individu pemohon.</span>
                        </div>

                        <div>
                            <label for="username_pemohon" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Username Sistem <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="username_pemohon" name="username_pemohon"
                                   value="{{ old('username_pemohon', auth()->user()->username) }}" required
                                   placeholder="Username akun login"
                                   class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50/50 transition">
                            <span class="text-[11px] text-slate-400 mt-1 block">Username akun sistem yang digunakan login.</span>
                        </div>
                    </div>
                </div>

                {{-- 2. PEMILIHAN ASET --}}
                <div class="space-y-4 pt-2">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="w-6 h-6 rounded-full bg-[#114E84] text-white flex items-center justify-center text-xs">2</span>
                        Pilih Aset yang Akan Dipindahkan
                    </h2>

                    <div>
                        <label for="aset_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Aset Inventaris <span class="text-rose-500">*</span>
                        </label>
                        <select id="aset_id" name="aset_id" required
                                class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white transition">
                            <option value="">-- Pilih Aset Inventaris --</option>
                            @foreach ($asets as $ast)
                                <option value="{{ $ast->id }}"
                                        data-kode="{{ $ast->kode_aset ?? '-' }}"
                                        data-kategori="{{ $ast->kategori ?? '-' }}"
                                        data-lokasi="{{ $ast->lokasi ?? '-' }}"
                                        data-pj="{{ $ast->penanggung_jawab ?? '-' }}"
                                        data-kondisi="{{ $ast->kondisi ?? 'Baik' }}"
                                        {{ old('aset_id') == $ast->id ? 'selected' : '' }}>
                                    {{ $ast->nama_aset }} ({{ $ast->kode_aset ?? 'AST-'.$ast->id }}) — Lokasi: {{ $ast->lokasi ?? 'Kantor Pusat' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dynamic Asset Preview Card --}}
                    <div id="assetPreviewCard" class="hidden rounded-xl bg-slate-50 border border-slate-200 p-4 transition-all">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Informasi Aset Saat Ini (Sistem)</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Kode Aset</span>
                                <span id="previewKode" class="font-mono font-semibold text-ink">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Kategori / Kondisi</span>
                                <span id="previewKategori" class="font-semibold text-ink">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Lokasi Sekarang</span>
                                <span id="previewLokasi" class="font-semibold text-blue-700">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Penanggung Jawab</span>
                                <span id="previewPj" class="font-semibold text-blue-700">-</span>
                            </div>
                        </div>
                    </div>
                </div>                {{-- 3. RINCIAN PENGISIAN MUTASI ASET (DYNAMIC FIELDS) --}}
                <div class="space-y-4 pt-2">
                    <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#114E84] text-white flex items-center justify-center text-xs">3</span>
                            Rincian Pengajuan Mutasi Aset
                        </h2>
                        <span class="text-[11px] text-slate-400">Field formulir terintegrasi secara dinamis</span>
                    </div>

                    @if ($dynamicFields->isEmpty())
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                            Belum ada field mutasi aset yang aktif. Hubungi Unit Kerja Administrasi Aset untuk mengaktifkan field formulir mutasi.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($dynamicFields as $df)
                                @continue(!$df->is_active)
                                @php
                                    $isFullWidth = in_array($df->field_type, ['textarea', 'file']);
                                @endphp

                                <div class="{{ $isFullWidth ? 'md:col-span-2' : 'md:col-span-1' }}">
                                    <label for="{{ $df->field_name }}" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        {{ $df->label }}
                                        @if ($df->is_required)
                                            <span class="text-rose-500">*</span>
                                        @else
                                            <span class="text-slate-400 font-normal text-[11px]">(Opsional)</span>
                                        @endif
                                    </label>

                                    @if ($df->field_type === 'select')
                                        <select id="{{ $df->field_name }}" name="{{ $df->field_name }}"
                                                {{ $df->is_required ? 'required' : '' }}
                                                class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white transition">
                                            <option value="">-- Pilih {{ $df->label }} --</option>
                                            @if (!empty($df->options) && is_array($df->options))
                                                @foreach ($df->options as $opt)
                                                    <option value="{{ $opt }}" {{ old($df->field_name) == $opt ? 'selected' : '' }}>
                                                        {{ $opt }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    @elseif ($df->field_type === 'textarea')
                                        <textarea id="{{ $df->field_name }}" name="{{ $df->field_name }}" rows="3"
                                                  {{ $df->is_required ? 'required' : '' }}
                                                  placeholder="{{ $df->help_text ?? 'Isi ' . strtolower($df->label) . '...' }}"
                                                  class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">{{ old($df->field_name) }}</textarea>
                                    @elseif ($df->field_type === 'file')
                                        <input type="file" id="{{ $df->field_name }}" name="{{ $df->field_name }}"
                                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                               {{ $df->is_required ? 'required' : '' }}
                                               class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#114E84] hover:file:bg-blue-100 transition border border-slate-200 rounded-xl p-1 bg-slate-50/50">
                                    @elseif ($df->field_type === 'date')
                                        <input type="date" id="{{ $df->field_name }}" name="{{ $df->field_name }}"
                                               value="{{ old($df->field_name) }}"
                                               {{ $df->is_required ? 'required' : '' }}
                                               class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white transition">
                                    @elseif (in_array($df->field_type, ['number', 'money']))
                                        <input type="number" id="{{ $df->field_name }}" name="{{ $df->field_name }}"
                                               step="{{ $df->field_type === 'money' ? '1' : '0.01' }}"
                                               value="{{ old($df->field_name) }}"
                                               {{ $df->is_required ? 'required' : '' }}
                                               placeholder="{{ $df->field_type === 'money' ? '0 (Nominal Rupiah)' : '0' }}"
                                               class="w-full py-2.5 px-3 text-sm font-mono border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    @elseif ($df->field_type === 'checkbox')
                                        <label class="flex items-center gap-2.5 cursor-pointer p-2.5 rounded-xl border border-slate-200 bg-slate-50/50">
                                            <input type="checkbox" id="{{ $df->field_name }}" name="{{ $df->field_name }}" value="1"
                                                   {{ old($df->field_name) ? 'checked' : '' }}
                                                   class="rounded text-[#114E84] focus:ring-blue-500">
                                            <span class="text-xs font-medium text-slate-700">Ya / Setuju</span>
                                        </label>
                                    @else
                                        <input type="text" id="{{ $df->field_name }}" name="{{ $df->field_name }}"
                                               value="{{ old($df->field_name) }}"
                                               {{ $df->is_required ? 'required' : '' }}
                                               placeholder="{{ $df->help_text ?? 'Masukkan ' . strtolower($df->label) . '...' }}"
                                               class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    @endif

                                    @if ($df->help_text && $df->field_type !== 'textarea')
                                        <span class="text-[11px] text-slate-400 mt-1 block">{{ $df->help_text }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-3">
                    <a href="{{ route('mutasi-aset.index') }}"
                       class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                        Batal
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition duration-200">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                        Kirim Pengajuan Mutasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript for Dynamic Asset Info Autofill --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAset = document.getElementById('aset_id');
            const previewCard = document.getElementById('assetPreviewCard');
            const previewKode = document.getElementById('previewKode');
            const previewKategori = document.getElementById('previewKategori');
            const previewLokasi = document.getElementById('previewLokasi');
            const previewPj = document.getElementById('previewPj');

            function updateAssetPreview() {
                const selected = selectAset.options[selectAset.selectedIndex];
                if (selected && selected.value) {
                    previewKode.textContent = selected.dataset.kode || '-';
                    previewKategori.textContent = (selected.dataset.kategori || '-') + ' (' + (selected.dataset.kondisi || 'Baik') + ')';
                    previewLokasi.textContent = selected.dataset.lokasi || '-';
                    previewPj.textContent = selected.dataset.pj || '-';
                    previewCard.classList.remove('hidden');
                } else {
                    previewCard.classList.add('hidden');
                }
            }

            selectAset.addEventListener('change', updateAssetPreview);
            updateAssetPreview();
        });
    </script>
@endsection
