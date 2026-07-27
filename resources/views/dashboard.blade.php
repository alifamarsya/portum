@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-ink">Selamat datang, {{ explode(' ', auth()->user()->nama_lengkap)[0] }}</h1>
        <p class="text-sm text-slate-500">{{ auth()->user()->role->label }} &middot; {{ auth()->user()->bagian ?? 'Divisi Umum' }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-card p-5 flex items-start gap-4 hover:shadow-hover transition-shadow">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[12.5px] text-slate-500 mb-1">Menunggu Persetujuan</p>
                <p class="text-2xl font-bold text-ink">{{ $menunggu }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Biaya Harian berstatus Diajukan</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-card p-5 flex items-start gap-4 hover:shadow-hover transition-shadow">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'alert', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[12.5px] text-slate-500 mb-1">Reminder &le; 90 Hari</p>
                <p class="text-2xl font-bold text-ink">{{ $reminderAktif }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Dari jadwal Pengadaan &amp; Pemeliharaan</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-card p-5 flex items-start gap-4 hover:shadow-hover transition-shadow">
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'file-text', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[12.5px] text-slate-500 mb-1">PKS Mendekati Jatuh Tempo</p>
                <p class="text-2xl font-bold text-ink">{{ $pksJatuhTempo }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Dalam 90 hari ke depan</p>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-brand to-brand-dark text-white rounded-xl p-5 flex items-center justify-between shadow-brand">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'lock', 'class' => 'w-5 h-5 text-gold'])
            </div>
            <div>
                <p class="font-semibold text-[13.5px]">Rantai Audit Aktif</p>
                <p class="text-[12px] text-slate-300">
                    @if ($lastLog)
                        Entri terakhir #{{ $lastLog->id }} &middot; {{ $lastLog->created_at->diffForHumans() }}
                    @else
                        Belum ada aktivitas tercatat.
                    @endif
                </p>
            </div>
        </div>
        @if ($lastLog)
            <span class="font-mono text-[11.5px] text-gold bg-white/10 px-3 py-1.5 rounded-lg">{{ \Illuminate\Support\Str::limit($lastLog->hash, 16, '…') }}</span>
        @endif
    </div>
    <p class="text-[12px] text-slate-400 mt-2.5 flex items-center gap-1.5">
        @include('partials.icon', ['name' => 'file-text', 'class' => 'w-3.5 h-3.5 flex-shrink-0'])
        Jalankan <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">php artisan audit:verify-chain</code> secara berkala untuk memastikan rantai di atas belum dimanipulasi langsung di database.
    </p>
@endsection
