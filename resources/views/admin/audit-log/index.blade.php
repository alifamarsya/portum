@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')
    <div class="mb-5">
        <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Pengaturan</p>
        <h1 class="text-xl font-bold text-ink">Audit Log</h1>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
            </span>
            <input name="username" value="{{ request('username') }}" placeholder="Cari username"
                   class="border border-slate-300 rounded-lg pl-9 pr-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
        </div>
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
            </span>
            <input name="modul" value="{{ request('modul') }}" placeholder="Cari modul"
                   class="border border-slate-300 rounded-lg pl-9 pr-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
        </div>
        <button class="bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition">Cari</button>
    </form>

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
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Hash</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $log)
                        <tr class="hover:bg-slate-50/60 transition">
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
                            <td class="px-4 py-3">
                                <span class="font-mono text-[11.5px] bg-slate-100 text-slate-600 px-2 py-1 rounded-md">{{ \Illuminate\Support\Str::limit($log->hash, 12) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center">
                                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
                                </div>
                                <p class="text-slate-400 text-sm">Belum ada aktivitas tercatat.</p>
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
