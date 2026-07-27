@extends('layouts.app')
@section('title', 'Risalah Rapat')
@section('content')
    <div class="flex items-start justify-between mb-5">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Rapat &amp; Referensi</p>
            <h1 class="text-xl font-bold text-ink">Risalah Rapat</h1>
        </div>
        <a href="{{ route('risalah.create') }}"
           class="inline-flex items-center gap-1.5 bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition shadow-card">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
            Tambah
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-[12px] uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Judul</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Pemimpin</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Tempat</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $r)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3 text-ink whitespace-nowrap">{{ $r->tanggal->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-ink font-medium">{{ $r->judul }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->pemimpin }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->tempat }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-3 text-[13px]">
                                    <a href="{{ route('risalah.edit', $r) }}" class="text-brand font-medium hover:underline">Ubah</a>
                                    <form method="POST" action="{{ route('risalah.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus risalah ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-slate-400 hover:text-red-600 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center">
                                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'book', 'class' => 'w-5 h-5'])
                                </div>
                                <p class="text-slate-400 text-sm mb-2">Belum ada risalah rapat.</p>
                                <a href="{{ route('risalah.create') }}" class="text-brand text-sm font-medium hover:underline">+ Tambah risalah pertama</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection
