@php
    $user = auth()->user();
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $latestNotifications = $user ? $user->notifications()->take(8)->get() : collect();
@endphp

<div class="relative" id="notificationDropdownContainer">
    {{-- Trigger Button --}}
    <button
        id="notificationDropdownBtn"
        type="button"
        aria-haspopup="true"
        aria-expanded="false"
        class="relative p-2 text-slate-500 hover:text-[#114E84] hover:bg-slate-100 rounded-xl transition duration-150 focus:outline-none focus:ring-2 focus:ring-[#114E84]/20"
        title="Notifikasi"
    >
        @include('partials.icon', ['name' => 'bell', 'class' => 'w-5 h-5'])

        {{-- Unread Badge --}}
        @if($unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white shadow-sm ring-2 ring-white">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
            <span class="absolute top-1.5 right-1.5 flex h-4 w-4 animate-ping rounded-full bg-rose-400 opacity-75"></span>
        @endif
    </button>

    {{-- Dropdown Card --}}
    <div
        id="notificationDropdownPanel"
        class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden z-50 transition-all duration-200 origin-top-right transform opacity-0 scale-95"
        role="menu"
        aria-orientation="vertical"
    >
        {{-- Header --}}
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-ink">Notifikasi</span>
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 text-[11px] font-semibold bg-[#114E84]/10 text-[#114E84] rounded-full">
                        {{ $unreadCount }} Baru
                    </span>
                @endif
            </div>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-[#114E84] hover:text-[#0E4272] hover:underline transition">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        {{-- Notification List --}}
        <div class="max-h-[380px] overflow-y-auto divide-y divide-slate-100">
            @forelse($latestNotifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $data = $notification->data;
                    $type = $data['type'] ?? '';
                    $title = $data['title'] ?? ($data['judul'] ?? 'Pemberitahuan');
                    $message = $data['message'] ?? ($data['pesan'] ?? '');
                    $actionUrl = route('notifications.read', $notification->id);
                @endphp

                <a href="{{ $actionUrl }}"
                   class="block p-3.5 transition duration-150 {{ $isUnread ? 'bg-blue-50/40 hover:bg-blue-50/70' : 'hover:bg-slate-50' }}">
                    <div class="flex items-start gap-3">
                        {{-- Icon by Type --}}
                        <div class="flex-shrink-0 mt-0.5">
                            @if($type === 'ticket_rejected')
                                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                                    @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-4 h-4'])
                                </div>
                            @elseif($type === 'ticket_completed')
                                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                                </div>
                            @elseif($type === 'ticket_auto_closed')
                                <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center">
                                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4'])
                                </div>
                            @elseif($type === 'ticket_allocated')
                                <div class="w-8 h-8 rounded-xl bg-blue-100 text-[#114E84] flex items-center justify-center">
                                    @include('partials.icon', ['name' => 'activity', 'class' => 'w-4 h-4'])
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                                    @include('partials.icon', ['name' => 'bell', 'class' => 'w-4 h-4'])
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <p class="text-xs font-bold text-ink truncate {{ $isUnread ? 'text-[#114E84]' : '' }}">
                                    {{ $title }}
                                </p>
                                @if($isUnread)
                                    <span class="w-2 h-2 rounded-full bg-blue-600 flex-shrink-0"></span>
                                @endif
                            </div>
                            <p class="text-[11.5px] text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $message }}
                            </p>
                            @if($type === 'ticket_rejected' && !empty($data['reason']))
                                <p class="text-[11px] font-medium text-rose-600 mt-1 bg-rose-50/80 px-2 py-0.5 rounded border border-rose-200/60 line-clamp-1">
                                    Alasan: {{ $data['reason'] }}
                                </p>
                            @endif
                            <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                                @include('partials.icon', ['name' => 'clock', 'class' => 'w-3 h-3 text-slate-400'])
                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                            </p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="py-8 px-4 text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                        @include('partials.icon', ['name' => 'bell', 'class' => 'w-6 h-6'])
                    </div>
                    <p class="text-xs font-semibold text-slate-700">Tidak ada notifikasi</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pembaruan tiket dan aktivitas akan tampil di sini</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if($latestNotifications->isNotEmpty())
            <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-center">
                <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline">
                    Lihat Semua Notifikasi &rarr;
                </a>
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    const btn = document.getElementById('notificationDropdownBtn');
    const panel = document.getElementById('notificationDropdownPanel');
    if (!btn || !panel) return;

    function openDropdown() {
        panel.classList.remove('hidden');
        requestAnimationFrame(() => {
            panel.classList.remove('opacity-0', 'scale-95');
            panel.classList.add('opacity-100', 'scale-100');
        });
        btn.setAttribute('aria-expanded', 'true');
    }

    function closeDropdown() {
        panel.classList.remove('opacity-100', 'scale-100');
        panel.classList.add('opacity-0', 'scale-95');
        setTimeout(() => panel.classList.add('hidden'), 150);
        btn.setAttribute('aria-expanded', 'false');
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = !panel.classList.contains('hidden');
        if (isOpen) {
            closeDropdown();
        } else {
            // Close profile dropdown if open
            const profilePanel = document.getElementById('profileDropdownPanel');
            if (profilePanel && !profilePanel.classList.contains('hidden')) {
                profilePanel.classList.add('hidden');
            }
            openDropdown();
        }
    });

    document.addEventListener('click', function (e) {
        if (!panel.contains(e.target) && !btn.contains(e.target)) {
            if (!panel.classList.contains('hidden')) closeDropdown();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.classList.contains('hidden')) {
            closeDropdown();
        }
    });
})();
</script>
