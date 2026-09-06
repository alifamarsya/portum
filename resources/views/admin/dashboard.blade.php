@extends('layouts.app')
@section('title', 'Dashboard Administrator')

@section('content')
@php
    $user = auth()->user();
    $firstName = explode(' ', $user->nama_lengkap)[0];
    $hour = (int) now()->format('H');
    $greeting = match(true) {
        $hour >= 4 && $hour < 11 => 'Selamat Pagi',
        $hour >= 11 && $hour < 15 => 'Selamat Siang',
        $hour >= 15 && $hour < 18 => 'Selamat Sore',
        default => 'Selamat Malam',
    };
@endphp

{{-- ================= PAGE HEADER ================= --}}
<div class="mb-6">
    <div class="flex items-center gap-2 mb-1.5">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/20">
            @include('partials.icon', ['name' => 'shield', 'class' => 'w-3 h-3 text-[#114E84]'])
            Administrator Workspace
        </span>
    </div>
    <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
        {{ $greeting }}, {{ $firstName }}
    </h1>
    <p class="text-xs sm:text-sm text-slate-500 mt-1">
        Pusat kendali administrasi sistem, manajemen pengguna, peran hak akses, jejak audit, dan supervisi layanan tiket.
    </p>
</div>

{{-- ================= 4 KPI METRIC CARDS ================= --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Card 1: Total Pengguna --}}
    <a href="{{ route('admin.users.index') }}"
       class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-card hover:border-[#114E84]/40 hover:shadow-hover transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pengguna</p>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'users', 'class' => 'w-5 h-5'])
                </div>
            </div>
            <p class="text-3xl font-extrabold text-ink tracking-tight">{{ number_format($totalUsers) }}</p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-emerald-700 font-medium flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                {{ $activeUsers }} Aktif
            </span>
            <span class="text-slate-400 font-medium">{{ $inactiveUsers }} Nonaktif</span>
        </div>
    </a>

    {{-- Card 2: Keamanan Akun / Reset Password --}}
    <a href="{{ route('admin.users.index') }}"
       class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-card hover:border-amber-400/50 hover:shadow-hover transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kepatuhan Password</p>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'key', 'class' => 'w-5 h-5'])
                </div>
            </div>
            <p class="text-3xl font-extrabold text-ink tracking-tight">{{ number_format($mustChangePwdUsers) }}</p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
            @if ($mustChangePwdUsers > 0)
                <span class="text-amber-600 font-semibold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Perlu ganti password
                </span>
            @else
                <span class="text-emerald-600 font-medium flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Kredensial aman
                </span>
            @endif
            <span class="text-[#114E84] group-hover:translate-x-0.5 transition-transform font-bold">Kelola ▸</span>
        </div>
    </a>

    {{-- Card 3: Audit Trail & Integritas --}}
    <a href="{{ route('admin.audit-log.index') }}"
       class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-card hover:border-emerald-400/50 hover:shadow-hover transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jejak Audit</p>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'activity', 'class' => 'w-5 h-5'])
                </div>
            </div>
            <p class="text-3xl font-extrabold text-ink tracking-tight">{{ number_format($totalAuditLogs) }}</p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-600 font-medium">
                +{{ $todayAuditLogs }} hari ini
            </span>
            @if (!$hasTamperedLogs)
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3 h-3 text-emerald-600'])
                    Chain Valid
                </span>
            @else
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md">
                    @include('partials.icon', ['name' => 'alert', 'class' => 'w-3 h-3 text-rose-600'])
                    Perlu Rehash
                </span>
            @endif
        </div>
    </a>

    {{-- Card 4: Sistem Tiket Layanan --}}
    <a href="{{ route('tickets.index') }}"
       class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-card hover:border-[#114E84]/50 hover:shadow-hover transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sistem Tiket Layanan</p>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-[#114E84] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'inbox', 'class' => 'w-5 h-5'])
                </div>
            </div>
            <p class="text-3xl font-extrabold text-ink tracking-tight">{{ number_format($totalTickets) }}</p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
            @if ($pendingTickets > 0)
                <span class="text-amber-600 font-bold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    {{ $pendingTickets }} Butuh Verifikasi
                </span>
            @else
                <span class="text-emerald-600 font-medium flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Antrian terpantau lancar
                </span>
            @endif
            <span class="text-[#114E84] group-hover:translate-x-0.5 transition-transform font-bold">Tiket ▸</span>
        </div>
    </a>
</div>

{{-- ================= TWO-COLUMN LOWER SECTION ================= --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    {{-- LEFT COLUMN: 7 cols on lg --}}
    <div class="lg:col-span-7 space-y-6">
        {{-- Card: Distribusi Pengguna Berdasarkan Role --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-ink flex items-center gap-2">
                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4 text-[#114E84]'])
                        Distribusi Pengguna per Peran (Role)
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Sebaran {{ $totalUsers }} akun pengguna di seluruh struktur Bank Sulteng</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline">
                    Lihat Semua User →
                </a>
            </div>

            <div class="space-y-3">
                @foreach ($rolesWithUserCount as $role)
                    @php
                        $percentage = $totalUsers > 0 ? round(($role->users_count / $totalUsers) * 100) : 0;
                        $badgeColor = match($role->nama) {
                            'superadmin' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'pimpinan', 'kepala_divisi' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'umum_rt' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'aset' => 'bg-teal-50 text-teal-700 border-teal-200',
                            'pengadaan' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                            'operator' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'user' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            default => 'bg-slate-50 text-slate-700 border-slate-200',
                        };
                        $barColor = match($role->nama) {
                            'superadmin' => 'bg-indigo-600',
                            'pimpinan', 'kepala_divisi' => 'bg-blue-600',
                            'umum_rt' => 'bg-amber-500',
                            'aset' => 'bg-teal-500',
                            'pengadaan' => 'bg-cyan-600',
                            'operator' => 'bg-rose-500',
                            'user' => 'bg-emerald-500',
                            default => 'bg-slate-500',
                        };
                    @endphp
                    <div class="p-3 rounded-xl border border-slate-100 hover:border-slate-200 bg-slate-50/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $badgeColor }}">
                                    {{ $role->label }}
                                </span>
                                <span class="text-[11px] font-mono text-slate-400">({{ $role->nama }})</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-ink">{{ $role->users_count }} user</span>
                                <span class="text-[11px] text-slate-400 ml-1">({{ $percentage }}%)</span>
                            </div>
                        </div>
                        {{-- Progress Bar --}}
                        <div class="w-full h-1.5 bg-slate-200/80 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Card: Overview Sistem Tiket & Status Layanan --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-ink flex items-center gap-2">
                        @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-[#114E84]'])
                        Overview Sistem Tiket Layanan
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Status distribusi dan permohonan tiket operasional cabang</p>
                </div>
                <a href="{{ route('tickets.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline">
                    Lihat Semua Tiket →
                </a>
            </div>

            {{-- Status Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200/60 text-center">
                    <p class="text-[11px] font-bold text-amber-700 uppercase">Menunggu</p>
                    <p class="text-xl font-extrabold text-amber-900 mt-0.5">{{ $pendingTickets }}</p>
                </div>
                <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-200/60 text-center">
                    <p class="text-[11px] font-bold text-blue-700 uppercase">Diproses</p>
                    <p class="text-xl font-extrabold text-blue-900 mt-0.5">{{ $processTickets }}</p>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200/60 text-center">
                    <p class="text-[11px] font-bold text-emerald-700 uppercase">Selesai</p>
                    <p class="text-xl font-extrabold text-emerald-900 mt-0.5">{{ $completedTickets }}</p>
                </div>
                <div class="p-3 rounded-xl bg-rose-50/70 border border-rose-200/60 text-center">
                    <p class="text-[11px] font-bold text-rose-700 uppercase">Ditolak</p>
                    <p class="text-xl font-extrabold text-rose-900 mt-0.5">{{ $rejectedTickets }}</p>
                </div>
            </div>

            {{-- Kategori Tiket --}}
            <p class="text-xs font-bold text-slate-700 mb-2.5">Distribusi per Kategori Layanan:</p>
            <div class="space-y-2">
                @foreach ($categoriesWithCount as $cat)
                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 bg-slate-50/40 text-xs">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-2 h-2 rounded-full bg-[#114E84]"></div>
                            <span class="font-medium text-slate-700 truncate">{{ $cat->name }}</span>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span class="text-[11px] text-slate-400">SLA: {{ $cat->default_sla_hours }}j</span>
                            <span class="font-bold text-ink px-2 py-0.5 rounded-md bg-white border border-slate-200">
                                {{ $cat->tickets_count }} tiket
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN: 5 cols on lg --}}
    <div class="lg:col-span-5 space-y-6">
        {{-- Card: Live Audit Trail --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-ink flex items-center gap-2">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-[#114E84]'])
                        Jejak Aktivitas Terkini
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Real-time audit log dari sistem Bank Sulteng</p>
                </div>
                <a href="{{ route('admin.audit-log.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline">
                    Semua →
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($recentAuditLogs as $log)
                    @php
                        $actionColor = match(strtoupper($log->aksi)) {
                            'LOGIN' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'INSERT', 'TAMBAH' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'UPDATE', 'UBAH' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'DELETE', 'HAPUS' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-slate-50 text-slate-700 border-slate-200',
                        };
                    @endphp
                    <div class="p-3 rounded-xl border border-slate-100 hover:border-slate-200 bg-slate-50/40 transition">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-6 h-6 rounded-full bg-[#114E84]/10 text-[#114E84] flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                    {{ strtoupper(substr($log->username ?? 'U', 0, 1)) }}
                                </div>
                                <span class="text-xs font-bold text-ink truncate">{{ $log->username }}</span>
                                <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold border {{ $actionColor }} uppercase">
                                    {{ $log->aksi }}
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-400 whitespace-nowrap">
                                {{ $log->created_at?->diffForHumans() ?? '-' }}
                            </span>
                        </div>
                        <p class="text-[11.5px] text-slate-600 mt-1.5 line-clamp-2 leading-relaxed font-sans">
                            <span class="font-semibold text-slate-800">[{{ $log->modul }}]</span>
                            {{ $log->keterangan }}
                        </p>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-xs">
                        Belum ada aktivitas audit yang tercatat.
                    </div>
                @endforelse
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                <a href="{{ route('admin.audit-log.index') }}"
                   class="inline-flex items-center justify-center gap-1.5 w-full py-2 rounded-xl text-xs font-semibold text-[#114E84] bg-[#114E84]/5 hover:bg-[#114E84]/10 transition">
                    <span>Lihat Seluruh Catatan Audit</span>
                    <span>→</span>
                </a>
            </div>
        </div>

        {{-- Card: Status Keamanan & Server Environment --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-ink flex items-center gap-2">
                        @include('partials.icon', ['name' => 'sliders', 'class' => 'w-4 h-4 text-[#114E84]'])
                        Status Sistem & Integritas
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Informasi status server dan enkripsi data</p>
                </div>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Integritas Hash Chain</span>
                    @if (!$hasTamperedLogs)
                        <span class="font-semibold text-emerald-700 flex items-center gap-1">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-emerald-500'])
                            SHA-256 Tamper-Evident
                        </span>
                    @else
                        <span class="font-semibold text-rose-600 flex items-center gap-1">
                            @include('partials.icon', ['name' => 'alert', 'class' => 'w-3.5 h-3.5 text-rose-500'])
                            Perlu Verifikasi
                        </span>
                    @endif
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Framework Engine</span>
                    <span class="font-mono font-medium text-slate-800">Laravel v{{ $systemInfo['laravel_version'] }}</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">PHP Runtime</span>
                    <span class="font-mono font-medium text-slate-800">v{{ $systemInfo['php_version'] }}</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Basis Data Driver</span>
                    <span class="font-mono font-medium text-slate-800 uppercase">{{ $systemInfo['db_driver'] }}</span>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-slate-500">Lingkungan Aplikasi</span>
                    <span class="font-semibold text-slate-700 uppercase">{{ $systemInfo['app_env'] }}</span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                <form method="POST" action="{{ route('admin.audit-log.rehash') }}" onsubmit="return confirm('Jalankan kalkulasi ulang rantai hash (rehash) untuk seluruh audit log?')">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition flex items-center justify-center gap-1.5">
                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5 text-slate-500'])
                        <span>Jalankan Rekalkulasi Hash Integritas</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
