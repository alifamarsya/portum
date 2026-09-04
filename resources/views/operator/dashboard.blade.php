@extends('layouts.app')
@section('title', 'Dashboard Operator Helpdesk')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-1">Monitoring &amp; Dispatching</p>
            <h1 class="text-2xl font-bold text-ink">Dashboard Operator Helpdesk</h1>
            <p class="text-xs text-slate-500 mt-1">Pantau lalu lintas tiket dan distribusikan ke bagian yang bertanggung jawab</p>
        </div>
        <a href="{{ route('tickets.index') }}"
           class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md transition duration-200">
            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4'])
            Lihat Semua Tiket
        </a>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        {{-- Tiket Baru Masuk --}}
        <div class="relative bg-gradient-to-br from-rose-500 to-orange-500 text-white p-5 rounded-2xl shadow-lg overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_top_right,_white_0%,_transparent_60%)]"></div>
            <div class="relative">
                <p class="text-xs font-bold uppercase tracking-wider text-white/80 mb-1">Tiket Baru Masuk</p>
                <p class="text-4xl font-extrabold">{{ $stats['baru_masuk'] }}</p>
                <p class="text-[11px] text-white/70 mt-1">Menunggu verifikasi</p>
                <a href="{{ route('tickets.index', ['status' => 'Menunggu Verifikasi']) }}"
                   class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-white/90 hover:text-white underline underline-offset-2">
                    Tangani Sekarang &rarr;
                </a>
            </div>
            <div class="absolute top-3 right-3 w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-6 h-6'])
            </div>
            @if ($stats['baru_masuk'] > 0)
                <span class="absolute top-2 right-2 w-3 h-3 bg-white rounded-full animate-ping opacity-60"></span>
            @endif
        </div>

        {{-- Tiket Sedang Diproses --}}
        <div class="bg-gradient-to-br from-blue-500/10 to-blue-500/5 p-5 rounded-2xl border border-blue-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-700 mb-1">Sedang Diproses</p>
                <p class="text-3xl font-extrabold text-blue-800">{{ $stats['diproses'] }}</p>
                <p class="text-[11px] text-blue-600 mt-1">Didistribusikan / Dikerjakan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-[#114E84] flex items-center justify-center">
                @include('partials.icon', ['name' => 'activity', 'class' => 'w-6 h-6'])
            </div>
        </div>

        {{-- Melewati SLA / Terlambat --}}
        <div class="bg-gradient-to-br from-amber-500/10 to-amber-500/5 p-5 rounded-2xl border border-amber-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 mb-1">Melebihi SLA</p>
                <p class="text-3xl font-extrabold text-amber-800">{{ $stats['terlambat'] }}</p>
                <p class="text-[11px] text-amber-600 mt-1">Perlu perhatian segera</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-6 h-6'])
            </div>
        </div>

        {{-- Selesai Bulan Ini --}}
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-500/5 p-5 rounded-2xl border border-emerald-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Selesai Bulan Ini</p>
                <p class="text-3xl font-extrabold text-emerald-800">{{ $stats['selesai_bulan_ini'] }}</p>
                <p class="text-[11px] text-emerald-600 mt-1">{{ now()->translatedFormat('F Y') }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
            </div>
        </div>
    </div>

    {{-- 2-column layout: Antrian + Distribusi --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Antrian Tiket Belum Diverifikasi --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-ink flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 {{ $stats['baru_masuk'] > 0 ? 'animate-pulse' : '' }}"></span>
                        Antrean Tiket Belum Diverifikasi
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Urutan masuk paling awal &mdash; segera distribusikan ke bagian yang tepat</p>
                </div>
                @if ($stats['baru_masuk'] > 0)
                    <span class="bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold px-2.5 py-1 rounded-full">
                        {{ $stats['baru_masuk'] }} tiket
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-left text-[11px] uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3.5 font-semibold">No. Tiket</th>
                            <th class="px-5 py-3.5 font-semibold">Waktu Masuk</th>
                            <th class="px-5 py-3.5 font-semibold">Pengaju</th>
                            <th class="px-5 py-3.5 font-semibold">Kategori</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($antrian as $t)
                            <tr class="hover:bg-rose-50/40 transition">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('tickets.show', $t) }}" class="font-mono font-bold text-[#114E84] hover:underline text-xs">
                                        {{ $t->ticket_number }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                                    <span class="block">{{ $t->created_at->format('d M Y, H:i') }}</span>
                                    <span class="text-rose-500 font-medium">{{ $t->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-semibold text-ink">{{ $t->user?->nama_lengkap ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $t->user?->bagian ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                        {{ $t->category?->name ?? 'Umum' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('tickets.show', $t) }}"
                                       class="inline-flex items-center gap-1 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                        Verifikasi &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center">
                                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-2">
                                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5'])
                                    </div>
                                    <p class="text-sm font-semibold text-slate-600">Tidak ada tiket yang menunggu!</p>
                                    <p class="text-xs text-slate-400">Semua tiket sudah ditangani dengan baik.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Distribusi per Departemen --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
            <h2 class="text-base font-bold text-ink mb-1">Beban per Departemen</h2>
            <p class="text-xs text-slate-400 mb-5">Tiket aktif yang sedang dikerjakan masing-masing bagian</p>

            <div class="space-y-4">
                @forelse ($distribusi as $dept)
                    @php
                        $total = $dept->total_aktif + $dept->total_selesai;
                        $pct   = $total > 0 ? round(($dept->total_aktif / $total) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-slate-700 truncate max-w-[160px]" title="{{ $dept->name }}">
                                {{ $dept->name }}
                            </span>
                            <span class="text-xs font-bold text-[#114E84]">{{ $dept->total_aktif }} aktif</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $dept->total_aktif > 5 ? 'bg-rose-400' : 'bg-[#114E84]' }} transition-all duration-500"
                                 style="width: {{ max($pct, 3) }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10.5px] text-slate-400 mt-0.5">
                            <span>{{ $dept->total_selesai }} selesai</span>
                            <span>{{ $total }} total</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Belum ada departemen terdaftar.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
