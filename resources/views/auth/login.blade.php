<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk &mdash; Portum Bank Sulteng</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#F0F6FC',
                            100: '#E0EDF8',
                            200: '#BAD6F1',
                            500: '#1D6FB8',
                            600: '#155c9d',
                            700: '#0F487F',
                            800: '#0B396F',
                            900: '#07274E',
                            950: '#041731',
                        },
                        gold: {
                            DEFAULT: '#D4A038',
                            soft: '#F6EEDB',
                            dark: '#A6791E',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #041731;
        }

        .solid-card {
            background: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 60px -15px rgba(4, 23, 49, 0.7),
                        0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .feature-badge {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-sulteng {
            background: linear-gradient(135deg, #1D6FB8 0%, #0F487F 100%);
            box-shadow: 0 10px 25px -5px rgba(29, 111, 184, 0.45);
            transition: all 0.2s ease;
        }

        .btn-sulteng:hover {
            background: linear-gradient(135deg, #2481d4 0%, #13599d 100%);
            box-shadow: 0 14px 28px -5px rgba(29, 111, 184, 0.6);
            transform: translateY(-1px);
        }

        .btn-sulteng:active {
            transform: translateY(0) scale(0.99);
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 antialiased selection:bg-brand-500 selection:text-white relative flex flex-col justify-center overflow-x-hidden">

    {{-- ================= FULLSCREEN BACKGROUND IMAGE (GEDUNG BANK SULTENG) ================= --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden bg-brand-950">
        {{-- Fullscreen Image Latar Belakang Gedung Bank Sulteng --}}
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-100 filter contrast-105 brightness-200"
             style="background-image: url('{{ asset('images/bank-sulteng-building.png') }}');">
        </div>

        {{-- Gradient Overlay Navy Blue Elegan agar Konten Tetap Jelas & Kontras --}}
        <div class="absolute inset-0 bg-gradient-to-r from-brand-950/90 via-brand-950/70 to-brand-900/70"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-brand-950/85 via-transparent to-brand-950/60"></div>

        {{-- Ambient Subtle Light Halo di Belakang Konten --}}
        <div class="absolute top-1/3 left-1/4 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-brand-500/20 rounded-full blur-[140px]"></div>
        <div class="absolute bottom-10 right-1/4 w-[450px] h-[450px] bg-brand-600/15 rounded-full blur-[130px]"></div>
    </div>

    {{-- ================= MAIN CONTENT CONTAINER ================= --}}
    <div class="relative z-10 w-full max-w-7xl mx-auto px-6 sm:px-10 lg:px-16 py-12 flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-16">

        {{-- ================= SISI KIRI: BRAND HERO & INFORMASI ================= --}}
        <div class="w-full lg:w-7/12 flex flex-col items-start text-left">

            {{-- Big Typography Heading (Solid White, No Gradient) --}}
            <h1 class="text-3xl sm:text-5xl xl:text-6xl font-extrabold tracking-tight text-white leading-[1.15] mb-6">
                Efisiensi &amp;<br>
                <span class="text-white">Akuntabilitas</span><br>
                Dalam Satu Portal.
            </h1>

            {{-- Description --}}
            <p class="text-sm sm:text-base xl:text-lg text-slate-200 max-w-xl leading-relaxed mb-8">
                Kelola tiket layanan internal, logistik aset, pengadaan, dan arsip operasional PT Bank Sulteng dengan standar keamanan terverifikasi.
            </p>

            {{-- Feature Highlights Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full max-w-lg mb-8">
                <div class="p-4 rounded-2xl feature-badge">
                    <div class="w-8 h-8 rounded-lg bg-gold/20 flex items-center justify-center text-gold mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-white">Audit Trail SHA-256</p>
                    <p class="text-[11px] text-slate-300 mt-0.5">Seluruh mutasi tercatat aman &amp; transparan</p>
                </div>

                <div class="p-4 rounded-2xl feature-badge">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/30 flex items-center justify-center text-blue-200 mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-white">SLA Real-time</p>
                    <p class="text-[11px] text-slate-300 mt-0.5">Monitoring status tiket secara langsung</p>
                </div>
            </div>

            {{-- Footer Copyright --}}
            <div class="text-xs text-slate-400 flex items-center gap-3">
                <span>&copy; {{ date('Y') }} PT Bank Sulteng.</span>
            </div>
        </div>

        {{-- ================= SISI KANAN: SOLID WHITE LOGIN CARD ================= --}}
        <div class="w-full lg:w-5/12 flex justify-center lg:justify-end">
            <div class="solid-card w-full max-w-[430px] rounded-[32px] p-8 sm:p-10 text-slate-900">

                {{-- Card Header --}}
                <div class="mb-6">
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        Masuk ke Akun
                    </h2>
                    <p class="mt-1.5 text-xs text-slate-500">
                        Silakan masukkan username dan kata sandi Anda.
                    </p>
                </div>

                {{-- Error Flash Notification --}}
                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="leading-snug">
                            <span class="font-bold">Gagal masuk:</span> {{ $errors->first() }}
                        </div>
                    </div>
                @endif

                {{-- Form Login --}}
                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4" id="loginForm">
                    @csrf

                    {{-- Username Field --}}
                    <div>
                        <label for="username" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Username
                        </label>
                        <div class="relative">
                            <input id="username"
                                   name="username"
                                   type="text"
                                   value="{{ old('username') }}"
                                   placeholder="Masukkan username Anda"
                                   autocomplete="username"
                                   autofocus
                                   required
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition shadow-sm">
                        </div>
                    </div>

                    {{-- Password Field --}}
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <input id="password"
                                   name="password"
                                   type="password"
                                   placeholder="Masukkan Password"
                                   autocomplete="current-password"
                                   required
                                   class="w-full pl-4 pr-11 py-3 bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition shadow-sm">
                            
                            {{-- Toggle Password Button --}}
                            <button type="button"
                                    id="togglePassword"
                                    aria-label="Tampilkan Password"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition">
                                <svg id="eyeOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="eyeClosed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me & Forgot Password --}}
                    <div class="flex items-center justify-between pt-1 text-xs">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none text-slate-600 hover:text-slate-900 transition">
                            <input type="checkbox"
                                   name="remember"
                                   value="1"
                                   class="w-4 h-4 rounded border-slate-300 text-brand-700 focus:ring-0 cursor-pointer">
                            <span>Remember Me</span>
                        </label>

                        <a href="mailto:admin@banksulteng.co.id?subject=Bantuan%20Akses%20Portum"
                           class="text-slate-600 hover:text-slate-900 transition underline underline-offset-2">
                            Forget Password?
                        </a>
                    </div>

                    {{-- Submit Button (Bank Sulteng Blue) --}}
                    <button type="submit"
                            id="submitBtn"
                            class="btn-sulteng w-full mt-5 py-3.5 px-4 rounded-xl text-white font-bold text-sm tracking-wide shadow-md flex items-center justify-center gap-2">
                        <span>Login</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>

                {{-- Help & Support --}}
                <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500">
                        Kendala akses atau belum punya akun?<br>
                        Hubungi <a href="mailto:admin@banksulteng.co.id" class="text-brand-700 hover:underline font-semibold">Administrator</a>.
                    </p>
                </div>
            </div>
        </div>

    </div>

    {{-- Password Toggle and Submit Loading State Scripts --}}
    <script>
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePassword');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                eyeOpen.classList.toggle('hidden', isPassword);
                eyeClosed.classList.toggle('hidden', !isPassword);
                this.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            });
        }

        // Submit state UX
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-80', 'cursor-wait');
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Loading...</span>
                `;
            });
        }
    </script>
    @if(isset($showForceChangeModal) && $showForceChangeModal)
    <!-- Modal Backdrop -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/60 backdrop-blur-sm overflow-y-auto">
        <!-- Modal Content -->
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 sm:p-7 relative animate-[fadeIn_0.2s_ease-out] my-auto">
            <div class="flex items-center gap-2.5 mb-5">
                <img src="{{ asset('images/bank-sulteng.png') }}" alt="Bank Sulteng" class="h-6 w-auto">
            </div>

            <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-3" style="background-color: #F6EEDB; color: #D4A038;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>

            <h1 class="text-lg font-bold mb-1 text-slate-900">Ganti Password Wajib</h1>
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">Ini adalah login pertama Anda. Demi keamanan akun, buat password baru yang kuat sebelum melanjutkan.</p>

            @if ($errors->any())
                <div class="mb-4 flex items-start gap-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-3.5 py-2.5 text-xs">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.force-change.submit') }}" class="space-y-4" id="forceChangeForm">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1 text-slate-700">Password Baru</label>
                    <div class="relative">
                        <input type="password" name="password" id="force_password" required
                               placeholder="Minimal 8 karakter kombinasi"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-10 py-2.5 text-slate-900 text-xs font-medium focus:bg-white focus:border-[#114E84] focus:ring-2 focus:ring-[#114E84]/20 transition outline-none">
                        <button type="button" onclick="togglePasswordVisibility('force_password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="Lihat/Sembunyikan password">
                            <svg class="w-4 h-4 icon-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg class="w-4 h-4 icon-eye-off hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                        </button>
                    </div>

                    {{-- Strength Meter Bar & Label --}}
                    <div class="mt-2 space-y-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-500 font-medium">Kekuatan Password:</span>
                            <span id="force_strength_label" class="font-bold text-slate-400">Belum diisi</span>
                        </div>
                        <div class="grid grid-cols-4 gap-1.5 h-1.5 w-full">
                            <div id="force_bar_1" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="force_bar_2" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="force_bar_3" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="force_bar_4" class="h-full rounded-full bg-slate-200 transition-all duration-300"></div>
                        </div>
                    </div>

                    {{-- Syarat Password Checklist --}}
                    <div class="mt-2.5 p-2.5 bg-slate-50 rounded-xl border border-slate-200/70 space-y-1 text-[11px]">
                        <div id="force_rule_len" class="flex items-center gap-2 text-slate-500 transition-colors">
                            <span class="w-3.5 h-3.5 rounded-full flex items-center justify-center bg-slate-200 text-white flex-shrink-0 text-[9px] font-bold">✕</span>
                            <span>Minimal 8 karakter</span>
                        </div>
                        <div id="force_rule_case" class="flex items-center gap-2 text-slate-500 transition-colors">
                            <span class="w-3.5 h-3.5 rounded-full flex items-center justify-center bg-slate-200 text-white flex-shrink-0 text-[9px] font-bold">✕</span>
                            <span>Huruf besar (A-Z) &amp; huruf kecil (a-z)</span>
                        </div>
                        <div id="force_rule_num" class="flex items-center gap-2 text-slate-500 transition-colors">
                            <span class="w-3.5 h-3.5 rounded-full flex items-center justify-center bg-slate-200 text-white flex-shrink-0 text-[9px] font-bold">✕</span>
                            <span>Mengandung angka (0-9)</span>
                        </div>
                        <div id="force_rule_sym" class="flex items-center gap-2 text-slate-500 transition-colors">
                            <span class="w-3.5 h-3.5 rounded-full flex items-center justify-center bg-slate-200 text-white flex-shrink-0 text-[9px] font-bold">✕</span>
                            <span>Mengandung simbol khusus (contoh: @#$!%*)</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-slate-700">Konfirmasi Password Baru</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="force_password_confirmation" required
                               placeholder="Ketik ulang password baru"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-10 py-2.5 text-slate-900 text-xs font-medium focus:bg-white focus:border-[#114E84] focus:ring-2 focus:ring-[#114E84]/20 transition outline-none">
                        <button type="button" onclick="togglePasswordVisibility('force_password_confirmation', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="Lihat/Sembunyikan password">
                            <svg class="w-4 h-4 icon-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg class="w-4 h-4 icon-eye-off hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                        </button>
                    </div>
                    <div id="force_match_indicator" class="mt-1 text-[11px] font-medium hidden"></div>
                </div>

                <button type="submit" id="forceChangeSubmitBtn" disabled class="btn-sulteng w-full rounded-xl py-3 mt-2 text-xs font-bold text-white tracking-wide shadow-md flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:brightness-100 transition">
                    <span>Simpan &amp; Lanjutkan</span>
                </button>
            </form>
            
            <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="text-xs text-slate-500 font-medium hover:text-slate-800 transition underline underline-offset-2">
                    Batalkan &amp; Keluar
                </button>
            </form>
        </div>
    </div>
    
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>
    
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

        const forceForm = document.getElementById('forceChangeForm');
        const forceSubmitBtn = document.getElementById('forceChangeSubmitBtn');
        const forcePwd = document.getElementById('force_password');
        const forceConfirm = document.getElementById('force_password_confirmation');
        const forceMatchInd = document.getElementById('force_match_indicator');

        function updateRequirement(elId, isValid) {
            const el = document.getElementById(elId);
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

        function evaluateForcePassword() {
            if (!forcePwd) return;
            const val = forcePwd.value;
            const confirmVal = forceConfirm ? forceConfirm.value : '';

            const hasLen = val.length >= 8;
            const hasCase = /[a-z]/.test(val) && /[A-Z]/.test(val);
            const hasNum = /[0-9]/.test(val);
            const hasSym = /[^A-Za-z0-9]/.test(val);

            updateRequirement('force_rule_len', hasLen);
            updateRequirement('force_rule_case', hasCase);
            updateRequirement('force_rule_num', hasNum);
            updateRequirement('force_rule_sym', hasSym);

            const score = (hasLen ? 1 : 0) + (hasCase ? 1 : 0) + (hasNum ? 1 : 0) + (hasSym ? 1 : 0);

            const bars = [
                document.getElementById('force_bar_1'),
                document.getElementById('force_bar_2'),
                document.getElementById('force_bar_3'),
                document.getElementById('force_bar_4')
            ];
            const label = document.getElementById('force_strength_label');

            bars.forEach(b => {
                b.className = 'h-full rounded-full bg-slate-200 transition-all duration-300';
            });

            if (val.length === 0) {
                label.textContent = 'Belum diisi';
                label.className = 'font-bold text-slate-400';
            } else if (score === 1) {
                label.textContent = 'Sangat Lemah';
                label.className = 'font-bold text-rose-600';
                bars[0].className = 'h-full rounded-full bg-rose-500 transition-all duration-300';
            } else if (score === 2) {
                label.textContent = 'Lemah';
                label.className = 'font-bold text-amber-600';
                bars[0].className = 'h-full rounded-full bg-amber-500 transition-all duration-300';
                bars[1].className = 'h-full rounded-full bg-amber-500 transition-all duration-300';
            } else if (score === 3) {
                label.textContent = 'Sedang';
                label.className = 'font-bold text-yellow-500';
                bars[0].className = 'h-full rounded-full bg-yellow-400 transition-all duration-300';
                bars[1].className = 'h-full rounded-full bg-yellow-400 transition-all duration-300';
                bars[2].className = 'h-full rounded-full bg-yellow-400 transition-all duration-300';
            } else if (score === 4) {
                label.textContent = 'Kuat (Sesuai Standar)';
                label.className = 'font-bold text-emerald-600';
                bars.forEach(b => b.className = 'h-full rounded-full bg-emerald-500 transition-all duration-300');
            }

            // Validasi kecocokan konfirmasi
            let match = false;
            if (confirmVal.length > 0) {
                forceMatchInd.classList.remove('hidden');
                if (val === confirmVal) {
                    forceMatchInd.textContent = '✓ Konfirmasi password cocok';
                    forceMatchInd.className = 'mt-1 text-[11px] font-semibold text-emerald-600';
                    match = true;
                } else {
                    forceMatchInd.textContent = '✕ Konfirmasi password tidak cocok';
                    forceMatchInd.className = 'mt-1 text-[11px] font-semibold text-rose-600';
                    match = false;
                }
            } else {
                forceMatchInd.classList.add('hidden');
                match = false;
            }

            if (score === 4 && match) {
                forceSubmitBtn.disabled = false;
            } else {
                forceSubmitBtn.disabled = true;
            }
        }

        if (forcePwd) {
            forcePwd.addEventListener('input', evaluateForcePassword);
        }
        if (forceConfirm) {
            forceConfirm.addEventListener('input', evaluateForcePassword);
        }

        if (forceForm && forceSubmitBtn) {
            forceForm.addEventListener('submit', function() {
                forceSubmitBtn.disabled = true;
                forceSubmitBtn.classList.add('opacity-80', 'cursor-wait');
                forceSubmitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Menyimpan...</span>
                `;
            });
        }
    </script>
    @endif
</body>
</html>