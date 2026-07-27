@extends('layouts.app')
@section('title', $cfg['judul'])
@section('content')
    <div class="flex items-start justify-between mb-5">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">{{ $cfg['modul'] }}</p>
            <h1 class="text-xl font-bold text-ink">{{ $cfg['judul'] }}</h1>
        </div>
        <a href="{{ route('modul.create', $key) }}"
           class="inline-flex items-center gap-1.5 bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition shadow-card">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
            Tambah Data
        </a>
    </div>

    <form method="GET" class="mb-4">
        <div class="relative w-72">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
            </span>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari data..."
                   class="border border-slate-300 rounded-lg pl-9 pr-3.5 py-2 text-sm w-full bg-white focus:border-brand focus:ring-1 focus:ring-brand transition">
        </div>
    </form>

    @php $listFields = collect($cfg['fields'])->filter(fn ($f) => $f['list'] ?? false); @endphp

    <div class="bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-[12px] uppercase tracking-wide text-slate-500">
                        @foreach ($listFields as $field => $meta)
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">{{ $meta['label'] }}</th>
                        @endforeach
                        @if ($cfg['maker_checker'])
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Approval</th>
                        @endif
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            @foreach ($listFields as $field => $meta)
                                <td class="px-4 py-3 text-ink">
                                    @if (($meta['type'] ?? '') === 'money')
                                        <span class="font-mono text-[13px]">Rp {{ number_format((float) $item->$field, 0, ',', '.') }}</span>
                                    @elseif (($meta['type'] ?? '') === 'checkbox')
                                        <span class="px-2 py-0.5 rounded-full text-xs {{ $item->$field ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $item->$field ? 'Ya' : 'Tidak' }}
                                        </span>
                                    @elseif (($meta['type'] ?? '') === 'date')
                                        @php $val = $item->$field; @endphp
                                        {{ $val ? ($val instanceof \Illuminate\Support\Carbon ? $val->format('d M Y') : \Illuminate\Support\Carbon::parse($val)->format('d M Y')) : '-' }}
                                    @elseif (($meta['type'] ?? '') === 'select')
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $item->$field }}</span>
                                    @else
                                        {{ \Illuminate\Support\Str::limit((string) $item->$field, 40) }}
                                    @endif
                                </td>
                            @endforeach
                            @if ($cfg['maker_checker'])
                                <td class="px-4 py-3">
                                    <span @class([
                                        'px-2.5 py-1 rounded-full text-xs font-medium',
                                        'bg-amber-50 text-amber-700' => $item->approval_status === 'Diajukan',
                                        'bg-emerald-50 text-emerald-700' => $item->approval_status === 'Disetujui',
                                        'bg-red-50 text-red-700' => $item->approval_status === 'Ditolak',
                                    ])>{{ $item->approval_status }}</span>
                                </td>
                            @endif
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-3 text-[13px]">
                                    @if ($cfg['maker_checker'] && $item->approval_status === 'Diajukan')
                                        <form method="POST" action="{{ route('modul.approve', [$key, $item->id]) }}">
                                            @csrf
                                            <button class="text-emerald-700 font-medium hover:underline">Setujui</button>
                                        </form>
                                        <form method="POST" action="{{ route('modul.reject', [$key, $item->id]) }}">
                                            @csrf
                                            <button class="text-red-600 font-medium hover:underline">Tolak</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('modul.edit', [$key, $item->id]) }}" class="text-brand font-medium hover:underline">Ubah</a>
                                    <form method="POST" action="{{ route('modul.destroy', [$key, $item->id]) }}"
                                          onsubmit="return confirm('Hapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-slate-400 hover:text-red-600 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-14 text-center">
                                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-5 h-5'])
                                </div>
                                <p class="text-slate-400 text-sm mb-2">Belum ada data {{ strtolower($cfg['judul']) }}.</p>
                                <a href="{{ route('modul.create', $key) }}" class="text-brand text-sm font-medium hover:underline">+ Tambah data pertama</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection
