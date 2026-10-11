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
                @php
                    $opPerms = [
                        'umum_rt', 'dokumen_arsip', 'administrasi_aset',
                        'logistik_pelaporan', 'pengadaan', 'pemeliharaan_pengawasan'
                    ];
                    $isOperational = in_array($cfg['perm'] ?? '', $opPerms);
                    $isStatusField = $isOperational && (
                        in_array($field, ['status', 'status_persetujuan', 'status_arsip', 'status_pembayaran'])
                        || stripos($field, 'status') !== false
                        || stripos($meta['label'] ?? '', 'status') !== false
                    );
                    $techFields = ['maker_id', 'checker_id', 'approval_status', 'approved_at', 'catatan_approval', 'dibuat_oleh'];
                @endphp
                @continue($isStatusField || in_array($field, $techFields) || ($meta['type'] ?? '') === 'auto_user' || ($meta['type'] ?? '') === 'file')
                @php
                    $isWide = in_array($meta['type'] ?? '', ['textarea']);
                    $isReq = (bool) ($meta['req'] ?? false);
                @endphp
                <div class="{{ $isWide ? 'md:col-span-2' : '' }}">
                    <label class="block text-[13px] font-medium mb-1.5 text-slate-700">
                        {{ $meta['label'] }} @if($isReq)<span class="text-red-500 font-bold">*</span>@endif
                    </label>

                    @if (($meta['type'] ?? '') === 'textarea')
                        <textarea name="{{ $field }}" rows="3" {{ $isReq ? 'required' : '' }}
                            class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm focus:ring-1 transition">{{ old($field, $item?->$field) }}</textarea>
                    @elseif (($meta['type'] ?? '') === 'select')
                        <select name="{{ $field }}" {{ $isReq ? 'required' : '' }}
                            class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm bg-white focus:ring-1 transition">
                            <option value="">— pilih {{ strtolower($meta['label'] ?? '') }} —</option>
                            @if (!empty($meta['opts']) && is_array($meta['opts']))
                                @foreach ($meta['opts'] as $opt)
                                    <option value="{{ $opt }}" @selected(old($field, $item?->$field) === $opt)>{{ $opt }}</option>
                                @endforeach
                            @endif
                        </select>
                    @elseif (($meta['type'] ?? '') === 'checkbox')
                        <label class="inline-flex items-center gap-2 mt-1">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $item?->$field)) class="rounded border-slate-300 text-brand focus:ring-brand">
                            <span class="text-sm text-slate-500">Ya</span>
                        </label>
                    @elseif (($meta['type'] ?? '') === 'date')
                        @php
                            $dateVal = old($field, $item?->$field);
                            if ($dateVal instanceof \Illuminate\Support\Carbon) {
                                $dateVal = $dateVal->format('Y-m-d');
                            } elseif (is_string($dateVal)) {
                                $dateVal = substr($dateVal, 0, 10);
                            }
                        @endphp
                        <input type="date" name="{{ $field }}" value="{{ $dateVal }}" {{ $isReq ? 'required' : '' }}
                               class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm focus:ring-1 transition">
                    @elseif (in_array($meta['type'] ?? '', ['number', 'money']))
                        <input type="number" step="{{ ($meta['type'] ?? '') === 'money' ? '1' : '0.01' }}" name="{{ $field }}" value="{{ old($field, $item?->$field) }}" {{ $isReq ? 'required' : '' }}
                               class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm font-mono focus:ring-1 transition">
                    @else
                        <input type="text" name="{{ $field }}" value="{{ old($field, $item?->$field) }}" {{ $isReq ? 'required' : '' }}
                               class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm focus:ring-1 transition">
                    @endif

                    @error($field)
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror

                    @if (!empty($meta['help']))
                        <p class="text-[12px] text-slate-400 mt-1">{{ $meta['help'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="pt-6 mt-6 border-t border-slate-100 flex gap-3">
            <button type="submit" class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-light transition">Simpan</button>
            <a href="{{ route('modul.index', $key) }}" class="text-sm text-slate-500 px-5 py-2.5 hover:text-ink transition">Batal</a>
        </div>
    </form>
@endsection
