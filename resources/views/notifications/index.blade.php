@extends('layouts.app')
@section('title', 'Daftar Notifikasi')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-ink flex items-center gap-2.5">
                    @include('partials.icon', ['name' => 'bell', 'class' => 'w-7 h-7 text-[#114E84]'])
                    Notifikasi Saya
                </h1>
                <p class="text-xs text-slate-500 mt-1">Riwayat pemberitahuan status tiket dan informasi permohonan layanan</p>
            </div>

            @if(auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-4 py-2 rounded-xl transition">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-slate-500'])
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        @if(session('status'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-emerald-600'])
                {{ session('status') }}
            </div>
        @endif

        {{-- Notifications List --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse($notifications as $notification)
                    @php
                        $isUnread = is_null($notification->read_at);
                        $data = $notification->data;
                        $type = $data['type'] ?? '';
                        $title = $data['title'] ?? ($data['judul'] ?? 'Pemberitahuan');
                        $message = $data['message'] ?? ($data['pesan'] ?? '');
                        $actionUrl = route('notifications.read', $notification->id);
                    @endphp

                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $isUnread ? 'bg-blue-50/30' : 'hover:bg-slate-50' }} transition">
                        <div class="flex items-start gap-3.5">
                            {{-- Icon by Type --}}
                            <div class="flex-shrink-0 mt-0.5">
                                @if($type === 'ticket_rejected')
                                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                                        @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5'])
                                    </div>
                                @elseif($type === 'ticket_completed')
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
                                    </div>
                                @elseif($type === 'ticket_auto_closed')
                                    <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center">
                                        @include('partials.icon', ['name' => 'archive', 'class' => 'w-5 h-5'])
                                    </div>
                                @elseif($type === 'ticket_allocated')
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-[#114E84] flex items-center justify-center">
                                        @include('partials.icon', ['name' => 'activity', 'class' => 'w-5 h-5'])
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                                        @include('partials.icon', ['name' => 'bell', 'class' => 'w-5 h-5'])
                                    </div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-sm font-bold text-ink {{ $isUnread ? 'text-[#114E84]' : '' }}">
                                        {{ $title }}
                                    </h3>
                                    @if($isUnread)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Baru</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed mb-2">
                                    {{ $message }}
                                </p>
                                @if($type === 'ticket_rejected' && !empty($data['reason']))
                                    <div class="p-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium mb-2">
                                        <strong>Alasan Penolakan:</strong> {{ $data['reason'] }}
                                    </div>
                                @endif
                                <p class="text-[11px] text-slate-400 flex items-center gap-1.5">
                                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-3.5 h-3.5 text-slate-400'])
                                    <span>{{ $notification->created_at->translatedFormat('d M Y, H:i') }} ({{ $notification->created_at->diffForHumans() }})</span>
                                </p>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="flex-shrink-0 flex items-center gap-2 self-end sm:self-center">
                            <a href="{{ $actionUrl }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold shadow-sm transition">
                                <span>Lihat Detail</span>
                                @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-16 px-4 text-center">
                        <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                            @include('partials.icon', ['name' => 'bell', 'class' => 'w-8 h-8'])
                        </div>
                        <h3 class="text-sm font-bold text-slate-700">Belum Ada Notifikasi</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                            Semua notifikasi dan informasi pembaruan tiket layanan Anda akan tersimpan dan tampil di sini.
                        </p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
