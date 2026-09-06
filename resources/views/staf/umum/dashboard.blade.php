@extends('layouts.app')
@section('title', 'Dashboard Staf Umum & Rumah Tangga')

@section('content')
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0C3860] via-[#114E84] to-[#1A62A2] p-6 sm:p-8 text-white shadow-lg mb-6">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur-md border border-white/20 text-blue-200 mb-3">
                    @include('partials.icon', ['name' => 'user-check', 'class' => 'w-3.5 h-3.5 text-blue-200'])
                    <span>Staf Bagian Umum &amp; Rumah Tangga &bull; Kantor Pusat</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Selamat Datang, {{ $user->nama_lengkap }}
                </h1>
                <p class="text-sm text-blue-100/90 mt-1 max-w-2xl leading-relaxed">
                    Pusat penanganan tugas tiket layanan umum perorangan serta pemantauan operasional harian (Biaya BBM/Perawatan, Kendaraan Dinas, dan Permintaan Cabang).
                </p>
            </div>

            <div class="flex items-center flex-wrap gap-2.5 flex-shrink-0">
                <a href="{{ route('modul.create', 'biaya_harian') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-900 text-xs sm:text-sm font-bold shadow-md transition">
                    @include('partials.icon', ['name' => 'plus-circle', 'class' => 'w-4 h-4'])
                    <span>+ Catat Biaya Harian</span>
                </a>
                <a href="{{ route('tickets.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs sm:text-sm font-semibold backdrop-blur-md transition shadow-sm">
                    @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-blue-200'])
                    <span>Semua Tiket Bagian</span>
                </a>
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

    {{-- ROW 2: STATS MODUL OPERASIONAL UMUM & RT --}}
    <div class="mb-2">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
            @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-emerald-600'])
            <span>Operasional Bagian Umum &amp; Rumah Tangga</span>
        </h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Card 1: Biaya Operasional Bulan Ini --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-emerald-300 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Biaya Bulan Ini</span>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-emerald-700 tracking-tight">
                    Rp {{ number_format($totalBiayaBulanIni, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1 font-medium">Disetujui per {{ now()->translatedFormat('F Y') }}</p>
            </div>

            {{-- Card 2: Menunggu Approval Kabag --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-amber-300 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Menunggu Approval</span>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-amber-800 tracking-tight">
                    {{ $biayaMenungguApproval }} <span class="text-xs font-normal text-slate-500">Pengajuan</span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1 font-medium">Biaya harian diverifikasi Kabag</p>
            </div>

            {{-- Card 3: Kendaraan Dinas --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-blue-300 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Kendaraan Dinas</span>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#114E84] flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-ink tracking-tight">
                    {{ $kendaraanAktif }} <span class="text-xs font-semibold text-slate-500">/ {{ $totalKendaraan }} Aktif</span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1 font-medium">{{ $kendaraanServis }} unit dalam servis/perawatan</p>
            </div>

            {{-- Card 4: Permintaan Cabang --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-purple-300 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Permintaan Cabang</span>
                    <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-purple-900 tracking-tight">
                    {{ $permintaanPending }} <span class="text-xs font-normal text-slate-500">Pending</span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1 font-medium">ATK &amp; Inventaris menunggu diproses</p>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT: 2 COLS LEFT, 1 COL RIGHT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- LEFT COLUMN (2 COLS) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- 1. DAFTAR TIKET TUGAS SAYA --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center">
                            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4'])
                        </div>
                        <div>
                            <h2 class="font-bold text-ink text-base">Antrean Tiket Tugas Saya</h2>
                            <p class="text-xs text-slate-500">Tiket yang didisposisikan oleh Kepala Bagian Umum khusus kepada Anda</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#114E84] border border-blue-100">
                        {{ $antreanTiket->count() }} Tiket Aktif
                    </span>
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
                                            Kategori: {{ $t->category?->name ?? 'Layanan Umum' }}
                                        </h3>
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-mono whitespace-nowrap">
                                        {{ $t->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>

                                {{-- Pemohon --}}
                                <p class="text-xs text-slate-600 mb-2.5">
                                    Pemohon: <strong class="text-slate-800 font-semibold">{{ $t->user?->nama_lengkap ?? $t->user?->username }}</strong>
                                    <span class="text-slate-400">({{ $t->user?->bagian ?? 'Unit Kerja Pemohon' }})</span>
                                </p>

                                {{-- Deskripsi Singkat Masalah --}}
                                <div class="text-xs text-slate-700 bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-3 leading-relaxed">
                                    <span class="font-bold text-slate-800">Uraian Masalah:</span> {{ Str::limit($t->description, 140) }}
                                </div>

                                {{-- Catatan Disposisi RBB dari Kabag --}}
                                @if ($t->disposition_notes)
                                    <div class="p-3 rounded-xl bg-blue-50/80 border border-blue-200/80 text-xs text-[#114E84] mb-3 leading-relaxed flex items-start gap-2">
                                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4 text-[#114E84] flex-shrink-0 mt-0.5'])
                                        <div>
                                            <span class="font-bold">Instruksi &amp; Catatan RBB Kabag Umum:</span>
                                            <p class="text-slate-700 mt-0.5">{{ $t->disposition_notes }}</p>
                                        </div>
                                    </div>
                                @endif

                                {{-- Aksi Cepat Tiket --}}
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-3">
                                    <div class="text-[11px] text-slate-400">
                                        Didisposisikan oleh: <strong class="text-slate-600">{{ $t->disposedBy?->nama_lengkap ?? 'Kepala Bagian Umum' }}</strong>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if ($t->status === 'Didistribusikan')
                                            <form method="POST" action="{{ route('tickets.update', $t) }}" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="Dalam Proses">
                                                <input type="hidden" name="notes" value="Staf mulai mengerjakan permohonan layanan tiket ini.">
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
                                                <input type="hidden" name="notes" value="Tugas telah selesai dikerjakan oleh staf dan menunggu konfirmasi pemohon.">
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

            {{-- 2. TABEL TRANSAKSI BIAYA HARIAN TERAKHIR --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                        </div>
                        <div>
                            <h2 class="font-bold text-ink text-base">Pencatatan Biaya Operasional Terbaru</h2>
                            <p class="text-xs text-slate-500">Riwayat pengeluaran BBM, perawatan, dan rumah tangga kantor</p>
                        </div>
                    </div>
                    <a href="{{ route('modul.index', 'biaya_harian') }}"
                       class="text-xs font-bold text-[#114E84] hover:underline flex items-center gap-1">
                        <span>Lihat Semua</span>
                        @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Kategori</th>
                                <th class="px-5 py-3">Kendaraan / Uraian</th>
                                <th class="px-5 py-3 text-right">Jumlah (Rp)</th>
                                <th class="px-5 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($transaksiBiayaTerbaru as $b)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-3.5 font-mono text-slate-600">
                                        {{ $b->tanggal ? $b->tanggal->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold
                                            {{ $b->kategori === 'BBM' ? 'bg-amber-50 text-amber-700 border border-amber-200' : ($b->kategori === 'Perawatan' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200') }}">
                                            {{ $b->kategori }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-700 font-medium">
                                        <div>{{ $b->kendaraan ?? $b->nama_beban ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $b->uraian }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-800">
                                        {{ number_format((float)$b->jumlah, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        @if ($b->approval_status === 'Disetujui')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Disetujui
                                            </span>
                                        @elseif ($b->approval_status === 'Ditolak')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                Ditolak
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                Diajukan
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-slate-400">
                                        Belum ada data biaya harian yang dicatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (1 COL) --}}
        <div class="space-y-6">
            {{-- 1. RINGKASAN BIAYA BULAN INI PER KATEGORI --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card p-5">
                <h2 class="font-bold text-ink text-sm mb-1 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'sliders', 'class' => 'w-4 h-4 text-[#114E84]'])
                    <span>Distribusi Pengeluaran Bulan Ini</span>
                </h2>
                <p class="text-xs text-slate-400 mb-4">{{ now()->translatedFormat('F Y') }}</p>

                <div class="space-y-3">
                    @php
                        $bbmVal = (float)($biayaPerKategori['BBM'] ?? 0);
                        $perawatanVal = (float)($biayaPerKategori['Perawatan'] ?? 0);
                        $rtVal = (float)($biayaPerKategori['Rumah Tangga'] ?? 0);
                        $sumCat = $totalBiayaBulanIni > 0 ? $totalBiayaBulanIni : 1;
                    @endphp

                    {{-- BBM --}}
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Bahan Bakar Minyak (BBM)
                            </span>
                            <span class="font-mono">Rp {{ number_format($bbmVal, 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-500 rounded-full" style="width: {{ min(100, round(($bbmVal / $sumCat) * 100)) }}%"></div>
                        </div>
                    </div>

                    {{-- Perawatan --}}
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                Servis &amp; Perawatan
                            </span>
                            <span class="font-mono">Rp {{ number_format($perawatanVal, 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ min(100, round(($perawatanVal / $sumCat) * 100)) }}%"></div>
                        </div>
                    </div>

                    {{-- Rumah Tangga --}}
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                Keperluan Rumah Tangga
                            </span>
                            <span class="font-mono">Rp {{ number_format($rtVal, 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-purple-500 rounded-full" style="width: {{ min(100, round(($rtVal / $sumCat) * 100)) }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-700">Total Pengeluaran:</span>
                    <span class="font-mono font-black text-emerald-700 text-sm">
                        Rp {{ number_format($totalBiayaBulanIni, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- 2. STATUS ARMADA KENDARAAN DINAS --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card p-5">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="font-bold text-ink text-sm">Armada Kendaraan Dinas</h2>
                        <p class="text-xs text-slate-400">Monitoring kondisi kendaraan operasional</p>
                    </div>
                    <a href="{{ route('modul.index', 'kendaraan') }}" class="text-xs font-bold text-[#114E84] hover:underline">
                        Kelola
                    </a>
                </div>

                <div class="space-y-2.5">
                    @forelse ($kendaraanList as $k)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                            <div>
                                <p class="font-bold text-slate-800">{{ $k->no_polisi }}</p>
                                <p class="text-[11px] text-slate-500">{{ $k->merk }} &bull; {{ $k->driver ?? 'Tanpa Driver' }}</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-semibold
                                {{ $k->status === 'Aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($k->status === 'Servis' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-600') }}">
                                {{ $k->status }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-3">Belum ada data kendaraan dinas.</p>
                    @endforelse
                </div>
            </div>

            {{-- 3. PERMINTAAN CABANG TERBARU --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card p-5">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="font-bold text-ink text-sm">Permintaan Cabang</h2>
                        <p class="text-xs text-slate-400">Pengajuan ATK &amp; perbaikan inventaris</p>
                    </div>
                    <a href="{{ route('modul.index', 'permintaan_cabang') }}" class="text-xs font-bold text-[#114E84] hover:underline">
                        Lihat
                    </a>
                </div>

                <div class="space-y-2.5">
                    @forelse ($permintaanTerbaru as $p)
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-slate-800">{{ $p->unit_kerja }}</span>
                                <span class="text-[10px] font-mono text-slate-400">
                                    {{ $p->tanggal ? $p->tanggal->format('d/m/Y') : '' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600 truncate">{{ $p->uraian ?? $p->jenis }}</p>
                            <div class="flex items-center justify-between mt-1.5">
                                <span class="text-[10.5px] text-slate-400">Jenis: <strong class="text-slate-600">{{ $p->jenis }}</strong></span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold
                                    {{ $p->status === 'Selesai' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $p->status ?? 'Diajukan' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-3">Belum ada permohonan cabang.</p>
                    @endforelse
                </div>
            </div>

            {{-- 4. PINTASAN AKSES MODUL CEPAT --}}
            <div class="bg-gradient-to-br from-[#114E84]/5 to-indigo-50/60 rounded-2xl border border-blue-200/60 p-5">
                <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-3">Pintasan Cepat Staf Umum</h3>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('modul.create', 'biaya_harian') }}"
                       class="flex items-center gap-2 p-2.5 rounded-xl bg-white hover:bg-blue-50 border border-slate-200/80 text-slate-700 hover:text-[#114E84] text-xs font-semibold shadow-2xs transition">
                        @include('partials.icon', ['name' => 'plus', 'class' => 'w-3.5 h-3.5 text-amber-600'])
                        <span>+ Input Biaya</span>
                    </a>
                    <a href="{{ route('modul.index', 'biaya_harian') }}"
                       class="flex items-center gap-2 p-2.5 rounded-xl bg-white hover:bg-blue-50 border border-slate-200/80 text-slate-700 hover:text-[#114E84] text-xs font-semibold shadow-2xs transition">
                        @include('partials.icon', ['name' => 'layers', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                        <span>Buku Biaya</span>
                    </a>
                    <a href="{{ route('modul.index', 'kendaraan') }}"
                       class="flex items-center gap-2 p-2.5 rounded-xl bg-white hover:bg-blue-50 border border-slate-200/80 text-slate-700 hover:text-[#114E84] text-xs font-semibold shadow-2xs transition">
                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5 text-blue-600'])
                        <span>Kendaraan</span>
                    </a>
                    <a href="{{ route('modul.index', 'permintaan_cabang') }}"
                       class="flex items-center gap-2 p-2.5 rounded-xl bg-white hover:bg-blue-50 border border-slate-200/80 text-slate-700 hover:text-[#114E84] text-xs font-semibold shadow-2xs transition">
                        @include('partials.icon', ['name' => 'inbox', 'class' => 'w-3.5 h-3.5 text-purple-600'])
                        <span>Cabang</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
