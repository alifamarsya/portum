@extends('layouts.app')
@section('title', 'Pelacakan Tiket ' . $ticket->ticket_number)

@section('content')
    <div class="mb-6">
        <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-brand transition mb-2">
            &larr; Kembali ke Daftar Tiket
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-0.5">Pelacakan &amp; Detail Tiket</p>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold font-mono text-[#114E84]">{{ $ticket->ticket_number }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $ticket->status_badge }}">
                        {{-- Tampilkan label ramah untuk pemohon, istilah internal untuk operator/bagian --}}
                        @if (auth()->user()->isUser())
                            {{ $ticket->pemohon_status_label }}
                        @else
                            {{ $ticket->status }}
                        @endif
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold {{ $ticket->priority_badge }}">
                        Prioritas: {{ $ticket->priority }}
                    </span>
                </div>
            </div>

            @if (auth()->user()->isOperator() && $ticket->status === 'Menunggu Verifikasi')
                <div class="flex items-center gap-2">
                    <a href="{{ route('tickets.edit', $ticket) }}"
                       class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-md transition">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-white'])
                        Verifikasi &amp; Alokasikan Tiket &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        {{-- Left Column: Informasi Utama Permintaan --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- WIDGET SLA METRICS: Side-by-Side dengan Circular Clock Timer --}}
            @php
                $sla = $ticket->sla_response;
                $slaRes = $ticket->sla_resolution;

                // SLA Response calculation for Circular Clock
                $slaPct = min(max((float)($sla['percentage_used'] ?? 0), 0), 100);
                $slaCircumference = 276.46; // 2 * pi * 44
                $slaOffset = $slaCircumference * (1 - ($slaPct / 100));
                $slaStroke = $sla['is_overdue'] ? '#f43f5e' : ($sla['is_warning'] ? '#f59e0b' : '#10b981');

                // SLA Resolution calculation for Circular Clock
                $slaResPct = min(max((float)($slaRes['percentage_used'] ?? 0), 0), 100);
                $slaResOffset = $slaCircumference * (1 - ($slaResPct / 100));
                $slaResStroke = $slaRes['is_overdue'] ? '#f43f5e' : ($slaRes['is_warning'] ? '#f59e0b' : '#10b981');
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
                {{-- 1. CARD SLA RESPONSE TIME (Operator) --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5 flex flex-col justify-between relative overflow-hidden">
                    <div>
                        {{-- Header Card --}}
                        <div class="flex items-start justify-between gap-3 pb-3 mb-4 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-ink">SLA Response Time</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sla['badge_class'] }}">
                                        {{ $sla['status'] }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5">Verifikasi Operator &bull; Maks. 2 Jam Kerja</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if ($sla['is_verified'])
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3 h-3 text-emerald-600'])
                                        Telah Diverifikasi
                                    </span>
                                @elseif ($sla['is_waiting_start'] ?? false)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu Jam Kerja
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-ping"></span>
                                        Berjalan
                                    </span>
                                @endif

                                @if (auth()->user()->isOperator() && $ticket->status === 'Menunggu Verifikasi')
                                    <a href="{{ route('tickets.edit', $ticket) }}"
                                       class="inline-flex items-center gap-1 bg-[#114E84] hover:bg-[#0E4272] text-white text-[11px] font-bold px-3 py-1.5 rounded-lg shadow-xs transition">
                                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3 h-3 text-white'])
                                        Verifikasi &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Body: Menunggu Jam Kerja vs Circular Timer --}}
                        @if ($sla['is_waiting_start'] ?? false)
                            <div class="flex items-center gap-4 p-3.5 bg-amber-50/60 rounded-xl border border-amber-200/80 my-2">
                                <div class="w-16 h-16 rounded-full border-2 border-dashed border-amber-300 flex items-center justify-center flex-shrink-0 bg-white text-amber-600 font-bold text-xs">
                                    2j
                                </div>
                                <div class="text-xs text-amber-900 leading-relaxed">
                                    <p class="font-bold">Timer Belum Dimulai</p>
                                    <p class="text-[11px] text-amber-700 mt-0.5">
                                        Berjalan otomatis saat jam kerja pada <strong>{{ $sla['start_at']->translatedFormat('l, d M Y - H:i') }} WITA</strong>. Target: 2 Jam Kerja Operator.
                                    </p>
                                </div>
                            </div>
                        @else
                            {{-- Circular Timer & Center Metrics --}}
                            <div class="flex flex-col sm:flex-row items-center gap-5 my-2">
                                {{-- Circular Clock SVG --}}
                                <div class="relative w-28 h-28 flex-shrink-0 flex items-center justify-center">
                                    <svg class="w-28 h-28 transform -rotate-90 origin-center" viewBox="0 0 100 100">
                                        {{-- Background Track --}}
                                        <circle cx="50" cy="50" r="44" stroke="#f1f5f9" stroke-width="7" fill="none" />
                                        {{-- Progress Arc --}}
                                        <circle cx="50" cy="50" r="44" stroke="{{ $slaStroke }}" stroke-width="7" stroke-linecap="round" fill="none"
                                                stroke-dasharray="{{ $slaCircumference }}"
                                                stroke-dashoffset="{{ $slaOffset }}"
                                                class="transition-all duration-700 ease-out" />
                                    </svg>
                                    {{-- Text in Center of Circle --}}
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                        @if ($sla['is_verified'])
                                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-0.5">
                                                @include('partials.icon', ['name' => 'check', 'class' => 'w-4 h-4 text-emerald-600', 'stroke' => 2.5])
                                            </div>
                                            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Selesai</span>
                                        @else
                                            <span class="text-xl font-black {{ $sla['is_overdue'] ? 'text-rose-600' : ($sla['is_warning'] ? 'text-amber-600' : 'text-slate-800') }} leading-none tracking-tight">
                                                {{ $slaPct }}%
                                            </span>
                                            <span class="text-[9.5px] font-semibold mt-1 uppercase tracking-wider {{ $sla['is_overdue'] ? 'text-rose-500' : ($sla['is_warning'] ? 'text-amber-600' : 'text-slate-400') }}">
                                                {{ $sla['is_overdue'] ? 'Overdue' : 'Terpakai' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Summary Metrics --}}
                                <div class="flex-1 w-full space-y-2.5 text-xs">
                                    {{-- Status / Sisa Waktu Highlight Pill --}}
                                    <div class="px-3 py-2 rounded-xl flex items-center justify-between {{ $sla['is_verified'] ? 'bg-emerald-50/80 border border-emerald-200 text-emerald-900' : ($sla['is_overdue'] ? 'bg-rose-50 border border-rose-200 text-rose-900' : ($sla['is_warning'] ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-slate-50 border border-slate-200 text-slate-800')) }}">
                                        <div class="flex items-center gap-1.5 font-medium text-[11px]">
                                            @if ($sla['is_verified'])
                                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                                                <span>Status:</span>
                                            @elseif ($sla['is_overdue'])
                                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                                <span class="text-rose-700 font-semibold">Keterlambatan:</span>
                                            @elseif ($sla['is_warning'])
                                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span class="text-amber-700 font-semibold">Sisa Waktu:</span>
                                            @else
                                                @include('partials.icon', ['name' => 'clock', 'class' => 'w-3.5 h-3.5 text-slate-500'])
                                                <span>Sisa Waktu:</span>
                                            @endif
                                        </div>
                                        <span class="font-bold text-xs">
                                            @if ($sla['is_verified'])
                                                {{ $sla['status'] }}
                                            @else
                                                {{ $sla['remaining_formatted'] }}
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Details --}}
                                    <div class="space-y-1.5 text-slate-600">
                                        <div class="flex justify-between py-0.5 border-b border-slate-100">
                                            <span class="text-slate-400">Mulai:</span>
                                            <span class="font-semibold text-slate-700 font-mono">{{ $sla['start_at']->format('d M, H:i') }}</span>
                                        </div>
                                        <div class="flex justify-between py-0.5 border-b border-slate-100">
                                            <span class="text-slate-400">Batas Waktu:</span>
                                            <span class="font-semibold {{ $sla['is_overdue'] ? 'text-rose-600' : 'text-slate-700' }} font-mono">{{ $sla['due_at']->format('d M, H:i') }}</span>
                                        </div>
                                        <div class="flex justify-between py-0.5">
                                            <span class="text-slate-400">Terpakai:</span>
                                            <span class="font-bold text-slate-800">{{ $sla['elapsed_formatted'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($sla['is_verified'] && $ticket->verified_at)
                        <div class="mt-3 pt-2.5 border-t border-slate-100 text-[11px] text-slate-500 flex items-center gap-1.5">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-emerald-600 flex-shrink-0'])
                            <span class="truncate">Diverifikasi {{ $ticket->verified_at->format('d M, H:i') }} WITA</span>
                        </div>
                    @endif
                </div>

                {{-- 2. CARD SLA RESOLUTION TIME (Bagian) --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5 flex flex-col justify-between relative overflow-hidden">
                    <div>
                        {{-- Header Card --}}
                        <div class="flex items-start justify-between gap-3 pb-3 mb-4 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-ink">SLA Resolution Time</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $slaRes['badge_class'] }}">
                                        {{ $slaRes['status'] }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    Target: <strong>{{ $slaRes['target_hours'] }} Jam</strong> &bull; Prioritas: <strong>{{ $ticket->priority ?? 'Sedang' }}</strong>
                                </p>
                            </div>
                            @if (!$slaRes['is_started'])
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-amber-50 text-amber-800 border border-amber-200 flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Bagian
                                </span>
                            @elseif ($slaRes['is_resolved'])
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 flex-shrink-0">
                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3 h-3 text-emerald-600'])
                                    Telah Selesai
                                </span>
                            @elseif ($slaRes['is_waiting_start'] ?? false)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-amber-50 text-amber-800 border border-amber-200 flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Jam Kerja
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-blue-50 text-blue-800 border border-blue-200 flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-ping"></span>
                                    Berjalan
                                </span>
                            @endif
                        </div>

                        {{-- Circular Timer & Center Metrics --}}
                        @if ($slaRes['is_started'] && !($slaRes['is_waiting_start'] ?? false))
                            <div class="flex flex-col sm:flex-row items-center gap-5 my-2">
                                {{-- Circular Clock SVG --}}
                                <div class="relative w-28 h-28 flex-shrink-0 flex items-center justify-center">
                                    <svg class="w-28 h-28 transform -rotate-90 origin-center" viewBox="0 0 100 100">
                                        {{-- Background Track --}}
                                        <circle cx="50" cy="50" r="44" stroke="#f1f5f9" stroke-width="7" fill="none" />
                                        {{-- Progress Arc --}}
                                        <circle cx="50" cy="50" r="44" stroke="{{ $slaResStroke }}" stroke-width="7" stroke-linecap="round" fill="none"
                                                stroke-dasharray="{{ $slaCircumference }}"
                                                stroke-dashoffset="{{ $slaResOffset }}"
                                                class="transition-all duration-700 ease-out" />
                                    </svg>
                                    {{-- Text in Center of Circle --}}
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                        @if ($slaRes['is_resolved'])
                                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-0.5">
                                                @include('partials.icon', ['name' => 'check', 'class' => 'w-4 h-4 text-emerald-600', 'stroke' => 2.5])
                                            </div>
                                            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Selesai</span>
                                        @else
                                            <span class="text-xl font-black {{ $slaRes['is_overdue'] ? 'text-rose-600' : ($slaRes['is_warning'] ? 'text-amber-600' : 'text-slate-800') }} leading-none tracking-tight">
                                                {{ $slaResPct }}%
                                            </span>
                                            <span class="text-[9.5px] font-semibold mt-1 uppercase tracking-wider {{ $slaRes['is_overdue'] ? 'text-rose-500' : ($slaRes['is_warning'] ? 'text-amber-600' : 'text-slate-400') }}">
                                                {{ $slaRes['is_overdue'] ? 'Overdue' : 'Terpakai' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Summary Metrics --}}
                                <div class="flex-1 w-full space-y-2.5 text-xs">
                                    {{-- Status / Sisa Waktu Highlight Pill --}}
                                    <div class="px-3 py-2 rounded-xl flex items-center justify-between {{ $slaRes['is_resolved'] ? 'bg-emerald-50/80 border border-emerald-200 text-emerald-900' : ($slaRes['is_overdue'] ? 'bg-rose-50 border border-rose-200 text-rose-900' : ($slaRes['is_warning'] ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-slate-50 border border-slate-200 text-slate-800')) }}">
                                        <div class="flex items-center gap-1.5 font-medium text-[11px]">
                                            @if ($slaRes['is_resolved'])
                                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                                                <span>Status:</span>
                                            @elseif ($slaRes['is_overdue'])
                                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                                <span class="text-rose-700 font-semibold">Keterlambatan:</span>
                                            @elseif ($slaRes['is_warning'])
                                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span class="text-amber-700 font-semibold">Sisa Waktu:</span>
                                            @else
                                                @include('partials.icon', ['name' => 'clock', 'class' => 'w-3.5 h-3.5 text-slate-500'])
                                                <span>Sisa Waktu:</span>
                                            @endif
                                        </div>
                                        <span class="font-bold text-xs">
                                            @if ($slaRes['is_resolved'])
                                                {{ $slaRes['status'] }}
                                            @else
                                                {{ $slaRes['remaining_formatted'] }}
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Details --}}
                                    <div class="space-y-1.5 text-slate-600">
                                        <div class="flex justify-between py-0.5 border-b border-slate-100">
                                            <span class="text-slate-400">Mulai:</span>
                                            <span class="font-semibold text-slate-700 font-mono">{{ $slaRes['start_at'] ? $slaRes['start_at']->format('d M, H:i') : '-' }}</span>
                                        </div>
                                        <div class="flex justify-between py-0.5 border-b border-slate-100">
                                            <span class="text-slate-400">Batas Waktu:</span>
                                            <span class="font-semibold {{ $slaRes['is_overdue'] ? 'text-rose-600' : 'text-slate-700' }} font-mono">{{ $slaRes['due_at'] ? $slaRes['due_at']->format('d M, H:i') : '-' }}</span>
                                        </div>
                                        <div class="flex justify-between py-0.5">
                                            <span class="text-slate-400">Terpakai:</span>
                                            <span class="font-bold text-slate-800">{{ $slaRes['elapsed_formatted'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif ($slaRes['is_waiting_start'] ?? false)
                            <div class="flex items-center gap-4 p-3.5 bg-amber-50/60 rounded-xl border border-amber-200/80 my-2">
                                <div class="w-16 h-16 rounded-full border-2 border-dashed border-amber-300 flex items-center justify-center flex-shrink-0 bg-white text-amber-600 font-bold text-xs">
                                    {{ $slaRes['target_hours'] }}j
                                </div>
                                <div class="text-xs text-amber-900 leading-relaxed">
                                    <p class="font-bold">Timer Belum Dimulai</p>
                                    <p class="text-[11px] text-amber-700 mt-0.5">
                                        Berjalan otomatis saat jam kerja pada <strong>{{ $slaRes['start_at'] ? $slaRes['start_at']->translatedFormat('l, d M Y - H:i') : '-' }} WITA</strong>. Target: {{ $slaRes['target_hours'] }} Jam Kerja.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-4 p-3.5 bg-amber-50/60 rounded-xl border border-amber-200/80 my-2">
                                <div class="w-16 h-16 rounded-full border-2 border-dashed border-amber-300 flex items-center justify-center flex-shrink-0 bg-white text-amber-600 font-bold text-xs">
                                    {{ $slaRes['target_hours'] }}j
                                </div>
                                <div class="text-xs text-amber-900 leading-relaxed">
                                    <p class="font-bold">Timer Belum Dimulai</p>
                                    <p class="text-[11px] text-amber-700 mt-0.5">
                                        Berjalan otomatis saat diterima oleh <strong>{{ $ticket->department?->name ?? 'Bagian Terkait' }}</strong>. Target: {{ $slaRes['target_hours'] }} Jam Kerja.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($ticket->kategori_pekerjaan || $ticket->skala_eselonisasi || $ticket->estimasi_biaya)
                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap gap-1.5 text-[10px]">
                            @if ($ticket->kategori_pekerjaan)
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                    Kategori: {{ $ticket->kategori_pekerjaan }}
                                </span>
                            @endif
                            @if ($ticket->skala_eselonisasi)
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                    Skala: {{ $ticket->skala_eselonisasi }}
                                </span>
                            @endif
                            @if ($ticket->estimasi_biaya)
                                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">
                                    Biaya: Rp {{ number_format($ticket->estimasi_biaya, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
                <h2 class="text-base font-bold text-ink mb-4 pb-2.5 border-b border-slate-100 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'file-text', 'class' => 'w-4 h-4 text-brand'])
                    Informasi Utama Permintaan
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Nomor Tiket</span>
                        <span class="font-bold font-mono text-[#114E84] text-base">{{ $ticket->ticket_number }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Tanggal Pengajuan</span>
                        <span class="font-semibold text-slate-700">{{ $ticket->created_at->format('d F Y, H:i') }} WITA</span>
                        <span class="text-[11px] text-slate-400 block">({{ $ticket->created_at->diffForHumans() }})</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Nama Pemohon</span>
                        <span class="font-bold text-ink">{{ $ticket->user?->nama_lengkap ?? '-' }}</span>
                        <span class="text-xs text-slate-500 block">{{ $ticket->user?->bagian ?? '-' }} ({{ $ticket->user?->jabatan ?? '-' }})</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Jenis Pengajuan</span>
                        <span class="inline-flex items-center gap-1.5 font-bold text-xs px-2.5 py-1 rounded-full border {{ $ticket->jenis_badge }}">
                            {{ $ticket->jenis_pengajuan ?? 'Permintaan' }}
                        </span>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="text-xs text-slate-400 block font-medium">Bagian Penanganan / Pelaksana</span>
                        @if ($ticket->department)
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="inline-flex items-center gap-2 font-bold text-[#114E84] bg-blue-50/70 border border-blue-200/60 px-3 py-1.5 rounded-lg text-xs">
                                    <span class="w-2 h-2 rounded-full bg-[#114E84]"></span>
                                    {{ $ticket->department->name }}
                                </span>
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 px-3 py-1.5 rounded-lg mt-1 font-medium border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Sedang menunggu verifikasi &amp; alokasi oleh Operator Helpdesk
                            </span>
                        @endif
                    </div>

                    {{-- Unit Kerja Tujuan (jika ada) --}}
                    @if ($ticket->unitKerja)
                        <div class="sm:col-span-2 flex flex-wrap gap-2 items-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 border border-violet-200">
                                @include('partials.icon', ['name' => 'briefcase', 'class' => 'w-3 h-3'])
                                Unit Kerja: {{ $ticket->unitKerja->nama }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Deskripsi Masalah --}}
                <div class="mb-6">
                    <span class="text-xs text-slate-400 block font-medium mb-1.5">Deskripsi Lengkap / Uraian Masalah:</span>
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm whitespace-pre-line leading-relaxed">
                        {{ $ticket->description }}
                    </div>
                </div>

                {{-- Lampiran PDF (Multiple) --}}
                @php
                    $attachments = $ticket->attachments;
                @endphp
                @if ($attachments->isNotEmpty() || $ticket->attachment_path)
                    <div>
                        <span class="text-xs text-slate-400 block font-medium mb-2">Dokumen Lampiran Pendukung (PDF):</span>
                        <div class="space-y-2">
                            @forelse ($attachments as $att)
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-lg bg-red-50 border border-red-200 flex items-center justify-center text-red-600 flex-shrink-0 font-bold text-xs">
                                            PDF
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-slate-800 truncate" title="{{ $att->file_name }}">
                                                {{ $att->file_name }}
                                            </p>
                                            <p class="text-[11px] text-slate-400 uppercase font-mono">{{ $att->formatted_size }} &bull; Dokumen Lampiran</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <a href="{{ route('tickets.attachment.view-file', $att) }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-white hover:bg-blue-50 text-[#114E84] text-xs font-semibold transition border border-blue-200 shadow-2xs">
                                            @include('partials.icon', ['name' => 'eye', 'class' => 'w-3.5 h-3.5'])
                                            Buka Dokumen
                                        </a>
                                        <a href="{{ route('tickets.attachment.download-file', $att) }}"
                                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold transition shadow-2xs">
                                            @include('partials.icon', ['name' => 'download', 'class' => 'w-3.5 h-3.5 text-white'])
                                            Unduh
                                        </a>
                                    </div>
                                </div>
                            @empty
                                @if ($ticket->attachment_path)
                                    @php
                                        $fileName = basename($ticket->attachment_path);
                                    @endphp
                                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-9 h-9 rounded-lg bg-red-50 border border-red-200 flex items-center justify-center text-red-600 flex-shrink-0 font-bold text-xs">
                                                PDF
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-bold text-slate-800 truncate">{{ $fileName }}</p>
                                                <p class="text-[11px] text-slate-400 uppercase font-mono">Lampiran Tiket</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <a href="{{ route('tickets.attachment', $ticket) }}" target="_blank"
                                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-white hover:bg-blue-50 text-[#114E84] text-xs font-semibold transition border border-blue-200 shadow-2xs">
                                                @include('partials.icon', ['name' => 'eye', 'class' => 'w-3.5 h-3.5'])
                                                Buka
                                            </a>
                                            <a href="{{ route('tickets.attachment.download', $ticket) }}"
                                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold transition shadow-2xs">
                                                @include('partials.icon', ['name' => 'download', 'class' => 'w-3.5 h-3.5 text-white'])
                                                Unduh
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            {{-- PANEL TINDAK LANJUT ROLE BAGIAN (Terima, Tolak, Update Progres, Konfirmasi Selesai) --}}
            @php
                $user = auth()->user();
                $isAuthorizedBagian = ($user->isBagian() && $user->effectiveDepartmentId() == $ticket->department_id) 
                    || $user->isSuperAdmin();
            @endphp

            @if ($isAuthorizedBagian)
                {{-- A. Tahap Penerimaan / Penolakan (Status: Dialokasikan / Diverifikasi / Didistribusikan sebelum diterima) --}}
                @if (in_array($ticket->status, ['Dialokasikan', 'Diverifikasi', 'Didistribusikan']) && is_null($ticket->completed_at))
                    <div class="bg-gradient-to-br from-blue-50/90 via-white to-indigo-50/50 rounded-2xl border-2 border-blue-300/80 shadow-lg p-6 relative overflow-hidden">
                        <div class="flex items-start justify-between gap-4 pb-4 mb-5 border-b border-blue-200/80">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#114E84] text-white flex items-center justify-center font-bold flex-shrink-0 shadow-xs">
                                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-5 h-5 text-white'])
                                </div>
                                <div>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-100 text-[#114E84] border border-blue-200 uppercase tracking-wider">
                                        Wewenang {{ $ticket->department?->name ?? 'Bagian' }}
                                    </span>
                                    <h3 class="text-base font-bold text-ink mt-0.5">Penerimaan &amp; Tindak Lanjut Tiket</h3>
                                    <p class="text-xs text-slate-500">Tiket telah diverifikasi &amp; dialokasikan ke bagian Anda. Anda dapat menerima untuk langsung memproses tiket atau menolaknya jika tidak sesuai.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            {{-- Form Terima Tiket --}}
                            <form method="POST" action="{{ route('tickets.accept', $ticket) }}" class="inline-block flex-1 sm:flex-initial">
                                @csrf
                                <button type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow transition">
                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-emerald-200'])
                                    <span>Terima &amp; Mulai Pengerjaan Tiket</span>
                                </button>
                            </form>

                            {{-- Trigger Tolak Tiket --}}
                            <button type="button" onclick="document.getElementById('reject-ticket-panel').classList.toggle('hidden')"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-3 rounded-xl bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200 shadow-2xs transition">
                                @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-4 h-4 text-rose-600'])
                                <span>Tolak Tiket Ini</span>
                            </button>
                        </div>

                        {{-- Form Penolakan Tersembunyi --}}
                        <div id="reject-ticket-panel" class="hidden mt-4 pt-4 border-t border-blue-200/80">
                            <form method="POST" action="{{ route('tickets.reject', $ticket) }}" class="space-y-3 p-4 rounded-xl bg-rose-50/80 border border-rose-200">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-rose-900 mb-1">Alasan Penolakan Tiket <span class="text-rose-600">*</span></label>
                                    <textarea name="notes" rows="2" required
                                              placeholder="Jelaskan alasan penolakan secara jelas (contoh: di luar kewenangan operasional, data tidak lengkap, pagu anggaran tidak memadai)..."
                                              class="w-full border border-rose-300 rounded-lg p-2.5 text-xs text-ink bg-white focus:ring-1 focus:ring-rose-500"></textarea>
                                </div>
                                <button type="submit" onclick="return confirm('Yakin ingin menolak tiket ini?')"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition">
                                    @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-3.5 h-3.5'])
                                    <span>Konfirmasi Tolak Tiket</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- B. Tahap Pengerjaan (Status: Dalam Proses): Form Update Progres & Konfirmasi Selesai --}}
                @if ($ticket->status === 'Dalam Proses')
                    <div class="bg-gradient-to-br from-violet-50/90 via-white to-blue-50/60 rounded-2xl border-2 border-violet-300/80 shadow-lg p-6 relative overflow-hidden space-y-5">
                        <div class="flex items-start justify-between gap-4 pb-4 border-b border-violet-200/80">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-violet-600 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-xs">
                                    @include('partials.icon', ['name' => 'sliders', 'class' => 'w-5 h-5 text-white'])
                                </div>
                                <div>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-violet-100 text-violet-800 border border-violet-200 uppercase tracking-wider">
                                        Pengerjaan Sedang Berlangsung
                                    </span>
                                    <h3 class="text-base font-bold text-ink mt-0.5">Kelola Progres &amp; Konfirmasi Penyelesaian</h3>
                                    <p class="text-xs text-slate-500">Anda dapat mengirim catatan uraian progres berkala untuk pemohon, atau menyelesaikan tiket ini jika pengerjaan telah rampung.</p>
                                </div>
                            </div>
                        </div>

                        {{-- 1. Form Kirim Catatan Update Progres Berkala --}}
                        <form method="POST" action="{{ route('tickets.add-progress', $ticket) }}" class="space-y-3 bg-white p-4 rounded-xl border border-slate-200/90 shadow-2xs">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Kirim Uraian / Catatan Progres Terkini:
                                </label>
                                <textarea name="notes" rows="2" required
                                          placeholder="Contoh: Petugas teknis sedang memeriksa instalasi di lokasi... atau Menunggu konfirmasi suku cadang dari vendor..."
                                          class="w-full border border-slate-300 rounded-xl p-3 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84]"></textarea>
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold shadow transition">
                                @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                                <span>Kirim Catatan Progres</span>
                            </button>
                        </form>

                        {{-- 2. Form Konfirmasi Selesai Pengerjaan --}}
                        <div class="pt-2 border-t border-violet-200/80">
                            <form method="POST" action="{{ route('tickets.complete', $ticket) }}" class="space-y-3"
                                  onsubmit="return confirm('Yakin ingin menyelesaikan tiket ini? Status akan menjadi Selesai dan pemohon diberikan waktu 2 hari kerja untuk konfirmasi.')">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-emerald-900 mb-1">
                                        Catatan Penyelesaian Akhir: <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <textarea name="notes" rows="2"
                                              placeholder="Contoh: Masalah telah selesai ditangani dengan baik. Seluruh fungsi kembali normal..."
                                              class="w-full border border-slate-300 rounded-xl p-3 text-xs text-ink focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600"></textarea>
                                </div>
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow transition">
                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-emerald-200'])
                                    <span>Konfirmasi Pekerjaan Selesai</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Panel Konfirmasi Penutupan untuk Pemohon (muncul saat status = Selesai) --}}
            @if (auth()->user()->isUser() && $ticket->user_id === auth()->id() && $ticket->status === 'Selesai')
                @php $confirmInfo = $ticket->confirmation_info; @endphp
                <div class="bg-gradient-to-br from-emerald-50 via-teal-50/40 to-white border-2 border-emerald-300 rounded-2xl p-6 shadow-card space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-3 border-b border-emerald-200/70">
                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-emerald-950">Pekerjaan Telah Selesai — Menunggu Konfirmasi Anda</h3>
                                <p class="text-xs text-emerald-800 mt-0.5">
                                    Staf pelaksana telah menyelesaikan kendala/permintaan ini pada <strong>{{ $ticket->completed_at ? $ticket->completed_at->format('d M Y, H:i') . ' WITA' : $ticket->updated_at->format('d M Y, H:i') . ' WITA' }}</strong>.
                                </p>
                            </div>
                        </div>

                        {{-- Countdown Badge --}}
                        <div class="flex-shrink-0">
                            @if ($confirmInfo['is_expired'])
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                                    Batas Waktu Berakhir (Auto-Close)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-3.5 h-3.5 text-amber-700'])
                                    <span>Sisa Waktu: <strong>{{ $confirmInfo['remaining_formatted'] }}</strong></span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Info Box Batas Waktu 2 x 24 Jam Kerja & Auto-Close --}}
                    <div class="p-3.5 rounded-xl bg-white/80 border border-emerald-200/90 text-xs text-slate-700 space-y-1.5">
                        <div class="flex items-center gap-2 font-semibold text-emerald-900">
                            @include('partials.icon', ['name' => 'info', 'class' => 'w-4 h-4 text-emerald-600'])
                            <span>Ketentuan Konfirmasi &amp; Penutupan Otomatis (Auto-Close):</span>
                        </div>
                        <ul class="list-disc pl-5 space-y-1 text-slate-600 leading-relaxed text-[12px]">
                            <li>Anda memiliki waktu <strong>2 x 24 jam kerja</strong> (08:00 - 17:00 WITA, Senin - Jumat) untuk mengonfirmasi hasil pekerjaan.</li>
                            <li>Batas akhir konfirmasi: <strong>{{ $confirmInfo['deadline'] ? $confirmInfo['deadline']->translatedFormat('l, d F Y H:i') . ' WITA' : '-' }}</strong>.</li>
                            <li>Jika dalam kurun waktu 2 hari kerja tidak ada respon, sistem akan secara otomatis <strong>menutup tiket (Auto-Close)</strong>.</li>
                        </ul>
                    </div>

                    @error('error')
                        <div class="bg-rose-50 border border-rose-200 rounded-lg px-4 py-2.5">
                            <p class="text-xs text-rose-700 font-medium">{{ $message }}</p>
                        </div>
                    @enderror

                    {{-- Tombol Aksi Konfirmasi Selesai --}}
                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <form method="POST" action="{{ route('tickets.confirm-close', $ticket) }}"
                              onsubmit="return confirm('Konfirmasi bahwa kendala/permintaan Anda sudah benar-benar selesai? Tiket akan ditutup resmi.')"
                              class="inline-block"
                        >
                            @csrf
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-6 py-3 rounded-xl shadow transition">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-emerald-200'])
                                <span>Ya, Masalah Selesai — Tutup Tiket</span>
                            </button>
                        </form>

                        {{-- Tombol Opsi Pekerjaan Belum Selesai (Membuat Tiket Baru) --}}
                        <button type="button" onclick="document.getElementById('report-incomplete-panel').classList.toggle('hidden')"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-xl bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200 shadow-2xs transition">
                            @include('partials.icon', ['name' => 'alert', 'class' => 'w-4 h-4 text-rose-600'])
                            <span>Pekerjaan Belum Selesai? Laporkan &amp; Buat Tiket Baru &rarr;</span>
                        </button>
                    </div>

                    {{-- Dropdown Form Lapor Belum Selesai --}}
                    <div id="report-incomplete-panel" class="hidden mt-3 p-4 rounded-xl bg-rose-50/70 border border-rose-200 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold text-rose-900">
                            @include('partials.icon', ['name' => 'alert', 'class' => 'w-4 h-4 text-rose-600'])
                            <span>Ketentuan Pengajuan Lanjutan</span>
                        </div>
                        <p class="text-xs text-rose-800 leading-relaxed">
                            Sesuai SOP, tiket yang telah diselesaikan oleh staf pelaksana dan/atau ditutup tidak dapat dibuka kembali. Apabila pekerjaan belum tuntas atau kendala masih berulang, uraikan alasannya di bawah ini dan sistem akan mengalihkan Anda ke formulir <strong>Tiket Pengajuan Baru</strong> dengan riwayat tiket ini secara otomatis.
                        </p>
                        <form method="POST" action="{{ route('tickets.report-incomplete', $ticket) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-rose-900 mb-1">
                                    Jelaskan bagian pekerjaan yang belum selesai / kendala yang masih terjadi: <span class="text-rose-600">*</span>
                                </label>
                                <textarea name="reason" rows="3" required
                                          placeholder="Contoh: AC masih belum dingin di area ruang rapat kasir..."
                                          class="w-full border border-rose-300 rounded-lg px-3 py-2 text-xs text-ink bg-white focus:ring-1 focus:ring-rose-500"></textarea>
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition">
                                @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                                <span>Lanjutkan ke Formulir Tiket Baru</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Panel Notifikasi Penutupan Resmi untuk Tiket yang Sudah Ditutup --}}
            @if (in_array($ticket->status, ['Ditutup Pemohon', 'Ditutup Otomatis (Sistem)']))
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 shadow-card flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $ticket->status === 'Ditutup Pemohon' ? 'bg-teal-100 text-teal-700' : 'bg-slate-200 text-slate-700' }} flex items-center justify-center flex-shrink-0">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-ink">
                                {{ $ticket->status === 'Ditutup Pemohon' ? 'Tiket Telah Ditutup & Dikonfirmasi Pemohon' : 'Tiket Ditutup Otomatis oleh Sistem' }}
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Ditutup pada {{ $ticket->closed_at ? $ticket->closed_at->format('d M Y, H:i') . ' WITA' : $ticket->updated_at->format('d M Y, H:i') . ' WITA' }}
                                {{ $ticket->status === 'Ditutup Otomatis (Sistem)' ? '(karena melewati batas waktu konfirmasi 2 hari kerja)' : '' }}.
                            </p>
                        </div>
                    </div>

                    @if (auth()->user()->isUser() && $ticket->user_id === auth()->id())
                        <a href="{{ route('tickets.create', ['description' => '[Tindak Lanjut dari Tiket ' . $ticket->ticket_number . "]\n\nKendala lanjutan:\n\nUraian sebelumnya:\n" . $ticket->description]) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-300 shadow-2xs transition flex-shrink-0">
                            @include('partials.icon', ['name' => 'plus', 'class' => 'w-3.5 h-3.5'])
                            <span>Kendala Belum Selesai? Buat Tiket Baru</span>
                        </a>
                    @endif
                </div>
            @endif
        {{-- Right Column: Timeline / Riwayat Proses (Jejak Langkah Vertikal) --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
                <div class="mb-5 pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-ink flex items-center gap-2">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-brand'])
                        Riwayat &amp; Jejak Langkah
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Pantau alur pengerjaan tiket Anda dari waktu ke waktu</p>
                </div>

                {{-- Vertical Timeline --}}
                <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                    @forelse ($ticket->histories as $index => $h)
                        <div class="relative group">
                            {{-- Step Marker Dot --}}
                            <div class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full border-2 border-white {{ $index === 0 ? 'bg-[#114E84] ring-4 ring-blue-100' : 'bg-slate-300' }} shadow-xs"></div>

                            <div>
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="text-xs font-bold text-ink">{{ $h->new_status }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono whitespace-nowrap">{{ $h->created_at->format('d M, H:i') }}</span>
                                </div>

                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Oleh: <strong class="text-slate-700 font-medium">{{ $h->user?->nama_lengkap ?? 'Sistem' }}</strong>
                                    <span class="text-slate-400">({{ $h->user?->role?->label ?? $h->user?->bagian ?? 'Petugas' }})</span>
                                </div>

                                @if ($h->notes)
                                    <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 mt-2 leading-relaxed">
                                        {{ $h->notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada jejak riwayat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection