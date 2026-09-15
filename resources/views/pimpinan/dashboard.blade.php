@extends('layouts.app')
@section('title', 'Dashboard Pimpinan Divisi — Bank Sulteng')

@section('content')
@php
    $periodeOptions = [
        'today'      => 'Hari Ini',
        '7_days'     => '7 Hari Terakhir',
        '30_days'    => '30 Hari Terakhir',
        'this_month' => 'Bulan Ini',
        'custom'     => 'Custom Range',
    ];
    $activePeriodeLabel = $periodeOptions[$periode] ?? 'Bulan Ini';
@endphp

{{-- =================== EXECUTIVE BANNER =================== --}}
<div class="relative overflow-hidden rounded-2xl mb-6"
     style="background: linear-gradient(135deg, #071A2F 0%, #0C2F52 40%, #114E84 70%, #1564A8 100%);">
    {{-- Decorative circles --}}
    <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full opacity-10"
         style="background: radial-gradient(circle, #D4A038 0%, transparent 70%); pointer-events:none;"></div>
    <div class="absolute right-32 bottom-0 w-48 h-48 rounded-full opacity-5"
         style="background: radial-gradient(circle, white 0%, transparent 70%); pointer-events:none;"></div>
    <div class="absolute left-0 top-0 w-1 h-full" style="background: linear-gradient(to bottom, #D4A038, transparent);"></div>

    <div class="relative z-10 p-6 sm:p-8">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
            {{-- Left: greeting + info --}}
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold mb-4"
                     style="background:rgba(212,160,56,0.15); border:1px solid rgba(212,160,56,0.3); color:#D4A038;">
                    @include('partials.icon', ['name'=>'shield', 'class'=>'w-3.5 h-3.5'])
                    <span>Executive Dashboard • Pimpinan Divisi Umum</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                    Selamat Datang,<br>
                    <span style="color:#D4A038;">{{ auth()->user()->nama_lengkap }}</span>
                </h1>
                <p class="text-sm text-blue-100/80 mt-2 max-w-xl leading-relaxed">
                    Monitoring terpadu seluruh aktivitas tiket layanan, kepatuhan SLA, dan beban kerja lintas
                    <span class="font-semibold text-white">3 Bagian Divisi Umum</span>.
                </p>
                <div class="flex flex-wrap items-center gap-2 mt-4 text-xs">
                    <span class="px-2.5 py-1 rounded-full font-semibold"
                          style="background:rgba(255,255,255,0.1); color:rgba(255,255,255,0.8); border:1px solid rgba(255,255,255,0.15);">
                        Periode: {{ $activePeriodeLabel }}
                    </span>
                    @if($deptFilter)
                        <span class="px-2.5 py-1 rounded-full font-semibold"
                              style="background:rgba(212,160,56,0.15); color:#D4A038; border:1px solid rgba(212,160,56,0.25);">
                            Bagian: {{ $departments->where('id',$deptFilter)->first()?->name ?? '-' }}
                        </span>
                    @endif
                    <span class="text-white/40">•</span>
                    <span class="text-white/50">{{ now()->translatedFormat('l, d F Y H:i') }} WIB</span>
                </div>
            </div>

            {{-- Right: quick actions --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('tickets.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition"
                   style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:white;"
                   onmouseover="this.style.background='rgba(255,255,255,0.18)'"
                   onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                    @include('partials.icon', ['name'=>'inbox', 'class'=>'w-4 h-4'])
                    <span>Semua Tiket</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- =================== FILTER BAR =================== --}}
<form method="GET" action="{{ route('pimpinan.dashboard') }}" id="filterForm"
      class="bg-white rounded-2xl border border-slate-200 shadow-card p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
        {{-- Periode filter --}}
        <div class="flex-1 min-w-0">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Periode</label>
            <div class="flex flex-wrap gap-1.5">
                @foreach($periodeOptions as $key => $label)
                    @if($key !== 'custom')
                        <a href="{{ route('pimpinan.dashboard', array_filter(['periode'=>$key, 'department_id'=>$deptFilter])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition border
                                  {{ $periode === $key
                                     ? 'bg-[#114E84] text-white border-[#114E84] shadow-sm'
                                     : 'bg-white text-slate-600 border-slate-200 hover:border-[#114E84] hover:text-[#114E84]' }}">
                            {{ $label }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Custom date range --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Dari</label>
                <input type="date" name="date_from" id="date_from"
                       value="{{ $periode === 'custom' ? ($dateFrom ?? $startDate->toDateString()) : '' }}"
                       class="border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-700 focus:border-[#114E84] focus:ring-0 outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Sampai</label>
                <input type="date" name="date_to" id="date_to"
                       value="{{ $periode === 'custom' ? ($dateTo ?? $endDate->toDateString()) : '' }}"
                       class="border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-700 focus:border-[#114E84] focus:ring-0 outline-none">
            </div>
            <input type="hidden" name="periode" value="custom">
            @if($deptFilter)<input type="hidden" name="department_id" value="{{ $deptFilter }}">@endif
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">&nbsp;</label>
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E3F6B] transition">
                    Terapkan
                </button>
            </div>
        </div>

        {{-- Filter Bagian --}}
        <div class="flex-shrink-0">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Bagian</label>
            <select name="department_id" onchange="document.getElementById('filterBagianForm').submit()"
                    id="filterBagianSelect"
                    class="border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-700 focus:border-[#114E84] outline-none">
                <option value="">Semua Bagian</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $deptFilter == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</form>
<form id="filterBagianForm" method="GET" action="{{ route('pimpinan.dashboard') }}" class="hidden">
    <input type="hidden" name="periode" value="{{ $periode }}">
    @if($periode === 'custom')
        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
        <input type="hidden" name="date_to" value="{{ $dateTo }}">
    @endif
    <input type="hidden" name="department_id" id="filterBagianValue">
</form>
<script>
    document.getElementById('filterBagianSelect')?.addEventListener('change', function() {
        document.getElementById('filterBagianValue').value = this.value;
        document.getElementById('filterBagianForm').submit();
    });
</script>

{{-- =================== KPI CARDS =================== --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    {{-- 1. Total Masuk --}}
    <div class="col-span-1 bg-gradient-to-br from-[#0C3860] to-[#1564A8] rounded-2xl p-5 text-white relative overflow-hidden shadow-lg">
        <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full opacity-10 bg-white"></div>
        <div class="w-9 h-9 rounded-xl mb-3 flex items-center justify-center" style="background:rgba(255,255,255,0.15);">
            @include('partials.icon', ['name'=>'archive', 'class'=>'w-4 h-4 text-white'])
        </div>
        <p class="text-4xl font-extrabold text-white tracking-tight">{{ $totalMasuk }}</p>
        <p class="text-xs font-bold text-blue-200/90 mt-1">Total Tiket Masuk</p>
        <p class="text-[10px] text-blue-100/60 mt-0.5">{{ $activePeriodeLabel }}</p>
    </div>

    {{-- 2. Tiket Aktif --}}
    <div class="col-span-1 bg-white rounded-2xl p-5 border border-amber-200/80 relative overflow-hidden shadow-card group hover:border-amber-400 transition-all">
        <div class="absolute -right-3 -bottom-3 w-16 h-16 rounded-full bg-amber-50 opacity-60"></div>
        <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-700 flex items-center justify-center mb-3">
            @include('partials.icon', ['name'=>'clock', 'class'=>'w-4 h-4'])
        </div>
        <p class="text-4xl font-extrabold text-amber-800 tracking-tight">{{ $totalAktif }}</p>
        <p class="text-xs font-bold text-amber-700 mt-1">Tiket Aktif</p>
        <div class="flex items-center gap-1.5 mt-1">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
            <p class="text-[10px] text-amber-600">Sedang diproses</p>
        </div>
    </div>

    {{-- 3. Tiket Selesai --}}
    <div class="col-span-1 bg-white rounded-2xl p-5 border border-emerald-200/80 relative overflow-hidden shadow-card hover:border-emerald-400 transition-all">
        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-700 flex items-center justify-center mb-3">
            @include('partials.icon', ['name'=>'check-circle', 'class'=>'w-4 h-4'])
        </div>
        <p class="text-4xl font-extrabold text-emerald-700 tracking-tight">{{ $totalSelesai }}</p>
        <p class="text-xs font-bold text-emerald-700 mt-1">Tiket Selesai</p>
        @php
            $completion = ($totalMasuk > 0) ? round(($totalSelesai / $totalMasuk) * 100) : 0;
        @endphp
        <p class="text-[10px] text-emerald-600 mt-1">{{ $completion }}% completion rate</p>
    </div>

    {{-- 4. Tiket Overdue --}}
    <div class="col-span-1 bg-white rounded-2xl p-5 border {{ $totalOverdue > 0 ? 'border-rose-300' : 'border-slate-200' }} relative overflow-hidden shadow-card transition-all {{ $totalOverdue > 0 ? 'hover:border-rose-400' : 'hover:border-slate-300' }}">
        <div class="w-9 h-9 rounded-xl {{ $totalOverdue > 0 ? 'bg-rose-500/10 text-rose-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center mb-3">
            @include('partials.icon', ['name'=>'alert-triangle', 'class'=>'w-4 h-4'])
        </div>
        <p class="text-4xl font-extrabold {{ $totalOverdue > 0 ? 'text-rose-700' : 'text-slate-400' }} tracking-tight">
            {{ $totalOverdue }}
        </p>
        <p class="text-xs font-bold {{ $totalOverdue > 0 ? 'text-rose-700' : 'text-slate-500' }} mt-1">Tiket Overdue</p>
        <p class="text-[10px] {{ $totalOverdue > 0 ? 'text-rose-500 animate-pulse font-semibold' : 'text-slate-400' }} mt-1">
            {{ $totalOverdue > 0 ? 'SLA resolusi dilanggar!' : 'SLA terjaga ✓' }}
        </p>
    </div>

    {{-- 5. Mutasi Aset (Opsional) --}}
    <div class="col-span-1 bg-white rounded-2xl p-5 border border-indigo-200/80 relative overflow-hidden shadow-card hover:border-indigo-400 transition-all">
        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-700 flex items-center justify-center mb-3">
            @include('partials.icon', ['name'=>'layers', 'class'=>'w-4 h-4'])
        </div>
        <div class="flex items-end gap-2">
            <p class="text-3xl font-extrabold text-indigo-700 tracking-tight">{{ $mutasiPending }}</p>
            <p class="text-sm text-slate-400 mb-0.5 font-medium">/ {{ $mutasiPending + $mutasiSelesai }}</p>
        </div>
        <p class="text-xs font-bold text-indigo-700 mt-1">Mutasi Aset</p>
        <p class="text-[10px] text-indigo-500 mt-1">{{ $mutasiPending }} pending • {{ $mutasiSelesai }} selesai</p>
    </div>
</div>

{{-- =================== CHART SECTION =================== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Chart A: Tren Tiket Masuk (2/3) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-bold text-ink text-[15px]">Tren Tiket Masuk</h2>
                <p class="text-xs text-slate-400">Frekuensi tiket masuk dalam periode terpilih</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/15">
                {{ $activePeriodeLabel }}
            </span>
        </div>
        <div class="relative" style="height:240px;">
            @if(array_sum($chartTrenData) === 0)
                <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                    @include('partials.icon', ['name'=>'inbox', 'class'=>'w-8 h-8 mb-2 opacity-30'])
                    <p class="text-sm font-medium">Belum ada tiket dalam periode ini</p>
                </div>
            @else
                <canvas id="chartTren"></canvas>
            @endif
        </div>
    </div>

    {{-- Chart B: Komposisi Status (1/3) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5">
        <div class="mb-4">
            <h2 class="font-bold text-ink text-[15px]">Komposisi Status</h2>
            <p class="text-xs text-slate-400">Distribusi status tiket dalam periode ini</p>
        </div>
        <div class="relative flex items-center justify-center" style="height:180px;">
            @if(array_sum($chartStatusData) === 0)
                <div class="text-center text-slate-400">
                    <p class="text-sm font-medium">Belum ada data</p>
                </div>
            @else
                <canvas id="chartStatus"></canvas>
            @endif
        </div>
        {{-- Custom legend --}}
        @if(count($chartStatusLabels) > 0)
        <div class="mt-3 space-y-1.5">
            @foreach($chartStatusLabels as $i => $label)
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0"
                              style="background:{{ $chartStatusColors[$i] ?? '#94a3b8' }};"></span>
                        <span class="text-[11px] text-slate-600 truncate max-w-[140px]">{{ $label }}</span>
                    </div>
                    <span class="text-[11px] font-bold text-slate-700">{{ $chartStatusData[$i] }}</span>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Chart C: Beban Per Bagian (Full Width) --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="font-bold text-ink text-[15px]">Distribusi Beban Tiket per Bagian</h2>
            <p class="text-xs text-slate-400">Perbandingan tiket aktif, selesai, dan overdue antar 3 Bagian</p>
        </div>
        <div class="flex items-center gap-3 text-[11px] font-medium">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm inline-block" style="background:#3B82F6;"></span> Aktif</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm inline-block" style="background:#10B981;"></span> Selesai</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm inline-block" style="background:#EF4444;"></span> Overdue</span>
        </div>
    </div>
    <div class="relative" style="height:220px;">
        @if(array_sum($chartDeptAktif) + array_sum($chartDeptSelesai) === 0)
            <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                <p class="text-sm font-medium">Belum ada data tiket per bagian</p>
            </div>
        @else
            <canvas id="chartDept"></canvas>
        @endif
    </div>
</div>

{{-- =================== DAFTAR TIKET OVERDUE =================== --}}
<div class="bg-white rounded-2xl border {{ $overdueTickets->isNotEmpty() ? 'border-rose-200' : 'border-slate-200' }} shadow-card overflow-hidden mb-6">
    <div class="p-5 border-b {{ $overdueTickets->isNotEmpty() ? 'border-rose-100' : 'border-slate-100' }} flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl {{ $overdueTickets->isNotEmpty() ? 'bg-rose-500/10 text-rose-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center">
                @include('partials.icon', ['name'=>'alert-triangle', 'class'=>'w-4 h-4'])
            </div>
            <div>
                <h2 class="font-bold text-ink text-base">Daftar Tiket Overdue (SLA Dilanggar)</h2>
                <p class="text-xs text-slate-500">Tiket aktif yang melampaui batas waktu resolusi SLA — diurutkan dari yang terlama</p>
            </div>
        </div>
        @if($overdueTickets->isNotEmpty())
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                {{ $overdueTickets->count() }} Tiket
            </span>
        @endif
    </div>

    @if($overdueTickets->isEmpty())
        <div class="p-10 text-center">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                @include('partials.icon', ['name'=>'check-circle', 'class'=>'w-7 h-7'])
            </div>
            <h3 class="font-bold text-ink">Seluruh Tiket Aktif Dalam SLA ✓</h3>
            <p class="text-sm text-slate-400 mt-1 max-w-md mx-auto">
                Tidak ada tiket yang melampaui batas waktu resolusi SLA saat ini. Kinerja divisi sangat baik!
            </p>
        </div>
    @else
        {{-- Desktop table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-rose-50/50">
                        <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-rose-700">No. Tiket</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-rose-700">Pemohon</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-rose-700">Bagian / Unit</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-rose-700">Keterlambatan</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-rose-700">Prioritas</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-rose-700">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($overdueTickets as $ticket)
                    <tr class="hover:bg-rose-50/30 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-[12px] font-semibold text-[#114E84]">{{ $ticket->ticket_number }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-ink text-[12.5px]">{{ $ticket->user?->nama_lengkap ?? '-' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $ticket->user?->bagian ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-[12.5px] font-medium text-ink">{{ $ticket->department?->name ?? 'Belum Dialokasikan' }}</div>
                            @if($ticket->unitKerja)
                                <div class="text-[11px] text-slate-400">{{ $ticket->unitKerja->nama }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                @include('partials.icon', ['name'=>'clock', 'class'=>'w-3 h-3'])
                                {{ $ticket->overdue_label }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1">
                                Due: {{ $ticket->sla_resolution_due_at?->translatedFormat('d M Y, H:i') }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold border {{ $ticket->priority_badge }}">
                                {{ $ticket->priority ?? '-' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-medium border {{ $ticket->status_badge }}">
                                {{ $ticket->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('tickets.show', $ticket) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                               style="background:#114E84;"
                               onmouseover="this.style.background='#0E3F6B'"
                               onmouseout="this.style.background='#114E84'">
                                Detail
                                @include('partials.icon', ['name'=>'chevron-right', 'class'=>'w-3 h-3'])
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden divide-y divide-slate-100">
            @foreach($overdueTickets as $ticket)
            <div class="p-4 hover:bg-rose-50/20 transition">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <span class="font-mono text-xs font-bold text-[#114E84]">{{ $ticket->ticket_number }}</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                        {{ $ticket->overdue_label }}
                    </span>
                </div>
                <p class="font-semibold text-ink text-sm">{{ $ticket->user?->nama_lengkap }}</p>
                <p class="text-xs text-slate-400">{{ $ticket->department?->name }}</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium border {{ $ticket->status_badge }}">{{ $ticket->status }}</span>
                    <a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-semibold text-[#114E84] hover:underline ml-auto">
                        Lihat Detail →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- =================== WORKLOAD ANALYSIS =================== --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden mb-4">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center">
                @include('partials.icon', ['name'=>'users', 'class'=>'w-4 h-4'])
            </div>
            <div>
                <h2 class="font-bold text-ink text-base">Analisis Beban Kerja</h2>
                <p class="text-xs text-slate-500">Statistik tiket per Bagian dan Unit Kerja • {{ $activePeriodeLabel }}</p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Bagian / Unit Kerja</th>
                    <th class="text-center px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Total</th>
                    <th class="text-center px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Aktif</th>
                    <th class="text-center px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Selesai</th>
                    <th class="text-center px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Overdue</th>
                    <th class="text-center px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">SLA Compliance</th>
                    <th class="text-center px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Avg. Resolusi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($workload as $w)
                    {{-- Bagian row --}}
                    <tr class="bg-slate-50/70 border-l-4 border-l-[#114E84]">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-[#114E84]/10 text-[#114E84] flex items-center justify-center flex-shrink-0">
                                    @include('partials.icon', ['name'=>'building', 'class'=>'w-3.5 h-3.5'])
                                </div>
                                <div>
                                    <p class="font-bold text-ink text-[13px]">{{ $w['department']->name }}</p>
                                    <p class="text-[10px] text-slate-400">Bagian Internal</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-lg font-extrabold text-ink">{{ $w['total'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $w['aktif'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $w['aktif'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $w['selesai'] > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $w['selesai'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $w['overdue'] > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $w['overdue'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($w['sla_compliance'] !== null)
                                @php
                                    $c = $w['sla_compliance'];
                                    $cc = $c >= 80 ? 'text-emerald-700' : ($c >= 60 ? 'text-amber-700' : 'text-rose-700');
                                    $bg = $c >= 80 ? 'bg-emerald-50' : ($c >= 60 ? 'bg-amber-50' : 'bg-rose-50');
                                @endphp
                                <div class="inline-flex flex-col items-center gap-0.5">
                                    <span class="text-sm font-extrabold {{ $cc }}">{{ $c }}%</span>
                                    <div class="w-16 h-1.5 rounded-full {{ $bg }} overflow-hidden">
                                        <div class="h-full rounded-full {{ $c >= 80 ? 'bg-emerald-500' : ($c >= 60 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                             style="width:{{ $c }}%"></div>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($w['avg_resolution_h'] !== null)
                                <span class="text-sm font-bold text-slate-700">{{ $w['avg_resolution_h'] }}j</span>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Unit Kerja sub-rows --}}
                    @foreach($w['unit_kerja'] as $uk)
                        @if($uk['total'] > 0)
                        <tr class="border-l-4 border-l-slate-200 hover:bg-slate-50/50 transition">
                            <td class="px-5 py-2.5 pl-14">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-slate-300">└</span>
                                    <span class="font-mono text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">{{ $uk['kode'] }}</span>
                                    <span class="text-[12px] text-slate-600 font-medium">{{ $uk['nama'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-center text-sm font-semibold text-slate-500">{{ $uk['total'] }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="text-xs {{ $uk['aktif'] > 0 ? 'text-amber-700 font-bold' : 'text-slate-300' }}">{{ $uk['aktif'] }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="text-xs {{ $uk['selesai'] > 0 ? 'text-emerald-700 font-bold' : 'text-slate-300' }}">{{ $uk['selesai'] }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="text-xs {{ $uk['overdue'] > 0 ? 'text-rose-700 font-bold' : 'text-slate-300' }}">{{ $uk['overdue'] }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-center"><span class="text-slate-300 text-xs">—</span></td>
                            <td class="px-4 py-2.5 text-center"><span class="text-slate-300 text-xs">—</span></td>
                        </tr>
                        @endif
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- =================== CHART.JS SCRIPTS =================== --}}
@if(array_sum($chartTrenData) > 0 || count($chartStatusLabels) > 0 || array_sum($chartDeptAktif) + array_sum($chartDeptSelesai) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const font = { family: '"Plus Jakarta Sans", Manrope, sans-serif' };

    // ── Chart A: Tren Tiket Masuk ──────────────────────────────
    @if(array_sum($chartTrenData) > 0)
    (function () {
        const ctx = document.getElementById('chartTren')?.getContext('2d');
        if (!ctx) return;
        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(17, 78, 132, 0.18)');
        gradient.addColorStop(1, 'rgba(17, 78, 132, 0)');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartTrenLabels),
                datasets: [{
                    label: 'Tiket Masuk',
                    data: @json($chartTrenData),
                    borderColor: '#114E84',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#114E84',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: c => ` ${c.parsed.y} tiket`
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

    // ── Chart B: Komposisi Status (Donut) ─────────────────────
    @if(count($chartStatusLabels) > 0)
    (function () {
        const ctx = document.getElementById('chartStatus')?.getContext('2d');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chartStatusLabels),
                datasets: [{
                    data: @json($chartStatusData),
                    backgroundColor: @json($chartStatusColors),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: c => ` ${c.label}: ${c.parsed} tiket`
                        }
                    }
                }
            }
        });
    })();
    @endif

    // ── Chart C: Per Bagian (Grouped Bar) ─────────────────────
    @if(array_sum($chartDeptAktif) + array_sum($chartDeptSelesai) > 0)
    (function () {
        const ctx = document.getElementById('chartDept')?.getContext('2d');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartDeptLabels),
                datasets: [
                    {
                        label: 'Aktif',
                        data: @json($chartDeptAktif),
                        backgroundColor: '#3B82F6',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Selesai',
                        data: @json($chartDeptSelesai),
                        backgroundColor: '#10B981',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Overdue',
                        data: @json($chartDeptOverdue),
                        backgroundColor: '#EF4444',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: c => ` ${c.dataset.label}: ${c.parsed.y}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { ...font, size: 11 }, color: '#475569' }
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
@endif
@endsection
