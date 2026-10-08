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
                <div class="flex items-center justify-between mb-1.5">
                    <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Deskripsi / Uraian Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <span id="char-counter" class="text-[11px] font-mono font-semibold text-slate-400">
                        <span id="char-count">0</span> / 100 karakter minimal
                    </span>
                </div>
                <textarea name="description" id="description" rows="5" required minlength="100"
                          placeholder="Jelaskan kebutuhan, lokasi, kendala spesifik, atau perincian barang/jasa secara rinci (minimal 100 karakter)..."
                          oninput="updateCharCount(this)"
                          class="w-full border border-slate-300 rounded-xl p-3.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition leading-relaxed @error('description') border-rose-400 @enderror">{{ old('description', request('description')) }}</textarea>
                <div class="flex items-center justify-between mt-1">
                    @error('description')
                        <p class="text-rose-500 text-xs">{{ $message }}</p>
                    @else
                        <p class="text-[11px] text-slate-400">Mohon uraikan secara detail agar petugas dapat memahami dan segera menindaklanjuti.</p>
                    @enderror
                </div>
            </div>

            {{-- 5. Lampiran Multiple Berkas PDF --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label for="attachments" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Dokumen Lampiran Pendukung
                    </label>
                    <span class="text-[11px] text-slate-400">Khusus berkas <strong>PDF</strong> &bull; Maks 100 MB / file</span>
                </div>

                <div id="drop-zone" class="border-2 border-dashed border-slate-300 hover:border-[#114E84] rounded-2xl p-6 text-center bg-slate-50/60 hover:bg-blue-50/20 transition cursor-pointer"
                     onclick="document.getElementById('attachments').click()">
                    <div class="w-12 h-12 mx-auto mb-2 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center shadow-2xs">
                        @include('partials.icon', ['name' => 'file-text', 'class' => 'w-6 h-6'])
                    </div>
                    <p class="text-xs font-bold text-slate-800">
                        Klik untuk memilih atau seret (drag &amp; drop) berkas PDF ke sini
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Dapat memilih lebih dari 1 file sekaligus atau menambahkan satu per satu (Maksimal 100 MB per file)
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
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Berkas Terpilih (<span id="file-count">0</span>):</p>
                        <button type="button" onclick="clearAllFiles()" class="text-[11px] text-rose-600 hover:text-rose-800 font-semibold hover:underline">
                            Hapus Semua
                        </button>
                    </div>
                    <div id="file-list" class="space-y-2"></div>
                </div>
            </div>

            {{-- Dynamic Custom Fields (Tambahan dari Konfigurasi Sistem Tiket) --}}
            @php
                $customFields = isset($formFields) ? $formFields->where('is_system', false) : collect();
            @endphp
            @if ($customFields->isNotEmpty())
                <div class="pt-4 border-t border-slate-100 space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#114E84]"></span>
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Informasi Tambahan</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($customFields as $cf)
                            <div class="{{ in_array($cf->field_type, ['textarea']) ? 'sm:col-span-2' : '' }}">
                                <label for="cf_{{ $cf->field_name }}" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    {{ $cf->label }}
                                    @if ($cf->is_required)
                                        <span class="text-rose-500">*</span>
                                    @endif
                                </label>
                                @if ($cf->field_type === 'textarea')
                                    <textarea name="{{ $cf->field_name }}" id="cf_{{ $cf->field_name }}" rows="3"
                                              {{ $cf->is_required ? 'required' : '' }}
                                              placeholder="{{ $cf->help_text ?? 'Isi ' . $cf->label . '...' }}"
                                              class="w-full border border-slate-300 rounded-xl p-3 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">{{ old($cf->field_name) }}</textarea>
                                @elseif ($cf->field_type === 'select')
                                    <select name="{{ $cf->field_name }}" id="cf_{{ $cf->field_name }}"
                                            {{ $cf->is_required ? 'required' : '' }}
                                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-ink bg-white focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                                        <option value="">-- Pilih {{ $cf->label }} --</option>
                                        @foreach ($cf->options ?? [] as $opt)
                                            <option value="{{ $opt }}" {{ old($cf->field_name) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($cf->field_type === 'number')
                                    <input type="number" name="{{ $cf->field_name }}" id="cf_{{ $cf->field_name }}"
                                           value="{{ old($cf->field_name) }}"
                                           {{ $cf->is_required ? 'required' : '' }}
                                           placeholder="{{ $cf->help_text ?? 'Isi angka...' }}"
                                           class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                                @elseif ($cf->field_type === 'date')
                                    <input type="date" name="{{ $cf->field_name }}" id="cf_{{ $cf->field_name }}"
                                           value="{{ old($cf->field_name) }}"
                                           {{ $cf->is_required ? 'required' : '' }}
                                           class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                                @else
                                    <input type="text" name="{{ $cf->field_name }}" id="cf_{{ $cf->field_name }}"
                                           value="{{ old($cf->field_name) }}"
                                           {{ $cf->is_required ? 'required' : '' }}
                                           placeholder="{{ $cf->help_text ?? 'Isi ' . $cf->label . '...' }}"
                                           class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                                @endif
                                @if ($cf->help_text)
                                    <p class="text-[11px] text-slate-400 mt-1">{{ $cf->help_text }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

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

        // Progressive File Queue menggunakan DataTransfer
        let fileQueue = new DataTransfer();

        function addFilesToQueue(files) {
            if (!files || files.length === 0) return;

            Array.from(files).forEach(file => {
                // Hindari duplikasi file dengan nama dan ukuran yang sama persis
                const alreadyExists = Array.from(fileQueue.files).some(
                    f => f.name === file.name && f.size === file.size
                );
                if (!alreadyExists) {
                    fileQueue.items.add(file);
                }
            });

            syncInputAndRender();
        }

        function onFilesSelected(input) {
            if (input.files && input.files.length > 0) {
                addFilesToQueue(input.files);
            }
            // Reset input value agar user bisa memilih file yang sama atau menambah file baru lagi
            input.value = '';
        }

        function removeQueuedFile(index) {
            const nextQueue = new DataTransfer();
            Array.from(fileQueue.files).forEach((file, i) => {
                if (i !== index) {
                    nextQueue.items.add(file);
                }
            });
            fileQueue = nextQueue;
            syncInputAndRender();
        }

        function clearAllFiles() {
            fileQueue = new DataTransfer();
            syncInputAndRender();
        }

        function syncInputAndRender() {
            const input = document.getElementById('attachments');
            input.files = fileQueue.files;

            const container = document.getElementById('file-list-container');
            const list = document.getElementById('file-list');
            const countSpan = document.getElementById('file-count');
            list.innerHTML = '';

            const total = fileQueue.files.length;
            countSpan.textContent = total;

            if (total === 0) {
                container.classList.add('hidden');
                document.getElementById('btn-submit').disabled = false;
                return;
            }

            container.classList.remove('hidden');
            let hasError = false;

            Array.from(fileQueue.files).forEach((file, index) => {
                const isPdf = file.name.toLowerCase().endsWith('.pdf') || file.type === 'application/pdf';
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                const isTooLarge = file.size > (100 * 1024 * 1024);

                const item = document.createElement('div');
                item.className = 'flex items-center justify-between p-3 rounded-xl border text-xs transition ' + 
                    (!isPdf || isTooLarge ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-slate-50 border-slate-200 text-slate-800');

                item.innerHTML = `
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-[10px] flex-shrink-0 ${!isPdf || isTooLarge ? 'bg-rose-200 text-rose-800' : 'bg-red-100 text-red-700'}">
                            PDF
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-800 truncate" title="${file.name}">${file.name}</p>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                ${sizeMB} MB
                                ${isTooLarge ? '<span class="text-rose-600 font-bold">&bull; Melebihi batas 100MB!</span>' : ''}
                                ${!isPdf ? '<span class="text-rose-600 font-bold">&bull; Bukan file PDF!</span>' : ''}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full ${!isPdf || isTooLarge ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}">
                            ${!isPdf || isTooLarge ? 'Tidak Valid' : 'Siap Diunggah'}
                        </span>
                        <button type="button" onclick="removeQueuedFile(${index})"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-100/70 transition"
                                title="Hapus berkas ini dari antrean">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                `;

                if (!isPdf || isTooLarge) {
                    hasError = true;
                }

                list.appendChild(item);
            });

            document.getElementById('btn-submit').disabled = hasError;
        }

        // Drag & Drop Listener pada Drop Zone
        const dropZone = document.getElementById('drop-zone');
        if (dropZone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.add('border-[#114E84]', 'bg-blue-50/50');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.remove('border-[#114E84]', 'bg-blue-50/50');
                });
            });

            dropZone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length > 0) {
                    addFilesToQueue(dt.files);
                }
            });
        }

        function updateCharCount(textarea) {
            const count = textarea.value.length;
            const countSpan = document.getElementById('char-count');
            const counterDiv = document.getElementById('char-counter');
            if (countSpan) countSpan.textContent = count;

            if (counterDiv) {
                if (count < 100) {
                    counterDiv.className = 'text-[11px] font-mono font-semibold text-rose-500';
                } else {
                    counterDiv.className = 'text-[11px] font-mono font-semibold text-emerald-600';
                }
            }
        }

        // Jalankan saat pertama kali halaman dimuat untuk menangani pre-fill (misal old() / back validation)
        document.addEventListener('DOMContentLoaded', function () {
            const desc = document.getElementById('description');
            if (desc) updateCharCount(desc);

            if (document.getElementById('jenis_pengajuan').value) {
                onJenisPengajuanChange();
            }
        });
    </script>
@endsection
