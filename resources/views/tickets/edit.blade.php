@extends('layouts.app')
@section('title', 'Verifikasi & Distribusi Tiket ' . $ticket->ticket_number)

@section('content')
    <div class="mb-6">
        <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-brand transition mb-2">
            &larr; Kembali ke Daftar Tiket
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-0.5">
                    @if (auth()->user()->isOperator())
                        Verifikasi &amp; Distribusi Tiket
                    @else
                        Update Status Tiket
                    @endif
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold font-mono text-[#114E84]">{{ $ticket->ticket_number }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $ticket->status_badge }}">
                        {{ $ticket->status }}
                    </span>
                </div>
            </div>
            <a href="{{ route('tickets.show', $ticket) }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition">
                @include('partials.icon', ['name' => 'eye', 'class' => 'w-3.5 h-3.5'])
                Lihat Detail Penuh
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- LEFT: Informasi Permintaan (Read-Only) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                    Informasi Permintaan
                </h2>

                <div class="space-y-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Nomor Tiket</span>
                        <span class="font-bold font-mono text-[#114E84]">{{ $ticket->ticket_number }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Waktu Pengajuan</span>
                        <span class="font-semibold text-slate-700">{{ $ticket->created_at->format('d F Y, H:i') }}</span>
                        <span class="text-[11px] text-slate-400 block">({{ $ticket->created_at->diffForHumans() }})</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Pemohon</span>
                        <span class="font-bold text-ink">{{ $ticket->user?->nama_lengkap ?? '-' }}</span>
                        <span class="text-xs text-slate-500 block">{{ $ticket->user?->bagian ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Kategori Awal</span>
                        <span class="font-semibold text-slate-700">{{ $ticket->category?->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Prioritas Awal</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $ticket->priority_badge }}">
                            {{ $ticket->priority }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium mb-1.5">Uraian / Deskripsi Masalah</span>
                        <div class="text-xs text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-3.5 leading-relaxed whitespace-pre-line max-h-48 overflow-y-auto">
                            {{ $ticket->description }}
                        </div>
                    </div>

                    @if ($ticket->attachment_path)
                        @php
                            $ext = strtolower(pathinfo($ticket->attachment_path, PATHINFO_EXTENSION));
                            $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                            $fileName = basename($ticket->attachment_path);
                        @endphp
                        <div class="pt-3 border-t border-slate-100">
                            <span class="text-xs text-slate-400 block font-medium mb-2">Berkas / Dokumen Lampiran</span>
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-[#114E84] flex-shrink-0">
                                        @include('partials.icon', ['name' => 'file-text', 'class' => 'w-5 h-5'])
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-slate-800 truncate" title="{{ $fileName }}">
                                            {{ $fileName }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 uppercase font-mono">{{ $ext ?: 'File' }} &bull; Lampiran Pemohon</p>
                                    </div>
                                </div>

                                @if ($isImg)
                                    <div class="mb-3 rounded-lg overflow-hidden border border-slate-200 bg-white max-h-48 flex items-center justify-center">
                                        <img src="{{ route('tickets.attachment', $ticket) }}" alt="Pratinjau Lampiran" class="w-full h-auto object-contain max-h-48">
                                    </div>
                                @endif

                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('tickets.attachment', $ticket) }}" target="_blank"
                                       class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-[#114E84] bg-white hover:bg-blue-50 px-3 py-2 rounded-lg border border-blue-200 shadow-2xs transition">
                                        @include('partials.icon', ['name' => 'eye', 'class' => 'w-3.5 h-3.5'])
                                        Buka Berkas
                                    </a>
                                    <a href="{{ route('tickets.attachment.download', $ticket) }}"
                                       class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-white bg-[#114E84] hover:bg-[#0E4272] px-3 py-2 rounded-lg shadow-2xs transition">
                                        @include('partials.icon', ['name' => 'download', 'class' => 'w-3.5 h-3.5'])
                                        Unduh Berkas
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="pt-3 border-t border-slate-100">
                            <span class="text-xs text-slate-400 block font-medium mb-1">Berkas / Dokumen Lampiran</span>
                            <p class="text-xs text-slate-400 italic">Tidak ada lampiran yang diunggah oleh pemohon.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT: Form Aksi Operator --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-1 pb-2 border-b border-slate-100">
                    @if (auth()->user()->isOperator())
                        Form Verifikasi &amp; Distribusi
                    @else
                        Perbarui Status Tiket
                    @endif
                </h2>
                <p class="text-xs text-slate-400 mb-5">
                    @if (auth()->user()->isOperator())
                        Tetapkan kategori yang tepat, tingkat prioritas, dan delegasikan ke departemen yang bertanggung jawab.
                    @else
                        Perbarui status pengerjaan tiket ini untuk pemohon.
                    @endif
                </p>

                @if ($errors->any())
                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 mb-5">
                        <p class="text-xs font-bold text-rose-700 mb-1">Terdapat kesalahan validasi:</p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li class="text-xs text-rose-600">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    @if (auth()->user()->isOperator())
                        {{-- Status Otomatis Dialokasikan (Tanpa Dropdown) --}}
                        <input type="hidden" name="status" value="Dialokasikan">
                        <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-[#114E84] text-white flex items-center justify-center flex-shrink-0 shadow-2xs">
                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-white'])
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-[#114E84]">Status Alokasi Tiket</p>
                                    <p class="text-[11px] text-slate-500">Status otomatis ditetapkan ke <strong>Dialokasikan</strong> saat tombol di bawah ditekan.</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                Dialokasikan
                            </span>
                        </div>

                        {{-- Prioritas --}}
                        <div>
                            <label for="priority" class="block text-sm font-bold text-slate-700 mb-1.5">
                                Tingkat Prioritas <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach ([
                                    'Rendah'  => ['color' => 'emerald', 'desc' => 'SLA 72 Jam'],
                                    'Sedang'  => ['color' => 'blue',    'desc' => 'SLA 48 Jam'],
                                    'Tinggi'  => ['color' => 'orange',  'desc' => 'SLA 12 Jam'],
                                    'Kritis'  => ['color' => 'rose',    'desc' => 'SLA 4 Jam'],
                                ] as $pval => $popt)
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="priority" value="{{ $pval }}" class="sr-only peer"
                                               {{ old('priority', $ticket->priority) === $pval ? 'checked' : '' }}>
                                        <div class="border-2 rounded-xl p-3 text-center transition
                                            border-slate-200 bg-slate-50 peer-checked:border-{{ $popt['color'] }}-500 peer-checked:bg-{{ $popt['color'] }}-50">
                                            <p class="text-xs font-bold text-slate-700 peer-checked:text-{{ $popt['color'] }}-700 {{ old('priority', $ticket->priority) === $pval ? 'text-'.$popt['color'].'-700' : '' }}">
                                                {{ $pval }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $popt['desc'] }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('priority')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Teruskan ke Departemen --}}
                        <div>
                            <label for="department_id" class="block text-sm font-bold text-slate-700 mb-1.5">
                                Teruskan Ke (Bagian Pelaksana) <span class="text-rose-500">*</span>
                            </label>
                            <div class="space-y-2">
                                @foreach ($departments as $dept)
                                    <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition
                                        {{ old('department_id', $ticket->department_id) == $dept->id ? 'border-[#114E84] bg-blue-50' : 'border-slate-200 hover:border-slate-300 bg-slate-50' }}">
                                        <input type="radio" name="department_id" value="{{ $dept->id }}" class="mt-0.5 accent-[#114E84]"
                                               {{ old('department_id', $ticket->department_id) == $dept->id ? 'checked' : '' }} required>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ $dept->name }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('department_id')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jenis Pengajuan (Permintaan / Permasalahan) --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                                Jenis Pengajuan
                                <span class="text-xs text-slate-400 font-normal ml-1">(opsional — diisi saat verifikasi)</span>
                            </label>
                            <div class="flex gap-3">
                                @foreach (['Permintaan' => 'sky', 'Permasalahan' => 'orange'] as $jenis => $color)
                                    <label class="flex-1 relative cursor-pointer">
                                        <input type="radio" name="jenis_pengajuan" value="{{ $jenis }}" class="sr-only peer"
                                               {{ old('jenis_pengajuan', $ticket->jenis_pengajuan) === $jenis ? 'checked' : '' }}>
                                        <div class="border-2 rounded-xl p-3 text-center transition
                                            border-slate-200 bg-slate-50 peer-checked:border-{{ $color }}-500 peer-checked:bg-{{ $color }}-50">
                                            <p class="text-xs font-bold text-slate-700">{{ $jenis }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('jenis_pengajuan')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        {{-- Dropdown Status untuk Internal Staff --}}
                        <div>
                            <label for="status" class="block text-sm font-bold text-slate-700 mb-1.5">
                                Status Tiket <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:border-brand transition">
                                @foreach (['Dalam Proses', 'Selesai'] as $st)
                                    <option value="{{ $st }}" {{ old('status', $ticket->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    {{-- Catatan Operator / Petugas --}}
                    <div>
                        <label for="notes" class="block text-sm font-bold text-slate-700 mb-1.5">
                            @if (auth()->user()->isOperator())
                                Catatan Operator
                                <span class="text-xs text-slate-400 font-normal ml-1">(opsional — terlihat oleh pemohon &amp; bagian penerima)</span>
                            @else
                                Catatan Pengerjaan
                                <span class="text-xs text-slate-400 font-normal ml-1">(opsional)</span>
                            @endif
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  placeholder="Contoh: Segera ditindaklanjuti, komputer tidak bisa menyala sejak pagi. Kontak: Ibu Ani, Ext. 012."
                                  class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:border-brand transition">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-3">
                        @if (auth()->user()->isOperator())
                            <button type="submit" id="btnDistribute"
                                    class="w-full sm:w-auto bg-gradient-to-r from-[#114E84] to-[#0E4272] hover:from-[#0E4272] hover:to-[#0A335A] text-white text-sm font-bold px-8 py-3 rounded-xl shadow-md transition duration-200 flex items-center justify-center gap-2">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                                Verifikasi &amp; Alokasikan ke Bagian
                            </button>
                        @else
                            <button type="submit"
                                    class="w-full sm:w-auto bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow transition">
                                Simpan Perubahan
                            </button>
                        @endif
                        <a href="{{ route('tickets.show', $ticket) }}"
                           class="text-sm text-slate-500 hover:text-slate-800 font-medium transition">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

