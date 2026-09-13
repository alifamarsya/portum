@extends('layouts.app')
@section('title', 'Buat Tiket Layanan Baru')

@section('content')
    <div class="max-w-7xl">
        <div class="mb-6">
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-brand transition mb-2">
                &larr; Kembali ke Daftar Tiket
            </a>
            <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-0.5">Formulir Permintaan</p>
            <h1 class="text-2xl font-bold text-ink">Buat Tiket Layanan Baru</h1>
            <p class="text-slate-500 text-xs mt-1">Sampaikan kendala atau kebutuhan operasional Anda. Tiket akan diverifikasi oleh Operator Helpdesk.</p>
        </div>

        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-xl border border-slate-200 shadow-card p-6 space-y-5">
            @csrf

            {{-- Pemohon Info Preview --}}
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row justify-between gap-3 text-xs">
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

            {{-- Jenis Pengajuan --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Jenis Pengajuan <span class="text-rose-500">*</span>
                </label>
                <select name="jenis_pengajuan" class="w-full py-2 px-3 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-brand focus:border-brand">
                    <option value="">-- Semua Jenis --</option>
                    <option value="Permintaan" {{ request('jenis_pengajuan') === 'Permintaan' ? 'selected' : '' }}>Permintaan</option>
                    <option value="Permasalahan" {{ request('jenis_pengajuan') === 'Permasalahan' ? 'selected' : '' }}>Permasalahan</option>
                </select>
            </div>

            {{-- Tingkat Prioritas --}}
            <div>
                <label for="priority" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Tingkat Prioritas <span class="text-rose-500">*</span>
                </label>
                <select name="priority" id="priority" required
                        class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition @error('priority') border-rose-400 @enderror">
                    <option value="Rendah" {{ old('priority') === 'Rendah' ? 'selected' : '' }}>Rendah (SLA 72 Jam)</option>
                    <option value="Sedang" {{ old('priority', 'Sedang') === 'Normal' ? 'selected' : '' }}>Sedang (SLA 48 Jam)</option>
                    <option value="Tinggi" {{ old('priority') === 'Tinggi' ? 'selected' : '' }}>Tinggi (SLA 12 Jam)</option>
                    <option value="Kritis" {{ old('priority') === 'Kritis' ? 'selected' : '' }}>Kritis (SLA 4 Jam)</option>
                </select>
                @error('priority')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi Permintaan --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Deskripsi / Uraian Masalah <span class="text-rose-500">*</span>
                </label>
                <textarea name="description" id="description" rows="5" required
                          placeholder="Jelaskan kebutuhan, lokasi, kendala teknis, atau permintaan barang/jasa secara rinci..."
                          class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition @error('description') border-rose-400 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Lampiran Dokumen / Foto --}}
            <div>
                <label for="attachment" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Lampiran Dokumen / Foto Pendukung <span class="text-xs text-slate-400 font-normal">(Opsional, max 10MB: JPG, PNG, PDF, DOC, ZIP)</span>
                </label>
                <input type="file" name="attachment" id="attachment"
                       class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer border border-slate-300 rounded-lg p-1.5">
                @error('attachment')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit Buttons --}}
            <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
                <button type="submit"
                        class="bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow transition">
                    Kirim Tiket
                </button>
                <a href="{{ route('tickets.index') }}"
                   class="text-sm text-slate-500 hover:text-slate-800 px-4 py-2.5 font-medium transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
