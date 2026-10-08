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

                {{-- 1. INFORMASI PEMOHON (DROPDOWN BERTINGKAT MASTER) --}}
                <div class="space-y-4">
                    <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#114E84] text-white flex items-center justify-center text-xs">1</span>
                            Data Pemohon (Individu yang Meminta)
                        </h2>
                        <span class="text-[11px] text-slate-400">Pilih divisi/cabang asal &amp; personel</span>
                    </div>

                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3 text-xs text-blue-800">
                        Pilih lokasi asal pemohon, lalu pilih nama personel yang mengajukan permohonan mutasi aset ini.
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="lokasi_asal_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Lokasi Asal <span class="text-rose-500">*</span>
                            </label>
                            <select id="lokasi_asal_id" name="lokasi_asal_id" required
                                    class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white transition">
                                <option value="">-- Pilih Lokasi Asal --</option>
                                @foreach ($masterLokasi as $lokasi)
                                    <option value="{{ $lokasi->id }}" {{ old('lokasi_asal_id') == $lokasi->id ? 'selected' : '' }}>
                                        {{ $lokasi->nama_lokasi }} ({{ $lokasi->tipe }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-[11px] text-slate-400 mt-1 block">Divisi / unit kerja / cabang asal pemohon.</span>
                            @error('lokasi_asal_id')
                                <span class="text-[11px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="pemohon_personel_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Pemohon <span class="text-rose-500">*</span>
                            </label>
                            <select id="pemohon_personel_id" name="pemohon_personel_id" required disabled
                                    class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 transition disabled:opacity-60 disabled:cursor-not-allowed">
                                <option value="">-- Pilih Lokasi Asal Terlebih Dahulu --</option>
                            </select>
                            <span class="text-[11px] text-slate-400 mt-1 block">Nama personel pemohon terdaftar di lokasi asal.</span>
                            @error('pemohon_personel_id')
                                <span class="text-[11px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                            @enderror
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
                </div>

                {{-- 3. RINCIAN PENGISIAN MUTASI ASET --}}
                <div class="space-y-4 pt-2">
                    <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#114E84] text-white flex items-center justify-center text-xs">3</span>
                            Rincian Pengajuan Mutasi Aset
                        </h2>
                        <span class="text-[11px] text-slate-400">Tentukan tujuan dan detail pemindahan aset</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Dropdown Bertingkat: Lokasi Tujuan --}}
                        <div>
                            <label for="lokasi_tujuan_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Lokasi Tujuan <span class="text-rose-500">*</span>
                            </label>
                            <select id="lokasi_tujuan_id" name="lokasi_tujuan_id" required
                                    class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white transition">
                                <option value="">-- Pilih Lokasi Tujuan --</option>
                                @foreach ($masterLokasi as $lokasi)
                                    <option value="{{ $lokasi->id }}" {{ old('lokasi_tujuan_id') == $lokasi->id ? 'selected' : '' }}>
                                        {{ $lokasi->nama_lokasi }} ({{ $lokasi->tipe }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-[11px] text-slate-400 mt-1 block">Divisi / cabang tujuan pemindahan aset.</span>
                            @error('lokasi_tujuan_id')
                                <span class="text-[11px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Dropdown Bertingkat: Penanggung Jawab Baru + Opsi Khusus --}}
                        <div>
                            <label for="penanggung_jawab_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Penanggung Jawab Baru <span class="text-rose-500">*</span>
                            </label>
                            <select id="penanggung_jawab_id" name="penanggung_jawab_id" required disabled
                                    class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 transition disabled:opacity-60 disabled:cursor-not-allowed">
                                <option value="">-- Pilih Lokasi Tujuan Terlebih Dahulu --</option>
                            </select>
                            <span class="text-[11px] text-slate-400 mt-1 block">Pilih personel tujuan atau opsi <em>★ Pemohon (Pindah ke Lokasi Tujuan)</em>.</span>
                            @error('penanggung_jawab_id')
                                <span class="text-[11px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Dynamic Fields Tambahan (Alasan, Dokumen, dsb.) --}}
                        @foreach ($dynamicFields as $df)
                            @continue(!$df->is_active || in_array($df->field_name, ['ke_lokasi', 'ke_penanggung_jawab']))
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

    {{-- JavaScript for Dynamic Asset Info & Cascading Master Dropdowns --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Data Master Lokasi & Personel (client-side untuk respons instan)
            const masterLokasiData = {!! json_encode($masterLokasiJson ?? []) !!};

            // Elements Bagian 1: Data Pemohon
            const selectLokasiAsal = document.getElementById('lokasi_asal_id');
            const selectPemohon = document.getElementById('pemohon_personel_id');

            // Elements Bagian 2: Preview Aset
            const selectAset = document.getElementById('aset_id');
            const previewCard = document.getElementById('assetPreviewCard');
            const previewKode = document.getElementById('previewKode');
            const previewKategori = document.getElementById('previewKategori');
            const previewLokasi = document.getElementById('previewLokasi');
            const previewPj = document.getElementById('previewPj');

            // Elements Bagian 3: Rincian Mutasi (Lokasi Tujuan & Penanggung Jawab)
            const selectLokasiTujuan = document.getElementById('lokasi_tujuan_id');
            const selectPj = document.getElementById('penanggung_jawab_id');

            // 1. Cascading Lokasi Asal -> Pemohon
            function updatePemohonDropdown(selectedLokasiId, defaultVal = null) {
                selectPemohon.innerHTML = '';
                if (!selectedLokasiId) {
                    selectPemohon.disabled = true;
                    selectPemohon.classList.add('bg-slate-50', 'opacity-60', 'cursor-not-allowed');
                    selectPemohon.classList.remove('bg-white');
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = '-- Pilih Lokasi Asal Terlebih Dahulu --';
                    selectPemohon.appendChild(opt);
                    return;
                }

                const lokasi = masterLokasiData.find(l => String(l.id) === String(selectedLokasiId));
                if (!lokasi || !lokasi.personels || lokasi.personels.length === 0) {
                    selectPemohon.disabled = true;
                    selectPemohon.classList.add('bg-slate-50', 'opacity-60', 'cursor-not-allowed');
                    selectPemohon.classList.remove('bg-white');
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = '-- Belum ada personel terdaftar di lokasi ini --';
                    selectPemohon.appendChild(opt);
                    return;
                }

                selectPemohon.disabled = false;
                selectPemohon.classList.remove('bg-slate-50', 'opacity-60', 'cursor-not-allowed');
                selectPemohon.classList.add('bg-white');

                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = '-- Pilih Nama Pemohon --';
                selectPemohon.appendChild(defaultOpt);

                lokasi.personels.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.nama;
                    if (defaultVal && String(p.id) === String(defaultVal)) {
                        opt.selected = true;
                    }
                    selectPemohon.appendChild(opt);
                });
            }

            selectLokasiAsal.addEventListener('change', function () {
                updatePemohonDropdown(this.value, null);
            });

            // 2. Cascading Lokasi Tujuan -> Penanggung Jawab Baru (dengan Opsi Khusus)
            function updatePjDropdown(selectedLokasiId, defaultVal = null) {
                selectPj.innerHTML = '';
                if (!selectedLokasiId) {
                    selectPj.disabled = true;
                    selectPj.classList.add('bg-slate-50', 'opacity-60', 'cursor-not-allowed');
                    selectPj.classList.remove('bg-white');
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = '-- Pilih Lokasi Tujuan Terlebih Dahulu --';
                    selectPj.appendChild(opt);
                    return;
                }

                const lokasi = masterLokasiData.find(l => String(l.id) === String(selectedLokasiId));

                selectPj.disabled = false;
                selectPj.classList.remove('bg-slate-50', 'opacity-60', 'cursor-not-allowed');
                selectPj.classList.add('bg-white');

                // Opsi default
                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = '-- Pilih Penanggung Jawab Baru --';
                selectPj.appendChild(defaultOpt);

                // OPSI KHUSUS: Pemohon (Pindah ke Lokasi Tujuan)
                const specialOpt = document.createElement('option');
                specialOpt.value = '__pemohon_pindah__';
                specialOpt.textContent = '★ Pemohon (Pindah ke Lokasi Tujuan)';
                specialOpt.className = 'font-bold text-[#114E84] bg-blue-50';
                if (defaultVal === '__pemohon_pindah__') {
                    specialOpt.selected = true;
                }
                selectPj.appendChild(specialOpt);

                // Personel di lokasi tujuan
                if (lokasi && lokasi.personels && lokasi.personels.length > 0) {
                    const optGroup = document.createElement('optgroup');
                    optGroup.label = `Personel di ${lokasi.nama}`;
                    lokasi.personels.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = p.nama;
                        if (defaultVal && String(p.id) === String(defaultVal)) {
                            opt.selected = true;
                        }
                        optGroup.appendChild(opt);
                    });
                    selectPj.appendChild(optGroup);
                }
            }

            selectLokasiTujuan.addEventListener('change', function () {
                updatePjDropdown(this.value, null);
            });

            // 3. Asset Preview
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

            // 4. Restore state jika ada input error atau data old()
            const oldLokasiAsal = "{{ old('lokasi_asal_id') }}";
            const oldPemohon = "{{ old('pemohon_personel_id') }}";
            const oldLokasiTujuan = "{{ old('lokasi_tujuan_id') }}";
            const oldPj = "{{ old('penanggung_jawab_id') }}";

            if (oldLokasiAsal) {
                updatePemohonDropdown(oldLokasiAsal, oldPemohon);
            }
            if (oldLokasiTujuan) {
                updatePjDropdown(oldLokasiTujuan, oldPj);
            }
        });
    </script>
@endsection
