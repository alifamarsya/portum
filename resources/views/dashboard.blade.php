@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
    $firstName = explode(' ', auth()->user()->nama_lengkap)[0];
    $maxChart = max(max($chartValues ?: [0]), 1);
    $points = [];
    $chartWidth = 650;
    $chartHeight = 180;
    $left = 40;
    $right = 12;
    $top = 18;
    $bottom = 28;
    $plotW = $chartWidth - $left - $right;
    $plotH = $chartHeight - $top - $bottom;
    $count = max(count($chartValues), 1);
    foreach ($chartValues as $i => $value) {
        $x = $left + ($count === 1 ? $plotW / 2 : ($i * $plotW / ($count - 1)));
        $y = $top + ($plotH - (($value / $maxChart) * $plotH));
        $points[] = round($x, 2) . ',' . round($y, 2);
    }
    $polyline = implode(' ', $points);
@endphp

<div class="pt-1 mb-5">
    <h2 class="text-[22px] leading-tight font-bold text-ink">Dashboard</h2>
    <p class="text-[12px] text-slate-500 mt-1">Selamat datang kembali, {{ $firstName }}!</p>
</div>

{{-- KPI --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 mb-3">
    <a href="#" class="group bg-white rounded-xl border border-slate-200/90 p-4 min-h-[137px] hover:shadow-hover transition">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] leading-4 text-slate-600">Menunggu<br>Persetujuan</p>
                <p class="text-[23px] font-bold text-ink mt-2">{{ $menunggu }}</p>
            </div>
        </div>
        <p class="text-[11px] text-brand mt-2.5">Lihat detail <span class="ml-1">→</span></p>
    </a>

    <a href="#" class="group bg-white rounded-xl border border-slate-200/90 p-4 min-h-[137px] hover:shadow-hover transition">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] leading-4 text-slate-600">Reminder<br>&le; 90 Hari</p>
                <p class="text-[23px] font-bold text-ink mt-2">{{ $reminderAktif }}</p>
            </div>
        </div>
        <p class="text-[11px] text-brand mt-2.5">Lihat detail <span class="ml-1">→</span></p>
    </a>

    <a href="#" class="group bg-white rounded-xl border border-slate-200/90 p-4 min-h-[137px] hover:shadow-hover transition">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'file-text', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] leading-4 text-slate-600">PKS Jatuh Tempo</p>
                <p class="text-[23px] font-bold text-ink mt-7">{{ $pksJatuhTempo }}</p>
            </div>
        </div>
        <p class="text-[11px] text-brand mt-2.5">Lihat detail <span class="ml-1">→</span></p>
    </a>

    <a href="{{ route('modul.index', 'aset') }}" class="group bg-white rounded-xl border border-slate-200/90 p-4 min-h-[137px] hover:shadow-hover transition">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'building', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] leading-4 text-slate-600">Total Aset</p>
                <p class="text-[23px] font-bold text-ink mt-7">{{ number_format($totalAset, 0, ',', '.') }}</p>
            </div>
        </div>
        <p class="text-[11px] text-brand mt-2.5">Lihat detail <span class="ml-1">→</span></p>
    </a>

    <a href="{{ route('analitik') }}" class="group bg-white rounded-xl border border-slate-200/90 p-4 min-h-[137px] hover:shadow-hover transition">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'building', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] leading-4 text-slate-600">Total Pengadaan<br>Bulan Ini</p>
                <p class="text-[21px] font-bold text-ink mt-4">Rp {{ number_format($totalPengadaan / 1000000, 2, ',', '.') }} M</p>
            </div>
        </div>
        <p class="text-[11px] text-brand mt-2.5">Lihat detail <span class="ml-1">→</span></p>
    </a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1.55fr_1fr] gap-3 mb-3">
    {{-- Trend --}}
    <section class="bg-white rounded-xl border border-slate-200/90 p-5 min-h-[235px]">
        <div class="flex items-center justify-between gap-3 mb-3">
            <h3 class="text-[14px] font-bold text-ink">Tren Biaya Operasional <span class="font-normal text-slate-500">(6 Bulan Terakhir)</span></h3>
            <select class="text-[11px] border-0 bg-slate-100 rounded-lg px-3 py-2 text-slate-600 focus:ring-0">
                <option>Semua Kategori</option>
            </select>
        </div>
        <div class="overflow-hidden">
            <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="w-full h-[175px]" preserveAspectRatio="none" role="img" aria-label="Tren biaya operasional enam bulan terakhir">
                @foreach ([0, .25, .5, .75, 1] as $ratio)
                    @php $gy = $top + ($plotH * $ratio); $value = $maxChart * (1 - $ratio); @endphp
                    <line x1="{{ $left }}" x2="{{ $chartWidth - $right }}" y1="{{ $gy }}" y2="{{ $gy }}" stroke="#E8EBF0" stroke-width="1" />
                    <text x="4" y="{{ $gy + 4 }}" font-size="10" fill="#8A93A3">{{ number_format($value / 1000000, 0, ',', '.') }} jt</text>
                @endforeach
                <polyline points="{{ $polyline }}" fill="none" stroke="#D99A1D" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                @foreach ($points as $i => $point)
                    @php [$px, $py] = explode(',', $point); @endphp
                    <circle cx="{{ $px }}" cy="{{ $py }}" r="4" fill="#D99A1D" />
                    <text x="{{ $px }}" y="{{ $chartHeight - 7 }}" text-anchor="middle" font-size="10" fill="#697386">{{ $chartLabels[$i] ?? '' }}</text>
                @endforeach
            </svg>
        </div>
    </section>

    {{-- Attention --}}
    <section class="bg-white rounded-xl border border-slate-200/90 p-5 min-h-[235px]">
        <h3 class="text-[14px] font-bold text-ink mb-4">Perlu Perhatian</h3>
        <div class="divide-y divide-slate-100">
            <a href="#" class="flex items-center gap-3 py-3 first:pt-0 group">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0"></span>
                <span class="text-[12px] font-medium flex-1">{{ $pksJatuhTempo }} PKS akan jatuh tempo dalam 30 hari</span>
                <span class="text-[11px] text-brand">Lihat&nbsp; →</span>
            </a>
            <a href="#" class="flex items-center gap-3 py-3 group">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                <span class="text-[12px] font-medium flex-1">{{ $menunggu }} dokumen menunggu persetujuan</span>
                <span class="text-[11px] text-brand">Lihat&nbsp; →</span>
            </a>
            <a href="#" class="flex items-center gap-3 py-3 last:pb-0 group">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                <span class="text-[12px] font-medium flex-1">{{ $reminderAktif }} permintaan dari cabang menunggu</span>
                <span class="text-[11px] text-brand">Lihat&nbsp; →</span>
            </a>
        </div>
    </section>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1.55fr_1fr] gap-3">
    {{-- Activity --}}
    <section class="bg-white rounded-xl border border-slate-200/90 p-5 min-h-[280px]">
        <h3 class="text-[14px] font-bold text-ink mb-4">Aktivitas Terbaru</h3>
        <div class="divide-y divide-slate-100">
            @forelse ($activities as $activity)
                <div class="flex items-center gap-3 py-2.5 first:pt-0">
                    <div class="w-8 h-8 rounded-full bg-slate-100 text-brand flex items-center justify-center text-[11px] font-bold flex-shrink-0">
                        {{ strtoupper(substr($activity->username ?: 'S', 0, 1)) }}
                    </div>
                    <p class="text-[11.5px] flex-1 min-w-0 truncate">
                        <span class="font-medium">{{ $activity->username ?: 'Sistem' }}</span>
                        {{ strtolower($activity->keterangan ?: $activity->aksi) }}
                    </p>
                    <span class="hidden sm:inline-flex px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-[9.5px] whitespace-nowrap">{{ $activity->modul ?: 'Sistem' }}</span>
                    <span class="text-[10.5px] text-slate-500 whitespace-nowrap">{{ optional($activity->created_at)->format('H:i') }}</span>
                </div>
            @empty
                <div class="py-10 text-center text-[12px] text-slate-400">Belum ada aktivitas.</div>
            @endforelse
        </div>
        @if ($activities->isNotEmpty())
            <div class="text-center mt-4">
                <a href="{{ auth()->user()->role->nama === 'superadmin' ? route('admin.audit-log.index') : '#' }}" class="inline-flex items-center gap-2 border border-slate-300 rounded-lg px-4 py-2 text-[11px] text-brand hover:bg-slate-50 transition">Lihat semua aktivitas <span>→</span></a>
            </div>
        @endif
    </section>

    {{-- Audit --}}
    <section class="bg-white rounded-xl border border-slate-200/90 p-5 min-h-[280px]">
        <h3 class="text-[14px] font-bold text-ink mb-8">Rantai Audit</h3>
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-8 h-8', 'stroke' => 1.5])
            </div>
            <div>
                <p class="text-[12px] font-bold text-ink">Sistem audit aktif dan terverifikasi</p>
                <p class="text-[10.5px] text-slate-500 mt-1">
                    Terakhir diverifikasi:
                    {{ $lastLog?->created_at?->format('d M Y') ?? 'Belum ada data' }}
                    @if ($lastLog) &nbsp;•&nbsp; {{ $lastLog->created_at->format('H:i') }} @endif
                </p>
            </div>
        </div>
        @if (auth()->user()->role->nama === 'superadmin')
            <a href="{{ route('admin.audit-log.index') }}" class="inline-flex mt-10 border border-slate-300 rounded-lg px-4 py-2 text-[11px] text-brand hover:bg-slate-50 transition">Lihat Audit Log</a>
        @endif
    </section>
</div>
@endsection
