@extends('layouts.app')
@section('title', 'Dashboard Layanan Tiket')

@section('content')
    {{-- Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-1">Helpdesk &amp; Layanan Operasional</p>
            <h1 class="text-2xl font-bold text-ink">
                Selamat Datang, {{ auth()->user()->nama_lengkap }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ auth()->user()->bagian ?? 'Cabang / Unit Kerja' }} &bull; {{ auth()->user()->jabatan ?? 'Pemohon' }}
            </p>
        </div>

        <a href="{{ route('tickets.create') }}"
           class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md transition duration-200">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
            Buat Tiket Baru
        </a>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Total Tiket --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Diajukan</p>
                <p class="text-3xl font-extrabold text-ink">{{ $stats['total'] }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Seluruh permohonan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-6 h-6'])
            </div>
        </div>

        {{-- Menunggu Verifikasi --}}
        <div class="bg-gradient-to-br from-amber-500/10 to-amber-500/5 p-5 rounded-2xl border border-amber-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 mb-1">Menunggu Verifikasi</p>
                <p class="text-3xl font-extrabold text-amber-800">{{ $stats['menunggu'] }}</p>
                <p class="text-[11px] text-amber-600 mt-1">Sedang antre di Operator</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-6 h-6'])
            </div>
        </div>

        {{-- Diproses --}}
        <div class="bg-gradient-to-br from-blue-500/10 to-blue-500/5 p-5 rounded-2xl border border-blue-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-700 mb-1">Sedang Diproses</p>
                <p class="text-3xl font-extrabold text-blue-800">{{ $stats['diproses'] }}</p>
                <p class="text-[11px] text-blue-600 mt-1">Diverifikasi / Tindak lanjut</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-[#114E84] flex items-center justify-center">
                @include('partials.icon', ['name' => 'activity', 'class' => 'w-6 h-6'])
            </div>
        </div>

        {{-- Selesai --}}
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-500/5 p-5 rounded-2xl border border-emerald-200 shadow-card flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Tiket Selesai</p>
                <p class="text-3xl font-extrabold text-emerald-800">{{ $stats['selesai'] }}</p>
                <p class="text-[11px] text-emerald-600 mt-1">Tuntas dikerjakan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
            </div>
        </div>
    </div>

    {{-- Recent Tickets Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-ink">5 Tiket Terakhir Diajukan</h2>
                <p class="text-xs text-slate-400">Pantau status terkini dari permintaan layanan Anda</p>
            </div>
            <a href="{{ route('tickets.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1">
                Lihat Semua Tiket &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-[11.5px] uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3.5 font-semibold">Nomor Tiket</th>
                        <th class="px-5 py-3.5 font-semibold">Tanggal</th>
                        <th class="px-5 py-3.5 font-semibold">Kategori</th>
                        <th class="px-5 py-3.5 font-semibold">Uraian / Masalah</th>
                        <th class="px-5 py-3.5 font-semibold">Prioritas</th>
                        <th class="px-5 py-3.5 font-semibold">Bagian Penanganan</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentTickets as $t)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono font-bold text-[#114E84] whitespace-nowrap">
                                <a href="{{ route('tickets.show', $t) }}" class="hover:underline">
                                    {{ $t->ticket_number }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $t->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="text-xs font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md">
                                    {{ $t->category?->name ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-700 text-xs min-w-[200px]">
                                <p class="line-clamp-1">{{ $t->description }}</p>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $t->priority_badge }}">
                                    {{ $t->priority }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-600 whitespace-nowrap">
                                @if ($t->department)
                                    <span class="inline-flex items-center gap-1 font-medium text-slate-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                                        {{ $t->department->name }}
                                    </span>
                                @else
                                    <span class="text-amber-600 font-medium text-xs">Menunggu Penanganan</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11.5px] font-medium border {{ $t->status_badge }}">
                                    {{-- Gunakan label ramah pemohon alih-alih istilah internal sistem --}}
                                    {{ $t->pemohon_status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('tickets.show', $t) }}"
                                   class="inline-flex items-center gap-1 bg-[#114E84]/10 hover:bg-[#114E84]/20 text-[#114E84] text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                    Lacak Tiket &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'inbox', 'class' => 'w-6 h-6'])
                                </div>
                                <p class="text-slate-600 font-semibold text-sm mb-1">Belum Ada Tiket yang Diajukan</p>
                                <p class="text-slate-400 text-xs mb-4">Jika Anda memiliki kendala sarana, aset, atau permintaan pengadaan, silakan buat tiket baru.</p>
                                <a href="{{ route('tickets.create') }}"
                                   class="inline-flex items-center gap-2 bg-[#114E84] text-white text-xs font-semibold px-4 py-2 rounded-xl shadow hover:bg-[#0E4272] transition">
                                    @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
                                    Buat Tiket Pertama
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Popup Ringkasan Notifikasi Penting untuk User --}}
    @if(isset($importantNotifications) && $importantNotifications->isNotEmpty())
        <div id="importantNotificationModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200" role="dialog" aria-modal="true">
            <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[88vh] animate-enter">
                {{-- Modal Header --}}
                <div class="px-6 py-5 bg-gradient-to-r from-[#114E84] to-[#0D3B64] text-white flex items-center justify-between relative overflow-hidden">
                    <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-white/5 pointer-events-none"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-10 h-10 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center text-amber-300">
                            @include('partials.icon', ['name' => 'bell', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white leading-tight">Pemberitahuan Penting Permohonan</h2>
                            <p class="text-xs text-white/80 mt-0.5">Terdapat <span class="font-bold text-amber-300">{{ $importantNotifications->count() }} pembaruan</span> yang memerlukan tindak lanjut Anda</p>
                        </div>
                    </div>
                    <button type="button" id="closeImportantModalBtn" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition" title="Tutup">
                        @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5'])
                    </button>
                </div>

                {{-- Modal Content List --}}
                <div class="p-6 overflow-y-auto space-y-4 divide-y divide-slate-100 max-h-[60vh]">
                    @foreach($importantNotifications as $item)
                        @php
                            $data = $item->data;
                            $type = $data['type'] ?? '';
                            $ticketId = $data['ticket_id'] ?? null;
                            $ticketNumber = $data['ticket_number'] ?? '';
                            $title = $data['title'] ?? ($data['judul'] ?? 'Pemberitahuan Tiket');
                            $msg = $data['message'] ?? ($data['pesan'] ?? '');
                            $readUrl = route('notifications.read', $item->id);
                        @endphp

                        <div class="pt-4 first:pt-0">
                            @if($type === 'ticket_rejected')
                                {{-- Card Ditolak --}}
                                <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200/80 space-y-2.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-3.5 h-3.5 text-rose-600'])
                                            Permohonan Ditolak
                                        </span>
                                        <span class="text-xs font-mono font-bold text-slate-600">{{ $ticketNumber }}</span>
                                    </div>
                                    <p class="text-xs text-slate-700 leading-relaxed font-medium">
                                        {{ $msg }}
                                    </p>
                                    @if(!empty($data['reason']))
                                        <div class="p-2.5 rounded-xl bg-white border border-rose-200 text-xs text-rose-800">
                                            <span class="font-bold">Alasan Penolakan:</span> {{ $data['reason'] }}
                                        </div>
                                    @endif
                                    <div class="pt-1 flex justify-end">
                                        <a href="{{ $readUrl }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white hover:bg-rose-100/60 text-rose-700 text-xs font-semibold border border-rose-200 transition">
                                            <span>Lihat Detail Tiket</span>
                                            @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                                        </a>
                                    </div>
                                </div>

                            @elseif($type === 'ticket_completed')
                                {{-- Card Selesai & Butuh Konfirmasi --}}
                                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/90 space-y-2.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            @include('partials.icon', ['name' => 'clock', 'class' => 'w-3.5 h-3.5 text-amber-600'])
                                            Pekerjaan Selesai &bull; Butuh Konfirmasi
                                        </span>
                                        <span class="text-xs font-mono font-bold text-slate-600">{{ $ticketNumber }}</span>
                                    </div>
                                    <p class="text-xs text-slate-700 leading-relaxed">
                                        {{ $msg }}
                                    </p>
                                    <div class="pt-1 flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ $readUrl }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200 transition">
                                            <span>Lihat Detail</span>
                                        </a>
                                        @if($ticketId)
                                            <form method="POST" action="{{ route('tickets.confirm-close', $ticketId) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi bahwa pekerjaan pada tiket ini telah selesai?')">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition">
                                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-white'])
                                                    <span>Konfirmasi Selesai</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                            @elseif($type === 'ticket_auto_closed')
                                {{-- Card Auto Closed --}}
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-200 text-slate-700 border border-slate-300">
                                            @include('partials.icon', ['name' => 'archive', 'class' => 'w-3.5 h-3.5 text-slate-600'])
                                            Ditutup Otomatis
                                        </span>
                                        <span class="text-xs font-mono font-bold text-slate-600">{{ $ticketNumber }}</span>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        {{ $msg }}
                                    </p>
                                    <div class="pt-1 flex justify-end">
                                        <a href="{{ $readUrl }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200 transition">
                                            <span>Lihat Tiket</span>
                                            @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1.5">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-[#114E84]'])
                            Tandai Semua Sudah Dibaca
                        </button>
                    </form>
                    <button type="button" id="dismissImportantModalBtn" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <script>
        (function () {
            const modal = document.getElementById('importantNotificationModal');
            const closeBtn = document.getElementById('closeImportantModalBtn');
            const dismissBtn = document.getElementById('dismissImportantModalBtn');

            function closeModal() {
                if (!modal) return;
                modal.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => modal.remove(), 250);
            }

            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (dismissBtn) dismissBtn.addEventListener('click', closeModal);

            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) closeModal();
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeModal();
            });
        })();
        </script>
    @endif
@endsection
