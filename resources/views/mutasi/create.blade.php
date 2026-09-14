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
                </div>

                {{-- 3. TUJUAN MUTASI --}}
                <div class="space-y-4 pt-2">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="w-6 h-6 rounded-full bg-[#114E84] text-white flex items-center justify-center text-xs">3</span>
                        Tujuan Mutasi &amp; Penanggung Jawab Baru
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="ke_lokasi" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Lokasi Tujuan Pemindahan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="ke_lokasi" name="ke_lokasi" value="{{ old('ke_lokasi') }}" required
                                   placeholder="Contoh: Kantor Cabang Palu, Ruang Rapat Lt.2, dll."
                                   class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <span class="text-[11px] text-slate-400 mt-1 block">Tentukan cabang, unit kerja, atau ruangan tujuan aset.</span>
                        </div>

                        <div>
                            <label for="ke_penanggung_jawab" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Penanggung Jawab Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="ke_penanggung_jawab" name="ke_penanggung_jawab" value="{{ old('ke_penanggung_jawab') }}" required
                                   placeholder="Contoh: Ahmad Fauzi / Pimpinan Cabang Luwuk"
                                   class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <span class="text-[11px] text-slate-400 mt-1 block">Nama pejabat / staf / bagian yang menerima aset.</span>
                        </div>
                    </div>

                    <div>
                        <label for="alasan" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Alasan Pemindahan / Mutasi Aset <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="alasan" name="alasan" rows="3" required
                                  placeholder="Jelaskan secara rinci kebutuhan atau alasan pemindahan aset inventaris ini..."
                                  class="w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">{{ old('alasan') }}</textarea>
                    </div>

                    <div>
                        <label for="dokumen" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Dokumen Pendukung <span class="text-slate-400 font-normal">(Opsional — Berita Acara, Nota Dinas, Foto Aset)</span>
                        </label>
                        <input type="file" id="dokumen" name="dokumen" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#114E84] hover:file:bg-blue-100 transition border border-slate-200 rounded-xl p-1 bg-slate-50/50">
                        <span class="text-[11px] text-slate-400 mt-1 block">Format: PDF, JPG, PNG, DOC/DOCX. Maksimal ukuran file 10MB.</span>
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
