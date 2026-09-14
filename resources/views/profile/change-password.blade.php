@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#114E84] to-[#0A335A] flex items-center justify-center shadow-md">
            @include('partials.icon', ['name' => 'password', 'class' => 'w-5 h-5 text-white'])
        </div>
        <div>
            <h1 class="text-xl font-bold text-ink">Ubah Password</h1>
            <p class="text-xs text-muted">Ganti kata sandi akun Anda dengan yang baru</p>
        </div>
    </div>

    {{-- Flash success --}}
    @if (session('status'))
        <div class="flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 text-sm animate-enter">
            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5 text-emerald-600 flex-shrink-0', 'stroke' => 2])
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        {{-- Top Banner --}}
        <div class="h-2 bg-gradient-to-r from-[#114E84] via-[#0E4272] to-[#0A335A]"></div>

        <form method="POST" action="{{ route('profile.update-password') }}" class="px-6 py-6 space-y-5">
            @csrf

            {{-- Password Lama --}}
            <div class="space-y-1.5">
                <label for="password_lama" class="block text-sm font-semibold text-ink">
                    Password Lama
                    <span class="text-rose-500 ml-0.5">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        @include('partials.icon', ['name' => 'lock', 'class' => 'w-4 h-4'])
                    </div>
                    <input
                        id="password_lama"
                        name="password_lama"
                        type="password"
                        autocomplete="current-password"
                        required
                        placeholder="Masukkan password lama Anda"
                        class="block w-full rounded-xl border pl-10 pr-4 py-2.5 text-sm text-ink placeholder-slate-400 transition focus:ring-2 focus:ring-[#114E84]/30 focus:border-[#114E84] {{ $errors->has('password_lama') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }}"
                    >
                </div>
                @error('password_lama')
                    <p class="text-xs text-rose-600 font-medium mt-1 flex items-center gap-1">
                        @include('partials.icon', ['name' => 'alert', 'class' => 'w-3.5 h-3.5'])
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div class="space-y-1.5">
                <label for="password_baru" class="block text-sm font-semibold text-ink">
                    Password Baru
                    <span class="text-rose-500 ml-0.5">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        @include('partials.icon', ['name' => 'password', 'class' => 'w-4 h-4'])
                    </div>
                    <input
                        id="password_baru"
                        name="password_baru"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="Minimal 8 karakter"
                        class="block w-full rounded-xl border pl-10 pr-4 py-2.5 text-sm text-ink placeholder-slate-400 transition focus:ring-2 focus:ring-[#114E84]/30 focus:border-[#114E84] {{ $errors->has('password_baru') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }}"
                    >
                </div>
                @error('password_baru')
                    <p class="text-xs text-rose-600 font-medium mt-1 flex items-center gap-1">
                        @include('partials.icon', ['name' => 'alert', 'class' => 'w-3.5 h-3.5'])
                        {{ $message }}
                    </p>
                @enderror
                <p class="text-xs text-slate-400 mt-1">Gunakan kombinasi huruf, angka, dan simbol untuk keamanan lebih baik.</p>
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div class="space-y-1.5">
                <label for="password_baru_confirmation" class="block text-sm font-semibold text-ink">
                    Konfirmasi Password Baru
                    <span class="text-rose-500 ml-0.5">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                    </div>
                    <input
                        id="password_baru_confirmation"
                        name="password_baru_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="Ulangi password baru"
                        class="block w-full rounded-xl border pl-10 pr-4 py-2.5 text-sm text-ink placeholder-slate-400 transition focus:ring-2 focus:ring-[#114E84]/30 focus:border-[#114E84] border-slate-200 bg-white"
                    >
                </div>
            </div>

            {{-- Divider --}}
            <hr class="border-slate-100">

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <button
                    type="submit"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r from-[#114E84] to-[#0A335A] text-white text-sm font-semibold shadow-md hover:shadow-lg hover:brightness-110 transition-all duration-200"
                >
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                    Simpan Password Baru
                </button>
                <a
                    href="{{ route('profile.show') }}"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white border border-slate-200 text-ink text-sm font-semibold hover:bg-slate-50 transition-all duration-200 shadow-card"
                >
                    @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4 rotate-180'])
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Security Tips --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
        <div class="flex items-start gap-3">
            @include('partials.icon', ['name' => 'alert', 'class' => 'w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5'])
            <div>
                <p class="text-xs font-bold text-amber-800 mb-1">Tips Keamanan Password</p>
                <ul class="text-xs text-amber-700 space-y-0.5 list-disc pl-4">
                    <li>Gunakan minimal 8 karakter</li>
                    <li>Kombinasikan huruf besar, kecil, angka, dan simbol</li>
                    <li>Jangan gunakan informasi pribadi yang mudah ditebak</li>
                    <li>Jangan gunakan password yang sama di sistem lain</li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
