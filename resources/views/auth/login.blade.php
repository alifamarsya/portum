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
</body>
</html>