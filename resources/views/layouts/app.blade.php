<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Portum</title>
    @include('partials.head-assets')
</head>
<body class="bg-canvas text-ink antialiased">
<div class="flex min-h-screen">

    <aside class="w-64 flex-shrink-0 bg-gradient-to-b from-brand to-brand-dark text-slate-300 flex flex-col">
        <div class="flex items-center gap-2.5 px-5 py-5 border-b border-white/10">
            @include('partials.brand-mark', ['class' => 'w-7 h-7 text-white'])
            <div class="leading-tight">
                <p class="text-white font-bold tracking-tight text-[15px]">Portum</p>
                <p class="text-[11px] text-slate-400">Divisi Umum &middot; Bank Sulteng</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-6 text-[13.5px]">
            @php
                $navGroup = function ($label, $items, $icon) {
                    return compact('label', 'items', 'icon');
                };
                $groups = [
                    $navGroup('Umum & Rumah Tangga', ['kendaraan', 'biaya_harian', 'permintaan_cabang'], 'truck'),
                    $navGroup('Aset & Logistik', ['invoice_sewa', 'aset', 'amortisasi', 'pks', 'memo_sewa_cabang', 'temuan'], 'archive'),
                    $navGroup('Pengadaan & Pemeliharaan', ['memo_internal', 'penawaran', 'negosiasi', 'draft_dokumen', 'spk', 'reminder'], 'wrench'),
                    $navGroup('Arsip Surat & Memo', ['surat_masuk', 'surat_keluar', 'memo_masuk', 'memo_keluar'], 'inbox'),
                ];
            @endphp

            <div class="space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition
                          {{ request()->routeIs('dashboard') ? 'active bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white text-slate-400' }}">
                    @include('partials.icon', ['name' => 'home', 'class' => 'w-4 h-4 flex-shrink-0'])
                    Dashboard
                </a>

                <a href="{{ route('analitik') }}"
                   class="nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition
                          {{ request()->routeIs('analitik') ? 'active bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white text-slate-400' }}">
                    @include('partials.icon', ['name' => 'chart', 'class' => 'w-4 h-4 flex-shrink-0'])
                    Analitik DW
                </a>
            </div>

            @foreach ($groups as $group)
                <div>
                    <p class="flex items-center gap-2 px-3 pb-2 text-[10.5px] font-semibold uppercase tracking-wider text-slate-500">
                        @include('partials.icon', ['name' => $group['icon'], 'class' => 'w-3.5 h-3.5 text-gold/70 flex-shrink-0', 'stroke' => 1.8])
                        {{ $group['label'] }}
                    </p>
                    <div class="space-y-0.5 border-l border-white/10 ml-[18px] pl-3">
                        @foreach ($group['items'] as $k)
                            <a href="{{ route('modul.index', $k) }}"
                               class="nav-link block px-3 py-1.5 rounded-lg transition truncate
                                      {{ request()->route('key') === $k ? 'active bg-white/10 text-white font-medium' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                {{ config("modules.$k.judul") }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div>
                <p class="flex items-center gap-2 px-3 pb-2 text-[10.5px] font-semibold uppercase tracking-wider text-slate-500">
                    @include('partials.icon', ['name' => 'book', 'class' => 'w-3.5 h-3.5 text-gold/70 flex-shrink-0', 'stroke' => 1.8])
                    Rapat &amp; Referensi
                </p>
                <div class="space-y-0.5 border-l border-white/10 ml-[18px] pl-3">
                    <a href="{{ route('risalah.index') }}" class="nav-link block px-3 py-1.5 rounded-lg transition {{ request()->routeIs('risalah.*') ? 'active bg-white/10 text-white font-medium' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        Risalah Rapat
                    </a>
                    <a href="{{ route('panduan.index') }}" class="nav-link block px-3 py-1.5 rounded-lg transition {{ request()->routeIs('panduan.*') ? 'active bg-white/10 text-white font-medium' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        Panduan
                    </a>
                    <a href="{{ route('modul.index', 'ref_akun') }}" class="nav-link block px-3 py-1.5 rounded-lg transition {{ request()->route('key') === 'ref_akun' ? 'active bg-white/10 text-white font-medium' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        Referensi Akun
                    </a>
                </div>
            </div>

            @if (auth()->user()->role->nama === 'superadmin')
                <div>
                    <p class="flex items-center gap-2 px-3 pb-2 text-[10.5px] font-semibold uppercase tracking-wider text-slate-500">
                        @include('partials.icon', ['name' => 'sliders', 'class' => 'w-3.5 h-3.5 text-gold/70 flex-shrink-0', 'stroke' => 1.8])
                        Pengaturan
                    </p>
                    <div class="space-y-0.5 border-l border-white/10 ml-[18px] pl-3">
                        <a href="{{ route('admin.users.index') }}" class="nav-link block px-3 py-1.5 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'active bg-white/10 text-white font-medium' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            Manajemen User
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="nav-link block px-3 py-1.5 rounded-lg transition {{ request()->routeIs('admin.roles.*') ? 'active bg-white/10 text-white font-medium' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            Manajemen Role
                        </a>
                        <a href="{{ route('admin.audit-log.index') }}" class="nav-link block px-3 py-1.5 rounded-lg transition {{ request()->routeIs('admin.audit-log.*') ? 'active bg-white/10 text-white font-medium' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            Audit Log
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        <div class="px-4 py-3 border-t border-white/10 text-[11px] text-slate-500 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
            <span class="font-mono">rantai audit aktif &middot; v1.0</span>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white/85 backdrop-blur border-b border-slate-200/80 px-6 py-3.5 flex items-center justify-between sticky top-0 z-10 shadow-[0_1px_0_rgba(16,24,39,0.03)]">
            <h1 class="font-semibold text-[15px] text-ink">@yield('title', 'Dashboard')</h1>
            <div class="flex items-center gap-4 text-sm">
                <div class="text-right leading-tight hidden sm:block">
                    <p class="font-medium text-ink">{{ auth()->user()->nama_lengkap }}</p>
                    <p class="text-[11.5px] text-slate-400">{{ auth()->user()->role->label }}</p>
                </div>
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand to-brand-light text-white flex items-center justify-center text-xs font-semibold shadow-sm ring-2 ring-white">
                    {{ strtoupper(substr(auth()->user()->nama_lengkap, 0, 1)) }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="Keluar">
                        @include('partials.icon', ['name' => 'logout', 'class' => 'w-[18px] h-[18px]'])
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-6 max-w-6xl w-full">
            @if (session('status'))
                <div class="mb-5 flex items-start gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm animate-enter">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-[18px] h-[18px] flex-shrink-0 mt-0.5', 'stroke' => 2])
                    <span>{{ session('status') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-5 flex items-start gap-2.5 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm animate-enter">
                    @include('partials.icon', ['name' => 'alert', 'class' => 'w-[18px] h-[18px] flex-shrink-0 mt-0.5', 'stroke' => 2])
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif
            <div class="animate-enter">
                @yield('content')
            </div>
        </main>
    </div>
</div>
</body>
</html>
