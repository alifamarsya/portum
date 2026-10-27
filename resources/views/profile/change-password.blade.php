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
                        class="block w-full rounded-xl border pl-10 pr-10 py-2.5 text-sm text-ink placeholder-slate-400 transition focus:ring-2 focus:ring-[#114E84]/30 focus:border-[#114E84] {{ $errors->has('password_lama') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }}"
                    >
                    <button type="button" onclick="togglePasswordVisibility('password_lama', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="Lihat/Sembunyikan password">
                        <svg class="w-4 h-4 icon-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg class="w-4 h-4 icon-eye-off hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                    </button>
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
                        placeholder="Minimal 8 karakter kombinasi"
                        class="block w-full rounded-xl border pl-10 pr-10 py-2.5 text-sm text-ink placeholder-slate-400 transition focus:ring-2 focus:ring-[#114E84]/30 focus:border-[#114E84] {{ $errors->has('password_baru') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }}"
                    >
                    <button type="button" onclick="togglePasswordVisibility('password_baru', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="Lihat/Sembunyikan password">
                        <svg class="w-4 h-4 icon-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg class="w-4 h-4 icon-eye-off hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                    </button>
                </div>
                @error('password_baru')
                    <p class="text-xs text-rose-600 font-medium mt-1 flex items-center gap-1">
                        @include('partials.icon', ['name' => 'alert', 'class' => 'w-3.5 h-3.5'])
                        {{ $message }}
                    </p>
                @enderror

                {{-- Strength Meter Bar & Label --}}
                <div class="mt-2 space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium text-[11px]">Kekuatan Password:</span>
                        <span id="strength_label" class="font-bold text-[11px] text-slate-400">Belum diisi</span>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5 h-1.5 w-full">
                        <div id="bar_1" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="bar_2" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="bar_3" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="bar_4" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                    </div>
                </div>

                {{-- Syarat Password Checklist --}}
                <div class="mt-2.5 p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1.5 text-xs">
                    <div id="rule_len" class="flex items-center gap-2 text-slate-500 transition-colors">
                        <span class="w-3.5 h-3.5 rounded-full flex items-center justify-center bg-slate-200 text-white flex-shrink-0 text-[9px] font-bold">✕</span>
                        <span>Minimal 8 karakter</span>
                    </div>
                    <div id="rule_case" class="flex items-center gap-2 text-slate-500 transition-colors">
                        <span class="w-3.5 h-3.5 rounded-full flex items-center justify-center bg-slate-200 text-white flex-shrink-0 text-[9px] font-bold">✕</span>
                        <span>Kombinasi huruf besar (A-Z) &amp; huruf kecil (a-z)</span>
                    </div>
                    <div id="rule_num" class="flex items-center gap-2 text-slate-500 transition-colors">
                        <span class="w-3.5 h-3.5 rounded-full flex items-center justify-center bg-slate-200 text-white flex-shrink-0 text-[9px] font-bold">✕</span>
                        <span>Mengandung angka (0-9)</span>
                    </div>
                    <div id="rule_sym" class="flex items-center gap-2 text-slate-500 transition-colors">
                        <span class="w-3.5 h-3.5 rounded-full flex items-center justify-center bg-slate-200 text-white flex-shrink-0 text-[9px] font-bold">✕</span>
                        <span>Mengandung simbol khusus (contoh: @$!%*#?&amp;)</span>
                    </div>
                </div>
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
                        class="block w-full rounded-xl border pl-10 pr-10 py-2.5 text-sm text-ink placeholder-slate-400 transition focus:ring-2 focus:ring-[#114E84]/30 focus:border-[#114E84] border-slate-200 bg-white"
                    >
                    <button type="button" onclick="togglePasswordVisibility('password_baru_confirmation', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="Lihat/Sembunyikan password">
                        <svg class="w-4 h-4 icon-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg class="w-4 h-4 icon-eye-off hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                    </button>
                </div>
                <div id="match_indicator" class="mt-1 text-xs font-medium hidden"></div>
            </div>

            {{-- Divider --}}
            <hr class="border-slate-100">

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <button
                    type="submit"
                    id="btnSubmitPassword"
                    disabled
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r from-[#114E84] to-[#0A335A] text-white text-sm font-semibold shadow-md hover:shadow-lg hover:brightness-110 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:brightness-100 disabled:shadow-none transition-all duration-200"
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

  

</div>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const eyeOpen = btn.querySelector('.icon-eye');
        const eyeOff = btn.querySelector('.icon-eye-off');
        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeOff.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeOff.classList.add('hidden');
        }
    }

    const pwdBaru = document.getElementById('password_baru');
    const pwdConfirm = document.getElementById('password_baru_confirmation');
    const btnSubmit = document.getElementById('btnSubmitPassword');
    const matchInd = document.getElementById('match_indicator');

    function updateProfileRequirement(elId, isValid) {
        const el = document.getElementById(elId);
        if (!el) return;
        const badge = el.querySelector('span');
        if (isValid) {
            el.classList.remove('text-slate-500');
            el.classList.add('text-emerald-700', 'font-semibold');
            badge.classList.remove('bg-slate-200');
            badge.classList.add('bg-emerald-500');
            badge.textContent = '✓';
        } else {
            el.classList.remove('text-emerald-700', 'font-semibold');
            el.classList.add('text-slate-500');
            badge.classList.remove('bg-emerald-500');
            badge.classList.add('bg-slate-200');
            badge.textContent = '✕';
        }
    }

    function evaluateProfilePassword() {
        if (!pwdBaru) return;
        const val = pwdBaru.value;
        const confirmVal = pwdConfirm ? pwdConfirm.value : '';

        const hasLen = val.length >= 8;
        const hasCase = /[a-z]/.test(val) && /[A-Z]/.test(val);
        const hasNum = /[0-9]/.test(val);
        const hasSym = /[^A-Za-z0-9]/.test(val);

        updateProfileRequirement('rule_len', hasLen);
        updateProfileRequirement('rule_case', hasCase);
        updateProfileRequirement('rule_num', hasNum);
        updateProfileRequirement('rule_sym', hasSym);

        const score = (hasLen ? 1 : 0) + (hasCase ? 1 : 0) + (hasNum ? 1 : 0) + (hasSym ? 1 : 0);

        const bars = [
            document.getElementById('bar_1'),
            document.getElementById('bar_2'),
            document.getElementById('bar_3'),
            document.getElementById('bar_4')
        ];
        const label = document.getElementById('strength_label');

        bars.forEach(b => {
            if (b) b.className = 'h-full rounded-full bg-slate-200 transition-all duration-300';
        });

        if (val.length === 0) {
            label.textContent = 'Belum diisi';
            label.className = 'font-bold text-[11px] text-slate-400';
        } else if (score === 1) {
            label.textContent = 'Sangat Lemah';
            label.className = 'font-bold text-[11px] text-rose-600';
            if (bars[0]) bars[0].className = 'h-full rounded-full bg-rose-500 transition-all duration-300';
        } else if (score === 2) {
            label.textContent = 'Lemah';
            label.className = 'font-bold text-[11px] text-amber-600';
            if (bars[0]) bars[0].className = 'h-full rounded-full bg-amber-500 transition-all duration-300';
            if (bars[1]) bars[1].className = 'h-full rounded-full bg-amber-500 transition-all duration-300';
        } else if (score === 3) {
            label.textContent = 'Sedang';
            label.className = 'font-bold text-[11px] text-yellow-500';
            if (bars[0]) bars[0].className = 'h-full rounded-full bg-yellow-400 transition-all duration-300';
            if (bars[1]) bars[1].className = 'h-full rounded-full bg-yellow-400 transition-all duration-300';
            if (bars[2]) bars[2].className = 'h-full rounded-full bg-yellow-400 transition-all duration-300';
        } else if (score === 4) {
            label.textContent = 'Kuat (Sesuai Standar)';
            label.className = 'font-bold text-[11px] text-emerald-600';
            bars.forEach(b => {
                if (b) b.className = 'h-full rounded-full bg-emerald-500 transition-all duration-300';
            });
        }

        // Match checking
        let match = false;
        if (confirmVal.length > 0) {
            matchInd.classList.remove('hidden');
            if (val === confirmVal) {
                matchInd.textContent = '✓ Konfirmasi password cocok';
                matchInd.className = 'mt-1 text-xs font-semibold text-emerald-600';
                match = true;
            } else {
                matchInd.textContent = '✕ Konfirmasi password tidak cocok';
                matchInd.className = 'mt-1 text-xs font-semibold text-rose-600';
                match = false;
            }
        } else {
            matchInd.classList.add('hidden');
            match = false;
        }

        if (score === 4 && match) {
            btnSubmit.disabled = false;
        } else {
            btnSubmit.disabled = true;
        }
    }

    if (pwdBaru) {
        pwdBaru.addEventListener('input', evaluateProfilePassword);
    }
    if (pwdConfirm) {
        pwdConfirm.addEventListener('input', evaluateProfilePassword);
    }
</script>
@endsection
