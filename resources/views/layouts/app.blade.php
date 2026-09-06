<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Bank Sulteng</title>
    @include('partials.head-assets')
    <style>
        @media (min-width: 1024px) {
            .portum-sidebar-rail { position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 50; }
            .portum-main { margin-left: 270px; min-height: 100vh; }
        }
        .portum-nav-details > summary { list-style: none; }
        .portum-nav-details > summary::-webkit-details-marker { display: none; }
        .portum-nav-details[open] > summary .portum-chevron { transform: rotate(90deg); }
        .portum-chevron { transition: transform .18s ease; }
        .portum-submenu { animation: portumSubmenu .18s ease-out; }
        @keyframes portumSubmenu { from { opacity: .3; transform: translateY(-3px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-canvas text-ink antialiased min-h-full flex flex-col selection:bg-brand selection:text-gold">
@php
    $user = auth()->user();
    $user?->loadMissing('role.permissions');
    $permissions = $user?->role?->permissions?->keyBy('perm_key') ?? collect();

    $canAccess = fn (string $key) => $permissions->has($key);
    $canWrite  = fn (string $key) => (bool) optional($permissions->get($key))->can_write;

    $operationalGroups = [
        [
            'perm' => 'umum_rt',
            'label' => 'Umum & Rumah Tangga',
            'icon' => 'building',
            'children' => [
                ['key' => 'kendaraan', 'label' => 'Kendaraan & Driver'],
                ['key' => 'biaya_harian', 'label' => 'Biaya BBM & Perawatan RT'],
                ['key' => 'permintaan_cabang', 'label' => 'Permintaan Cabang (ATK/Inv)'],
            ],
        ],
        [
            'perm' => 'aset_logistik',
            'label' => 'Aset & Logistik',
            'icon' => 'layers',
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
                ['key' => 'penawaran', 'label' => 'Penawaran Vendor'],
                ['key' => 'negosiasi', 'label' => 'Negosiasi Harga'],
                ['key' => 'draft_dokumen', 'label' => 'Draft Dokumen SPK'],
                ['key' => 'spk', 'label' => 'Surat Perintah Kerja (SPK)'],
                ['key' => 'reminder', 'label' => 'Reminder & Monitoring'],
            ],
        ],
        [
            'perm' => 'arsip_surat_memo',
            'label' => 'Arsip Surat & Memo',
            'icon' => 'archive',
            'children' => [
                ['key' => 'surat_masuk', 'label' => 'Surat Masuk'],
                ['key' => 'surat_keluar', 'label' => 'Surat Keluar'],
                ['key' => 'memo_masuk', 'label' => 'Memo Masuk'],
                ['key' => 'memo_keluar', 'label' => 'Memo Keluar'],
            ],
        ],
    ];

    $referenceItems = [
        ['perm' => 'risalah', 'label' => 'Risalah Rapat', 'route' => 'risalah.index', 'active' => request()->routeIs('risalah.*'), 'icon' => 'file-text'],
        ['perm' => 'panduan', 'label' => 'Buku Panduan', 'route' => 'panduan.index', 'active' => request()->routeIs('panduan.*'), 'icon' => 'book'],
        ['perm' => 'ref_akun', 'label' => 'Referensi Akun (COA)', 'route' => 'modul.index', 'parameter' => 'ref_akun', 'active' => request()->route('key') === 'ref_akun', 'icon' => 'sliders'],
    ];
    $visibleReferences = collect($referenceItems)->filter(fn ($item) => $canAccess($item['perm']));
@endphp

{{-- ================= DESKTOP SIDEBAR ================= --}}
<aside class="portum-sidebar-rail hidden lg:flex flex-col bg-canvas select-none">
    {{-- 1. Top Logo Area (Seamless with Dashboard Background) --}}
    <div class="h-[72px] px-4 flex items-center justify-center bg-canvas flex-shrink-0">
        <a href="{{ $user?->isUser() ? route('user.dashboard') : ($user?->isOperator() ? route('operator.dashboard') : ($user?->isSuperAdmin() ? route('admin.dashboard') : ($user?->isKabag() ? route('kabag.dashboard') : ($user?->hasRole('umum_rt') ? route('staf-umum.dashboard') : route('dashboard'))))) }}" class="flex items-center" style="max-width:170px;">
            <img src="{{ asset('images/bank-sulteng.png') }}"
                 alt="Bank Sulteng"
                 style="max-height:44px; width:auto; max-width:155px; object-fit:contain; display:block;">
        </a>
    </div>

    {{-- 2. Blue Sidebar Body with Rounded Top-Right Corner --}}
    <div class="flex-1 flex flex-col bg-gradient-to-b from-[#114E84] via-[#0E4272] to-[#0A335A] text-white rounded-tr-[36px] rounded-br-[36px] overflow-hidden shadow-2xl">
        {{-- Navigation Menu --}}
        <nav class="flex-1 overflow-y-auto pt-4 pb-2 space-y-4 text-[13px]">
            {{-- 1. LAYANAN & MONITORING --}}
            @if ($canAccess('dashboard') || $canAccess('ticketing'))
                <div class="pt-1">
                    <p class="px-5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-white/40">Layanan &amp; Monitoring</p>
                    <div class="space-y-0.5 px-3">
                        @if ($canAccess('dashboard'))
                            @php
                                $dashboardRoute = route('dashboard');
                                $dashboardLabel = 'Dashboard';
                                if ($user?->isUser()) {
                                    $dashboardRoute = route('user.dashboard');
                                    $dashboardLabel = 'Dashboard Tiket';
                                } elseif ($user?->isOperator()) {
                                    $dashboardRoute = route('operator.dashboard');
                                    $dashboardLabel = 'Dashboard Operator';
                                } elseif ($user?->isSuperAdmin()) {
                                    $dashboardRoute = route('admin.dashboard');
                                    $dashboardLabel = 'Dashboard Admin';
                                } elseif ($user?->isKabag()) {
                                    $dashboardRoute = route('kabag.dashboard');
                                    $dashboardLabel = 'Dashboard Kabag';
                                } elseif ($user?->hasRole('umum_rt')) {
                                    $dashboardRoute = route('staf-umum.dashboard');
                                    $dashboardLabel = 'Dashboard Staf Umum';
                                }
                                $isDashActive = request()->url() === $dashboardRoute;
                            @endphp
                            <a href="{{ $dashboardRoute }}"
                               class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ $isDashActive ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 flex items-center justify-center {{ $isDashActive ? 'text-[#114E84]' : 'text-white/80' }}">
                                        @include('partials.icon', ['name' => 'home', 'class' => 'w-[18px] h-[18px]'])
                                    </div>
                                    <span class="text-[12.5px]">{{ $dashboardLabel }}</span>
                                </div>
                                <span class="{{ $isDashActive ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                            </a>
                        @endif

                        @if ($canAccess('ticketing'))
                            <a href="{{ route('tickets.index') }}"
                               class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ request()->routeIs('tickets.*') ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-6 h-6 flex items-center justify-center {{ request()->routeIs('tickets.*') ? 'text-[#114E84]' : 'text-white/80' }} flex-shrink-0">
                                        @include('partials.icon', ['name' => 'inbox', 'class' => 'w-[17px] h-[17px]'])
                                    </div>
                                    <span class="text-[12.5px] truncate">{{ $user?->isUser() ? 'Tiket Saya' : 'Sistem Tiket' }}</span>
                                </div>
                                @if ($user?->isKabag())
                                    @php
                                        $kabagAntri = \App\Models\Ticket::where('department_id', $user->effectiveDepartmentId())
                                            ->where('status', 'Diverifikasi')
                                            ->whereNull('assigned_to')
                                            ->count();
                                    @endphp
                                    @if ($kabagAntri > 0)
                                        <span class="bg-amber-400 text-[#0E1726] text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none min-w-[18px] text-center" title="{{ $kabagAntri }} tiket butuh disposisi">
                                            {{ $kabagAntri }}
                                        </span>
                                    @else
                                        <span class="{{ request()->routeIs('tickets.*') ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                                    @endif
                                @elseif ($user?->hasRole('umum_rt'))
                                    @php
                                        $stafAntri = \App\Models\Ticket::where('assigned_to', $user->id)
                                            ->whereIn('status', ['Didistribusikan', 'Dalam Proses'])
                                            ->count();
                                    @endphp
                                    @if ($stafAntri > 0)
                                        <span class="bg-blue-400 text-[#0E1726] text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none min-w-[18px] text-center" title="{{ $stafAntri }} tiket tugas Anda">
                                            {{ $stafAntri }}
                                        </span>
                                    @else
                                        <span class="{{ request()->routeIs('tickets.*') ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                                    @endif
                                @elseif (!$user?->isUser())
                                    @php
                                        $antriCount = \App\Models\Ticket::where('status', 'Menunggu Verifikasi')->count();
                                    @endphp
                                    @if ($antriCount > 0)
                                        <span class="bg-amber-400 text-[#0E1726] text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none min-w-[18px] text-center">
                                            {{ $antriCount }}
                                        </span>
                                    @else
                                        <span class="{{ request()->routeIs('tickets.*') ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                                    @endif
                                @else
                                    <span class="{{ request()->routeIs('tickets.*') ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 4. MODUL OPERASIONAL --}}
            @php
                $visibleOpGroups = collect($operationalGroups)->filter(fn ($g) => $canAccess($g['perm']));
            @endphp
            @if ($visibleOpGroups->isNotEmpty())
                <div class="pt-1">
                    <p class="px-5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-white/40">Operasional</p>
                    <div class="space-y-0.5 px-3">
                        @foreach ($visibleOpGroups as $group)
                            @php
                                $groupActive = collect($group['children'])->contains(fn ($child) => request()->route('key') === $child['key']);
                            @endphp
                            <details class="portum-nav-details group" {{ $groupActive ? 'open' : '' }}>
                                <summary class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl cursor-pointer select-none transition text-white/90 hover:bg-white/10 hover:text-white {{ $groupActive ? 'bg-white/15 font-semibold text-white' : '' }}">
                                    <span class="flex items-center gap-3 min-w-0">
                                        <div class="w-6 h-6 flex items-center justify-center text-white/80 flex-shrink-0">
                                            @include('partials.icon', ['name' => $group['icon'], 'class' => 'w-[17px] h-[17px]'])
                                        </div>
                                        <span class="truncate text-[12.5px]">{{ $group['label'] }}</span>
                                    </span>
                                    <span class="portum-chevron text-white/50 text-xs leading-none">
                                        @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3.5 h-3.5'])
                                    </span>
                                </summary>
                                <div class="portum-submenu mt-0.5 ml-5 pl-3 border-l border-white/20 space-y-0.5 py-1">
                                    @foreach ($group['children'] as $child)
                                        @php $active = request()->route('key') === $child['key']; @endphp
                                        <a href="{{ route('modul.index', $child['key']) }}"
                                           class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[12px] transition {{ $active ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $active ? 'bg-[#114E84]' : 'bg-white/40' }} flex-shrink-0"></span>
                                            <span class="truncate">{{ $child['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 5. REFERENSI & PANDUAN --}}
            @if ($visibleReferences->isNotEmpty())
                <div class="pt-1">
                    <p class="px-5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-white/40">Referensi</p>
                    <div class="space-y-0.5 px-3">
                        @foreach ($visibleReferences as $item)
                            <a href="{{ isset($item['parameter']) ? route($item['route'], $item['parameter']) : route($item['route']) }}"
                               class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ $item['active'] ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-6 h-6 flex items-center justify-center {{ $item['active'] ? 'text-[#114E84]' : 'text-white/80' }} flex-shrink-0">
                                        @include('partials.icon', ['name' => $item['icon'], 'class' => 'w-[17px] h-[17px]'])
                                    </div>
                                    <span class="text-[12.5px] truncate">{{ $item['label'] }}</span>
                                </div>
                                <span class="{{ $item['active'] ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 6. ADMINISTRASI SISTEM --}}
            @if ($canAccess('user_mgmt') || $canAccess('role_mgmt') || $canAccess('audit_log'))
                <div class="pt-1">
                    <p class="px-5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-white/40">Administrasi</p>
                    <div class="space-y-0.5 px-3">
                        @if ($canAccess('user_mgmt'))
                            <a href="{{ route('admin.users.index') }}"
                               class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.users.*') ? 'text-[#114E84]' : 'text-white/80' }}">
                                        @include('partials.icon', ['name' => 'users', 'class' => 'w-[17px] h-[17px]'])
                                    </div>
                                    <span class="text-[12.5px]">Manajemen User</span>
                                </div>
                                <span class="{{ request()->routeIs('admin.users.*') ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                            </a>
                        @endif

                        @if ($canAccess('role_mgmt'))
                            <a href="{{ route('admin.roles.index') }}"
                               class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ request()->routeIs('admin.roles.*') ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.roles.*') ? 'text-[#114E84]' : 'text-white/80' }}">
                                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-[17px] h-[17px]'])
                                    </div>
                                    <span class="text-[12.5px]">Peran &amp; Hak Akses</span>
                                </div>
                                <span class="{{ request()->routeIs('admin.roles.*') ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                            </a>
                        @endif

                        @if ($canAccess('audit_log'))
                            <a href="{{ route('admin.audit-log.index') }}"
                               class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ request()->routeIs('admin.audit-log.*') ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.audit-log.*') ? 'text-[#114E84]' : 'text-white/80' }}">
                                        @include('partials.icon', ['name' => 'lock', 'class' => 'w-[17px] h-[17px]'])
                                    </div>
                                    <span class="text-[12.5px]">Audit Log &amp; Hash</span>
                                </div>
                                <span class="{{ request()->routeIs('admin.audit-log.*') ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </nav>

        {{-- 3. Footer: Powered by Bank Sulteng & User/Logout --}}
        <div class="p-3.5 border-t border-white/10 bg-black/15">
            <div class="flex items-center justify-between gap-2 mb-2 px-1">
                <div class="text-[11px] text-white/70">
                    Powered by <strong class="text-white font-bold">Bank Sulteng</strong>
                </div>
            </div>
            <div class="flex items-center gap-2 p-2 rounded-xl bg-white/10 backdrop-blur-xs">
                <div class="w-7 h-7 rounded-lg bg-white/20 text-white font-bold flex items-center justify-center text-xs flex-shrink-0">
                    {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11.5px] font-bold text-white truncate leading-tight">{{ $user->nama_lengkap }}</p>
                    <p class="text-[9.5px] text-white/70 truncate">{{ $user->role->label }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="p-1 rounded-lg text-white/70 hover:text-white hover:bg-white/20 transition" title="Keluar" aria-label="Keluar">
                        @include('partials.icon', ['name' => 'logout', 'class' => 'w-3.5 h-3.5'])
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

{{-- ================= MOBILE HEADER ================= --}}
<div class="lg:hidden bg-gradient-to-r from-[#114E84] via-[#0E4272] to-[#0A335A] text-white px-4 h-14 flex items-center justify-between sticky top-0 z-40 shadow-sm">
    <div class="flex items-center gap-3">
        <button id="mobileMenuBtn" class="p-1.5 rounded-lg bg-white/10 text-white hover:bg-white/20 transition flex items-center justify-center" aria-label="Buka Menu">
            @include('partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
        </button>
        <div class="bg-white rounded-full px-3 py-1 shadow-xs flex items-center h-8">
            <img src="{{ asset('images/bank-sulteng.png') }}"
                 alt="Bank Sulteng"
                 class="h-5 w-auto max-w-[120px] object-contain"
                 style="max-height: 20px; width: auto;">
        </div>
    </div>
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-white/20 text-white font-bold flex items-center justify-center text-xs">
            {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
        </div>
    </div>
</div>

{{-- Mobile Sidebar Drawer --}}
<div id="mobileDrawer" class="lg:hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden transition-opacity">
    <div class="fixed inset-y-0 left-0 w-[280px] bg-gradient-to-b from-[#114E84] to-[#0A335A] text-white flex flex-col shadow-2xl overflow-y-auto">
        <div class="pt-4 pb-3 pl-0 pr-3 flex items-center justify-between border-b border-white/10">
            <div class="bg-white rounded-r-full py-2 px-4 shadow-sm inline-flex items-center">
                <img src="{{ asset('images/bank-sulteng.png') }}" alt="Bank Sulteng" class="h-5 w-auto" style="max-height: 22px; width: auto;">
            </div>
            <button id="closeMobileDrawer" class="p-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10" aria-label="Tutup Menu">
                @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5'])
            </button>
        </div>
        <nav class="flex-1 p-3 space-y-3 text-xs">
            {{-- 1. LAYANAN & MONITORING --}}
            @if ($canAccess('dashboard') || $canAccess('ticketing'))
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-white/50 uppercase px-2 mb-1">Layanan &amp; Monitoring</p>
                    @if ($canAccess('dashboard'))
                        @php
                            $mobileDashRoute = route('dashboard');
                            $mobileDashLabel = 'Dashboard';
                            if ($user?->isUser()) {
                                $mobileDashRoute = route('user.dashboard');
                                $mobileDashLabel = 'Dashboard Tiket';
                            } elseif ($user?->isOperator()) {
                                $mobileDashRoute = route('operator.dashboard');
                                $mobileDashLabel = 'Dashboard Operator';
                            } elseif ($user?->isSuperAdmin()) {
                                $mobileDashRoute = route('admin.dashboard');
                                $mobileDashLabel = 'Dashboard Admin';
                            } elseif ($user?->isKabag()) {
                                $mobileDashRoute = route('kabag.dashboard');
                                $mobileDashLabel = 'Dashboard Kabag';
                            } elseif ($user?->hasRole('umum_rt')) {
                                $mobileDashRoute = route('staf-umum.dashboard');
                                $mobileDashLabel = 'Dashboard Staf Umum';
                            }
                            $isMobileDashActive = request()->url() === $mobileDashRoute;
                        @endphp
                        <a href="{{ $mobileDashRoute }}" class="flex items-center gap-3 p-2 rounded-xl {{ $isMobileDashActive ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10' }}">
                            @include('partials.icon', ['name' => 'home', 'class' => 'w-4 h-4'])
                            <span>{{ $mobileDashLabel }}</span>
                        </a>
                    @endif

                    @if ($canAccess('ticketing'))
                        <a href="{{ route('tickets.index') }}" class="flex items-center justify-between gap-3 p-2 rounded-xl {{ request()->routeIs('tickets.*') ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4'])
                                <span class="truncate">{{ $user?->isUser() ? 'Tiket Saya' : 'Sistem Tiket' }}</span>
                            </div>
                            @if ($user?->isKabag())
                                @php
                                    $kabagAntriMobile = \App\Models\Ticket::where('department_id', $user->effectiveDepartmentId())
                                        ->where('status', 'Diverifikasi')
                                        ->whereNull('assigned_to')
                                        ->count();
                                @endphp
                                @if ($kabagAntriMobile > 0)
                                    <span class="bg-amber-400 text-[#0E1726] text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none min-w-[18px] text-center">
                                        {{ $kabagAntriMobile }}
                                    </span>
                                @endif
                            @endif
                        </a>
                    @endif
                </div>
            @endif

            {{-- 4. MODUL OPERASIONAL --}}
            @if ($visibleOpGroups->isNotEmpty())
                @foreach ($visibleOpGroups as $group)
                    <div class="pt-1">
                        <p class="text-[10px] font-bold text-white/50 uppercase px-2 mb-1">{{ $group['label'] }}</p>
                        <div class="space-y-0.5 pl-2">
                            @foreach ($group['children'] as $child)
                                <a href="{{ route('modul.index', $child['key']) }}" class="block px-2 py-1.5 rounded-lg text-white/80 hover:text-white {{ request()->route('key') === $child['key'] ? 'text-white font-bold bg-white/20' : '' }}">
                                    • {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- 5. REFERENSI & PANDUAN --}}
            @if ($visibleReferences->isNotEmpty())
                <div class="pt-1">
                    <p class="text-[10px] font-bold text-white/50 uppercase px-2 mb-1">Referensi</p>
                    <div class="space-y-0.5 pl-2">
                        @foreach ($visibleReferences as $item)
                            <a href="{{ isset($item['parameter']) ? route($item['route'], $item['parameter']) : route($item['route']) }}" class="block px-2 py-1.5 rounded-lg text-white/80 hover:text-white {{ $item['active'] ? 'text-white font-bold bg-white/20' : '' }}">
                                • {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 6. ADMINISTRASI SISTEM --}}
            @if ($canAccess('user_mgmt') || $canAccess('role_mgmt') || $canAccess('audit_log'))
                <div class="space-y-1 pt-1">
                    <p class="text-[10px] font-bold text-white/50 uppercase px-2 mb-1">Administrasi</p>
                    @if ($canAccess('user_mgmt'))
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 p-2 rounded-xl {{ request()->routeIs('admin.users.*') ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10' }}">
                            @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4'])
                            <span>Manajemen User</span>
                        </a>
                    @endif
                    @if ($canAccess('role_mgmt'))
                        <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 p-2 rounded-xl {{ request()->routeIs('admin.roles.*') ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10' }}">
                            @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4'])
                            <span>Peran &amp; Hak Akses</span>
                        </a>
                    @endif
                    @if ($canAccess('audit_log'))
                        <a href="{{ route('admin.audit-log.index') }}" class="flex items-center gap-3 p-2 rounded-xl {{ request()->routeIs('admin.audit-log.*') ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10' }}">
                            @include('partials.icon', ['name' => 'lock', 'class' => 'w-4 h-4'])
                            <span>Audit Log &amp; Hash</span>
                        </a>
                    @endif
                </div>
            @endif
        </nav>
        <div class="p-3 border-t border-white/10 flex items-center justify-between bg-black/10">
            <div class="min-w-0">
                <p class="text-xs font-bold text-white truncate">{{ $user->nama_lengkap }}</p>
                <p class="text-[10px] text-white/70 truncate">{{ $user->role->label }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-white/80 hover:text-white p-2">
                    @include('partials.icon', ['name' => 'logout', 'class' => 'w-4 h-4'])
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ================= MAIN CONTENT WRAPPER ================= --}}
<div class="portum-main min-w-0 flex-1 flex flex-col bg-canvas">
    {{-- Clean Top Header --}}
    <header class="glass-header sticky top-0 z-30 h-[72px] border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3">
        {{-- Breadcrumb / Current Route Context --}}
        <div class="flex items-center gap-2 min-w-0">
            <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium min-w-0">
                <a href="{{ $user?->isUser() ? route('user.dashboard') : ($user?->isOperator() ? route('operator.dashboard') : ($user?->isSuperAdmin() ? route('admin.dashboard') : ($user?->isKabag() ? route('kabag.dashboard') : ($user?->hasRole('umum_rt') ? route('staf-umum.dashboard') : route('dashboard'))))) }}" class="hover:text-brand transition-colors flex-shrink-0">Bank Sulteng</a>
                <span class="text-slate-300 flex-shrink-0">/</span>
                <span class="text-ink font-semibold truncate">@yield('title', 'Dashboard')</span>
            </div>
        </div>

        {{-- Right Controls --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            <div class="relative hidden lg:flex items-center" style="width:220px;">
                <span class="absolute left-2.5 text-slate-400 pointer-events-none">
                    @include('partials.icon', ['name' => 'search', 'class' => 'w-3.5 h-3.5'])
                </span>
                <input type="text"
                       placeholder="Cari aset, memo, PKS..."
                       class="w-full bg-slate-100 text-xs rounded-lg pl-8 pr-3 py-1.5 border border-slate-200 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 text-slate-700 placeholder-slate-400 outline-none transition">
            </div>

            <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500 font-medium px-2 py-1.5 bg-white border border-slate-200 rounded-lg">
                @include('partials.icon', ['name' => 'calendar', 'class' => 'w-3.5 h-3.5 text-slate-400'])
                <span class="hidden md:inline">{{ now()->translatedFormat('d M Y') }}</span>
                <span class="md:hidden">{{ now()->format('d/m') }}</span>
            </div>
        </div>
    </header>

    {{-- Main View Body --}}
    <main class="flex-1 px-5 lg:px-8 py-6 max-w-7xl w-full mx-auto">
        @if (session('status'))
            <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 text-sm animate-enter">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5 text-emerald-600 flex-shrink-0', 'stroke' => 2])
                <span class="font-medium">{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 flex items-start gap-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 text-sm animate-enter">
                @include('partials.icon', ['name' => 'alert', 'class' => 'w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5', 'stroke' => 2])
                <ul class="list-disc pl-4 space-y-0.5 font-medium">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="animate-enter">
            @yield('content')
        </div>
    </main>
</div>

<script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileDrawer = document.getElementById('mobileDrawer');
    const closeMobileDrawer = document.getElementById('closeMobileDrawer');

    if (mobileMenuBtn && mobileDrawer && closeMobileDrawer) {
        mobileMenuBtn.addEventListener('click', () => mobileDrawer.classList.remove('hidden'));
        closeMobileDrawer.addEventListener('click', () => mobileDrawer.classList.add('hidden'));
        mobileDrawer.addEventListener('click', (e) => {
            if (e.target === mobileDrawer) mobileDrawer.classList.add('hidden');
        });
    }
</script>
</body>
</html>

