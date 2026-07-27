@extends('layouts.app')
@section('title', ($item ? 'Ubah' : 'Tambah') . ' Risalah Rapat')
@section('content')
    <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Rapat &amp; Referensi</p>
    <h1 class="text-xl font-bold text-ink mb-5">{{ $item ? 'Ubah' : 'Tambah' }} Risalah Rapat</h1>

    <form method="POST" action="{{ $item ? route('risalah.update', $item) : route('risalah.store') }}"
          class="bg-white rounded-xl border border-slate-200 shadow-card p-6 max-w-2xl space-y-4">
        @csrf
        @if ($item) @method('PUT') @endif
        @foreach (['nomor'=>'text','judul'=>'text','tanggal'=>'date','waktu'=>'text','tempat'=>'text','pemimpin'=>'text','peserta'=>'textarea','agenda'=>'textarea','pembahasan'=>'textarea','keputusan'=>'textarea','tindak_lanjut'=>'textarea'] as $field => $type)
            <div>
                <label class="block text-[13px] font-medium mb-1.5 text-slate-700">{{ ucwords(str_replace('_',' ',$field)) }}</label>
                @if ($type === 'textarea')
                    <textarea name="{{ $field }}" rows="3" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">{{ old($field, $item?->$field) }}</textarea>
                @else
                    <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field, $item?->$field) }}" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                @endif
            </div>
        @endforeach
        <div class="pt-6 mt-2 border-t border-slate-100 flex gap-3">
            <button class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-light transition">Simpan</button>
            <a href="{{ route('risalah.index') }}" class="text-sm text-slate-500 px-5 py-2.5 hover:text-ink transition">Batal</a>
        </div>
    </form>
@endsection
