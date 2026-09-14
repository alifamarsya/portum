@extends('layouts.app')
@section('title', 'Pelacakan Tiket ' . $ticket->ticket_number)

@section('content')
    <div class="mb-6">
        <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-brand transition mb-2">
            &larr; Kembali ke Daftar Tiket
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-0.5">Pelacakan &amp; Detail Tiket</p>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold font-mono text-[#114E84]">{{ $ticket->ticket_number }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $ticket->status_badge }}">
                        {{-- Tampilkan label ramah untuk pemohon, istilah internal untuk operator/bagian --}}
                        @if (auth()->user()->isUser())
                            {{ $ticket->pemohon_status_label }}
                        @else
                            {{ $ticket->status }}
                        @endif
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold {{ $ticket->priority_badge }}">
                        Prioritas: {{ $ticket->priority }}
                    </span>
                </div>
            </div>

            @if (auth()->user()->isOperator() && $ticket->status === 'Menunggu Verifikasi')
                <div class="flex items-center gap-2">
                    <a href="{{ route('tickets.edit', $ticket) }}"
                       class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-md transition">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-white'])
                        Verifikasi &amp; Alokasikan Tiket &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        {{-- Left Column: Informasi Utama Permintaan --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- WIDGET SLA RESPONSE TIME (Maksimal 2 Jam Kerja Operator) --}}
            @php $sla = $ticket->sla_response; @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6 overflow-hidden relative">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $sla['is_overdue'] ? 'bg-rose-100 text-rose-700' : ($sla['is_warning'] ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-[#114E84]') }} flex items-center justify-center flex-shrink-0">
                            @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-ink">SLA Response Time (Verifikasi Operator)</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold border {{ $sla['badge_class'] }}">
                                    {{ $sla['status'] }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Target respon verifikasi: <strong>maksimal 2 jam kerja</strong> (08:00 - 17:00 WITA, Senin - Jumat)
                            </p>
                        </div>
                    </div>

                    @if (auth()->user()->isOperator() && $ticket->status === 'Menunggu Verifikasi')
                        <a href="{{ route('tickets.edit', $ticket) }}"
                           class="inline-flex items-center gap-1.5 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow-xs transition flex-shrink-0">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-white'])
                            Verifikasi Sekarang &rarr;
                        </a>
                    @endif
                </div>

                {{-- Progress Bar --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-slate-500 font-medium">Penggunaan Kuota Waktu SLA</span>
                        <span class="font-bold {{ $sla['is_overdue'] ? 'text-rose-600' : ($sla['is_warning'] ? 'text-amber-600' : 'text-slate-700') }}">
                            {{ $sla['elapsed_formatted'] }} / 2 jam ({{ $sla['percentage_used'] }}%)
                        </span>
                    </div>
                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $sla['is_overdue'] ? 'bg-rose-500' : ($sla['is_warning'] ? 'bg-amber-500' : 'bg-[#114E84]') }}"
                             style="width: {{ $sla['percentage_used'] }}%"></div>
                    </div>
                </div>

                {{-- Key SLA Metrics Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-slate-400 block">SLA Mulai Dihitung</span>
                        <span class="font-semibold text-slate-700 font-mono">{{ $sla['start_at']->format('d M, H:i') }} WITA</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Batas Waktu (Due)</span>
                        <span class="font-semibold {{ $sla['is_overdue'] ? 'text-rose-700' : 'text-slate-700' }} font-mono">{{ $sla['due_at']->format('d M, H:i') }} WITA</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Waktu Terpakai</span>
                        <span class="font-bold text-slate-800">{{ $sla['elapsed_formatted'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">{{ $sla['is_verified'] ? 'Hasil Evaluasi' : 'Sisa Waktu' }}</span>
                        <span class="font-bold {{ $sla['is_overdue'] ? 'text-rose-600' : ($sla['is_warning'] ? 'text-amber-600' : 'text-emerald-700') }}">
                            {{ $sla['remaining_formatted'] }}
                        </span>
                    </div>
                </div>

                @if ($sla['is_verified'] && $ticket->verified_at)
                    <div class="mt-3 text-[11px] text-slate-500 flex items-center gap-1.5">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                        <span>Diverifikasi oleh <strong>{{ $ticket->verifiedBy?->nama_lengkap ?? 'Operator Helpdesk' }}</strong> pada {{ $ticket->verified_at->format('d M Y, H:i') }} WITA.</span>
                    </div>
                @endif
            </div>

            {{-- WIDGET SLA RESOLUTION TIME (Waktu Penyelesaian Penanganan Tiket) --}}
            @php $slaRes = $ticket->sla_resolution; @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6 overflow-hidden relative">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $slaRes['is_overdue'] ? 'bg-rose-100 text-rose-700' : ($slaRes['is_warning'] ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }} flex items-center justify-center flex-shrink-0">
                            @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-ink">SLA Resolution Time (Penyelesaian Tiket)</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold border {{ $slaRes['badge_class'] }}">
                                    {{ $slaRes['status'] }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Target Resolusi: <strong>{{ $slaRes['target_hours'] }} Jam Kerja</strong> &bull; Prioritas: <strong>{{ $ticket->priority ?? 'Sedang' }}</strong> (Kritis 4j, Tinggi 12j, Sedang 48j, Rendah 72j)
                            </p>
                        </div>
                    </div>

                    @if (!$slaRes['is_started'])
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Menunggu Persetujuan Kabag
                        </span>
                    @elseif ($slaRes['is_resolved'])
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                            Telah Selesai
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                            Timer Sedang Berjalan
                        </span>
                    @endif
                </div>

                {{-- Progress Bar --}}
                @if ($slaRes['is_started'])
                    <div class="mb-4">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="text-slate-500 font-medium">Penggunaan Kuota Waktu SLA Resolusi</span>
                            <span class="font-bold {{ $slaRes['is_overdue'] ? 'text-rose-600' : ($slaRes['is_warning'] ? 'text-amber-600' : 'text-slate-700') }}">
                                {{ $slaRes['elapsed_formatted'] }} / {{ $slaRes['target_hours'] }} jam ({{ $slaRes['percentage_used'] }}%)
                            </span>
                        </div>
                        <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $slaRes['is_overdue'] ? 'bg-rose-500' : ($slaRes['is_warning'] ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                 style="width: {{ $slaRes['percentage_used'] }}%"></div>
                        </div>
                    </div>

                    {{-- Key SLA Metrics Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
                        <div>
                            <span class="text-slate-400 block">Mulai (Disetujui Kabag)</span>
                            <span class="font-semibold text-slate-700 font-mono">{{ $slaRes['start_at'] ? $slaRes['start_at']->format('d M, H:i') . ' WITA' : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Batas Waktu (Due)</span>
                            <span class="font-semibold {{ $slaRes['is_overdue'] ? 'text-rose-700' : 'text-slate-700' }} font-mono">{{ $slaRes['due_at'] ? $slaRes['due_at']->format('d M, H:i') . ' WITA' : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Waktu Terpakai</span>
                            <span class="font-bold text-slate-800">{{ $slaRes['elapsed_formatted'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">{{ $slaRes['is_resolved'] ? 'Hasil Evaluasi' : 'Sisa Waktu' }}</span>
                            <span class="font-bold {{ $slaRes['is_overdue'] ? 'text-rose-600' : ($slaRes['is_warning'] ? 'text-amber-600' : 'text-emerald-700') }}">
                                {{ $slaRes['remaining_formatted'] }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="p-3.5 bg-amber-50/60 rounded-xl border border-amber-200/80 text-xs text-amber-900 leading-relaxed">
                        <div class="flex items-center gap-2 font-bold mb-1">
                            @include('partials.icon', ['name' => 'info', 'class' => 'w-4 h-4 text-amber-600'])
                            <span>Timer SLA Resolution belum dimulai</span>
                        </div>
                        <p class="text-amber-700">
                            Timer akan mulai berjalan otomatis secara resmi saat Kepala Bagian <strong>{{ $ticket->department?->name ?? 'Terkait' }}</strong> menyetujui dan mendisposisikan tiket kepada staf pelaksana sesuai tingkat prioritas <strong>{{ $ticket->priority ?? 'Sedang' }}</strong> ({{ $slaRes['target_hours'] }} Jam Kerja).
                        </p>
                    </div>
                @endif

                {{-- Faktor Penyesuaian Durasi yang Ditetapkan Kabag --}}
                @if ($ticket->kategori_pekerjaan || $ticket->skala_eselonisasi || $ticket->estimasi_biaya)
                    <div class="mt-3.5 pt-3 border-t border-slate-100 flex flex-wrap gap-2 text-[11px] items-center">
                        <span class="text-slate-400 font-semibold uppercase text-[10px]">Parameter Durasi:</span>
                        @if ($ticket->kategori_pekerjaan)
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 font-medium">
                                Kategori: {{ $ticket->kategori_pekerjaan }}
                            </span>
                        @endif
                        @if ($ticket->skala_eselonisasi)
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 font-medium">
                                Skala: {{ $ticket->skala_eselonisasi }}
                            </span>
                        @endif
                        @if ($ticket->estimasi_biaya)
                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">
                                Biaya: Rp {{ number_format($ticket->estimasi_biaya, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
                <h2 class="text-base font-bold text-ink mb-4 pb-2.5 border-b border-slate-100 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'file-text', 'class' => 'w-4 h-4 text-brand'])
                    Informasi Utama Permintaan
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Nomor Tiket</span>
                        <span class="font-bold font-mono text-[#114E84] text-base">{{ $ticket->ticket_number }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Tanggal Pengajuan</span>
                        <span class="font-semibold text-slate-700">{{ $ticket->created_at->format('d F Y, H:i') }} WITA</span>
                        <span class="text-[11px] text-slate-400 block">({{ $ticket->created_at->diffForHumans() }})</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Nama Pemohon</span>
                        <span class="font-bold text-ink">{{ $ticket->user?->nama_lengkap ?? '-' }}</span>
                        <span class="text-xs text-slate-500 block">{{ $ticket->user?->bagian ?? '-' }} ({{ $ticket->user?->jabatan ?? '-' }})</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Jenis Pengajuan</span>
                        <span class="inline-flex items-center gap-1.5 font-bold text-xs px-2.5 py-1 rounded-full border {{ $ticket->jenis_badge }}">
                            {{ $ticket->jenis_pengajuan ?? 'Permintaan' }}
                        </span>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="text-xs text-slate-400 block font-medium">Bagian Penanganan / Pelaksana</span>
                        @if ($ticket->department)
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="inline-flex items-center gap-2 font-bold text-[#114E84] bg-blue-50/70 border border-blue-200/60 px-3 py-1.5 rounded-lg text-xs">
                                    <span class="w-2 h-2 rounded-full bg-[#114E84]"></span>
                                    {{ $ticket->department->name }}
                                </span>

                                @if ($ticket->assignedStaff)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-lg">
                                        @include('partials.icon', ['name' => 'users', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                                        Staf Pelaksana: {{ $ticket->assignedStaff->nama_lengkap }} ({{ $ticket->assignedStaff->username }})
                                    </span>
                                @elseif ($ticket->isAwaitingKabagDisposition())
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 px-3 py-1.5 rounded-lg">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu Review RBB &amp; Disposisi Kabag
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 px-3 py-1.5 rounded-lg mt-1 font-medium border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Sedang menunggu verifikasi &amp; alokasi oleh Operator Helpdesk
                            </span>
                        @endif
                    </div>

                    {{-- Unit Kerja Tujuan (jika ada) --}}
                    @if ($ticket->unitKerja)
                        <div class="sm:col-span-2 flex flex-wrap gap-2 items-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 border border-violet-200">
                                @include('partials.icon', ['name' => 'briefcase', 'class' => 'w-3 h-3'])
                                Unit Kerja: {{ $ticket->unitKerja->nama }}
                            </span>
                        </div>
                    @endif

                    {{-- Informasi Disposisi Kabag jika sudah didisposisi --}}
                    @if ($ticket->disposed_at)
                        <div class="sm:col-span-2 p-4 rounded-xl bg-gradient-to-r from-blue-50/80 to-indigo-50/50 border border-blue-200/80">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-bold text-[#114E84] flex items-center gap-1.5">
                                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5 text-[#114E84]'])
                                    Verifikasi &amp; Disposisi Kepala Bagian
                                </span>
                                <span class="text-[11px] text-slate-500 font-mono">{{ \Carbon\Carbon::parse($ticket->disposed_at)->format('d M Y, H:i') }} WIB</span>
                            </div>
                            <p class="text-xs text-slate-700 leading-relaxed">
                                <span class="font-bold text-slate-800">Catatan RBB &amp; Pagu Anggaran:</span> {{ $ticket->disposition_notes }}
                            </p>
                            <div class="flex items-center gap-2 mt-2 text-[11px] text-slate-500">
                                <span>Didisposisikan oleh: <strong class="text-slate-700">{{ $ticket->disposedBy?->nama_lengkap ?? 'Kepala Bagian' }}</strong></span>
                                <span>&bull;</span>
                                <span>Dialokasikan ke staf: <strong class="text-[#114E84]">{{ $ticket->assignedStaff?->nama_lengkap ?? '-' }}</strong></span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Deskripsi Masalah --}}
                <div class="mb-6">
                    <span class="text-xs text-slate-400 block font-medium mb-1.5">Deskripsi Lengkap / Uraian Masalah:</span>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm whitespace-pre-line leading-relaxed">
                        {{ $ticket->description }}
                    </div>
                </div>

                {{-- Lampiran --}}
                @if ($ticket->attachment_path)
                    @php
                        $ext = strtolower(pathinfo($ticket->attachment_path, PATHINFO_EXTENSION));
                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                        $fileName = basename($ticket->attachment_path);
                    @endphp
                    <div>
                        <span class="text-xs text-slate-400 block font-medium mb-2">Berkas / Dokumen Lampiran:</span>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-[#114E84] flex-shrink-0">
                                    @include('partials.icon', ['name' => 'file-text', 'class' => 'w-5 h-5'])
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-slate-800 truncate" title="{{ $fileName }}">
                                        {{ $fileName }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 uppercase font-mono">{{ $ext ?: 'File' }} &bull; Lampiran Tiket</p>
                                </div>
                            </div>

                            @if ($isImg)
                                <div class="mb-3 rounded-lg overflow-hidden border border-slate-200 bg-white max-h-60 flex items-center justify-center">
                                    <img src="{{ route('tickets.attachment', $ticket) }}" alt="Pratinjau Lampiran" class="w-full h-auto object-contain max-h-60">
                                </div>
                            @endif

                            <div class="flex flex-wrap gap-2.5">
                                <a href="{{ route('tickets.attachment', $ticket) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white hover:bg-blue-50 text-[#114E84] text-xs font-semibold transition border border-blue-200 shadow-2xs">
                                    @include('partials.icon', ['name' => 'eye', 'class' => 'w-4 h-4'])
                                    Buka Berkas
                                </a>
                                <a href="{{ route('tickets.attachment.download', $ticket) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold transition shadow-2xs">
                                    @include('partials.icon', ['name' => 'download', 'class' => 'w-4 h-4 text-white'])
                                    Unduh Berkas
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- PANEL DISPOSISI KEPALA BAGIAN (Hanya tampil bagi Kabag terkait / Superadmin saat tiket butuh disposisi) --}}
            @php
                $isAuthorizedKabag = (auth()->user()->isKabag() && auth()->user()->effectiveDepartmentId() == $ticket->department_id) || auth()->user()->isSuperAdmin();
            @endphp
            @if ($isAuthorizedKabag && $ticket->isAwaitingKabagDisposition())
                <div class="bg-gradient-to-br from-amber-50/90 via-white to-orange-50/50 rounded-2xl border-2 border-amber-300/80 shadow-lg p-6 relative overflow-hidden">
                    <div class="flex items-start justify-between gap-4 pb-4 mb-5 border-b border-amber-200/80">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-xs">
                                @include('partials.icon', ['name' => 'shield', 'class' => 'w-5 h-5 text-white'])
                            </div>
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-wider">
                                    Wewenang Kepala Bagian
                                </span>
                                <h3 class="text-base font-bold text-ink mt-0.5">Verifikasi RBB, Pagu Anggaran &amp; Disposisi Staf</h3>
                                <p class="text-xs text-slate-500">Tiket dari Operator tidak langsung ke meja staf. Pastikan permintaan sesuai RBB sebelum didelegasikan.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Form Disposisi ke Staf & Penetapan SLA Resolusi --}}
                    <form method="POST" action="{{ route('tickets.dispose', $ticket) }}" class="space-y-4" id="form-disposition">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Pilih Staf Pelaksana yang Ditugaskan <span class="text-rose-500">*</span>
                            </label>
                            <select name="assigned_to" required
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-ink bg-white focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                                <option value="">-- Pilih Staf dari Tim {{ $ticket->department?->name }} --</option>
                                @foreach ($departmentStaff as $staf)
                                    <option value="{{ $staf->id }}" {{ old('assigned_to') == $staf->id ? 'selected' : '' }}>
                                        {{ $staf->nama_lengkap }} ({{ $staf->username }}) — {{ $staf->jabatan ?? 'Staf' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Staf di atas terdaftar resmi di sistem Manajemen Pengguna untuk bagian Anda.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Catatan Pengecekan RBB &amp; Ketersediaan Pagu Anggaran <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="disposition_notes" rows="3" required
                                      placeholder="Contoh: Kebutuhan telah dicek dan sesuai dengan RBB 2026. Pagu anggaran tersedia pada pos beban operasional. Disposisikan ke staf untuk segera ditindaklanjuti."
                                      class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition leading-relaxed">{{ old('disposition_notes') }}</textarea>
                        </div>

                        <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow transition">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-emerald-200'])
                                <span>Setujui Sesuai RBB &amp; Disposisikan ke Staf</span>
                            </button>
                        </div>
                    </form>

                    {{-- Form Penolakan oleh Kabag jika Tidak Sesuai RBB --}}
                    <div class="mt-4 pt-4 border-t border-amber-200/80">
                        <details class="group">
                            <summary class="cursor-pointer text-xs font-bold text-rose-600 hover:text-rose-700 select-none flex items-center gap-1.5">
                                <span>Tolak Permintaan Ini (Tidak Sesuai RBB / Pagu Habis)?</span>
                            </summary>
                            <form method="POST" action="{{ route('tickets.reject', $ticket) }}" class="mt-3 space-y-3 p-3.5 rounded-xl bg-rose-50/60 border border-rose-200">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-rose-900 mb-1">Alasan Penolakan</label>
                                    <textarea name="notes" rows="2" required
                                              placeholder="Jelaskan alasan penolakan, misal: 'Kebutuhan di luar pagu anggaran tahun 2026'..."
                                              class="w-full border border-rose-200 rounded-lg px-3 py-2 text-xs text-ink bg-white focus:ring-1 focus:ring-rose-500"></textarea>
                                </div>
                                <button type="submit"
                                        onclick="return confirm('Yakin ingin menolak tiket ini?')"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition">
                                    @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-3.5 h-3.5'])
                                    <span>Tolak Tiket</span>
                                </button>
                            </form>
                        </details>
                    </div>
                </div>
            @endif

            {{-- Panel Konfirmasi Penutupan untuk Pemohon (muncul saat status = Selesai) --}}
            @if (auth()->user()->isUser() && $ticket->user_id === auth()->id() && $ticket->status === 'Selesai')
                @php $confirmInfo = $ticket->confirmation_info; @endphp
                <div class="bg-gradient-to-br from-emerald-50 via-teal-50/40 to-white border-2 border-emerald-300 rounded-2xl p-6 shadow-card space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-3 border-b border-emerald-200/70">
                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-emerald-950">Pekerjaan Telah Selesai — Menunggu Konfirmasi Anda</h3>
                                <p class="text-xs text-emerald-800 mt-0.5">
                                    Staf pelaksana telah menyelesaikan kendala/permintaan ini pada <strong>{{ $ticket->completed_at ? $ticket->completed_at->format('d M Y, H:i') . ' WITA' : $ticket->updated_at->format('d M Y, H:i') . ' WITA' }}</strong>.
                                </p>
                            </div>
                        </div>

                        {{-- Countdown Badge --}}
                        <div class="flex-shrink-0">
                            @if ($confirmInfo['is_expired'])
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                                    Batas Waktu Berakhir (Auto-Close)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-3.5 h-3.5 text-amber-700'])
                                    <span>Sisa Waktu: <strong>{{ $confirmInfo['remaining_formatted'] }}</strong></span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Info Box Batas Waktu 2 x 24 Jam Kerja & Auto-Close --}}
                    <div class="p-3.5 rounded-xl bg-white/80 border border-emerald-200/90 text-xs text-slate-700 space-y-1.5">
                        <div class="flex items-center gap-2 font-semibold text-emerald-900">
                            @include('partials.icon', ['name' => 'info', 'class' => 'w-4 h-4 text-emerald-600'])
                            <span>Ketentuan Konfirmasi &amp; Penutupan Otomatis (Auto-Close):</span>
                        </div>
                        <ul class="list-disc pl-5 space-y-1 text-slate-600 leading-relaxed text-[12px]">
                            <li>Anda memiliki waktu <strong>2 x 24 jam kerja</strong> (08:00 - 17:00 WITA, Senin - Jumat) untuk mengonfirmasi hasil pekerjaan.</li>
                            <li>Batas akhir konfirmasi: <strong>{{ $confirmInfo['deadline'] ? $confirmInfo['deadline']->translatedFormat('l, d F Y H:i') . ' WITA' : '-' }}</strong>.</li>
                            <li>Jika dalam kurun waktu 2 hari kerja tidak ada respon, sistem akan secara otomatis <strong>menutup tiket (Auto-Close)</strong>.</li>
                        </ul>
                    </div>

                    @error('error')
                        <div class="bg-rose-50 border border-rose-200 rounded-lg px-4 py-2.5">
                            <p class="text-xs text-rose-700 font-medium">{{ $message }}</p>
                        </div>
                    @enderror

                    {{-- Tombol Aksi Konfirmasi Selesai --}}
                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <form method="POST" action="{{ route('tickets.confirm-close', $ticket) }}"
                              onsubmit="return confirm('Konfirmasi bahwa kendala/permintaan Anda sudah benar-benar selesai? Tiket akan ditutup resmi.')"
                              class="inline-block"
                        >
                            @csrf
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-6 py-3 rounded-xl shadow transition">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-emerald-200'])
                                <span>Ya, Masalah Selesai — Tutup Tiket</span>
                            </button>
                        </form>

                        {{-- Tombol Opsi Pekerjaan Belum Selesai (Membuat Tiket Baru) --}}
                        <button type="button" onclick="document.getElementById('report-incomplete-panel').classList.toggle('hidden')"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-xl bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200 shadow-2xs transition">
                            @include('partials.icon', ['name' => 'alert', 'class' => 'w-4 h-4 text-rose-600'])
                            <span>Pekerjaan Belum Selesai? Laporkan &amp; Buat Tiket Baru &rarr;</span>
                        </button>
                    </div>

                    {{-- Dropdown Form Lapor Belum Selesai --}}
                    <div id="report-incomplete-panel" class="hidden mt-3 p-4 rounded-xl bg-rose-50/70 border border-rose-200 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold text-rose-900">
                            @include('partials.icon', ['name' => 'alert', 'class' => 'w-4 h-4 text-rose-600'])
                            <span>Ketentuan Pengajuan Lanjutan</span>
                        </div>
                        <p class="text-xs text-rose-800 leading-relaxed">
                            Sesuai SOP, tiket yang telah diselesaikan oleh staf pelaksana dan/atau ditutup tidak dapat dibuka kembali. Apabila pekerjaan belum tuntas atau kendala masih berulang, uraikan alasannya di bawah ini dan sistem akan mengalihkan Anda ke formulir <strong>Tiket Pengajuan Baru</strong> dengan riwayat tiket ini secara otomatis.
                        </p>
                        <form method="POST" action="{{ route('tickets.report-incomplete', $ticket) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-rose-900 mb-1">
                                    Jelaskan bagian pekerjaan yang belum selesai / kendala yang masih terjadi: <span class="text-rose-600">*</span>
                                </label>
                                <textarea name="reason" rows="3" required
                                          placeholder="Contoh: AC masih belum dingin di area ruang rapat kasir..."
                                          class="w-full border border-rose-300 rounded-lg px-3 py-2 text-xs text-ink bg-white focus:ring-1 focus:ring-rose-500"></textarea>
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition">
                                @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                                <span>Lanjutkan ke Formulir Tiket Baru</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Panel Notifikasi Penutupan Resmi untuk Tiket yang Sudah Ditutup --}}
            @if (in_array($ticket->status, ['Ditutup Pemohon', 'Ditutup Otomatis (Sistem)']))
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 shadow-card flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $ticket->status === 'Ditutup Pemohon' ? 'bg-teal-100 text-teal-700' : 'bg-slate-200 text-slate-700' }} flex items-center justify-center flex-shrink-0">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-ink">
                                {{ $ticket->status === 'Ditutup Pemohon' ? 'Tiket Telah Ditutup &amp; Dikonfirmasi Pemohon' : 'Tiket Ditutup Otomatis oleh Sistem' }}
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Ditutup pada {{ $ticket->closed_at ? $ticket->closed_at->format('d M Y, H:i') . ' WITA' : $ticket->updated_at->format('d M Y, H:i') . ' WITA' }}
                                {{ $ticket->status === 'Ditutup Otomatis (Sistem)' ? '(karena melewati batas waktu konfirmasi 2 hari kerja)' : '' }}.
                            </p>
                        </div>
                    </div>

                    @if (auth()->user()->isUser() && $ticket->user_id === auth()->id())
                        <a href="{{ route('tickets.create', ['description' => '[Tindak Lanjut dari Tiket ' . $ticket->ticket_number . "]\n\nKendala lanjutan:\n\nUraian sebelumnya:\n" . $ticket->description]) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-300 shadow-2xs transition flex-shrink-0">
                            @include('partials.icon', ['name' => 'plus', 'class' => 'w-3.5 h-3.5'])
                            <span>Kendala Belum Selesai? Buat Tiket Baru</span>
                        </a>
                    @endif
                </div>
            @endif
        {{-- Right Column: Timeline / Riwayat Proses (Jejak Langkah Vertikal) --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
                <div class="mb-5 pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-ink flex items-center gap-2">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-brand'])
                        Riwayat &amp; Jejak Langkah
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Pantau alur pengerjaan tiket Anda dari waktu ke waktu</p>
                </div>

                {{-- Vertical Timeline --}}
                <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                    @forelse ($ticket->histories as $index => $h)
                        <div class="relative group">
                            {{-- Step Marker Dot --}}
                            <div class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full border-2 border-white {{ $index === 0 ? 'bg-[#114E84] ring-4 ring-blue-100' : 'bg-slate-300' }} shadow-xs"></div>

                            <div>
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="text-xs font-bold text-ink">{{ $h->new_status }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono whitespace-nowrap">{{ $h->created_at->format('d M, H:i') }}</span>
                                </div>

                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Oleh: <strong class="text-slate-700 font-medium">{{ $h->user?->nama_lengkap ?? 'Sistem' }}</strong>
                                    <span class="text-slate-400">({{ $h->user?->role?->label ?? $h->user?->bagian ?? 'Petugas' }})</span>
                                </div>

                                @if ($h->notes)
                                    <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 mt-2 leading-relaxed">
                                        {{ $h->notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada jejak riwayat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection