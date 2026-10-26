@extends('layouts.app')
@section('title', 'Dashboard Pimpinan Divisi — Bank Sulteng')

@section('content')
@php
    $periodeOptions = [
        'today'      => 'Hari Ini',
        '7_days'     => '7 Hari',
        '30_days'    => '30 Hari',
        'this_month' => 'Bulan Ini',
        'custom'     => 'Kustom',
    ];
    $activePeriodeLabel = $periodeOptions[$periode] ?? 'Bulan Ini';
    $kadivPendingMutasi = \App\Models\AsMutasiAset::where('status', 'Menunggu Approval')->count();
@endphp

{{-- ========================================================================= --}}
{{-- 1. HEADER & FILTER BAR (Clean & To The Point)                              --}}
{{-- ========================================================================= --}}
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-4 sm:p-5 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/20">
                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-3 h-3 text-[#114E84]'])
                    Pengawasan Eksekutif
                </span>
                <span class="text-xs text-slate-300">•</span>
                <span class="text-xs text-slate-500 font-medium">Divisi Umum Bank Sulteng</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight">
                Dashboard Pimpinan Divisi
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Monitoring terpadu performa layanan 3 Bagian, kepatuhan batas waktu SLA, serta persetujuan operasional.
            </p>
        </div>

        {{-- Filters: Quick Pills + Bagian Dropdown --}}
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs">
                @foreach($periodeOptions as $key => $label)
                    @if($key !== 'custom')
                        <a href="{{ route('pimpinan.dashboard', array_filter(['periode' => $key, 'department_id' => $deptFilter])) }}"
                           class="px-3 py-1.5 rounded-lg font-semibold transition {{ $periode === $key ? 'bg-white text-[#114E84] shadow-xs' : 'text-slate-600 hover:text-ink' }}">
                            {{ $label }}
                        </a>
                    @endif
                @endforeach
            </div>

            <form id="filterBagianForm" method="GET" action="{{ route('pimpinan.dashboard') }}" class="inline-block">
                <input type="hidden" name="periode" value="{{ $periode }}">
                @if($periode === 'custom')
                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                @endif
                <select name="department_id" onchange="this.form.submit()"
                        class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white shadow-2xs focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                    <option value="">Semua Bagian (3 Bagian)</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $deptFilter == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 2. ROW KPI UTAMA (4 Metrik Kunci Eksekutif)                                --}}
{{-- ========================================================================= --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Card 1: Total Tiket Masuk --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-card hover:border-[#114E84]/30 transition">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Tiket Masuk</span>
            <div class="w-8 h-8 rounded-lg bg-[#114E84]/10 text-[#114E84] flex items-center justify-center">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-[#114E84]'])
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-ink">{{ number_format($totalMasuk) }}</div>
        <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-500">
            <span class="font-semibold text-emerald-600">{{ number_format($totalSelesai) }} selesai</span>
            <span>({{ $totalMasuk > 0 ? round(($totalSelesai / $totalMasuk) * 100) : 0 }}%)</span>
        </div>
    </div>

    {{-- Card 2: Tiket Aktif Berjalan --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-card hover:border-[#114E84]/30 transition">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tiket Aktif Berjalan</span>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-blue-600'])
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-blue-700">{{ number_format($totalAktif) }}</div>
        <p class="text-xs text-slate-500 mt-1">Dalam pengerjaan 3 Bagian</p>
    </div>

    {{-- Card 3: Overdue SLA (Peringatan / Normal) --}}
    <div class="bg-white rounded-2xl border {{ $totalOverdue > 0 ? 'border-rose-200 bg-rose-50/20' : 'border-slate-200/80' }} p-4 sm:p-5 shadow-card hover:shadow-hover transition">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider {{ $totalOverdue > 0 ? 'text-rose-600' : 'text-slate-400' }}">Overdue SLA</span>
            <div class="w-8 h-8 rounded-lg {{ $totalOverdue > 0 ? 'bg-rose-100 text-rose-700 animate-pulse' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                @include('partials.icon', ['name' => $totalOverdue > 0 ? 'alert-triangle' : 'check-circle', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black {{ $totalOverdue > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
            {{ number_format($totalOverdue) }}
        </div>
        <div class="mt-1 text-xs">
            @if($totalOverdue > 0)
                <span class="font-bold text-rose-600">Perlu perhatian pimpinan</span>
            @else
                <span class="font-semibold text-emerald-600">100% Layanan tepat waktu</span>
            @endif
        </div>
    </div>

    {{-- Card 4: Persetujuan Menunggu Tindakan --}}
    <div class="bg-white rounded-2xl border {{ $kadivPendingMutasi > 0 ? 'border-amber-200 bg-amber-50/20' : 'border-slate-200/80' }} p-4 sm:p-5 shadow-card hover:border-[#114E84]/30 transition">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Persetujuan Mutasi</span>
            <div class="w-8 h-8 rounded-lg {{ $kadivPendingMutasi > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center">
                @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-amber-600'])
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-ink">{{ number_format($kadivPendingMutasi) }}</div>
        <div class="mt-1 flex items-center justify-between text-xs">
            @if($kadivPendingMutasi > 0)
                <span class="inline-flex items-center gap-1 font-bold text-amber-700 text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                    {{ $kadivPendingMutasi }} Butuh Tindakan
                </span>
            @else
                <span class="text-slate-500 text-[11px]">Tidak ada pending</span>
            @endif
            <a href="{{ route('mutasi-aset.index', ['status' => 'Menunggu Approval']) }}" class="text-[#114E84] font-bold hover:underline flex items-center gap-0.5">
                Tinjau →
            </a>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 3. KINERJA 3 BAGIAN OPERASIONAL (Matriks Ringkas & Bersih)                 --}}
{{-- ========================================================================= --}}
<div class="mb-3 flex items-center justify-between">
    <div>
        <h2 class="text-sm sm:text-base font-bold text-ink">Kinerja Layanan 3 Bagian</h2>
        <p class="text-xs text-slate-500">Perbandingan volume pengerjaan tiket aktif, penyelesaian, dan status kepatuhan SLA</p>
    </div>
    <span class="text-xs font-semibold text-slate-400">Periode: {{ $activePeriodeLabel }}</span>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    @foreach($deptCards as $dc)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-5 flex flex-col justify-between hover:border-[#114E84]/40 transition">
            <div>
                {{-- Bagian Header & Status Badge --}}
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center flex-shrink-0">
                            @if(str_contains($dc['name'], 'Umum'))
                                @include('partials.icon', ['name' => 'building', 'class' => 'w-4 h-4'])
                            @elseif(str_contains($dc['name'], 'Aset'))
                                @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4'])
                            @else
                                @include('partials.icon', ['name' => 'cart', 'class' => 'w-4 h-4'])
                            @endif
                        </div>
                        <div>
                            <h3 class="font-bold text-ink text-sm leading-tight">{{ $dc['name'] }}</h3>
                            <p class="text-[11px] text-slate-400">Divisi Umum</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $dc['badge_class'] }}">
                        {{ $dc['status_beban'] }}
                    </span>
                </div>

                {{-- 3 Key Metrics: Aktif, Selesai, Overdue --}}
                <div class="grid grid-cols-3 gap-2 p-2.5 bg-slate-50/80 rounded-xl mb-3 border border-slate-100 text-center">
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase">Aktif</span>
                        <p class="text-base font-extrabold text-blue-700">{{ $dc['aktif'] }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase">Selesai</span>
                        <p class="text-base font-extrabold text-emerald-700">{{ $dc['selesai'] }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase">Overdue</span>
                        <p class="text-base font-extrabold {{ $dc['overdue_res'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                            {{ $dc['overdue_res'] }}
                        </p>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div>
                    <div class="flex items-center justify-between text-xs mb-1 font-semibold">
                        <span class="text-slate-500 text-[11px]">Tingkat Penyelesaian</span>
                        <span class="text-ink font-bold text-[11px]">{{ $dc['completion_rate'] }}%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $dc['completion_rate'] }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Footer Avg Time --}}
            <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span>Total: <strong class="text-slate-700">{{ $dc['total'] }}</strong> tiket</span>
                @if($dc['avg_hours'])
                    <span>Rata-rata: ~{{ $dc['avg_hours'] }} jam</span>
                @endif
            </div>
        </div>
    @endforeach
</div>

{{-- ========================================================================= --}}
{{-- 4. PUSAT PENGAWASAN & TINDAKAN (Overdue Tickets & Mutasi Aset)              --}}
{{-- ========================================================================= --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    {{-- Widget 1: Eskalasi Tiket Overdue --}}
    <div class="bg-white rounded-2xl border {{ $overdueTickets->isNotEmpty() ? 'border-rose-200 shadow-sm' : 'border-slate-200/80 shadow-card' }} p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl {{ $overdueTickets->isNotEmpty() ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                        @include('partials.icon', ['name' => $overdueTickets->isNotEmpty() ? 'alert-triangle' : 'check-circle', 'class' => 'w-4 h-4'])
                    </div>
                    <div>
                        <h3 class="font-bold text-ink text-sm">Eskalasi Tiket Melewati SLA</h3>
                        <p class="text-xs text-slate-400">Tiket aktif yang melampaui target waktu penanganan</p>
                    </div>
                </div>
                @if($overdueTickets->isNotEmpty())
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                        {{ $overdueTickets->count() }} Terlambat
                    </span>
                @endif
            </div>

            @if($overdueTickets->isEmpty())
                <div class="py-8 text-center">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5 text-emerald-600'])
                    </div>
                    <p class="font-bold text-ink text-xs">Seluruh Tiket Berjalan Sesuai Target SLA</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada keterlambatan penanganan pada periode ini.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100 max-h-64 overflow-y-auto pr-1">
                    @foreach($overdueTickets as $ot)
                        <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 font-semibold text-ink">
                                    <span class="font-mono text-[#114E84]">{{ $ot->ticket_number }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="truncate">{{ $ot->department?->name ?? 'Belum dialokasikan' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                                    Pemohon: {{ $ot->user?->nama_lengkap }} ({{ $ot->user?->bagian ?? 'Unit Kerja' }})
                                </p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $ot->overdue_label }}
                                </span>
                                <a href="{{ route('tickets.show', $ot) }}"
                                   class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] transition">
                                    Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
            <span>SLA Monitoring System</span>
            <a href="{{ route('tickets.index', ['status' => 'Dalam Proses']) }}" class="text-[#114E84] font-semibold hover:underline">
                Lihat Seluruh Tiket Berjalan →
            </a>
        </div>
    </div>

    {{-- Widget 2: Monitoring Mutasi & Persetujuan Aset --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
                        @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-purple-700'])
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-ink text-sm">Monitoring Mutasi &amp; Aset</h3>
                            @if ($kadivPendingMutasi > 0)
                                <span class="bg-amber-100 text-amber-800 border border-amber-200 text-[10px] font-bold px-2 py-0.5 rounded-full leading-none flex items-center gap-1" title="{{ $kadivPendingMutasi }} mutasi butuh tindakan persetujuan">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 inline-block animate-pulse"></span>
                                    {{ $kadivPendingMutasi }} Butuh Tindakan
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400">Perpindahan fisik dan permohonan mutasi aset</p>
                    </div>
                </div>
                <a href="{{ route('mutasi-aset.index') }}" class="text-xs font-bold text-[#114E84] hover:underline">
                    Lihat Semua →
                </a>
            </div>

            {{-- Mini Status Mutasi --}}
            <div class="grid grid-cols-3 gap-2 mb-3 text-center">
                <div class="p-2 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] font-semibold text-slate-400">Menunggu Approval</span>
                    <p class="text-base font-extrabold text-amber-700">{{ $kadivPendingMutasi }}</p>
                </div>
                <div class="p-2 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] font-semibold text-slate-400">Diproses</span>
                    <p class="text-base font-extrabold text-blue-700">{{ $mutasiStats['diproses'] ?? 0 }}</p>
                </div>
                <div class="p-2 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] font-semibold text-slate-400">Selesai</span>
                    <p class="text-base font-extrabold text-emerald-700">{{ $mutasiStats['selesai'] ?? 0 }}</p>
                </div>
            </div>

            {{-- Daftar Mutasi Terkini --}}
            <div class="divide-y divide-slate-100 max-h-56 overflow-y-auto pr-1">
                @forelse($recentMutations as $mut)
                    <div class="py-2 flex items-center justify-between gap-2 text-xs">
                        <div class="min-w-0">
                            <p class="font-semibold text-ink truncate">
                                <span class="font-mono text-[#114E84]">{{ $mut->no_mutasi }}</span>
                                <span class="text-slate-300">•</span>
                                {{ $mut->aset?->nama_aset ?? 'Aset Inventaris' }}
                            </p>
                            <p class="text-[10px] text-slate-400 truncate">
                                {{ $mut->dari_lokasi ?: '-' }} ➔ {{ $mut->ke_lokasi ?: '-' }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold
                                @if(in_array($mut->status, ['Selesai', 'Ditutup'])) bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($mut->status === 'Menunggu Approval') bg-amber-50 text-amber-800 border border-amber-200
                                @else bg-blue-50 text-blue-700 border border-blue-200 @endif">
                                {{ $mut->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400 text-xs">
                        Belum ada permohonan mutasi aset terdaftar.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
            <span>Manajemen Aset Divisi</span>
            @if($kadivPendingMutasi > 0)
                <a href="{{ route('mutasi-aset.index', ['status' => 'Menunggu Approval']) }}" class="text-amber-700 font-bold hover:underline">
                    Proses {{ $kadivPendingMutasi }} Approval →
                </a>
            @else
                <span class="text-emerald-600 font-medium">Tidak ada permohonan pending</span>
            @endif
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 5. TREN LAYANAN & RINGKASAN FINANSIAL OPERASIONAL                          --}}
{{-- ========================================================================= --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Chart Tren Layanan (2 Kolom) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-ink text-sm">Tren Volume Tiket Layanan</h3>
                <p class="text-xs text-slate-400">Perbandingan pergerakan tiket masuk vs tiket selesai</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#114E84]"></span> Masuk</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Selesai</span>
            </div>
        </div>
        <div class="relative" style="height: 220px;">
            @if(array_sum($chartTrenMasuk) === 0 && array_sum($chartTrenSelesai) === 0)
                <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                    @include('partials.icon', ['name' => 'inbox', 'class' => 'w-8 h-8 mb-1.5 opacity-30'])
                    <p class="text-xs font-medium">Belum ada transaksi tiket pada periode ini</p>
                </div>
            @else
                <canvas id="chartTrenTiket"></canvas>
            @endif
        </div>
    </div>

    {{-- Ringkasan Finansial Operasional (1 Kolom) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-5 flex flex-col justify-between">
        <div>
            <div class="mb-3">
                <h3 class="font-bold text-ink text-sm">Ringkasan Finansial Divisi</h3>
                <p class="text-xs text-slate-400">Realisasi biaya &amp; pengadaan terverifikasi</p>
            </div>

            <div class="space-y-3">
                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Biaya Operasional</span>
                    <p class="text-base sm:text-lg font-black text-ink mt-0.5">Rp {{ number_format($dwTotalBiaya, 0, ',', '.') }}</p>
                    <span class="text-[10.5px] text-slate-400">Biaya harian &amp; utilitas</span>
                </div>

                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Realisasi Pengadaan</span>
                    <p class="text-base sm:text-lg font-black text-ink mt-0.5">Rp {{ number_format($dwTotalPengadaan, 0, ',', '.') }}</p>
                    <span class="text-[10.5px] text-slate-400">Total SPK rekanan</span>
                </div>

                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Nilai Buku Aset</span>
                    <p class="text-base sm:text-lg font-black text-ink mt-0.5">Rp {{ number_format($dwTotalNilaiBuku, 0, ',', '.') }}</p>
                    <span class="text-[10.5px] text-slate-400">Sisa nilai buku amortisasi</span>
                </div>
            </div>
        </div>

        <div class="pt-3 mt-3 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
            <span>Sistem Terintegrasi</span>
            <span class="font-semibold text-[#114E84]">Divisi Umum</span>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 6. CHART.JS SCRIPT                                                        --}}
{{-- ========================================================================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const font = { family: '"Plus Jakarta Sans", Manrope, sans-serif' };

    @if(array_sum($chartTrenMasuk) > 0 || array_sum($chartTrenSelesai) > 0)
    (function () {
        const ctx = document.getElementById('chartTrenTiket')?.getContext('2d');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartTrenLabels),
                datasets: [
                    {
                        label: 'Tiket Masuk',
                        data: @json($chartTrenMasuk),
                        borderColor: '#114E84',
                        backgroundColor: 'rgba(17, 78, 132, 0.08)',
                        borderWidth: 2,
                        pointBackgroundColor: '#114E84',
                        pointRadius: 3,
                        fill: true,
                        tension: 0.3,
                    },
                    {
                        label: 'Tiket Selesai',
                        data: @json($chartTrenSelesai),
                        borderColor: '#10B981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointBackgroundColor: '#10B981',
                        pointRadius: 3,
                        tension: 0.3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: c => ` ${c.dataset.label}: ${c.parsed.y} tiket`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { ...font, size: 10 }, color: '#94a3b8' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { ...font, size: 10 }, color: '#94a3b8', stepSize: 1 }
                    }
                }
            }
        });
    })();
    @endif
});
</script>
@endsection
