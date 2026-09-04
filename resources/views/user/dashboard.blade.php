@extends('layouts.app')
@section('title', 'Dashboard Layanan Tiket')

@section('content')
    {{-- Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-1">Helpdesk &amp; Layanan Operasional</p>
            <h1 class="text-2xl font-bold text-ink">
                Selamat Datang, {{ auth()->user()->nama_lengkap }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ auth()->user()->bagian ?? 'Cabang / Unit Kerja' }} &bull; {{ auth()->user()->jabatan ?? 'Pemohon' }}
            </p>
        </div>

        <a href="{{ route('tickets.create') }}"
           class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md transition duration-200">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
            Buat Tiket Baru
        </a>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Total Tiket --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Diajukan</p>
                <p class="text-3xl font-extrabold text-ink">{{ $stats['total'] }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Seluruh permohonan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-6 h-6'])
            </div>
        </div>

        {{-- Menunggu Verifikasi --}}
        <div class="bg-gradient-to-br from-amber-500/10 to-amber-500/5 p-5 rounded-2xl border border-amber-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 mb-1">Menunggu Verifikasi</p>
                <p class="text-3xl font-extrabold text-amber-800">{{ $stats['menunggu'] }}</p>
                <p class="text-[11px] text-amber-600 mt-1">Sedang antre di Operator</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-6 h-6'])
            </div>
        </div>

        {{-- Diproses --}}
        <div class="bg-gradient-to-br from-blue-500/10 to-blue-500/5 p-5 rounded-2xl border border-blue-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-700 mb-1">Sedang Diproses</p>
                <p class="text-3xl font-extrabold text-blue-800">{{ $stats['diproses'] }}</p>
                <p class="text-[11px] text-blue-600 mt-1">Diverifikasi / Tindak lanjut</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-[#114E84] flex items-center justify-center">
                @include('partials.icon', ['name' => 'activity', 'class' => 'w-6 h-6'])
            </div>
        </div>

        {{-- Selesai --}}
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-500/5 p-5 rounded-2xl border border-emerald-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Tiket Selesai</p>
                <p class="text-3xl font-extrabold text-emerald-800">{{ $stats['selesai'] }}</p>
                <p class="text-[11px] text-emerald-600 mt-1">Tuntas dikerjakan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
            </div>
        </div>
    </div>

    {{-- Recent Tickets Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-ink">5 Tiket Terakhir Diajukan</h2>
                <p class="text-xs text-slate-400">Pantau status terkini dari permintaan layanan Anda</p>
            </div>
            <a href="{{ route('tickets.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1">
                Lihat Semua Tiket &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-[11.5px] uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3.5 font-semibold">Nomor Tiket</th>
                        <th class="px-5 py-3.5 font-semibold">Tanggal</th>
                        <th class="px-5 py-3.5 font-semibold">Kategori</th>
                        <th class="px-5 py-3.5 font-semibold">Uraian / Masalah</th>
                        <th class="px-5 py-3.5 font-semibold">Prioritas</th>
                        <th class="px-5 py-3.5 font-semibold">Bagian Penanganan</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentTickets as $t)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono font-bold text-[#114E84] whitespace-nowrap">
                                <a href="{{ route('tickets.show', $t) }}" class="hover:underline">
                                    {{ $t->ticket_number }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $t->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="text-xs font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md">
                                    {{ $t->category?->name ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-700 text-xs min-w-[200px]">
                                <p class="line-clamp-1">{{ $t->description }}</p>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $t->priority_badge }}">
                                    {{ $t->priority }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-600 whitespace-nowrap">
                                @if ($t->department)
                                    <span class="inline-flex items-center gap-1 font-medium text-slate-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                                        {{ $t->department->name }}
                                    </span>
                                @else
                                    <span class="text-amber-600 font-medium text-xs">Menunggu Penanganan</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11.5px] font-medium border {{ $t->status_badge }}">
                                    {{-- Gunakan label ramah pemohon alih-alih istilah internal sistem --}}
                                    {{ $t->pemohon_status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('tickets.show', $t) }}"
                                   class="inline-flex items-center gap-1 bg-[#114E84]/10 hover:bg-[#114E84]/20 text-[#114E84] text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                    Lacak Tiket &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'inbox', 'class' => 'w-6 h-6'])
                                </div>
                                <p class="text-slate-600 font-semibold text-sm mb-1">Belum Ada Tiket yang Diajukan</p>
                                <p class="text-slate-400 text-xs mb-4">Jika Anda memiliki kendala sarana, aset, atau permintaan pengadaan, silakan buat tiket baru.</p>
                                <a href="{{ route('tickets.create') }}"
                                   class="inline-flex items-center gap-2 bg-[#114E84] text-white text-xs font-semibold px-4 py-2 rounded-xl shadow hover:bg-[#0E4272] transition">
                                    @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
                                    Buat Tiket Pertama
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
