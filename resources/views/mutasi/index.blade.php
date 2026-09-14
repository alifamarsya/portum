@extends('layouts.app')
@section('title', 'Mutasi Aset')

@section('content')
    {{-- Header Area --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-1">Pengajuan &amp; Monitoring</p>
            <h1 class="text-2xl font-bold text-ink flex items-center gap-2.5">
                <span>Daftar Mutasi Aset</span>
                @if (auth()->user()->isKabag())
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">Kepala Bagian: {{ auth()->user()->department?->name ?? 'Aset & Logistik' }}</span>
                @elseif (auth()->user()->isUkAdministrasiAset() || auth()->user()->hasRole('aset'))
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800">Staf Administrasi Aset</span>
                @elseif (auth()->user()->isOperator())
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Operator Helpdesk</span>
                @elseif (auth()->user()->isUser())
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">Pengajuan Saya</span>
                @endif
            </h1>
        </div>

        {{-- Actions: Tombol Pengajuan Baru (hanya untuk role User) --}}
        @if (auth()->user()->isUser())
        <div>
            <a href="{{ route('mutasi-aset.create') }}"
               class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md hover:shadow-lg transition duration-200">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
                Buat Pengajuan Mutasi
            </a>
        </div>
        @endif
    </div>

    {{-- Stats Overview Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <p class="text-[10.5px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total</p>
            <p class="text-xl font-bold text-ink">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-amber-50/50 p-3.5 rounded-xl border border-amber-200 shadow-2xs">
            <p class="text-[10.5px] font-semibold text-amber-700 uppercase tracking-wider mb-1">Diajukan</p>
            <p class="text-xl font-bold text-amber-800">{{ $stats['diajukan'] }}</p>
        </div>
        <div class="bg-blue-50/50 p-3.5 rounded-xl border border-blue-200 shadow-2xs">
            <p class="text-[10.5px] font-semibold text-blue-700 uppercase tracking-wider mb-1">Diproses</p>
            <p class="text-xl font-bold text-blue-800">{{ $stats['diproses'] }}</p>
        </div>
        <div class="bg-purple-50/50 p-3.5 rounded-xl border border-purple-200 shadow-2xs">
            <p class="text-[10.5px] font-semibold text-purple-700 uppercase tracking-wider mb-1">Menunggu Kabag</p>
            <p class="text-xl font-bold text-purple-800">{{ $stats['menunggu_approval'] }}</p>
        </div>
        <div class="bg-emerald-50/50 p-3.5 rounded-xl border border-emerald-200 shadow-2xs">
            <p class="text-[10.5px] font-semibold text-emerald-700 uppercase tracking-wider mb-1">Disetujui</p>
            <p class="text-xl font-bold text-emerald-800">{{ $stats['disetujui'] }}</p>
        </div>
        <div class="bg-teal-50/50 p-3.5 rounded-xl border border-teal-200 shadow-2xs">
            <p class="text-[10.5px] font-semibold text-teal-700 uppercase tracking-wider mb-1">Ditutup</p>
            <p class="text-xl font-bold text-teal-800">{{ $stats['ditutup'] }}</p>
        </div>
        <div class="bg-rose-50/50 p-3.5 rounded-xl border border-rose-200 shadow-2xs">
            <p class="text-[10.5px] font-semibold text-rose-700 uppercase tracking-wider mb-1">Ditolak</p>
            <p class="text-xl font-bold text-rose-800">{{ $stats['ditolak'] }}</p>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-card p-4 mb-6">
        <form method="GET" action="{{ route('mutasi-aset.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari No. Mutasi / Aset / Pemohon</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="No. mutasi, kode/nama aset, nama pemohon..."
                           class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-brand focus:border-brand">
                    <div class="absolute left-3 top-2.5 text-slate-400">
                        @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Pengajuan</label>
                <select name="status" class="w-full py-2 px-3 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-brand focus:border-brand">
                    <option value="">-- Semua Status --</option>
                    <option value="Diajukan" {{ request('status') === 'Diajukan' ? 'selected' : '' }}>Diajukan</option>
                    <option value="Diproses" {{ request('status') === 'Diproses' ? 'selected' : '' }}>Diproses (Verifikasi Staf)</option>
                    <option value="Menunggu Approval" {{ request('status') === 'Menunggu Approval' ? 'selected' : '' }}>Menunggu Approval Kabag</option>
                    <option value="Disetujui" {{ request('status') === 'Disetujui' ? 'selected' : '' }}>Disetujui (Menunggu Konfirmasi)</option>
                    <option value="Ditutup" {{ request('status') === 'Ditutup' ? 'selected' : '' }}>Ditutup (Selesai)</option>
                    <option value="Ditolak" {{ request('status') === 'Ditolak' ? 'selected' : '' }}>Ditutup (Ditolak)</option>
                    <option value="Tidak Valid" {{ request('status') === 'Tidak Valid' ? 'selected' : '' }}>Ditutup (Data Tidak Valid)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                       class="w-full py-2 px-3 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-brand focus:border-brand">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-medium py-2 px-4 rounded-lg transition shadow-xs flex items-center justify-center gap-1.5">
                    @include('partials.icon', ['name' => 'search', 'class' => 'w-3.5 h-3.5'])
                    Filter
                </button>
                @if (request()->hasAny(['search', 'status', 'tanggal_mulai', 'tanggal_selesai']))
                    <a href="{{ route('mutasi-aset.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium py-2 px-3 rounded-lg transition" title="Reset Filter">
                        @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-4 h-4'])
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4">No. Mutasi &amp; Tgl</th>
                        <th class="py-3.5 px-4">Pemohon</th>
                        <th class="py-3.5 px-4">Aset yang Dimutasi</th>
                        <th class="py-3.5 px-4">Perpindahan Lokasi</th>
                        <th class="py-3.5 px-4">Penanggung Jawab Baru</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($mutasiList as $item)
                        @php
                            $badge = $item->status_badge;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            {{-- No. Mutasi & Tanggal --}}
                            <td class="py-3 px-4 font-medium text-ink">
                                <a href="{{ route('mutasi-aset.show', $item) }}" class="font-bold text-[#114E84] hover:underline flex items-center gap-1.5">
                                    <span>{{ $item->no_mutasi }}</span>
                                </a>
                                <span class="text-[11px] text-slate-400 block mt-0.5">
                                    {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                                </span>
                            </td>

                            {{-- Pemohon --}}
                            <td class="py-3 px-4">
                                <p class="font-medium text-ink">{{ $item->nama_pemohon ?: ($item->pengaju?->nama_lengkap ?? '-') }}</p>
                                <p class="text-[11px] text-slate-500 truncate max-w-[160px]">
                                    {{ $item->jabatan_pemohon ?: ($item->pengaju?->jabatan ?? $item->pengaju?->bagian ?? '-') }}
                                    @if ($item->username_pemohon)
                                        <span class="text-slate-400 font-mono">({{ $item->username_pemohon }})</span>
                                    @endif
                                </p>
                            </td>

                            {{-- Nama Aset --}}
                            <td class="py-3 px-4">
                                <p class="font-semibold text-ink">{{ $item->aset?->nama_aset ?? '-' }}</p>
                                <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-600">
                                    {{ $item->aset?->kode_aset ?? 'AST-'.str_pad($item->aset_id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            {{-- Lokasi Asal -> Tujuan --}}
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-slate-500 truncate max-w-[110px]" title="{{ $item->dari_lokasi }}">{{ $item->dari_lokasi }}</span>
                                    <span class="text-slate-400 text-xs">➔</span>
                                    <span class="font-semibold text-[#114E84] truncate max-w-[110px]" title="{{ $item->ke_lokasi }}">{{ $item->ke_lokasi }}</span>
                                </div>
                            </td>

                            {{-- PJ Baru --}}
                            <td class="py-3 px-4">
                                <p class="font-medium text-ink truncate max-w-[140px]">{{ $item->ke_penanggung_jawab }}</p>
                                <p class="text-[10.5px] text-slate-400 truncate max-w-[140px]">Semula: {{ $item->dari_penanggung_jawab ?? '-' }}</p>
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $badge['class'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $badge['label'] }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('mutasi-aset.show', $item) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-[#114E84] text-slate-700 hover:text-white rounded-lg transition font-medium text-xs">
                                    @include('partials.icon', ['name' => 'eye', 'class' => 'w-3.5 h-3.5'])
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                    @include('partials.icon', ['name' => 'layers', 'class' => 'w-6 h-6'])
                                </div>
                                <p class="font-semibold text-slate-700 text-sm">Belum Ada Pengajuan Mutasi Aset</p>
                                <p class="text-xs text-slate-500 mt-1">Gunakan tombol "Buat Pengajuan Mutasi" untuk mengajukan perpindahan aset baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mutasiList->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $mutasiList->links() }}
            </div>
        @endif
    </div>
@endsection
