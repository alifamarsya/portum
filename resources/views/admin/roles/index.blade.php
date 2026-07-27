@extends('layouts.app')
@section('title', 'Manajemen Role')
@section('content')
    <div class="mb-5">
        <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Pengaturan</p>
        <h1 class="text-xl font-bold text-ink">Matriks Role &amp; Permission</h1>
    </div>
    <div class="space-y-4">
        @foreach ($roles as $role)
            <form method="POST" action="{{ route('admin.roles.permissions', $role) }}" class="bg-white rounded-xl border border-slate-200 shadow-card p-5">
                @csrf
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-gold-light text-gold flex items-center justify-center flex-shrink-0">
                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-[18px] h-[18px]'])
                    </div>
                    <h2 class="font-semibold text-ink">{{ $role->label }} <span class="text-slate-400 text-sm font-normal font-mono">({{ $role->nama }})</span></h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-4">
                    @foreach ($permKeys as $key)
                        @php $perm = $role->permissions->firstWhere('perm_key', $key); @endphp
                        <label @class([
                            'flex items-center gap-2 border rounded-lg px-3 py-2 text-[12.5px] cursor-pointer transition',
                            'border-brand bg-brand/[.04] text-ink font-medium' => $perm?->can_write,
                            'border-slate-200 text-slate-500 hover:border-slate-300' => !$perm?->can_write,
                        ])>
                            <input type="checkbox" name="write_{{ $key }}" value="1" @checked($perm?->can_write) class="rounded border-slate-300">
                            {{ $key }}
                        </label>
                    @endforeach
                </div>
                <button class="inline-flex items-center gap-1.5 bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                    Simpan
                </button>
            </form>
        @endforeach
    </div>
@endsection
