{{--
    Profile Dropdown Partial
    Usage: @include('partials.profile-dropdown')
    Requires: $user (auth()->user() with role loaded) set in the parent layout.
--}}
@php
    $dropUser = $user ?? auth()->user();
    $dropUser?->loadMissing('role');

    $avatarInitials = strtoupper(substr($dropUser->nama_lengkap ?? '?', 0, 2));

    $rolePanelLabel = match(strtolower($dropUser->role?->nama ?? '')) {
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
        default                 => ucfirst($dropUser->role?->label ?? 'Panel'),
    };

    $isProfileActive       = request()->routeIs('profile.show');
    $isChangePwdActive     = request()->routeIs('profile.change-password');
@endphp

{{-- Wrapper — relative so the dropdown positions correctly --}}
<div class="relative" id="profileDropdownWrapper">

    {{-- Trigger Button --}}
    <button
        id="profileDropdownBtn"
        type="button"
        aria-haspopup="true"
        aria-expanded="false"
        aria-controls="profileDropdownMenu"
        class="flex items-center gap-2 pl-1 pr-2.5 py-1 rounded-xl hover:bg-slate-100 border border-transparent hover:border-slate-200 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#114E84]/40 group"
    >
        {{-- Avatar circle --}}
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#114E84] to-[#0A335A] text-white font-extrabold flex items-center justify-center text-xs shadow-sm flex-shrink-0 select-none">
            {{ $avatarInitials }}
        </div>
        {{-- Name (hidden on small screens) --}}
        <span class="hidden sm:block text-xs font-semibold text-ink max-w-[100px] truncate leading-tight">
            {{ explode(' ', $dropUser->nama_lengkap)[0] }}
        </span>
        {{-- Chevron --}}
        <svg id="profileDropdownChevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200 hidden sm:block">
            <path d="m6 9 6 6 6-6"/>
        </svg>
    </button>

    {{-- Dropdown Panel --}}
    <div
        id="profileDropdownMenu"
        role="menu"
        aria-labelledby="profileDropdownBtn"
        class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-[0_8px_32px_-4px_rgba(15,23,42,0.14),0_4px_12px_-2px_rgba(15,23,42,0.08)] border border-slate-100 z-50 overflow-hidden
               opacity-0 scale-95 translate-y-1 pointer-events-none
               transition-all duration-200 ease-out origin-top-right"
        style="will-change: opacity, transform;"
    >
        {{-- User Info Header --}}
        <div class="bg-gradient-to-br from-[#114E84] via-[#0E4272] to-[#0A335A] px-4 pt-4 pb-5 relative overflow-hidden">
            {{-- Decorative circles --}}
            <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/5"></div>
            <div class="absolute -right-1 bottom-0 w-12 h-12 rounded-full bg-white/5"></div>

            <div class="flex items-center gap-3 relative z-10">
                {{-- Avatar --}}
                <div class="w-12 h-12 rounded-xl bg-white/20 border-2 border-white/30 text-white font-extrabold flex items-center justify-center text-base shadow-sm flex-shrink-0 backdrop-blur-xs select-none">
                    {{ $avatarInitials }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-white leading-tight truncate">{{ $dropUser->nama_lengkap }}</p>
                    <p class="text-[11px] text-white/70 truncate mt-0.5">@{{ $dropUser->username }}</p>
                    <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full bg-white/15 border border-white/20 text-[10px] font-semibold text-white/90 backdrop-blur-xs">
                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-2.5 h-2.5'])
                        {{ $dropUser->role?->label ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Menu Items --}}
        <div class="p-1.5 space-y-0.5" role="none">

            {{-- Data Pribadi --}}
            <a
                href="{{ route('profile.show') }}"
                role="menuitem"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150
                       {{ $isProfileActive ? 'bg-[#EBF3FB] text-[#114E84] font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-ink' }}"
            >
                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0
                            {{ $isProfileActive ? 'bg-[#114E84] text-white' : 'bg-slate-100 text-slate-500' }}">
                    @include('partials.icon', ['name' => 'user-circle', 'class' => 'w-3.5 h-3.5'])
                </div>
                <span>Data Pribadi</span>
                @if ($isProfileActive)
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#114E84]"></span>
                @endif
            </a>

            {{-- Ubah Password --}}
            <a
                href="{{ route('profile.change-password') }}"
                role="menuitem"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150
                       {{ $isChangePwdActive ? 'bg-[#EBF3FB] text-[#114E84] font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-ink' }}"
            >
                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0
                            {{ $isChangePwdActive ? 'bg-[#114E84] text-white' : 'bg-slate-100 text-slate-500' }}">
                    @include('partials.icon', ['name' => 'password', 'class' => 'w-3.5 h-3.5'])
                </div>
                <span>Ubah Password</span>
                @if ($isChangePwdActive)
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#114E84]"></span>
                @endif
            </a>

            {{-- Divider --}}
            <div class="border-t border-slate-100 my-1"></div>

            {{-- Keluar --}}
            <form method="POST" action="{{ route('logout') }}" role="none">
                @csrf
                <button
                    type="submit"
                    role="menuitem"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-colors duration-150"
                >
                    <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center flex-shrink-0">
                        @include('partials.icon', ['name' => 'logout', 'class' => 'w-3.5 h-3.5'])
                    </div>
                    <span>Keluar</span>
                </button>
            </form>
        </div>

        {{-- Footer: Role Panel Badge --}}
        <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[10.5px] text-slate-400 font-medium">{{ $rolePanelLabel }}</span>
            <span class="text-[10px] text-slate-300">v{{ config('app.version', '1.0') }}</span>
        </div>
    </div>
</div>

{{-- JS: toggle dropdown + close on outside click --}}
<script>
(function () {
    const btn     = document.getElementById('profileDropdownBtn');
    const menu    = document.getElementById('profileDropdownMenu');
    const chevron = document.getElementById('profileDropdownChevron');

    if (!btn || !menu) return;

    function openMenu() {
        menu.classList.remove('opacity-0', 'scale-95', 'translate-y-1', 'pointer-events-none');
        menu.classList.add('opacity-100', 'scale-100', 'translate-y-0', 'pointer-events-auto');
        btn.setAttribute('aria-expanded', 'true');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
    }

    function closeMenu() {
        menu.classList.add('opacity-0', 'scale-95', 'translate-y-1', 'pointer-events-none');
        menu.classList.remove('opacity-100', 'scale-100', 'translate-y-0', 'pointer-events-auto');
        btn.setAttribute('aria-expanded', 'false');
        if (chevron) chevron.style.transform = '';
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = btn.getAttribute('aria-expanded') === 'true';
        isOpen ? closeMenu() : openMenu();
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target) && !btn.contains(e.target)) {
            closeMenu();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });
})();
</script>
