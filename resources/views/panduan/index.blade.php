@extends('layouts.app')
@section('title', 'Panduan')
@section('content')
    <div class="mb-5">
        <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Rapat &amp; Referensi</p>
        <h1 class="text-xl font-bold text-ink">Panduan Penggunaan</h1>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            @forelse ($items->groupBy('kategori') as $kategori => $group)
                <div class="bg-white rounded-xl border border-slate-200 shadow-card p-5">
                    <h2 class="font-semibold text-ink mb-3 flex items-center gap-2">
                        @include('partials.icon', ['name' => 'book', 'class' => 'w-4 h-4 text-gold'])
                        {{ $kategori }}
                    </h2>
                    <div class="divide-y divide-slate-100">
                        @foreach ($group as $p)
                            <details class="group py-2.5">
                                <summary class="cursor-pointer flex items-center justify-between gap-3 text-sm font-medium text-ink hover:text-brand transition">
                                    {{ $p->judul }}
                                    <span class="chev text-slate-400 transition-transform flex-shrink-0">
                                        @include('partials.icon', ['name' => 'chevron-down', 'class' => 'w-4 h-4'])
                                    </span>
                                </summary>
                                <div class="text-sm text-slate-600 mt-2.5 leading-relaxed">{!! nl2br(e($p->konten)) !!}</div>
                                <form method="POST" action="{{ route('panduan.destroy', $p) }}" class="mt-2.5" onsubmit="return confirm('Hapus panduan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-slate-400 hover:text-red-600 transition">Hapus panduan ini</button>
                                </form>
                            </details>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-200 shadow-card p-10 text-center">
                    <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        @include('partials.icon', ['name' => 'book', 'class' => 'w-5 h-5'])
                    </div>
                    <p class="text-slate-400 text-sm">Belum ada panduan.</p>
                </div>
            @endforelse
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-card p-5 h-fit">
            <h2 class="font-semibold text-ink mb-3 flex items-center gap-2">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-gold'])
                Tambah Panduan
            </h2>
            <form method="POST" action="{{ route('panduan.store') }}" class="space-y-3">
                @csrf
                <input name="judul" placeholder="Judul" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <input name="kategori" placeholder="Kategori" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <textarea name="konten" placeholder="Konten" rows="4" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition"></textarea>
                <input type="number" name="urutan" placeholder="Urutan" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <button class="w-full bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition">Simpan</button>
            </form>
        </div>
    </div>
@endsection
