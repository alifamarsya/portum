<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Portum</title>
    @include('partials.head-assets')
    <style>
        @media (min-width: 1024px) {
            .portum-sidebar-rail { position: fixed; inset: 0 auto 0 0; width: 272px; z-index: 50; }
            .portum-main { margin-left: 272px; min-height: 100vh; }
        }
        .portum-nav-details > summary { list-style: none; }
        .portum-nav-details > summary::-webkit-details-marker { display: none; }
        .portum-nav-details[open] > summary .portum-chevron { transform: rotate(90deg); }
        .portum-chevron { transition: transform .18s ease; }
        .portum-submenu { animation: portumSubmenu .16s ease-out; }
        @keyframes portumSubmenu { from { opacity: .2; transform: translateY(-3px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-canvas text-ink antialiased">
@php
    $user = auth()->user();
    $permissions = $user?->role?->permissions?->keyBy('perm_key') ?? collect();

    $canAccess = fn (string $key) => $user?->role?->nama === 'superadmin' || $permissions->has($key);
    $canWrite  = fn (string $key) => $user?->role?->nama === 'superadmin' || (bool) optional($permissions->get($key))->can_write;

    /*
     * Kategori = dropdown.
     * Setiap child mengarah ke sub-modulnya sendiri.
     * Permission parent tetap menjadi pengaman tampilan menu.
     */
    $operationalGroups = [
        [
            'perm' => 'umum_rt',
            'label' => 'Umum & Rumah Tangga',
            'icon' => 'building',
            'children' => [
                ['key' => 'kendaraan', 'label' => 'Kendaraan & Driver'],
                ['key' => 'biaya_harian', 'label' => 'Biaya BBM / Perawatan / Rumah Tangga'],
                ['key' => 'permintaan_cabang', 'label' => 'Permintaan Cabang (ATK/Inventaris)'],
            ],
        ],
        [
            'perm' => 'aset_logistik',
            'label' => 'Aset & Logistik',
            'icon' => 'building',
            'children' => [
                ['key' => 'invoice_sewa', 'label' => 'Invoice Sewa'],
                ['key' => 'aset', 'label' => 'Data Aset & Inventaris'],
                ['key' => 'amortisasi', 'label' => 'Amortisasi Aset'],
                ['key' => 'pks', 'label' => 'PKS & Jatuh Tempo'],
                ['key' => 'memo_sewa_cabang', 'label' => 'Memo Sewa Cabang'],
                ['key' => 'temuan', 'label' => 'Temuan Aset'],
            ],
        ],
        [
            'perm' => 'pengadaan',
            'label' => 'Pengadaan & Pemeliharaan',
            'icon' => 'cart',
            'children' => [
                ['key' => 'memo_internal', 'label' => 'Memo Internal'],
                ['key' => 'penawaran', 'label' => 'Penawaran'],
                ['key' => 'negosiasi', 'label' => 'Negosiasi'],
                ['key' => 'draft_dokumen', 'label' => 'Draft Dokumen'],
                ['key' => 'spk', 'label' => 'SPK'],
                ['key' => 'reminder', 'label' => 'Reminder'],
            ],
        ],
        [
            'perm' => 'arsip_surat_memo',
            'label' => 'Arsip Surat & Memo',
            'icon' => 'file-text',
            'children' => [
                ['key' => 'surat_masuk', 'label' => 'Surat Masuk'],
                ['key' => 'surat_keluar', 'label' => 'Surat Keluar'],
                ['key' => 'memo_masuk', 'label' => 'Memo Masuk'],
                ['key' => 'memo_keluar', 'label' => 'Memo Keluar'],
            ],
        ],
    ];
@endphp

{{-- DESKTOP: fixed rail. Logo dan sidebar tetap terpisah seperti referensi Google Drive. --}}
<div class="portum-sidebar-rail hidden lg:block bg-white">
    <div class="h-[112px] px-7 flex items-center bg-white">
        <img src="{{ asset('images/bank-sulteng.png') }}"
             alt="Bank Sulteng"
             class="w-[190px] h-auto object-contain object-left">
    </div>

    <aside class="absolute top-[112px] left-0 right-0 bottom-0 bg-gradient-to-b from-brand to-brand-dark text-slate-200 rounded-tr-[56px] shadow-[8px_0_24px_rgba(16,24,39,.10)] flex flex-col overflow-hidden">
        <nav class="flex-1 overflow-y-auto px-4 pt-8 pb-5 text-[13px]">
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition
                   {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white font-semibold': 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    @include('partials.icon', ['name' => 'home', 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                    <span>Dashboard</span>
                </a>

                @if ($canAccess('analytics_dw'))
                    <a href="{{ route('analitik') }}"
                       class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition
                       {{ request()->routeIs('analitik') ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        @include('partials.icon', ['name' => 'chart', 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                        <span>Analitik DW</span>
                    </a>
                @endif
            </div>

            <div class="mt-7">
                <p class="px-4 mb-3 text-[10.5px] font-bold uppercase tracking-[.08em] text-gold">Operasional</p>
                <div class="space-y-1">
                    @foreach ($operationalGroups as $group)
                        @if ($canAccess($group['perm']))
                            @php
                                $groupActive = collect($group['children'])->contains(fn ($child) => request()->route('key') === $child['key']);
                            @endphp
                            <details class="portum-nav-details" {{ $groupActive ? 'open' : '' }}>
                                <summary class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl cursor-pointer transition text-slate-300 hover:bg-white/10 hover:text-white {{ $groupActive ? 'bg-white/10 text-white font-semibold' : '' }}">
                                    <span class="flex items-center gap-3 min-w-0">
                                        @include('partials.icon', ['name' => $group['icon'], 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                                        <span class="truncate">{{ $group['label'] }}</span>
                                    </span>
                                    <span class="portum-chevron text-slate-400 text-lg leading-none">›</span>
                                </summary>
                                <div class="portum-submenu mt-1 ml-3 pl-4 border-l border-white/10 space-y-0.5">
                                    @foreach ($group['children'] as $child)
                                        @php $active = request()->route('key') === $child['key']; @endphp
                                        <a href="{{ route('modul.index', $child['key']) }}"
                                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-[12px] leading-4 transition {{ $active ? 'bg-white/10 text-white font-semibold' : 'text-slate-400 hover:bg-white/10 hover:text-white' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $active ? 'bg-gold' : 'bg-slate-500' }} flex-shrink-0"></span>
                                            <span>{{ $child['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    @endforeach
                </div>
            </div>

            @php
                $referenceItems = [
                    ['perm' => 'risalah', 'label' => 'Risalah Rapat', 'route' => 'risalah.index', 'active' => request()->routeIs('risalah.*'), 'icon' => 'file-text'],
                    ['perm' => 'panduan', 'label' => 'Panduan', 'route' => 'panduan.index', 'active' => request()->routeIs('panduan.*'), 'icon' => 'book'],
                    ['perm' => 'ref_akun', 'label' => 'Referensi Akun', 'route' => 'modul.index', 'parameter' => 'ref_akun', 'active' => request()->route('key') === 'ref_akun', 'icon' => 'file-text'],
                ];
                $visibleReferences = collect($referenceItems)->filter(fn ($item) => $canAccess($item['perm']));
            @endphp

            @if ($visibleReferences->isNotEmpty())
                <div class="mt-7">
                    <p class="px-4 mb-3 text-[10.5px] font-bold uppercase tracking-[.08em] text-gold">Referensi</p>
                    <div class="space-y-1">
                        @foreach ($visibleReferences as $item)
                            <a href="{{ isset($item['parameter']) ? route($item['route'], $item['parameter']) : route($item['route']) }}"
                               class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl transition
                               {{ $item['active'] ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                                @include('partials.icon', ['name' => $item['icon'], 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($user?->role?->nama === 'superadmin')
                <div class="mt-7">
                    <p class="px-4 mb-3 text-[10.5px] font-bold uppercase tracking-[.08em] text-gold">Administrasi</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.users.index') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            @include('partials.icon', ['name' => 'users', 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                            <span>Manajemen User</span>
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.roles.*') ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            @include('partials.icon', ['name' => 'shield', 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                            <span>Role &amp; Permission</span>
                        </a>
                        <a href="{{ route('admin.audit-log.index') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.audit-log.*') ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            @include('partials.icon', ['name' => 'file-text', 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                            <span>Audit Log</span>
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        <div class="px-6 py-4 border-t border-white/10 flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_0_4px_rgba(52,211,153,.10)] flex-shrink-0"></span>
            <div class="min-w-0">
                <p class="text-[11px] text-slate-300 font-medium">Sistem Aktif</p>
                <p class="text-[10px] text-slate-500 truncate">Rantai audit aktif · v1.0</p>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-white/10 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#D9A52A] text-white flex items-center justify-center font-bold flex-shrink-0">
                {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
            </div>
            <div class="min-w-0 leading-tight">
                <p class="text-[13px] font-semibold text-white truncate">{{ $user->nama_lengkap }}</p>
                <p class="text-[10.5px] text-slate-400 truncate">{{ $user->role->label }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                @csrf
                <button class="text-slate-400 hover:text-white transition" title="Keluar" aria-label="Keluar">
                    @include('partials.icon', ['name' => 'logout', 'class' => 'w-4 h-4'])
                </button>
            </form>
        </div>
    </aside>
</div>

{{-- Mobile header --}}
<div class="lg:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
    <img src="{{ asset('images/bank-sulteng.png') }}" alt="Bank Sulteng" class="w-[145px] h-auto">
    <div class="flex items-center gap-2">
        <span class="text-sm font-semibold">{{ $user->nama_lengkap }}</span>
        <div class="w-9 h-9 rounded-full bg-[#D9A52A] text-white flex items-center justify-center font-bold">{{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}</div>
    </div>
</div>

{{-- Content tidak berada di dalam container sidebar; sidebar fixed terpisah. --}}
<div class="portum-main min-w-0 bg-white">
    <header class="h-[72px] bg-white px-5 lg:px-8 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <div class="hidden sm:flex w-full max-w-[360px] items-center gap-2.5 bg-[#F4F5F7] rounded-full px-4 py-2.5 text-slate-400">
                @include('partials.icon', ['name' => 'search', 'class' => 'w-[18px] h-[18px] flex-shrink-0'])
                <span class="text-[12px] truncate">Cari data, dokumen, atau modul...</span>
            </div>
            <h1 class="sr-only">@yield('title', 'Dashboard')</h1>
        </div>

        <div class="flex items-center gap-4 flex-shrink-0">
            <button class="relative text-slate-600 hover:text-brand transition" aria-label="Notifikasi">
                @include('partials.icon', ['name' => 'bell', 'class' => 'w-[22px] h-[22px]'])
                <span class="absolute -right-2 -top-2 min-w-[17px] h-[17px] px-1 rounded-full bg-red-500 text-white text-[9px] font-bold flex items-center justify-center">3</span>
            </button>
            <button class="text-slate-600 hover:text-brand transition" aria-label="Bantuan">?</button>
            <button class="text-slate-600 hover:text-brand transition" aria-label="Pengaturan">⚙</button>
            <div class="h-8 w-px bg-slate-200"></div>
            <div class="hidden sm:block text-right leading-tight">
                <p class="text-[13px] font-semibold text-ink">{{ $user->nama_lengkap }}</p>
                <p class="text-[10.5px] text-slate-500">{{ $user->role->label }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-[#D9A52A] text-white flex items-center justify-center font-bold">
                {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
            </div>
            <span class="text-slate-500">⌄</span>
        </div>
    </header>

    <main class="px-5 lg:px-8 pb-8">
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
        <div class="animate-enter">@yield('content')</div>
    </main>
</div>
</body>
</html>
