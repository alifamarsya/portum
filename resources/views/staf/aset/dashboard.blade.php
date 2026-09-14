@extends('layouts.app')
@section('title', 'Dashboard Staf Aset/Inventaris & Logistik')

@section('content')
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0C3860] via-[#114E84] to-[#1A62A2] p-6 sm:p-8 text-white shadow-lg mb-6">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur-md border border-white/20 text-blue-200 mb-3">
                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-3.5 h-3.5 text-blue-200'])
                    <span>Staf Bagian Aset/Inventaris &amp; Logistik &bull; Kantor Pusat</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Selamat Datang, {{ $user->nama_lengkap }}
                </h1>
                <p class="text-sm text-blue-100/90 mt-1 max-w-2xl leading-relaxed">
                    Pusat penanganan tugas tiket layanan aset perorangan serta pemantauan inventarisasi barang, PKS jatuh tempo, dan permohonan sewa cabang.
                </p>
            </div>
        </div>
    </div>

    {{-- ROW 1: STATS TIKET TUGAS STAF INI --}}
    <div class="mb-2">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-[#114E84]'])
            <span>Tugas Tiket Layanan Saya</span>
            <span class="text-[11px] font-normal lowercase text-slate-400">(khusus penugasan kepada Anda)</span>
        </h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Card 1: Tugas Aktif --}}
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50/40 rounded-2xl p-5 border border-blue-200/80 shadow-2xs group hover:border-[#114E84] transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-900">Tugas Aktif Saya</span>
                    <span class="w-9 h-9 rounded-xl bg-blue-500/15 text-[#114E84] flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-[#114E84] tracking-tight">{{ $ticketStats['tugas_aktif'] }}</p>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] font-semibold text-blue-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-ping"></span>
                    <span>Menunggu &amp; sedang Anda kerjakan</span>
                </div>
            </div>

            {{-- Card 2: Sedang Dikerjakan --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-violet-300 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sedang Diproses</span>
                    <span class="w-9 h-9 rounded-xl bg-violet-50 text-violet-700 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'sliders', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-violet-900 tracking-tight">{{ $ticketStats['sedang_dikerjakan'] }}</p>
                <p class="text-[11px] text-slate-400 mt-2 font-medium">Status tahap: Dalam Proses</p>
            </div>

            {{-- Card 3: Tiket Selesai --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-emerald-300 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tugas Diselesaikan</span>
                    <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-emerald-700 tracking-tight">{{ $ticketStats['selesai'] }}</p>
                <p class="text-[11px] text-slate-400 mt-2 font-medium">Telah diselesaikan oleh Anda</p>
            </div>

            {{-- Card 4: Total Tugas Masuk --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-slate-300 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Ditugaskan</span>
                    <span class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-ink tracking-tight">{{ $ticketStats['total_tugas'] }}</p>
                <p class="text-[11px] text-slate-400 mt-2 font-medium">Keseluruhan beban kerja Anda</p>
            </div>
        </div>
    </div>

    {{-- ROW 2: STATS MODUL OPERASIONAL ASET & LOGISTIK --}}
    <div class="mb-2">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
            @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-emerald-600'])
            <span>Operasional Bagian Aset/Inventaris &amp; Logistik</span>
        </h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Card 1: Nilai Aset Terdata --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-blue-300 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Nilai Aset Terdata</span>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#114E84] flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-ink tracking-tight">
                    Rp {{ number_format($totalNilaiPerolehan, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1 font-medium">{{ $totalAset }} unit barang terdaftar</p>
            </div>

            {{-- Card 2: PKS Jatuh Tempo --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-amber-300 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">PKS Jatuh Tempo</span>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-amber-800 tracking-tight">
                    {{ $pksJatuhTempo }} <span class="text-xs font-normal text-slate-500">PKS (&le; 90 Hari)</span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1 font-medium">Dari total {{ $pksAktif }} PKS aktif</p>
            </div>

            {{-- Card 3: Aset Perlu Perbaikan --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-rose-300 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Perlu Perbaikan</span>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'alert-circle', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-rose-700 tracking-tight">
                    {{ $asetRusakRingan + $asetRusakBerat }} <span class="text-xs font-normal text-slate-500">Unit</span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1 font-medium">{{ $asetRusakBerat }} rusak berat &bull; {{ $asetRusakRingan }} rusak ringan</p>
            </div>

            {{-- Card 4: Permohonan Sewa Cabang --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs group hover:border-purple-300 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Memo Sewa Cabang</span>
                    <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                        @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4'])
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-purple-900 tracking-tight">
                    {{ $memoSewaPending }} <span class="text-xs font-normal text-slate-500">Pending</span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1 font-medium">Pengajuan sewa kantor / ATM</p>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT: 2 COLS LEFT, 1 COL RIGHT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- LEFT COLUMN (2 COLS) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- 1. DAFTAR TIKET TUGAS SAYA --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center">
                            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4'])
                        </div>
                        <div>
                            <h2 class="font-bold text-ink text-base">Antrean Tiket Tugas Saya</h2>
                            <p class="text-xs text-slate-500">Tiket yang didisposisikan oleh Kepala Bagian Aset khusus kepada Anda</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#114E84] border border-blue-100">
                        {{ $antreanTiket->count() }} Tiket Aktif
                    </span>
                </div>

                @if ($antreanTiket->isEmpty())
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6 text-emerald-500'])
                        </div>
                        <h3 class="text-sm font-bold text-slate-700">Tidak Ada Tugas Tiket Tertunda</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                            Saat ini seluruh tiket yang ditugaskan kepada Anda telah terselesaikan. Tiket baru akan muncul saat Kepala Bagian melakukan disposisi kerja.
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($antreanTiket as $t)
                            <div class="p-5 hover:bg-slate-50/60 transition">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <span class="font-mono text-xs font-bold text-[#114E84]">{{ $t->ticket_number }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $t->priority_badge }}">
                                                {{ $t->priority }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $t->status_badge }}">
                                                {{ $t->status }}
                                            </span>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Kategori: {{ $t->category?->name ?? 'Aset & Logistik' }}
                                        </h3>
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-mono whitespace-nowrap">
                                        {{ $t->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>

                                {{-- Pemohon --}}
                                <p class="text-xs text-slate-600 mb-2.5">
                                    Pemohon: <strong class="text-slate-800 font-semibold">{{ $t->user?->nama_lengkap ?? $t->user?->username }}</strong>
                                    <span class="text-slate-400">({{ $t->user?->bagian ?? 'Unit Kerja Pemohon' }})</span>
                                </p>

                                {{-- Deskripsi Singkat Masalah --}}
                                <div class="text-xs text-slate-700 bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-3 leading-relaxed">
                                    <span class="font-bold text-slate-800">Uraian Masalah:</span> {{ Str::limit($t->description, 140) }}
                                </div>

                                {{-- Catatan Disposisi RBB dari Kabag Aset --}}
                                @if ($t->disposition_notes)
                                    <div class="p-3 rounded-xl bg-blue-50/80 border border-blue-200/80 text-xs text-[#114E84] mb-3 leading-relaxed flex items-start gap-2">
                                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4 text-[#114E84] flex-shrink-0 mt-0.5'])
                                        <div>
                                            <span class="font-bold">Instruksi &amp; Catatan RBB Kabag Aset:</span>
                                            <p class="text-slate-700 mt-0.5">{{ $t->disposition_notes }}</p>
                                        </div>
                                    </div>
                                @endif

                                {{-- Aksi Cepat Tiket --}}
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-3">
                                    <div class="text-[11px] text-slate-400">
                                        Didisposisikan oleh: <strong class="text-slate-600">{{ $t->disposedBy?->nama_lengkap ?? 'Kepala Bagian Aset' }}</strong>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if ($t->status === 'Didistribusikan')
                                            <form method="POST" action="{{ route('tickets.update', $t) }}" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="Dalam Proses">
                                                <input type="hidden" name="notes" value="Staf aset mulai menindaklanjuti permohonan tiket ini.">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold shadow-xs transition">
                                                    @include('partials.icon', ['name' => 'sliders', 'class' => 'w-3.5 h-3.5'])
                                                    <span>Mulai Kerjakan</span>
                                                </button>
                                            </form>
                                        @elseif ($t->status === 'Dalam Proses')
                                            <form method="POST" action="{{ route('tickets.update', $t) }}" class="inline"
                                                  onsubmit="return confirm('Tandai tiket ini telah selesai dikerjakan?')">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="Selesai">
                                                <input type="hidden" name="notes" value="Tugas layanan aset telah selesai dikerjakan oleh staf dan menunggu konfirmasi pemohon.">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5'])
                                                    <span>Tandai Selesai</span>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('tickets.show', $t) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                            <span>Detail Tiket</span>
                                            @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3.5 h-3.5'])
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 2. TABEL PKS MENDEKATI JATUH TEMPO --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                        </div>
                        <div>
                            <h2 class="font-bold text-ink text-base">PKS Mendekati Jatuh Tempo (&le; 90 Hari)</h2>
                            <p class="text-xs text-slate-500">Perjanjian kerjasama yang memerlukan koordinasi perpanjangan atau evaluasi</p>
                        </div>
                    </div>
                    <a href="{{ route('modul.index', 'pks') }}"
                       class="text-xs font-bold text-[#114E84] hover:underline flex items-center gap-1">
                        <span>Lihat Semua PKS</span>
                        @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3">No. PKS &bull; Judul</th>
                                <th class="px-5 py-3">Vendor / Pihak</th>
                                <th class="px-5 py-3">Divisi Owner</th>
                                <th class="px-5 py-3">Jatuh Tempo</th>
                                <th class="px-5 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($pksNearDue as $p)
                                @php
                                    $sisaHari = $p->jatuh_tempo ? (int) now()->diffInDays($p->jatuh_tempo, false) : null;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-800">{{ $p->judul }}</div>
                                        <div class="text-[11px] font-mono text-slate-400">{{ $p->no_pks ?? 'Tanpa Nomor' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-700 font-medium">
                                        {{ $p->vendor ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600">
                                        {{ $p->div_owner ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 font-mono">
                                        <div class="font-semibold {{ $sisaHari !== null && $sisaHari <= 30 ? 'text-rose-600' : 'text-amber-700' }}">
                                            {{ $p->jatuh_tempo ? $p->jatuh_tempo->format('d M Y') : '-' }}
                                        </div>
                                        @if ($sisaHari !== null)
                                            <div class="text-[10px] {{ $sisaHari <= 30 ? 'text-rose-500 font-bold' : 'text-slate-400' }}">
                                                {{ $sisaHari >= 0 ? "($sisaHari hari lagi)" : '(Lewat jatuh tempo)' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold
                                            {{ $p->status === 'Akan Jatuh Tempo' ? 'bg-amber-50 text-amber-700 border border-amber-200' : ($p->status === 'Aktif' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-600') }}">
                                            {{ $p->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-slate-400">
                                        Tidak ada PKS yang akan jatuh tempo dalam waktu dekat (&le; 90 hari).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (1 COL) --}}
        <div class="space-y-6">
            {{-- 1. DISTRIBUSI KONDISI ASET --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card p-5">
                <h2 class="font-bold text-ink text-sm mb-1 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'sliders', 'class' => 'w-4 h-4 text-[#114E84]'])
                    <span>Kondisi Fisik Inventaris Aset</span>
                </h2>
                <p class="text-xs text-slate-400 mb-4">Monitoring kelayakan operasional barang</p>

                <div class="space-y-3">
                    @php
                        $sumAset = $totalAset > 0 ? $totalAset : 1;
                    @endphp

                    {{-- Baik --}}
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Kondisi Baik
                            </span>
                            <span class="font-mono">{{ $asetBaik }} unit ({{ round(($asetBaik / $sumAset) * 100) }}%)</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min(100, round(($asetBaik / $sumAset) * 100)) }}%"></div>
                        </div>
                    </div>

                    {{-- Rusak Ringan --}}
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Rusak Ringan (Bisa Diperbaiki)
                            </span>
                            <span class="font-mono">{{ $asetRusakRingan }} unit ({{ round(($asetRusakRingan / $sumAset) * 100) }}%)</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-500 rounded-full" style="width: {{ min(100, round(($asetRusakRingan / $sumAset) * 100)) }}%"></div>
                        </div>
                    </div>

                    {{-- Rusak Berat --}}
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                Rusak Berat (Usul Hapus)
                            </span>
                            <span class="font-mono">{{ $asetRusakBerat }} unit ({{ round(($asetRusakBerat / $sumAset) * 100) }}%)</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-rose-500 rounded-full" style="width: {{ min(100, round(($asetRusakBerat / $sumAset) * 100)) }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-700">Total Terinventarisasi:</span>
                    <span class="font-mono font-black text-[#114E84] text-sm">
                        {{ $totalAset }} Unit
                    </span>
                </div>
            </div>

            {{-- 2. ASET TERBARU --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card p-5">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="font-bold text-ink text-sm">Aset Terdaftar Terbaru</h2>
                        <p class="text-xs text-slate-400">Inventarisasi barang terkini</p>
                    </div>
                    <a href="{{ route('modul.index', 'aset') }}" class="text-xs font-bold text-[#114E84] hover:underline">
                        Buku Aset
                    </a>
                </div>

                <div class="space-y-2.5">
                    @forelse ($asetTerbaru as $a)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                            <div class="min-w-0 pr-2">
                                <p class="font-bold text-slate-800 truncate">{{ $a->nama_aset }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $a->kode_aset ?? 'Tanpa Kode' }} &bull; {{ $a->lokasi ?? 'Kantor Pusat' }}</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-semibold flex-shrink-0
                                {{ $a->kondisi === 'Baik' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($a->kondisi === 'Rusak Ringan' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                {{ $a->kondisi }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-3">Belum ada aset terdaftar.</p>
                    @endforelse
                </div>
            </div>

            {{-- 3. MEMO SEWA CABANG TERBARU --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card p-5">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="font-bold text-ink text-sm">Memo Sewa Cabang</h2>
                        <p class="text-xs text-slate-400">Pengajuan sewa kantor / galeri ATM</p>
                    </div>
                    <a href="{{ route('modul.index', 'memo_sewa_cabang') }}" class="text-xs font-bold text-[#114E84] hover:underline">
                        Lihat
                    </a>
                </div>

                <div class="space-y-2.5">
                    @forelse ($memoSewaTerbaru as $m)
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-slate-800 truncate">{{ $m->cabang }}</span>
                                <span class="text-[10px] font-mono text-slate-400">
                                    {{ $m->tanggal ? $m->tanggal->format('d/m/Y') : '' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600 truncate">{{ $m->jenis ?? 'Sewa Gedung / ATM' }}</p>
                            <div class="flex items-center justify-between mt-1.5">
                                <span class="font-mono font-semibold text-slate-700">Rp {{ number_format((float)$m->nilai, 0, ',', '.') }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold
                                    {{ $m->status_persetujuan === 'Disetujui' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $m->status_persetujuan ?? 'Diajukan' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-3">Belum ada pengajuan sewa cabang.</p>
                    @endforelse
                </div>
            </div>

            {{-- 4. PINTASAN AKSES MODUL CEPAT --}}
            <div class="bg-gradient-to-br from-[#114E84]/5 to-indigo-50/60 rounded-2xl border border-blue-200/60 p-5">
                <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-3">Pintasan Cepat Staf Aset</h3>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('modul.create', 'aset') }}"
                       class="flex items-center gap-2 p-2.5 rounded-xl bg-white hover:bg-blue-50 border border-slate-200/80 text-slate-700 hover:text-[#114E84] text-xs font-semibold shadow-2xs transition">
                        @include('partials.icon', ['name' => 'plus', 'class' => 'w-3.5 h-3.5 text-amber-600'])
                        <span>+ Input Aset</span>
                    </a>
                    <a href="{{ route('modul.index', 'aset') }}"
                       class="flex items-center gap-2 p-2.5 rounded-xl bg-white hover:bg-blue-50 border border-slate-200/80 text-slate-700 hover:text-[#114E84] text-xs font-semibold shadow-2xs transition">
                        @include('partials.icon', ['name' => 'archive', 'class' => 'w-3.5 h-3.5 text-blue-600'])
                        <span>Buku Aset</span>
                    </a>
                    <a href="{{ route('modul.index', 'pks') }}"
                       class="flex items-center gap-2 p-2.5 rounded-xl bg-white hover:bg-blue-50 border border-slate-200/80 text-slate-700 hover:text-[#114E84] text-xs font-semibold shadow-2xs transition">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                        <span>PKS &amp; Sewa</span>
                    </a>
                    <a href="{{ route('modul.index', 'memo_sewa_cabang') }}"
                       class="flex items-center gap-2 p-2.5 rounded-xl bg-white hover:bg-blue-50 border border-slate-200/80 text-slate-700 hover:text-[#114E84] text-xs font-semibold shadow-2xs transition">
                        @include('partials.icon', ['name' => 'layers', 'class' => 'w-3.5 h-3.5 text-purple-600'])
                        <span>Memo Sewa</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
