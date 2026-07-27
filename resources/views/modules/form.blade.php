@extends('layouts.app')
@section('title', ($item ? 'Ubah' : 'Tambah') . ' — ' . $cfg['judul'])
@section('content')
    <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">{{ $cfg['modul'] }}</p>
    <h1 class="text-xl font-bold text-ink mb-5">{{ $item ? 'Ubah' : 'Tambah' }} {{ $cfg['judul'] }}</h1>

    <form method="POST"
          action="{{ $item ? route('modul.update', [$key, $item->id]) : route('modul.store', $key) }}"
          class="bg-white rounded-xl border border-slate-200 shadow-card p-6 max-w-3xl">
        @csrf
        @if ($item) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
            @foreach ($cfg['fields'] as $field => $meta)
                @continue(in_array($field, ['maker_id', 'checker_id', 'approval_status', 'approved_at', 'catatan_approval', 'dibuat_oleh']))
                @php $isWide = in_array($meta['type'] ?? '', ['textarea']); @endphp
                <div class="{{ $isWide ? 'md:col-span-2' : '' }}">
                    <label class="block text-[13px] font-medium mb-1.5 text-slate-700">
                        {{ $meta['label'] }} @if($meta['req'] ?? false)<span class="text-red-500">*</span>@endif
                    </label>

                    @if (($meta['type'] ?? '') === 'textarea')
                        <textarea name="{{ $field }}" rows="3"
                            class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">{{ old($field, $item?->$field) }}</textarea>
                    @elseif (($meta['type'] ?? '') === 'select' && !empty($meta['opts']))
                        <select name="{{ $field }}" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:border-brand focus:ring-1 focus:ring-brand transition">
                            <option value="">— pilih —</option>
                            @foreach ($meta['opts'] as $opt)
                                <option value="{{ $opt }}" @selected(old($field, $item?->$field) === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    @elseif (($meta['type'] ?? '') === 'checkbox')
                        <label class="inline-flex items-center gap-2 mt-1">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $item?->$field)) class="rounded border-slate-300">
                            <span class="text-sm text-slate-500">Ya</span>
                        </label>
                    @elseif (($meta['type'] ?? '') === 'date')
                        <input type="date" name="{{ $field }}" value="{{ old($field, optional($item?->$field)->format('Y-m-d') ?? $item?->$field) }}"
                               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                    @elseif (in_array($meta['type'] ?? '', ['number', 'money']))
                        <input type="number" step="0.01" name="{{ $field }}" value="{{ old($field, $item?->$field) }}"
                               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-mono focus:border-brand focus:ring-1 focus:ring-brand transition">
                    @elseif (($meta['type'] ?? '') === 'file')
                        <input type="text" name="{{ $field }}" value="{{ old($field, $item?->$field) }}"
                               placeholder="Nama file (upload asli menyusul)"
                               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                    @else
                        <input type="text" name="{{ $field }}" value="{{ old($field, $item?->$field) }}"
                               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                    @endif

                    @if (!empty($meta['help']))
                        <p class="text-[12px] text-slate-400 mt-1">{{ $meta['help'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="pt-6 mt-2 border-t border-slate-100 flex gap-3">
            <button class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-light transition">Simpan</button>
            <a href="{{ route('modul.index', $key) }}" class="text-sm text-slate-500 px-5 py-2.5 hover:text-ink transition">Batal</a>
        </div>
    </form>
@endsection
