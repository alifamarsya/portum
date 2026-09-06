@extends('layouts.app')
@section('title', 'Sistem Tiket Layanan')

@section('content')
    {{-- Header Area --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-1">Layanan Terpusat &amp; Helpdesk</p>
            <h1 class="text-2xl font-bold text-ink flex items-center gap-2.5">
                <span>Daftar Tiket Layanan</span>
                @if (!is_null(auth()->user()->department_id))
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800">Bagian: {{ auth()->user()->department?->name ?? 'Internal' }}</span>
                @elseif (auth()->user()->isUser())
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">Tiket Saya</span>
                @elseif (auth()->user()->isOperator())
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Operator Helpdesk</span>
                @elseif (auth()->user()->isKepalaDivisi())
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">Monitoring Eksekutif</span>
                @endif
            </h1>
        </div>

        {{-- Actions --}}
        @if (!auth()->user()->hasRole('superadmin'))
            <a href="{{ route('tickets.create') }}"
               class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition duration-200">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
                Buat Tiket Baru
            </a>
        @endif
    </div>

    {{-- Stats Overview Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <p class="text-xs font-medium text-slate-500 mb-1">Total Tiket</p>
            <p class="text-2xl font-bold text-ink">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-200 shadow-2xs">
            <p class="text-xs font-medium text-amber-700 mb-1">Menunggu Verifikasi</p>
            <p class="text-2xl font-bold text-amber-800">{{ $stats['menunggu'] }}</p>
        </div>
        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-200 shadow-2xs">
            <p class="text-xs font-medium text-blue-700 mb-1">Dalam Proses</p>
            <p class="text-2xl font-bold text-blue-800">{{ $stats['dalam_proses'] + $stats['diverifikasi'] }}</p>
        </div>
        <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-200 shadow-2xs">
            <p class="text-xs font-medium text-emerald-700 mb-1">Tiket Selesai</p>
            <p class="text-2xl font-bold text-emerald-800">{{ $stats['selesai'] }}</p>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-card p-4 mb-6">
        <form method="GET" action="{{ route('tickets.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Tiket / Deskripsi</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="No. tiket / kata kunci..."
                           class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-brand focus:border-brand">
                    <div class="absolute left-3 top-2.5 text-slate-400">
                        @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full py-2 px-3 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-brand focus:border-brand">
                    <option value="">-- Semua Status --</option>
                    @foreach (['Menunggu Verifikasi', 'Diverifikasi', 'Didistribusikan', 'Dalam Proses', 'Selesai', 'Ditutup Pemohon', 'Ditolak'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                            {{ auth()->user()->isUser() ? match($st) {
                                'Menunggu Verifikasi' => 'Diajukan',
                                'Didistribusikan' => 'Sedang Ditangani',
                                'Dalam Proses' => 'Sedang Dikerjakan',
                                'Ditutup Pemohon' => 'Ditutup (Dikonfirmasi)',
                                'Ditolak' => 'Tidak Dapat Diproses',
                                default => $st
                            } : $st }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori</label>
                <select name="category_id" class="w-full py-2 px-3 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-brand focus:border-brand">
                    <option value="">-- Semua Kategori --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-medium py-2 px-4 rounded-lg transition">
                    Terapkan Filter
                </button>
                @if (request()->hasAny(['search', 'status', 'category_id', 'department_id']))
                    <a href="{{ route('tickets.index') }}" class="px-3 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-lg transition whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Main Tickets Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-[12px] uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3.5 font-semibold whitespace-nowrap">No. Tiket</th>
                        <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Pemohon</th>
                        <th class="px-4 py-3.5 font-semibold">Kategori &amp; Uraian</th>
                        <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Prioritas</th>
                        <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Tujuan Bagian</th>
                        <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-3.5 font-semibold text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tickets as $t)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 font-mono font-bold text-[#114E84] whitespace-nowrap">
                                <a href="{{ route('tickets.show', $t) }}" class="hover:underline">
                                    {{ $t->ticket_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-medium text-ink">{{ $t->user?->nama_lengkap ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $t->user?->bagian ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 min-w-[220px]">
                                <span class="inline-block text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded mb-1">
                                    {{ $t->category?->name ?? 'Umum' }}
                                </span>
                                <p class="text-slate-700 text-xs line-clamp-2">{{ $t->description }}</p>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $t->priority_badge }}">
                                    {{ $t->priority }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                @if ($t->department)
                                    <span class="inline-flex items-center gap-1 font-medium text-slate-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                                        {{ $t->department->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Belum dialokasikan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11.5px] font-medium border {{ $t->status_badge }}">
                                    {{ auth()->user()->isUser() ? $t->pemohon_status_label : $t->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500">
                                {{ $t->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('tickets.show', $t) }}"
                                       class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition">
                                        Detail
                                    </a>
                                    @if ((auth()->user()->isOperator() || !is_null(auth()->user()->department_id)) && !auth()->user()->isKepalaDivisi())
                                        <a href="{{ route('tickets.edit', $t) }}"
                                           class="inline-flex items-center gap-1 bg-brand-light/10 text-[#114E84] hover:bg-brand-light/20 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition">
                                            Proses
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'inbox', 'class' => 'w-6 h-6'])
                                </div>
                                <p class="text-slate-500 font-medium text-sm mb-1">Tidak ada data tiket.</p>
                                <p class="text-slate-400 text-xs">Tiket layanan yang dibuat akan muncul di daftar ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
@endsection
