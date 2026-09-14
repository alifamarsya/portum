@extends('layouts.app')
@section('title', 'Dashboard Staf Pengadaan & Pemeliharaan')

@section('content')
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0C3860] via-[#114E84] to-[#1A62A2] p-6 sm:p-8 text-white shadow-lg mb-6">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur-md border border-white/20 text-blue-200 mb-3">
                    @include('partials.icon', ['name' => 'cart', 'class' => 'w-3.5 h-3.5 text-blue-200'])
                    <span>Bagian Pengadaan &amp; Pemeliharaan Aset &bull; Kantor Pusat</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Selamat Datang, {{ $user->nama_lengkap }}
                </h1>
                <p class="text-sm text-blue-100/90 mt-1 max-w-2xl leading-relaxed">
                    Pusat penanganan tugas tiket layanan operasional perorangan serta modul internal pengadaan barang/jasa, pemeliharaan rutin, monitoring kondisi fisik, dan pengawasan aset.
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-white/15 backdrop-blur-md text-white border border-white/20">
                    {{ $user->jabatan ?? ($user->role?->label ?? 'Staf Operasional') }}
                </span>
            </div>
        </div>
    </div>

    {{-- ROW 1: STATS TIKET TUGAS STAF INI --}}
    <div class="mb-2">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-[#114E84]'])
            <span>Tugas Tiket Layanan Saya</span>
            <span class="text-[11px] font-normal lowercase text-slate-400">(khusus penugasan kepada Anda)</span>
        </h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Card 1: Tugas Aktif --}}
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50/40 rounded-2xl p-5 border border-blue-200/80 shadow-2xs group hover:border-[#114E84] transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-900">Tugas Aktif Saya</span>
                    <span class="w-9 h-9 rounded-xl bg-blue-500/15 text-[#114E84] flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-[#114E84] tracking-tight">{{ $ticketStats['tugas_aktif'] }}</p>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] font-semibold text-blue-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-ping"></span>
                    <span>Menunggu &amp; sedang Anda kerjakan</span>
                </div>
            </div>

            {{-- Card 2: Sedang Dikerjakan --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-violet-300 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sedang Diproses</span>
                    <span class="w-9 h-9 rounded-xl bg-violet-50 text-violet-700 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'sliders', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-violet-900 tracking-tight">{{ $ticketStats['sedang_dikerjakan'] }}</p>
                <p class="text-[11px] text-slate-400 mt-2 font-medium">Status tahap: Dalam Proses</p>
            </div>

            {{-- Card 3: Tiket Selesai --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-emerald-300 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tugas Diselesaikan</span>
                    <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-emerald-700 tracking-tight">{{ $ticketStats['selesai'] }}</p>
                <p class="text-[11px] text-slate-400 mt-2 font-medium">Telah diselesaikan oleh Anda</p>
            </div>

            {{-- Card 4: Total Tugas Masuk --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-slate-300 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Ditugaskan</span>
                    <span class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-ink tracking-tight">{{ $ticketStats['total_tugas'] }}</p>
                <p class="text-[11px] text-slate-400 mt-2 font-medium">Keseluruhan beban kerja Anda</p>
            </div>
        </div>
    </div>

    {{-- ROW 2: STATS MODUL OPERASIONAL --}}
    {{-- A. Jika Staf Pengadaan atau SuperAdmin --}}
    @if ($user->hasRole(['uk_pengadaan', 'pengadaan']) || $user->isSuperAdmin())
        <div class="mb-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                @include('partials.icon', ['name' => 'cart', 'class' => 'w-4 h-4 text-[#114E84]'])
                <span>Operasional Unit Kerja Pengadaan Aset &amp; Inventaris</span>
            </h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                {{-- Card 1: SPK Aktif --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-blue-300 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">SPK Aktif / Berjalan</span>
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#114E84] flex items-center justify-center font-bold">
                            @include('partials.icon', ['name' => 'file-text', 'class' => 'w-4 h-4'])
                        </span>
                    </div>
                    <p class="text-2xl font-black text-ink tracking-tight">{{ $spkAktif }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Dari total {{ $totalSpk }} SPK terdata</p>
                </div>

                {{-- Card 2: Reminder Aktif --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-amber-300 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Reminder Jatuh Tempo</span>
                        <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                        </span>
                    </div>
                    <p class="text-2xl font-black text-amber-600 tracking-tight">{{ $reminderAktif }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Jatuh tempo &le; 90 hari ke depan</p>
                </div>

                {{-- Card 3: Penawaran Vendor --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-emerald-300 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Penawaran Vendor</span>
                        <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4'])
                        </span>
                    </div>
                    <p class="text-2xl font-black text-emerald-700 tracking-tight">{{ $penawaranTotal }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Dokumen penawaran vendor</p>
                </div>

                {{-- Card 4: Rencana Kebutuhan --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-indigo-300 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Perencanaan Kebutuhan</span>
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                            @include('partials.icon', ['name' => 'sliders', 'class' => 'w-4 h-4'])
                        </span>
                    </div>
                    <p class="text-2xl font-black text-indigo-700 tracking-tight">{{ $perencanaanTotal }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Daftar rencana kebutuhan aset</p>
                </div>
            </div>
        </div>
    @endif

    {{-- B. Jika Staf Pemeliharaan atau SuperAdmin --}}
    @if ($user->hasRole(['uk_pemeliharaan']) || $user->isSuperAdmin())
        <div class="mb-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                @include('partials.icon', ['name' => 'wrench', 'class' => 'w-4 h-4 text-emerald-600'])
                <span>Operasional Unit Kerja Pemeliharaan &amp; Pengawasan Aset</span>
            </h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                {{-- Card 1: Jadwal Pemeliharaan --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-blue-300 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Jadwal Pemeliharaan</span>
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#114E84] flex items-center justify-center font-bold">
                            @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                        </span>
                    </div>
                    <p class="text-2xl font-black text-[#114E84] tracking-tight">{{ $jadwalTotal }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ $jadwalDirencanakan }} direncanakan &bull; {{ $jadwalDikerjakan }} dikerjakan</p>
                </div>

                {{-- Card 2: Monitoring Kondisi --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-amber-300 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Inspeksi &amp; Monitoring</span>
                        <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4'])
                        </span>
                    </div>
                    <p class="text-2xl font-black text-amber-600 tracking-tight">{{ $monitoringTotal }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Catatan kondisi fisik aset</p>
                </div>

                {{-- Card 3: Pengawasan Penggunaan --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-violet-300 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pengawasan Penggunaan</span>
                        <span class="w-8 h-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center font-bold">
                            @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4'])
                        </span>
                    </div>
                    <p class="text-2xl font-black text-violet-700 tracking-tight">{{ $pengawasanTotal }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Catatan pengawasan pemakaian</p>
                </div>

                {{-- Card 4: Tindak Lanjut Perbaikan --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-rose-300 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tindak Lanjut Perbaikan</span>
                        <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                            @include('partials.icon', ['name' => 'sliders', 'class' => 'w-4 h-4'])
                        </span>
                    </div>
                    <p class="text-2xl font-black text-rose-700 tracking-tight">{{ $tindakLanjutTotal }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ $tindakLanjutAktif }} aktif / dalam proses</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ROW 3: ANTREAN TIKET AKTIF & TABEL OPERASIONAL --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        {{-- 1. ANTREAN TIKET AKTIF STAF INI --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center">
                        @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4'])
                    </div>
                    <div>
                        <h2 class="font-bold text-ink text-base">Antrean Tugas Tiket Anda</h2>
                        <p class="text-xs text-slate-500">Tiket layanan yang didisposisikan oleh Kabag kepada Anda</p>
                    </div>
                </div>
                <a href="{{ route('tickets.index') }}"
                   class="text-xs font-bold text-[#114E84] hover:underline flex items-center gap-1">
                    <span>Lihat Semua</span>
                    @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
                </a>
            </div>

            @if ($antreanTiket->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6 text-emerald-500'])
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Tidak Ada Tugas Tiket Tertunda</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Saat ini seluruh tiket yang ditugaskan kepada Anda telah terselesaikan. Tiket baru akan muncul saat Kepala Bagian melakukan disposisi kerja.
                    </p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($antreanTiket as $t)
                        <div class="p-5 hover:bg-slate-50/60 transition">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <span class="font-mono text-xs font-bold text-[#114E84]">{{ $t->ticket_number }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $t->priority_badge }}">
                                            {{ $t->priority }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $t->status_badge }}">
                                            {{ $t->status }}
                                        </span>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">
                                        Kategori: {{ $t->category?->name ?? 'Pengadaan & Pemeliharaan' }}
                                    </h3>
                                </div>
                                <span class="text-[11px] text-slate-400 font-mono whitespace-nowrap">
                                    {{ $t->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-600 mb-2.5">
                                Pemohon: <strong class="text-slate-800 font-semibold">{{ $t->user?->nama_lengkap ?? $t->user?->username }}</strong>
                                <span class="text-slate-400">({{ $t->user?->bagian ?? 'Unit Kerja Pemohon' }})</span>
                            </p>

                            <div class="text-xs text-slate-700 bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-3 leading-relaxed">
                                <span class="font-bold text-slate-800">Uraian Masalah:</span> {{ Str::limit($t->description, 140) }}
                            </div>

                            @if ($t->disposition_notes)
                                <div class="p-3 rounded-xl bg-blue-50/80 border border-blue-200/80 text-xs text-[#114E84] mb-3 leading-relaxed flex items-start gap-2">
                                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4 text-[#114E84] flex-shrink-0 mt-0.5'])
                                    <div>
                                        <span class="font-bold">Instruksi &amp; Catatan Kabag Pengadaan:</span>
                                        <p class="text-slate-700 mt-0.5">{{ $t->disposition_notes }}</p>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-3">
                                <div class="text-[11px] text-slate-400">
                                    Didisposisikan oleh: <strong class="text-slate-600">{{ $t->disposedBy?->nama_lengkap ?? 'Kepala Bagian Pengadaan' }}</strong>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if ($t->status === 'Didistribusikan')
                                        <form method="POST" action="{{ route('tickets.update', $t) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="Dalam Proses">
                                            <input type="hidden" name="notes" value="Staf mulai menindaklanjuti permohonan tiket ini.">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold shadow-xs transition">
                                                @include('partials.icon', ['name' => 'sliders', 'class' => 'w-3.5 h-3.5'])
                                                <span>Mulai Kerjakan</span>
                                            </button>
                                        </form>
                                    @elseif ($t->status === 'Dalam Proses')
                                        <form method="POST" action="{{ route('tickets.update', $t) }}" class="inline"
                                              onsubmit="return confirm('Tandai tiket ini telah selesai dikerjakan?')">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="Selesai">
                                            <input type="hidden" name="notes" value="Tugas layanan telah selesai dikerjakan oleh staf dan menunggu konfirmasi pemohon.">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5'])
                                                <span>Tandai Selesai</span>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('tickets.show', $t) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                        <span>Detail Tiket</span>
                                        @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3.5 h-3.5'])
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- 2. OPERASIONAL DETAIL (Pengadaan Reminder / Pemeliharaan Jadwal) --}}
        @if ($user->hasRole(['uk_pemeliharaan']))
            {{-- Tabel Jadwal Pemeliharaan Terkini --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                        </div>
                        <div>
                            <h2 class="font-bold text-ink text-base">Jadwal Pemeliharaan Rutin</h2>
                            <p class="text-xs text-slate-500">Jadwal pemeliharaan aset &amp; inventaris</p>
                        </div>
                    </div>
                    <a href="{{ route('modul.index', 'jadwal_pemeliharaan') }}"
                       class="text-xs font-bold text-[#114E84] hover:underline flex items-center gap-1">
                        <span>Lihat Semua</span>
                        @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3">No. Jadwal &bull; Aset</th>
                                <th class="px-5 py-3">Jenis</th>
                                <th class="px-5 py-3">Rencana</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($jadwalTerbaru as $j)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-3 font-medium">
                                        <span class="font-mono text-slate-500">{{ $j->no_jadwal ?? '-' }}</span><br>
                                        <strong class="text-ink">{{ $j->nama_aset ?? ($j->aset?->nama_aset ?? '-') }}</strong>
                                    </td>
                                    <td class="px-5 py-3">{{ $j->jenis_pemeliharaan ?? '-' }}</td>
                                    <td class="px-5 py-3 font-mono text-slate-600">{{ $j->tanggal_rencana ? $j->tanggal_rencana->format('d M Y') : '-' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold
                                            {{ $j->status === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : ($j->status === 'Dikerjakan' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $j->status ?? 'Direncanakan' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                                        Belum ada jadwal pemeliharaan terdata.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- Tabel Reminder Jatuh Tempo Pengadaan --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                        </div>
                        <div>
                            <h2 class="font-bold text-ink text-base">Reminder Pengadaan (&le; 90 Hari)</h2>
                            <p class="text-xs text-slate-500">Pengingat jatuh tempo kontrak vendor &amp; tindak lanjut</p>
                        </div>
                    </div>
                    <a href="{{ route('modul.index', 'reminder') }}"
                       class="text-xs font-bold text-[#114E84] hover:underline flex items-center gap-1">
                        <span>Lihat Semua</span>
                        @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3">Perihal / Judul</th>
                                <th class="px-5 py-3">Vendor / Terkait</th>
                                <th class="px-5 py-3">Jatuh Tempo</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($reminderList as $rem)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-3 font-medium text-ink">
                                        {{ $rem->judul ?? ($rem->perihal ?? '-') }}
                                    </td>
                                    <td class="px-5 py-3 text-slate-600">{{ $rem->vendor ?? '-' }}</td>
                                    <td class="px-5 py-3 font-mono text-slate-600">
                                        {{ $rem->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($rem->tanggal_jatuh_tempo)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800">
                                            {{ $rem->status ?? 'Aktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                                        Belum ada reminder pengadaan mendekati jatuh tempo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- ROW 4: TIKET SELESAI TERAKHIR OLEH STAF INI --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                </div>
                <div>
                    <h2 class="font-bold text-ink text-base">Riwayat Tiket yang Diselesaikan</h2>
                    <p class="text-xs text-slate-500">Tiket layanan yang baru-baru ini Anda selesaikan</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3">No. Tiket</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Pemohon</th>
                        <th class="px-5 py-3">Tanggal Selesai</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($tiketSelesai as $ts)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3 font-mono font-bold text-[#114E84]">
                                {{ $ts->ticket_number }}
                            </td>
                            <td class="px-5 py-3 font-medium text-slate-800">
                                {{ $ts->category?->name ?? 'Pengadaan' }}
                            </td>
                            <td class="px-5 py-3">
                                {{ $ts->user?->nama_lengkap ?? $ts->user?->username }}
                            </td>
                            <td class="px-5 py-3 font-mono text-slate-500">
                                {{ $ts->updated_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $ts->status_badge }}">
                                    {{ $ts->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('tickets.show', $ts) }}"
                                   class="text-[#114E84] hover:underline font-semibold text-xs">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada riwayat tiket yang diselesaikan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
