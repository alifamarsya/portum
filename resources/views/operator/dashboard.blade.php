@extends('layouts.app')
@section('title', 'Dashboard Operator Helpdesk')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-ink">Dashboard Operator</h1>
            <p class="text-xs text-slate-500 mt-1">Pantau tiket baru dan verifikasi alokasi ke bagian penanggung jawab</p>
        </div>
        <a href="{{ route('tickets.index') }}"
           class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md transition duration-200">
            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4'])
            Semua Tiket
        </a>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Tiket Baru Masuk --}}
        <div class="relative bg-gradient-to-br from-rose-500 to-orange-500 text-white p-5 rounded-2xl shadow-lg overflow-hidden">
            <div class="relative">
                <p class="text-xs font-bold uppercase tracking-wider text-white/80 mb-1">Tiket Baru</p>
                <p class="text-3xl font-extrabold">{{ $stats['baru_masuk'] }}</p>
                <p class="text-[11px] text-white/70 mt-1">Menunggu verifikasi</p>
            </div>
            <div class="absolute top-4 right-4 w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-5 h-5'])
            </div>
        </div>

        {{-- Tiket Sedang Diproses --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Diproses</p>
                <p class="text-3xl font-extrabold text-blue-800">{{ $stats['diproses'] }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Diverifikasi / Dikerjakan</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center">
                @include('partials.icon', ['name' => 'activity', 'class' => 'w-5 h-5'])
            </div>
        </div>

        {{-- Melewati SLA Response Time --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Lewat SLA Respon</p>
                <p class="text-3xl font-extrabold {{ $stats['terlambat'] > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $stats['terlambat'] }}</p>
                <p class="text-[11px] text-slate-400 mt-1">&gt; 2 jam kerja</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
            </div>
        </div>

        {{-- Selesai Bulan Ini --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Selesai</p>
                <p class="text-3xl font-extrabold text-emerald-700">{{ $stats['selesai_bulan_ini'] }}</p>
                <p class="text-[11px] text-slate-400 mt-1">{{ now()->translatedFormat('F Y') }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5'])
            </div>
        </div>
    </div>

    {{-- Stacked Layout (Atas-Bawah): Antrean Tiket di Atas Full-Width, Beban Departemen di Bawah --}}
    <div class="space-y-6">

        {{-- 1. Antrean Tiket Belum Diverifikasi (Full-Width) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-ink flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 {{ $stats['baru_masuk'] > 0 ? 'animate-pulse' : '' }}"></span>
                        Antrean Tiket Belum Diverifikasi
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Segera verifikasi dan alokasikan ke bagian yang sesuai</p>
                </div>
                @if ($stats['baru_masuk'] > 0)
                    <span class="bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold px-3 py-1 rounded-full">
                        {{ $stats['baru_masuk'] }} tiket menunggu
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-left text-[11px] uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3.5 font-semibold">No. Tiket</th>
                            <th class="px-5 py-3.5 font-semibold">Waktu Masuk</th>
                            <th class="px-5 py-3.5 font-semibold">Pemohon</th>
                            <th class="px-5 py-3.5 font-semibold">Jenis</th>
                            <th class="px-5 py-3.5 font-semibold">SLA Respon (Maks 2 Jam)</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($antrian as $t)
                            @php $sla = $t->sla_response; @endphp
                            <tr class="hover:bg-slate-50/80 transition {{ $sla['is_overdue'] ? 'bg-rose-50/40' : ($sla['is_warning'] ? 'bg-amber-50/20' : '') }}">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('tickets.show', $t) }}" class="font-mono font-bold text-[#114E84] hover:underline text-xs">
                                        {{ $t->ticket_number }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600 whitespace-nowrap">
                                    <span class="font-medium">{{ $t->created_at->format('d M, H:i') }}</span>
                                    <span class="text-slate-400 text-[11px]">({{ $t->created_at->diffForHumans() }})</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-semibold text-ink">{{ $t->user?->nama_lengkap ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $t->user?->bagian ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($t->jenis_pengajuan)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $t->jenis_badge }}">
                                            {{ $t->jenis_pengajuan }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $sla['badge_class'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $sla['is_overdue'] ? 'bg-rose-500 animate-ping' : ($sla['is_warning'] ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500') }}"></span>
                                            {{ $sla['remaining_formatted'] }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-mono">
                                            (Batas: {{ $sla['due_at']->format('H:i') }})
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('tickets.edit', $t) }}"
                                       class="inline-flex items-center gap-1.5 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold px-3.5 py-1.5 rounded-lg transition shadow-2xs">
                                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-white'])
                                        Verifikasi &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center">
                                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5'])
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">Semua tiket telah diverifikasi</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada antrean tiket baru yang menunggu saat ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 2. Beban per Departemen (Full Width Grid di Bawah) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-ink">Beban per Departemen</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Sebaran tiket aktif pada masing-masing bagian penanggung jawab</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @forelse ($distribusi as $dept)
                    @php
                        $total = $dept->total_aktif + $dept->total_selesai;
                        $pct   = $total > 0 ? round(($dept->total_aktif / $total) * 100) : 0;
                    @endphp
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <span class="text-xs font-bold text-slate-800 leading-snug" title="{{ $dept->name }}">
                                {{ $dept->name }}
                            </span>
                            <span class="text-xs font-bold text-[#114E84] px-2 py-0.5 rounded-full bg-blue-50 border border-blue-200 whitespace-nowrap">
                                {{ $dept->total_aktif }} aktif
                            </span>
                        </div>
                        <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden mt-3">
                            <div class="h-full rounded-full {{ $dept->total_aktif > 5 ? 'bg-rose-500' : 'bg-[#114E84]' }} transition-all duration-500"
                                 style="width: {{ max($pct, 4) }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-400 mt-1.5 font-medium">
                            <span>{{ $dept->total_selesai }} selesai</span>
                            <span>{{ $total }} total</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 col-span-3">Belum ada departemen terdaftar.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
