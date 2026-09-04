@extends('layouts.app')
@section('title', 'Dashboard')

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
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight leading-tight">
            {{ $greeting }}, {{ $firstName }} 👋
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">
            Ringkasan operasional Bank Sulteng — {{ now()->translatedFormat('l, d F Y') }}
        </p>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        <a href="{{ route('modul.create', 'biaya_harian') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-2xs transition-colors">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-3.5 h-3.5 text-slate-400'])
            <span class="hidden sm:inline">Biaya Harian</span>
            <span class="sm:hidden">Biaya</span>
        </a>
        <a href="{{ route('modul.create', 'memo_internal') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-brand text-white hover:bg-slate-800 shadow-2xs transition-colors">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-3.5 h-3.5 text-gold'])
            <span class="hidden sm:inline">Pengadaan</span>
            <span class="sm:hidden">Tambah</span>
        </a>
    </div>
</div>

{{-- ================= KPI METRIC CARDS (4 cards, responsive) ================= --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    {{-- Card: Approval Tertunda --}}
    <a href="{{ route('modul.index', 'biaya_harian') }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-amber-300 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Approval<br>Tertunda</p>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-ink tracking-tight">{{ $menunggu }}</p>
        <p class="text-[10px] text-amber-600 font-medium mt-1 flex items-center gap-0.5">
            <span>Perlu persetujuan</span>
            @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-2.5 h-2.5 group-hover:translate-x-0.5 transition-transform'])
        </p>
    </a>

    {{-- Card: PKS Jatuh Tempo --}}
    <a href="{{ route('modul.index', 'pks') }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-rose-300 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">PKS<br>Jatuh Tempo</p>
            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'alert', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-ink tracking-tight">{{ $pksJatuhTempo }}</p>
        <p class="text-[10px] text-rose-600 font-medium mt-1 flex items-center gap-0.5">
            <span>≤ 90 hari ke depan</span>
            @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-2.5 h-2.5 group-hover:translate-x-0.5 transition-transform'])
        </p>
    </a>

    {{-- Card: Total Aset --}}
    <a href="{{ route('modul.index', 'aset') }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-emerald-300 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Total<br>Unit Aset</p>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-ink tracking-tight">{{ number_format($totalAset) }}</p>
        <p class="text-[10px] text-emerald-600 font-medium mt-1 flex items-center gap-0.5">
            <span>Inventaris kantor</span>
            @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-2.5 h-2.5 group-hover:translate-x-0.5 transition-transform'])
        </p>
    </a>

    {{-- Card: Total Pengadaan --}}
    <a href="{{ route('analitik') }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Total<br>Pengadaan</p>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'cart', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-base font-bold text-ink tracking-tight leading-tight">
            Rp {{ number_format($totalPengadaan / 1000000, 1, ',', '.') }} Jt
        </p>
        <p class="text-[10px] text-blue-600 font-medium mt-1 flex items-center gap-0.5">
            <span>Lihat analitik DW</span>
            @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-2.5 h-2.5 group-hover:translate-x-0.5 transition-transform'])
        </p>
    </a>
</div>

{{-- ================= CHART + PKS SECTION ================= --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">

    {{-- Biaya Chart (Chart.js) --}}
    <section class="xl:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-2xs p-4 sm:p-5 min-w-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h2 class="text-sm font-bold text-ink">Tren Biaya Operasional</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Realisasi 6 bulan terakhir</p>
            </div>
            <div class="flex items-center gap-3 text-[10px] font-medium text-slate-500">
                <span class="flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-sm inline-block bg-[#3b82f6]"></span> BBM
                </span>
                <span class="flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-sm inline-block bg-[#f59e0b]"></span> Perawatan
                </span>
                <span class="flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-sm inline-block bg-[#10b981]"></span> RT
                </span>
            </div>
        </div>
        <div class="w-full" style="position:relative; height:180px;">
            <canvas id="biayaChart"></canvas>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
            <p class="text-[11px] text-slate-500">
                Total 6 bulan: <span class="font-bold text-ink">Rp {{ number_format($totalBiaya6Bln / 1000000, 1, ',', '.') }} Jt</span>
            </p>
            <a href="{{ route('analitik') }}" class="text-[11px] font-semibold text-brand hover:underline flex items-center gap-1">
                Analitik DW
                @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
            </a>
        </div>
    </section>

    {{-- PKS Akan Jatuh Tempo --}}
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-2xs p-4 sm:p-5 min-w-0">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-ink">Kontrak Segera Berakhir</h2>
            <a href="{{ route('modul.index', 'pks') }}" class="text-[11px] text-slate-400 hover:text-brand font-semibold transition-colors">
                Lihat semua
            </a>
        </div>
        <div class="space-y-2.5">
            @forelse ($pksNearDue as $pks)
                @php
                    $daysLeft = now()->diffInDays($pks->jatuh_tempo, false);
                    $urgencyColor = $daysLeft <= 30
                        ? 'bg-rose-100 text-rose-700 border-rose-200'
                        : 'bg-amber-50 text-amber-700 border-amber-200';
                    $dotColor = $daysLeft <= 30 ? 'bg-rose-500' : 'bg-amber-400';
                @endphp
                <div class="flex items-start gap-2.5 p-2.5 rounded-lg bg-slate-50 border border-slate-100 hover:border-slate-200 transition-colors">
                    <span class="w-2 h-2 rounded-full {{ $dotColor }} mt-1.5 flex-shrink-0"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-ink truncate leading-tight">{{ $pks->judul }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 truncate">{{ $pks->vendor ?? 'Vendor tidak dicatat' }}</p>
                    </div>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border {{ $urgencyColor }} flex-shrink-0 whitespace-nowrap">
                        {{ $daysLeft }} hr
                    </span>
                </div>
            @empty
                <div class="py-6 text-center">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-2">
                        @include('partials.icon', ['name' => 'check', 'class' => 'w-5 h-5 text-emerald-500'])
                    </div>
                    <p class="text-xs text-slate-500 font-medium">Tidak ada kontrak<br>yang akan jatuh tempo.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>

{{-- ================= ACTIVITY LOG ================= --}}
<section class="bg-white rounded-xl border border-slate-200/80 shadow-2xs p-4 sm:p-5 min-w-0">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-bold text-ink">Aktivitas & Audit Trail</h2>
        @if (auth()->user()->role->nama === 'superadmin')
            <a href="{{ route('admin.audit-log.index') }}" class="text-[11px] font-semibold text-slate-400 hover:text-brand transition-colors flex items-center gap-1">
                Lihat semua
                @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
            </a>
        @endif
    </div>
    <div class="divide-y divide-slate-100">
        @forelse ($activities as $activity)
            <div class="py-2.5 first:pt-0 last:pb-0 flex items-center gap-3 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-slate-100 font-bold flex items-center justify-center text-xs flex-shrink-0 text-slate-600">
                    {{ strtoupper(substr($activity->username ?: 'S', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-ink truncate">
                        <span class="font-semibold">{{ $activity->username ?: 'Sistem' }}</span>
                        <span class="text-slate-500"> &mdash; {{ $activity->keterangan ?: $activity->aksi }}</span>
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $activity->modul ?? 'Sistem' }}</p>
                </div>
                <span class="text-[11px] text-slate-400 font-mono whitespace-nowrap flex-shrink-0">
                    {{ optional($activity->created_at)->format('H:i') }}
                </span>
            </div>
        @empty
            <div class="py-6 text-center text-xs text-slate-400">
                Belum ada aktivitas tercatat.
            </div>
        @endforelse
    </div>
    @if ($lastLog)
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
            <p class="text-[10px] text-slate-500">
                Audit SHA-256 aktif &bull;
                Hash terakhir: <code class="font-mono text-[9px] text-slate-600">{{ substr($lastLog->hash, 0, 20) }}…</code>
            </p>
        </div>
    @endif
</section>

{{-- ================= CHART.JS SCRIPTS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = @json($chartLabels);
    const bbm    = @json($chartBbm);
    const rawat  = @json($chartPerawatan);
    const rt     = @json($chartRt);

    const toJt = v => +(v / 1_000_000).toFixed(2);

    new Chart(document.getElementById('biayaChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'BBM',
                    data: bbm.map(toJt),
                    backgroundColor: '#3b82f6cc',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Perawatan',
                    data: rawat.map(toJt),
                    backgroundColor: '#f59e0bcc',
                    borderColor: '#f59e0b',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Rumah Tangga',
                    data: rt.map(toJt),
                    backgroundColor: '#10b981cc',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { size: 11 },
                    bodyFont: { size: 11 },
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: Rp ${ctx.parsed.y.toLocaleString('id-ID')} Jt`,
                    },
                },
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 10 }, color: '#94a3b8' },
                },
                y: {
                    stacked: true,
                    grid: { color: '#f1f5f9' },
                    border: { dash: [3, 3], display: false },
                    ticks: {
                        font: { size: 10 },
                        color: '#94a3b8',
                        callback: v => `${v} Jt`,
                    },
                },
            },
        },
    });
})();
</script>
@endsection
