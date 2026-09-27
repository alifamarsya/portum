@extends('layouts.app')
@section('title', 'Buat Tiket Layanan Baru')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#114E84] transition mb-2">
                &larr; Kembali ke Daftar Tiket
            </a>
            <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-0.5">Formulir Permintaan Layanan</p>
            <h1 class="text-2xl font-bold text-ink">Buat Tiket Layanan Baru</h1>
            <p class="text-slate-500 text-xs mt-1">Sampaikan kendala atau kebutuhan operasional Anda. Tiket akan diverifikasi oleh Operator Helpdesk dan diteruskan ke Bagian terkait.</p>
        </div>

        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-2xl border border-slate-200 shadow-card p-6 sm:p-8 space-y-6">
            @csrf

            {{-- Pemohon Info Preview --}}
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row justify-between gap-3 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Nama Pemohon:</span>
                    <span class="font-bold text-ink text-sm">{{ auth()->user()->nama_lengkap }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Unit Kerja / Bagian:</span>
                    <span class="font-semibold text-slate-700">{{ auth()->user()->bagian ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Jabatan:</span>
                    <span class="font-semibold text-slate-700">{{ auth()->user()->jabatan ?? '-' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- 1. Jenis Pengajuan --}}
                <div>
                    <label for="jenis_pengajuan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Jenis Pengajuan <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenis_pengajuan" id="jenis_pengajuan" required
                            onchange="onJenisPengajuanChange()"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-ink bg-white focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition @error('jenis_pengajuan') border-rose-400 @enderror">
                        <option value="">-- Pilih Jenis Pengajuan --</option>
                        <option value="Permintaan" {{ old('jenis_pengajuan', request('jenis_pengajuan')) === 'Permintaan' ? 'selected' : '' }}>
                            Permintaan (Kebutuhan Sarana, Barang &amp; Jasa)
                        </option>
                        <option value="Permasalahan" {{ old('jenis_pengajuan', request('jenis_pengajuan')) === 'Permasalahan' ? 'selected' : '' }}>
                            Permasalahan (Kerusakan, Kendala Teknis &amp; Gangguan)
                        </option>
                    </select>
                    @error('jenis_pengajuan')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 2. Kategori Tiket (Dinamis / Dependent) --}}
                <div>
                    <label for="category_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kategori Permintaan / Kendala <span class="text-rose-500">*</span>
                    </label>
                    <select name="category_id" id="category_id" required
                            onchange="onCategoryChange()"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-ink bg-white focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition @error('category_id') border-rose-400 @enderror">
                        <option value="">-- Pilih Jenis Pengajuan Terlebih Dahulu --</option>
                    </select>
                    @error('category_id')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 3. Info Banner SLA Kategori yang Dipilih --}}
            <div id="sla-info-card" class="hidden p-4 rounded-xl bg-gradient-to-r from-blue-50/80 to-indigo-50/60 border border-blue-200/80 transition-all duration-300">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#114E84] text-white flex items-center justify-center flex-shrink-0 shadow-xs mt-0.5">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5 text-white'])
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold text-ink" id="sla-cat-name">-</span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300 font-mono" id="sla-badge">
                                SLA: - Jam Kerja
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mt-1">
                            Tiket pada kategori ini memiliki target resolusi pengerjaan maksimal <strong id="sla-hours-text">-</strong> jam kerja oleh <strong id="sla-dept-name">Bagian Terkait</strong> sejak diverifikasi dan diterima.
                        </p>
                    </div>
                </div>
            </div>

            {{-- 4. Deskripsi / Uraian Masalah --}}
            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Deskripsi / Uraian Lengkap <span class="text-rose-500">*</span>
                </label>
                <textarea name="description" id="description" rows="5" required
                          placeholder="Jelaskan kebutuhan, lokasi, kendala spesifik, atau perincian barang/jasa secara detail..."
                          class="w-full border border-slate-300 rounded-xl p-3.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition leading-relaxed @error('description') border-rose-400 @enderror">{{ old('description', request('description')) }}</textarea>
                @error('description')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 5. Lampiran Multiple Berkas PDF --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label for="attachments" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Dokumen Lampiran Pendukung
                    </label>
                    <span class="text-[11px] text-slate-400">Khusus berkas <strong>PDF</strong> &bull; Maks 100 MB / file</span>
                </div>

                <div class="border-2 border-dashed border-slate-300 hover:border-[#114E84] rounded-2xl p-5 text-center bg-slate-50/60 hover:bg-blue-50/20 transition cursor-pointer"
                     onclick="document.getElementById('attachments').click()">
                    <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                        @include('partials.icon', ['name' => 'file-text', 'class' => 'w-5 h-5'])
                    </div>
                    <p class="text-xs font-semibold text-slate-700">
                        Klik untuk memilih satu atau beberapa dokumen PDF
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Dapat mengunggah lebih dari 1 file PDF sekaligus (Maksimal 100 MB per file)
                    </p>
                    <input type="file" name="attachments[]" id="attachments" multiple accept=".pdf"
                           class="hidden" onchange="onFilesSelected(this)">
                </div>

                @error('attachments')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('attachments.*')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                {{-- File Selected Preview List --}}
                <div id="file-list-container" class="hidden space-y-2 pt-2">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Berkas Terpilih:</p>
                    <div id="file-list" class="space-y-1.5"></div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
                <button type="submit" id="btn-submit"
                        class="bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold px-7 py-3 rounded-xl shadow-md hover:shadow-lg transition">
                    Kirim Tiket Permintaan
                </button>
                <a href="{{ route('tickets.index') }}"
                   class="text-xs text-slate-500 hover:text-slate-800 px-4 py-3 font-semibold transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Data Kategori Pre-rendered dari Server untuk Akses Seketika (Tanpa Lag AJAX) --}}
    @php
        $categoriesData = $categories->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'jenis_pengajuan' => $c->jenis_pengajuan,
                'sla_resolution_hours' => $c->sla_resolution_hours,
                'department_id' => $c->department_id,
                'department_name' => $c->department?->name ?? 'Bagian Terkait',
            ];
        })->values();
    @endphp
    <script>
        const allCategories = {!! json_encode($categoriesData) !!};

        const preselectedCategoryId = "{{ old('category_id', request('category_id')) }}";

        function onJenisPengajuanChange() {
            const jenis = document.getElementById('jenis_pengajuan').value;
            const catSelect = document.getElementById('category_id');

            catSelect.innerHTML = '';

            if (!jenis) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- Pilih Jenis Pengajuan Terlebih Dahulu --';
                catSelect.appendChild(opt);
                document.getElementById('sla-info-card').classList.add('hidden');
                return;
            }

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Pilih Kategori ' + jenis + ' --';
            catSelect.appendChild(defaultOpt);

            const filtered = allCategories.filter(c => c.jenis_pengajuan === jenis);

            filtered.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name + ' (SLA: ' + c.sla_resolution_hours + ' Jam)';
                opt.dataset.sla = c.sla_resolution_hours;
                opt.dataset.name = c.name;
                opt.dataset.dept = c.department_name;
                if (preselectedCategoryId && preselectedCategoryId == c.id) {
                    opt.selected = true;
                }
                catSelect.appendChild(opt);
            });

            onCategoryChange();
        }

        function onCategoryChange() {
            const catSelect = document.getElementById('category_id');
            const selectedOpt = catSelect.options[catSelect.selectedIndex];
            const slaCard = document.getElementById('sla-info-card');

            if (selectedOpt && selectedOpt.value) {
                const sla = selectedOpt.dataset.sla;
                const name = selectedOpt.dataset.name;
                const dept = selectedOpt.dataset.dept;

                document.getElementById('sla-cat-name').textContent = name;
                document.getElementById('sla-badge').textContent = 'SLA: ' + sla + ' Jam Kerja';
                document.getElementById('sla-hours-text').textContent = sla;
                document.getElementById('sla-dept-name').textContent = dept;

                slaCard.classList.remove('hidden');
            } else {
                slaCard.classList.add('hidden');
            }
        }

        function onFilesSelected(input) {
            const container = document.getElementById('file-list-container');
            const list = document.getElementById('file-list');
            list.innerHTML = '';

            if (!input.files || input.files.length === 0) {
                container.classList.add('hidden');
                return;
            }

            container.classList.remove('hidden');
            let hasError = false;

            Array.from(input.files).forEach((file, index) => {
                const isPdf = file.name.toLowerCase().endsWith('.pdf') || file.type === 'application/pdf';
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                const isTooLarge = file.size > (100 * 1024 * 1024);

                const item = document.createElement('div');
                item.className = 'flex items-center justify-between p-2.5 rounded-xl border text-xs ' + 
                    (!isPdf || isTooLarge ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-slate-50 border-slate-200 text-slate-800');

                item.innerHTML = `
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-6 h-6 rounded flex items-center justify-center font-bold text-[10px] ${!isPdf || isTooLarge ? 'bg-rose-200 text-rose-800' : 'bg-red-100 text-red-700'}">PDF</span>
                        <div class="min-w-0">
                            <p class="font-semibold truncate">${file.name}</p>
                            <p class="text-[11px] text-slate-400 font-mono">${sizeMB} MB ${isTooLarge ? '— Melebihi batas 100MB!' : ''} ${!isPdf ? '— Bukan file PDF!' : ''}</p>
                        </div>
                    </div>
                    <span class="text-xs ${!isPdf || isTooLarge ? 'text-rose-600 font-bold' : 'text-emerald-600 font-bold'}">
                        ${!isPdf || isTooLarge ? '✗ Tidak Valid' : '✓ Siap Upload'}
                    </span>
                `;

                if (!isPdf || isTooLarge) {
                    hasError = true;
                }

                list.appendChild(item);
            });

            document.getElementById('btn-submit').disabled = hasError;
            if (hasError) {
                alert('Terdapat file yang bukan PDF atau ukurannya melebihi 100 MB. Harap periksa kembali berkas yang Anda pilih.');
            }
        }

        // Jalankan saat pertama kali halaman dimuat untuk menangani pre-fill (misal old() / back validation)
        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('jenis_pengajuan').value) {
                onJenisPengajuanChange();
            }
        });
    </script>
@endsection
