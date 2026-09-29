@extends('layouts.app')
@section('title', 'Ajukan Penghapusan Aset')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header & Breadcrumb --}}
    <div>
        <nav class="flex items-center gap-2 text-xs text-slate-500 mb-2">
            <a href="{{ route('modul.index', 'aset') }}" class="hover:text-brand transition">Inventarisasi Aset</a>
            <span>/</span>
            <span class="text-slate-800 font-medium">Pengajuan Penghapusan</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-ink flex items-center gap-2.5">
                    @include('partials.icon', ['name' => 'trash-2', 'class' => 'w-7 h-7 text-rose-600'])
                    Pengajuan Penghapusan Aset
                </h1>
                <p class="text-xs text-slate-500 mt-1">Data aset tidak langsung dihapus, melainkan menunggu persetujuan dari Kepala Divisi.</p>
            </div>
            <a href="{{ route('modul.index', 'aset') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                &larr; Kembali ke Inventaris
            </a>
        </div>
    </div>

    {{-- Alert Tata Kelola Approval Kadiv --}}
    <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 flex items-start gap-3.5 text-amber-900 text-xs">
        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-amber-700 mt-0.5">
            @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4'])
        </div>
        <div>
            <p class="font-bold text-[13px] mb-0.5">Tata Kelola Penghapusan Aset</p>
            <p class="text-amber-800/90 leading-relaxed">
                Sesuai kebijakan inventarisasi, setiap penghapusan aset inventaris wajib diverifikasi dan disetujui oleh <strong>Kepala Divisi (Kadiv)</strong>. Setelah disetujui, aset akan otomatis dipindahkan ke <strong>Riwayat Aset Terhapus</strong> untuk keperluan audit trail dan tidak lagi muncul di daftar aktif.
            </p>
        </div>
    </div>

    {{-- Ringkasan Informasi Aset Terpilih --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-card">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
            @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-[#114E84]'])
            Informasi Aset yang Diajukan
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
                <span class="block text-[11px] text-slate-500 font-medium">Nama Aset</span>
                <span class="text-sm font-bold text-slate-900 mt-0.5 block">{{ $aset->nama_aset }}</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Kode: <strong>{{ $aset->kode_aset ?? '-' }}</strong></span>
            </div>
            <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
                <span class="block text-[11px] text-slate-500 font-medium">Kategori &amp; Lokasi</span>
                <span class="text-sm font-bold text-slate-900 mt-0.5 block">{{ $aset->kategori ?? '-' }}</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Lokasi: <strong>{{ $aset->lokasi ?? '-' }}</strong></span>
            </div>
            <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
                <span class="block text-[11px] text-slate-500 font-medium">Nilai Perolehan / Kondisi</span>
                <span class="text-sm font-bold text-slate-900 mt-0.5 block">Rp {{ number_format((float) ($aset->nilai_perolehan ?? 0), 0, ',', '.') }}</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Kondisi:
                    <span class="px-2 py-0.5 rounded-md font-semibold text-[10px]
                        @if($aset->kondisi === 'Baik') bg-emerald-100 text-emerald-800
                        @elseif($aset->kondisi === 'Rusak Ringan') bg-amber-100 text-amber-800
                        @else bg-rose-100 text-rose-800 @endif">
                        {{ $aset->kondisi ?? 'Baik' }}
                    </span>
                </span>
            </div>
        </div>
    </div>

    {{-- Form Pengajuan Disposal --}}
    <form action="{{ route('disposal-aset.ajukan.submit', $aset->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-card space-y-5">
        @csrf

        {{-- Alasan Penghapusan (Wajib) --}}
        <div>
            <label for="alasan_penghapusan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Alasan Penghapusan <span class="text-rose-500">*</span>
            </label>
            <textarea id="alasan_penghapusan" name="alasan_penghapusan" rows="4" required
                      placeholder="Jelaskan alasan rinci mengapa aset ini perlu dihapus dari inventaris (contoh: Rusak berat dan biaya perbaikan melebihi nilai ekonomis, habis masa pakai, atau hilang)..."
                      class="w-full text-xs rounded-xl border-slate-300 focus:border-[#114E84] focus:ring focus:ring-blue-100 shadow-2xs placeholder:text-slate-400 p-3 @error('alasan_penghapusan') border-rose-500 @enderror">{{ old('alasan_penghapusan') }}</textarea>
            @error('alasan_penghapusan')
                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-[11px] text-slate-400 mt-1">Alasan ini akan ditinjau langsung oleh Kepala Divisi sebagai pertimbangan persetujuan.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Metode Penghapusan --}}
            <div>
                <label for="metode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Metode Penghapusan
                </label>
                <select id="metode" name="metode"
                        class="w-full text-xs rounded-xl border-slate-300 focus:border-[#114E84] focus:ring focus:ring-blue-100 shadow-2xs p-2.5">
                    <option value="Dimusnahkan" {{ old('metode') === 'Dimusnahkan' ? 'selected' : '' }}>Dimusnahkan (Rusak Berat / Tidak Bernilai)</option>
                    <option value="Dihapusbukukan" {{ old('metode') === 'Dihapusbukukan' ? 'selected' : '' }}>Dihapusbukukan (Write-off Akuntansi)</option>
                    <option value="Dihibahkan" {{ old('metode') === 'Dihibahkan' ? 'selected' : '' }}>Dihibahkan (Pihak Ketiga / CSR)</option>
                    <option value="Dijual" {{ old('metode') === 'Dijual' ? 'selected' : '' }}>Dijual (Lelang / Scrap / Penjualan Terbuka)</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Pilih metode disposal yang sesuai.</p>
            </div>

            {{-- Dokumen Pendukung --}}
            <div>
                <label for="dokumen" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Dokumen Berita Acara / Lampiran (Opsional)
                </label>
                <input type="file" id="dokumen" name="dokumen" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 border border-slate-300 rounded-xl p-1.5">
                <p class="text-[11px] text-slate-400 mt-1">Format: PDF, JPG, PNG (Maksimal 5MB).</p>
                @error('dokumen')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Keterangan Tambahan --}}
        <div>
            <label for="keterangan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Catatan / Keterangan Tambahan
            </label>
            <textarea id="keterangan" name="keterangan" rows="2"
                      placeholder="Informasi pendukung lain jika ada (opsional)..."
                      class="w-full text-xs rounded-xl border-slate-300 focus:border-[#114E84] focus:ring focus:ring-blue-100 shadow-2xs placeholder:text-slate-400 p-3">{{ old('keterangan') }}</textarea>
        </div>

        {{-- Form Actions --}}
        <div class="pt-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-3">
            <a href="{{ route('modul.index', 'aset') }}" class="text-center sm:text-left text-xs font-semibold text-slate-600 hover:text-slate-800 transition">
                Batal
            </a>
            <button type="submit"
                    onclick="return confirm('Kirim pengajuan penghapusan aset ini ke Kepala Divisi?')"
                    class="inline-flex items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition">
                @include('partials.icon', ['name' => 'send', 'class' => 'w-4 h-4'])
                Kirim Pengajuan ke Kepala Divisi
            </button>
        </div>
    </form>
</div>
@endsection
