@extends('layouts.app')
@section('title', 'Executive Dashboard Pimpinan Divisi — Bank Sulteng')

@section('content')
@php
    $periodeOptions = [
        'today'      => 'Hari Ini',
        '7_days'     => '7 Hari Terakhir',
        '30_days'    => '30 Hari Terakhir',
        'this_month' => 'Bulan Ini',
        'custom'     => 'Kustom',
    ];
    $activePeriodeLabel = $periodeOptions[$periode] ?? 'Bulan Ini';
@endphp

{{-- ========================================================================= --}}
{{-- 1. EXECUTIVE HEADER & FILTER BAR (Clean & Compact)                       --}}
{{-- ========================================================================= --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        {{-- Left: Identity & Scope --}}
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/20">
                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-3 h-3 text-[#114E84]'])
                    Executive Monitoring
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs text-slate-500 font-medium">Kepala Divisi Umum</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight flex items-center gap-2">
                <span>Dashboard Pengawasan &amp; Kinerja Layanan</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Monitoring beban kerja tiket, SLA overdue 3 Bagian, mutasi aset, serta analitik Data Warehouse operasional.
            </p>
        </div>

        {{-- Right: Filters --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Quick Period Pills --}}
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

            {{-- Filter Bagian Dropdown --}}
            <form id="filterBagianForm" method="GET" action="{{ route('pimpinan.dashboard') }}" class="inline-block">
                <input type="hidden" name="periode" value="{{ $periode }}">
                @if($periode === 'custom')
                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                @endif
                <select name="department_id" onchange="this.form.submit()"
                        class="border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white shadow-2xs">
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
{{-- 2. MONITORING BEBAN KERJA & TIKET OVERDUE PER BAGIAN (3 Bagian Matrix)    --}}
{{-- ========================================================================= --}}
<div class="mb-2 flex items-center justify-between">
    <div>
        <h2 class="text-base font-bold text-ink">Monitoring Beban Kerja &amp; Overdue per Bagian</h2>
        <p class="text-xs text-slate-500">Evaluasi volume pengerjaan tiket aktif, tingkat kepatuhan SLA, dan penilaian kecukupan staf</p>
    </div>
    <span class="text-xs font-semibold text-slate-400">Periode: {{ $activePeriodeLabel }}</span>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    @foreach($deptCards as $dc)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5 flex flex-col justify-between transition-all hover:border-[#114E84]/40 hover:shadow-hover">
            {{-- Card Top: Header Bagian & Status Beban Kerja --}}
            <div>
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center flex-shrink-0">
                            @if(str_contains($dc['name'], 'Umum'))
                                @include('partials.icon', ['name' => 'building', 'class' => 'w-4 h-4'])
                            @elseif(str_contains($dc['name'], 'Aset'))
                                @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4'])
                            @else
                                @include('partials.icon', ['name' => 'cart', 'class' => 'w-4 h-4'])
                            @endif
                        </div>
                        <div>
                            <h3 class="font-bold text-ink text-sm leading-tight">{{ $dc['name'] }}</h3>
                            <p class="text-[11px] text-slate-400">Unit Bagian Divisi Umum</p>
                        </div>
                    </div>
                </div>

                {{-- Status Assessment Badge --}}
                <div class="mb-4">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $dc['badge_class'] }}">
                        @include('partials.icon', ['name' => $dc['status_icon'], 'class' => 'w-3.5 h-3.5'])
                        Status Tim: {{ $dc['status_beban'] }}
                    </span>
                    <p class="text-[11.5px] text-slate-500 mt-1.5 leading-snug">
                        {{ $dc['status_desc'] }}
                    </p>
                </div>

                {{-- Metrics Breakdown: Aktif vs Selesai --}}
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl mb-4 border border-slate-100">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tiket Aktif</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-2xl font-black text-amber-700">{{ $dc['aktif'] }}</span>
                            <span class="text-xs text-slate-400 font-medium">sedang dikerjakan</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tiket Selesai</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-2xl font-black text-emerald-700">{{ $dc['selesai'] }}</span>
                            <span class="text-xs text-slate-400 font-medium">/ {{ $dc['total'] }} tiket</span>
                        </div>
                    </div>
                </div>

                {{-- Progress Bar Beban Penyelesaian --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between text-xs mb-1 font-semibold">
                        <span class="text-slate-600">Penyelesaian Tiket</span>
                        <span class="text-slate-700">{{ $dc['completion_rate'] }}%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $dc['completion_rate'] }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Card Bottom: SLA Overdue Breakdown --}}
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $dc['overdue_res'] > 0 ? 'bg-rose-500 animate-ping' : 'bg-emerald-500' }}"></span>
                    <span class="font-bold {{ $dc['overdue_res'] > 0 ? 'text-rose-700 font-extrabold' : 'text-slate-600' }}">
                        {{ $dc['overdue_res'] }} Tiket Overdue SLA
                    </span>
                </div>
                @if($dc['avg_hours'])
                    <span class="text-[11px] text-slate-400 font-medium" title="Rata-rata waktu penyelesaian tiket">
                        Avg: ~{{ $dc['avg_hours'] }} jam
                    </span>
                @endif
            </div>
        </div>
    @endforeach
