@extends('layouts.app')
@section('title', 'Dashboard ' . ($department->name ?? 'Kepala Bagian'))

@section('content')
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0C3860] via-[#114E84] to-[#1A62A2] p-6 sm:p-8 text-white shadow-lg mb-6">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur-md border border-white/20 text-amber-300 mb-3">
                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5 text-amber-300'])
                    <span>Dashboard Kepala Bagian &bull; {{ $department->name ?? 'Internal Bank' }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Selamat Datang, {{ auth()->user()->nama_lengkap }}
                </h1>
                <p class="text-sm text-blue-100/90 mt-1 max-w-2xl leading-relaxed">
                    Pusat kendali evaluasi tiket layanan, verifikasi kesesuaian RBB &amp; pagu anggaran, serta supervisi penugasan kerja tim staf {{ $department->name ?? 'bagian' }}.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('tickets.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs sm:text-sm font-semibold backdrop-blur-md transition shadow-sm">
                    @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-amber-300'])
                    <span>Semua Tiket Bagian</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Card 1: Perlu Disposisi --}}
        <div class="bg-gradient-to-br from-amber-50 to-orange-50/40 rounded-2xl p-5 border border-amber-200/80 shadow-2xs relative overflow-hidden group hover:border-amber-400 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Butuh Disposisi</span>
                <span class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-700 flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-amber-900 tracking-tight">{{ $stats['perlu_disposisi'] }}</p>
            <div class="flex items-center gap-1.5 mt-2 text-[11px] font-semibold text-amber-700">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                <span>Pengecekan RBB &amp; Pagu Anggaran</span>
            </div>
        </div>

        {{-- Card 2: Sedang Dikerjakan Staf --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-blue-300 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Dikerjakan Staf</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-ink tracking-tight">{{ $stats['sedang_dikerjakan'] }}</p>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Sedang diproses oleh staf tim</p>
        </div>

        {{-- Card 3: Tiket Selesai --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-emerald-300 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tiket Selesai</span>
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-emerald-700 tracking-tight">{{ $stats['selesai'] }}</p>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Terselesaikan &amp; ditutup pemohon</p>
        </div>

        {{-- Card 4: Total Tiket Masuk --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Tiket Masuk</span>
                <span class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-ink tracking-tight">{{ $stats['total'] }}</p>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Tiket dialokasikan ke {{ $department->name ?? 'bagian' }}</p>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- LEFT (2 COLS): Antrean Disposisi Kabag --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-700 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-amber-600'])
                        </div>
                        <div>
                            <h2 class="font-bold text-ink text-base">Antrean Disposisi Kepala Bagian</h2>
                            <p class="text-xs text-slate-500">Tiket dari Operator yang membutuhkan verifikasi RBB, anggaran, dan penunjukan staf</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        {{ $antreanDisposisi->count() }} Menunggu
                    </span>
                </div>

                @if ($antreanDisposisi->isEmpty())
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
                        </div>
                        <h3 class="font-bold text-ink text-sm">Semua Tiket Telah Didisposisikan</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            Tidak ada tiket tertunda di meja Kepala Bagian saat ini. Tiket baru dari Operator akan langsung muncul di sini.
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($antreanDisposisi as $t)
                            <div class="p-4 sm:p-5 hover:bg-slate-50/70 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <span class="font-mono font-bold text-xs text-[#114E84]">{{ $t->ticket_number }}</span>
                                        <span class="px-2 py-0.5 rounded text-[10.5px] font-semibold {{ $t->priority_badge }}">
                                            {{ $t->priority }}
                                        </span>
                                        <span class="text-slate-300">•</span>
                                        <span class="text-xs text-slate-600 font-medium">{{ $t->category?->name ?? 'Umum' }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span class="text-[11px] text-slate-400">{{ $t->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-ink font-semibold line-clamp-2 leading-relaxed">
                                        {{ $t->description }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-2 text-[11px] text-slate-500">
                                        <span class="font-semibold text-slate-700">Pemohon:</span>
                                        <span>{{ $t->user?->nama_lengkap ?? '-' }}</span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span>{{ $t->user?->bagian ?? 'Unit Kerja' }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <a href="{{ route('tickets.show', $t) }}"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold shadow-xs transition">
                                        @include('partials.icon', ['name' => 'pencil', 'class' => 'w-3 h-3 text-amber-300'])
                                        <span>Review &amp; Disposisi</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Tiket yang Sedang Ditangani Staf --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-[#114E84] flex items-center justify-center">
                            @include('partials.icon', ['name' => 'activity', 'class' => 'w-4 h-4 text-[#114E84]'])
                        </div>
                        <div>
                            <h2 class="font-bold text-ink text-base">Progress Tiket di Tim Staf</h2>
                            <p class="text-xs text-slate-500">Tiket yang telah didisposisikan dan sedang ditangani oleh staf pelaksana</p>
                        </div>
                    </div>
                </div>

                @if ($tiketBerjalan->isEmpty())
                    <div class="p-6 text-center text-xs text-slate-400">
                        Belum ada tiket yang sedang berjalan di tim staf.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($tiketBerjalan as $tb)
                            <div class="p-4 hover:bg-slate-50/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <span class="font-mono font-bold text-xs text-[#114E84]">{{ $tb->ticket_number }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold border {{ $tb->status_badge }}">
                                            {{ $tb->status }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-700 line-clamp-1">{{ $tb->description }}</p>
                                    <div class="flex items-center gap-1.5 mt-1.5 text-[11px] text-slate-500">
                                        <span>Staf Penanggung Jawab:</span>
                                        <span class="font-bold text-ink">{{ $tb->assignedStaff?->nama_lengkap ?? 'Belum Ditunjuk' }}</span>
                                        <span class="text-slate-400 font-mono">({{ $tb->assignedStaff?->username ?? '-' }})</span>
                                    </div>
                                </div>

                                <a href="{{ route('tickets.show', $tb) }}"
                                   class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1 flex-shrink-0">
                                    <span>Lihat Detail</span>
                                    @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT (1 COL): Monitoring Tim Staf & Modul Operasional --}}
        <div class="space-y-6">
            {{-- Tim Staf Bagian --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card p-5 sm:p-6">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-bold text-ink text-sm sm:text-base">Tim Staf Pelaksana</h2>
                        <p class="text-xs text-slate-500">Akun staf terdaftar di {{ $department->name ?? 'bagian' }}</p>
                    </div>
                    <span class="text-xs font-bold text-[#114E84] bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-200">
                        {{ $staffMembers->count() }} Anggota
                    </span>
                </div>

                @if ($staffMembers->isEmpty())
                    <div class="p-4 text-center text-xs text-slate-400">
                        Belum ada akun staf yang didaftarkan Admin untuk bagian ini.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($staffMembers as $staff)
                            <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-slate-50 transition flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-[#114E84]/10 text-[#114E84] font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($staff->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-ink truncate">{{ $staff->nama_lengkap }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono truncate">{{ $staff->username }} &bull; {{ $staff->jabatan ?? 'Staf' }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold {{ $staff->active_tickets_count > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $staff->active_tickets_count }} Tiket Aktif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Ringkasan Modul Operasional Sesuai Bagian --}}
            @if (!empty($deptMetrics))
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card p-5 sm:p-6">
                    <h2 class="font-bold text-ink text-sm sm:text-base mb-1">{{ $deptMetrics['label'] }}</h2>
                    <p class="text-xs text-slate-500 mb-4">Pemantauan data operasional inti divisi</p>

                    <div class="space-y-3.5">
                        @if (isset($deptMetrics['biaya_bulan_ini']))
                            <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100 flex items-center justify-between">
                                <span class="text-xs text-slate-600 font-medium">Beban Biaya Bulan Ini:</span>
                                <span class="text-xs font-bold font-mono text-[#114E84]">Rp {{ number_format($deptMetrics['biaya_bulan_ini'], 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if (isset($deptMetrics['menunggu_approval']))
                            <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-100 flex items-center justify-between">
                                <span class="text-xs text-slate-600 font-medium">Pengajuan Biaya Menunggu:</span>
                                <span class="text-xs font-bold text-amber-800">{{ $deptMetrics['menunggu_approval'] }} Transaksi</span>
                            </div>
                        @endif

                        @if (isset($deptMetrics['total_aset']))
                            <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100 flex items-center justify-between">
                                <span class="text-xs text-slate-600 font-medium">Total Item Inventaris &amp; Aset:</span>
                                <span class="text-xs font-bold text-[#114E84]">{{ $deptMetrics['total_aset'] }} Item</span>
                            </div>
                        @endif

                        @if (isset($deptMetrics['pks_jatuh_tempo']))
                            <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-100 flex items-center justify-between">
                                <span class="text-xs text-slate-600 font-medium">PKS Akan Jatuh Tempo (&le;90 Hari):</span>
                                <span class="text-xs font-bold text-amber-800">{{ $deptMetrics['pks_jatuh_tempo'] }} Kontrak</span>
                            </div>
                        @endif

                        @if (isset($deptMetrics['total_pengadaan']))
                            <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100 flex items-center justify-between">
                                <span class="text-xs text-slate-600 font-medium">Akumulasi Nilai Pengadaan:</span>
                                <span class="text-xs font-bold font-mono text-[#114E84]">Rp {{ number_format($deptMetrics['total_pengadaan'], 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if (isset($deptMetrics['reminder_vendor']))
                            <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-100 flex items-center justify-between">
                                <span class="text-xs text-slate-600 font-medium">Reminder Vendor &amp; SPK Aktif:</span>
                                <span class="text-xs font-bold text-amber-800">{{ $deptMetrics['reminder_vendor'] }} Reminder</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
