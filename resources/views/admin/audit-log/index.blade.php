@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Pengaturan</p>
            <h1 class="text-xl font-bold text-ink">Audit Log & Verifikasi Integritas</h1>
        </div>
    </div>

    {{-- Banner Peringatan Jika Ada Manipulasi --}}
    @if (count($tamperedIds) > 0)
        <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm flex items-start gap-3">
            <div class="text-red-500 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-red-800">PERINGATAN: TERDETEKSI ANOMALI / MANIPULASI DATA!</h4>
                <p class="text-xs text-red-700 mt-0.5">
                    Sistem mendeteksi ketidakcocokan nilai hash pada <strong>{{ count($tamperedIds) }} baris data</strong> di bawah. Data pada baris bergaris merah telah diubah secara ilegal di database internal dan merusak integritas rantai hash (hash chain).
                </p>
            </div>
        </div>
    @endif

    {{-- Form Filter Tunggal (Sisi Kiri) & Tombol Sinkronkan (Sisi Kanan Sejajar) --}}
    <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form method="GET" class="flex items-center gap-2 w-full md:w-auto">
            <div class="relative flex-1 md:w-80">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                </span>
                <input name="search" value="{{ request('search') }}" placeholder="Cari username atau modul..."
                       class="w-full border border-slate-300 rounded-lg pl-9 pr-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
            </div>
            <button type="submit" class="bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition whitespace-nowrap">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('admin.audit-log.index') }}" class="text-xs text-slate-500 hover:text-slate-700 underline">Reset</a>
            @endif
        </form>

        <form action="{{ route('admin.audit-log.rehash') }}" method="POST" class="inline-block">
            @csrf
            <button type="submit" class="bg-slate-800 text-white text-xs font-semibold px-3.5 py-2.5 rounded-lg hover:bg-slate-700 transition flex items-center gap-1.5 shadow-sm whitespace-nowrap">
                🔄 Sinkronkan / Verifikasi Ulang Hash
            </button>
        </form>
    </div>

    {{-- Tabel Utama Audit Log --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-[12px] uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Waktu</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">User</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Aksi</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Modul</th>
                        <th class="px-4 py-3 font-semibold">Keterangan</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Status Hash</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $log)
                        <tr class="transition {{ $log->is_tampered ? 'bg-red-50/70 border-l-4 border-l-red-500' : 'hover:bg-slate-50/60' }}">
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500 font-mono text-[12.5px]">{{ $log->created_at }}</td>
                            <td class="px-4 py-3 text-ink font-medium">{{ $log->username }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'px-2.5 py-1 rounded-full text-xs font-medium capitalize',
                                    'bg-emerald-50 text-emerald-700' => str_contains(strtolower($log->aksi), 'tambah') || str_contains(strtolower($log->aksi), 'create'),
                                    'bg-blue-50 text-blue-700' => str_contains(strtolower($log->aksi), 'ubah') || str_contains(strtolower($log->aksi), 'update'),
                                    'bg-red-50 text-red-700' => str_contains(strtolower($log->aksi), 'hapus') || str_contains(strtolower($log->aksi), 'delete'),
                                    'bg-slate-100 text-slate-600' => !str_contains(strtolower($log->aksi), 'tambah') && !str_contains(strtolower($log->aksi), 'create') && !str_contains(strtolower($log->aksi), 'ubah') && !str_contains(strtolower($log->aksi), 'update') && !str_contains(strtolower($log->aksi), 'hapus') && !str_contains(strtolower($log->aksi), 'delete'),
                                ])>{{ $log->aksi }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $log->modul }} / {{ $log->entitas }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ \Illuminate\Support\Str::limit($log->keterangan, 50) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($log->is_tampered)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-700 border border-red-300">
                                        ⚠️ Rusak / Dimanipulasi
                                    </span>
                                @else
                                    <span class="font-mono text-[11.5px] bg-slate-100 text-slate-600 px-2 py-1 rounded-md">
                                        {{ \Illuminate\Support\Str::limit($log->hash, 10) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center text-slate-400">
                                Belum ada aktivitas tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>

    <p class="text-xs text-slate-400 mt-3 flex items-center gap-1.5">
        @include('partials.icon', ['name' => 'lock', 'class' => 'w-3.5 h-3.5 flex-shrink-0'])
        Jalankan <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">php artisan audit:verify-chain</code> untuk memverifikasi integritas seluruh rantai hash di atas.
    </p>
@endsection