</div>

{{-- ========================================================================= --}}
{{-- 3. ANALITIK DATA WAREHOUSE & INSIGHT OPERASIONAL (CPMK Data Warehouse)     --}}
{{-- ========================================================================= --}}
<div class="mb-2 flex items-center justify-between">
    <div>
        <h2 class="text-base font-bold text-ink">Grafik &amp; Analitik Data Warehouse</h2>
        <p class="text-xs text-slate-500">Agregasi data historis dari Fact Tables (Biaya Operasional, Realisasi Pengadaan &amp; Amortisasi)</p>
    </div>
    <span class="text-xs text-slate-500 font-mono bg-white border border-slate-200 px-2.5 py-1 rounded-lg shadow-2xs">
        Data Warehouse ETL Synchronized
    </span>
</div>

{{-- Mini KPI DW --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-card flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
            @include('partials.icon', ['name' => 'archive', 'class' => 'w-5 h-5'])
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Biaya Operasional (DW)</p>
            <p class="text-lg font-black text-ink">Rp {{ number_format($dwTotalBiaya, 0, ',', '.') }}</p>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Biaya harian &amp; utilitas terverifikasi</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-card flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center flex-shrink-0">
            @include('partials.icon', ['name' => 'cart', 'class' => 'w-5 h-5'])
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Realisasi Pengadaan (DW)</p>
            <p class="text-lg font-black text-ink">Rp {{ number_format($dwTotalPengadaan, 0, ',', '.') }}</p>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Total SPK &amp; negosiasi rekanan</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-card flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
            @include('partials.icon', ['name' => 'layers', 'class' => 'w-5 h-5'])
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nilai Buku Aset Terkini (DW)</p>
            <p class="text-lg font-black text-ink">Rp {{ number_format($dwTotalNilaiBuku, 0, ',', '.') }}</p>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Sisa nilai buku seluruh amortisasi</p>
        </div>
    </div>
</div>

{{-- Charts Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Chart 1: Tren Tiket & Biaya Bulanan (2/3) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-ink text-sm">Tren Tiket Layanan &amp; Biaya Operasional</h3>
                <p class="text-xs text-slate-400">Pergerakan volume tiket masuk vs selesai dalam periode aktif</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-[#114E84]"></span> Tiket Masuk</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Tiket Selesai</span>
            </div>
        </div>
        <div class="relative" style="height: 240px;">
            @if(array_sum($chartTrenMasuk) === 0 && array_sum($chartTrenSelesai) === 0)
                <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                    @include('partials.icon', ['name' => 'inbox', 'class' => 'w-8 h-8 mb-2 opacity-30'])
                    <p class="text-xs font-medium">Belum ada transaksi tiket pada periode ini</p>
                </div>
            @else
                <canvas id="chartTrenTiket"></canvas>
            @endif
        </div>
    </div>

    {{-- Chart 2: Realisasi Pengadaan per Rekanan (1/3) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5">
        <div class="mb-4">
            <h3 class="font-bold text-ink text-sm">Distribusi Nilai Pengadaan per Rekanan</h3>
            <p class="text-xs text-slate-400">Porsi nilai kontrak SPK pada vendor utama (Data Warehouse)</p>
        </div>
        <div class="relative flex items-center justify-center" style="height: 220px;">
            @if(empty($dwVendorData) || array_sum($dwVendorData) === 0)
                <div class="text-center text-slate-400">
                    <p class="text-xs font-medium">Belum ada data pengadaan vendor</p>
                </div>
            @else
                <canvas id="chartDwPengadaan"></canvas>
            @endif
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 4. MUTASI ASET TERKINI & HUB TIKET OVERDUE                                --}}
{{-- ========================================================================= --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    {{-- Widget Mutasi Aset (Section 3) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
                        @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4'])
                    </div>
                    <div>
                        <h3 class="font-bold text-ink text-sm">Monitoring Mutasi &amp; Pergerakan Aset</h3>
                        <p class="text-xs text-slate-400">Perpindahan fisik aset antar unit kerja &amp; kantor cabang</p>
                    </div>
                </div>
                <a href="{{ route('mutasi-aset.index') }}" class="text-xs font-bold text-[#114E84] hover:underline">
                    Kelola Semua →
                </a>
            </div>

            {{-- Mutasi Mini Stats --}}
            <div class="grid grid-cols-4 gap-2 mb-4 text-center">
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                    <p class="text-[10.5px] font-semibold text-slate-400">Diajukan</p>
                    <p class="text-lg font-extrabold text-blue-700">{{ $mutasiStats['diajukan'] }}</p>
                </div>
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                    <p class="text-[10.5px] font-semibold text-slate-400">Diproses</p>
                    <p class="text-lg font-extrabold text-amber-700">{{ $mutasiStats['diproses'] }}</p>
                </div>
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                    <p class="text-[10.5px] font-semibold text-slate-400">Selesai</p>
                    <p class="text-lg font-extrabold text-emerald-700">{{ $mutasiStats['selesai'] }}</p>
                </div>
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                    <p class="text-[10.5px] font-semibold text-slate-400">Total</p>
                    <p class="text-lg font-extrabold text-ink">{{ $mutasiStats['total'] }}</p>
                </div>
            </div>

            {{-- 5 Mutasi Terkini --}}
            <div class="divide-y divide-slate-100">
                @forelse($recentMutations as $mut)
                    <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 font-semibold text-ink">
                                <span class="font-mono text-[#114E84]">{{ $mut->no_mutasi }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="truncate">{{ $mut->aset?->nama_aset ?? 'Aset Inventaris' }}</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                                Dari: <span class="text-slate-600 font-medium">{{ $mut->dari_lokasi ?: '-' }}</span> ➔ Ke: <span class="text-slate-600 font-medium">{{ $mut->ke_lokasi ?: '-' }}</span>
                            </p>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10.5px] font-bold
                                @if(in_array($mut->status, ['Selesai', 'Ditutup'])) bg-emerald-100 text-emerald-800
                                @elseif($mut->status === 'Diajukan') bg-blue-100 text-blue-800
                                @elseif(in_array($mut->status, ['Diproses', 'Menunggu Approval'])) bg-amber-100 text-amber-800
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ $mut->status }}
                            </span>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $mut->created_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400 text-xs">
                        Belum ada permohonan mutasi aset terdaftar.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Widget Action Hub Tiket Overdue --}}
    <div class="bg-white rounded-2xl border {{ $overdueTickets->isNotEmpty() ? 'border-rose-200' : 'border-slate-200' }} shadow-card p-5 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl {{ $overdueTickets->isNotEmpty() ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }} flex items-center justify-center">
                        @include('partials.icon', ['name' => $overdueTickets->isNotEmpty() ? 'alert-triangle' : 'check-circle', 'class' => 'w-4 h-4'])
                    </div>
                    <div>
                        <h3 class="font-bold text-ink text-sm">Eskalasi Tiket Overdue (Melampaui SLA)</h3>
                        <p class="text-xs text-slate-400">Tiket aktif yang mendesak dan butuh perhatian segera Pimpinan</p>
                    </div>
                </div>
                @if($overdueTickets->isNotEmpty())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                        {{ $overdueTickets->count() }} Butuh Tindakan
                    </span>
                @endif
            </div>

            @if($overdueTickets->isEmpty())
                <div class="py-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2.5">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
                    </div>
                    <h4 class="font-bold text-ink text-sm">Seluruh Tiket Berjalan Sesuai SLA</h4>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Tidak ada tiket yang melewati target batas waktu respon maupun resolusi. Kinerja layanan ketiga Bagian berjalan optimal.
                    </p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($overdueTickets as $ot)
                        <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 font-semibold text-ink">
                                    <span class="font-mono text-[#114E84]">{{ $ot->ticket_number }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="truncate">{{ $ot->department?->name ?? 'Belum dialokasikan' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5 truncate">
                                    Pemohon: <span class="font-medium text-ink">{{ $ot->user?->nama_lengkap }}</span> ({{ $ot->user?->bagian ?? 'Unit' }})
                                </p>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-3 h-3'])
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

        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
            <span>SLA Monitoring Engine</span>
            <a href="{{ route('tickets.index', ['status' => 'Dalam Proses']) }}" class="text-[#114E84] font-semibold hover:underline">
                Lihat Seluruh Tiket Berjalan →
            </a>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 5. CHART.JS INITIALIZATION                                                --}}
{{-- ========================================================================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const font = { family: '"Plus Jakarta Sans", Manrope, sans-serif' };

    // Format Rupiah Tooltip
    const formatRupiah = (val) => new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(val);

    // ── Chart 1: Tren Tiket Masuk vs Selesai ───────────────────
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
                        backgroundColor: 'rgba(17, 78, 132, 0.12)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#114E84',
                        pointRadius: 3.5,
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
                        pointRadius: 3.5,
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

    // ── Chart 2: Pengadaan DW (Doughnut) ───────────────────────
    @if(!empty($dwVendorData) && array_sum($dwVendorData) > 0)
    (function () {
        const ctx = document.getElementById('chartDwPengadaan')?.getContext('2d');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($dwVendorLabels),
                datasets: [{
                    data: @json($dwVendorData),
                    backgroundColor: [
                        '#114E84', '#D4A038', '#10B981', '#6366F1', '#EC4899', '#64748B'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { ...font, size: 10 }, color: '#64748b' }
                    },
                    tooltip: {
                        callbacks: {
                            label: c => ` ${c.label}: ${formatRupiah(c.raw)}`
                        }
                    }
                }
            }
        });
    })();
    @endif
});
</script>
@endsection
