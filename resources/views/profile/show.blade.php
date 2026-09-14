@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
@php
    $rolePanelLabel = match(strtolower($user->role?->nama ?? '')) {
        'superadmin', 'admin'   => 'Administrator Panel',
        'operator'              => 'Operator Panel',
        'kabag_umum'            => 'Kabag Umum Panel',
        'kabag_aset'            => 'Kabag Aset Panel',
        'kabag_pengadaan'       => 'Kabag Pengadaan Panel',
        'uk_umum_rt'            => 'Staf Umum & RT Panel',
        'uk_dokumen'            => 'Staf Dokumen Panel',
        'uk_administrasi_aset'  => 'Staf Aset Panel',
        'uk_logistik'           => 'Staf Logistik Panel',
        'uk_pengadaan'          => 'Staf Pengadaan Panel',
        'uk_pemeliharaan'       => 'Staf Pemeliharaan Panel',
        'user'                  => 'User Panel',
        default                 => ucfirst($user->role?->label ?? 'Panel'),
    };
    $avatarInitials = strtoupper(substr($user->nama_lengkap, 0, 1));
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#114E84] to-[#0A335A] flex items-center justify-center shadow-md">
            @include('partials.icon', ['name' => 'user-circle', 'class' => 'w-5 h-5 text-white'])
        </div>
        <div>
            <h1 class="text-xl font-bold text-ink">Profil Saya</h1>
            <p class="text-xs text-muted">Informasi akun dan data pribadi Anda</p>
        </div>
    </div>

    {{-- Avatar Card --}}
    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        {{-- Cover gradient --}}
        <div class="h-20 bg-gradient-to-r from-[#114E84] via-[#0E4272] to-[#0A335A] relative">
            <div class="absolute -bottom-8 left-6">
                <div class="w-16 h-16 rounded-2xl bg-white shadow-md border-2 border-white flex items-center justify-center">
                    <span class="text-2xl font-extrabold text-[#114E84]">{{ $avatarInitials }}</span>
                </div>
            </div>
        </div>
        <div class="pt-12 pb-5 px-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-lg font-bold text-ink">{{ $user->nama_lengkap }}</h2>
                    <p class="text-sm text-muted">@{{ $user->username }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#E8F1FB] text-[#114E84] border border-[#114E84]/20">
                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-3 h-3'])
                    {{ $user->role?->label ?? '-' }}
                </span>
            </div>
            <p class="mt-2 text-xs text-slate-400 font-medium">{{ $rolePanelLabel }}</p>
        </div>
    </div>

    {{-- Info Detail --}}
    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-ink">Informasi Akun</h3>
        </div>
        <dl class="divide-y divide-slate-50">
            @php
                $rows = [
                    ['label' => 'Nama Lengkap',    'value' => $user->nama_lengkap, 'icon' => 'user-circle'],
                    ['label' => 'Username',         'value' => $user->username,     'icon' => 'key'],
                    ['label' => 'Email',            'value' => $user->email ?? '-', 'icon' => 'link'],
                    ['label' => 'Jabatan',          'value' => $user->jabatan ?? '-', 'icon' => 'building'],
                    ['label' => 'Bagian / Divisi',  'value' => $user->bagian ?? '-',  'icon' => 'layers'],
                    ['label' => 'Peran / Role',     'value' => $user->role?->label ?? '-', 'icon' => 'shield'],
                    ['label' => 'Status',           'value' => $user->is_active ? 'Aktif' : 'Non-aktif', 'icon' => 'check-circle'],
                    ['label' => 'Login Terakhir',   'value' => $user->last_login ? $user->last_login->translatedFormat('d M Y, H:i') : '-', 'icon' => 'clock'],
                ];
            @endphp
            @foreach ($rows as $row)
                <div class="flex items-center gap-4 px-6 py-3.5">
                    <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center flex-shrink-0 text-slate-400">
                        @include('partials.icon', ['name' => $row['icon'], 'class' => 'w-3.5 h-3.5'])
                    </div>
                    <dt class="text-xs text-muted font-medium w-36 flex-shrink-0">{{ $row['label'] }}</dt>
                    <dd class="text-sm font-semibold text-ink truncate">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    {{-- Quick Actions --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('profile.change-password') }}"
           class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r from-[#114E84] to-[#0A335A] text-white text-sm font-semibold shadow-md hover:shadow-lg hover:brightness-110 transition-all duration-200">
            @include('partials.icon', ['name' => 'password', 'class' => 'w-4 h-4'])
            Ubah Password
        </a>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
           class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white border border-slate-200 text-ink text-sm font-semibold hover:bg-slate-50 transition-all duration-200 shadow-card">
            @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4 rotate-180'])
            Kembali
        </a>
    </div>

</div>
@endsection
