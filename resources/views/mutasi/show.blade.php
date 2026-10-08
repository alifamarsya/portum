@extends('layouts.app')
@section('title', 'Detail Mutasi ' . $mutasi->no_mutasi)

@section('content')
@php
    $badge = $mutasi->status_badge;
    $user = auth()->user();

    // Tentukan state stepper (4 tahapan: Pengajuan, Cek Operator, Bagian Aset, Konfirmasi Ditutup)
    $step1 = true; // Selalu selesai saat dibuat
    $step2 = in_array($mutasi->status, ['Diproses', 'Menunggu Approval', 'Disetujui', 'Ditutup']);
    $step3 = in_array($mutasi->status, ['Disetujui', 'Ditutup']) || ($mutasi->status === 'Menunggu Approval' && $mutasi->verified_at);
    $step4 = ($mutasi->status === 'Ditutup' && $mutasi->status_hasil === 'Disetujui');

    $isRejected = ($mutasi->status === 'Ditutup' && in_array($mutasi->status_hasil, ['Ditolak', 'Tidak Valid']));
    $isWaitingConfirm = ($mutasi->status === 'Disetujui');
    $isClosedApproved = ($mutasi->status === 'Ditutup' && $mutasi->status_hasil === 'Disetujui');
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    {{-- Header & Status --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('mutasi-aset.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#114E84] transition mb-1">
                @include('partials.icon', ['name' => 'chevron-down', 'class' => 'w-3.5 h-3.5 rotate-90'])
                Kembali ke Daftar Mutasi
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-ink">{{ $mutasi->no_mutasi }}</h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                    {{ $badge['label'] }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Diajukan pada {{ $mutasi->created_at->translatedFormat('d F Y, H:i') }} WIB oleh <strong class="text-slate-700">{{ $mutasi->nama_pemohon ?: ($mutasi->pengaju?->nama_lengkap ?? '-') }}</strong> ({{ $mutasi->jabatan_pemohon ?: ($mutasi->pengaju?->jabatan ?? '-') }})
            </p>
        </div>

        {{-- Action Buttons Header --}}
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Operator Action Button --}}
            @if (($user->isOperator() || $user->isSuperAdmin()) && $mutasi->status === 'Diajukan')
                <button type="button" onclick="openModal('operatorModal')"
                        class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                    Pengecekan Operator &amp; Teruskan
                </button>
            @endif

            {{-- Bagian Aset Action Button --}}
            @if (($user->isBagianAset() || $user->isSuperAdmin()) && in_array($mutasi->status, ['Diproses', 'Menunggu Approval']))
                <button type="button" onclick="openModal('verifikasiBagianAsetModal')"
                        class="inline-flex items-center gap-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4'])
                    Verifikasi Bagian Aset
                </button>
            @endif

            {{-- Pengaju Action Button: Konfirmasi & Tutup --}}
            @if ($isWaitingConfirm && ($user->id === $mutasi->pengaju_id || $user->isSuperAdmin()))
                <button type="button" onclick="openModal('konfirmasiPengajuModal')"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md transition">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                    Konfirmasi &amp; Tutup Mutasi
                </button>
            @endif
        </div>
    </div>

    {{-- 4-Stage Visual Workflow Stepper --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-5">Alur Tahapan Mutasi Aset (4 Tahap)</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 relative">
            {{-- Step 1: Diajukan --}}
            <div class="relative p-3.5 rounded-xl border {{ $step1 ? 'bg-blue-50/50 border-blue-200' : 'bg-slate-50 border-slate-200' }}">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full bg-[#114E84] text-white flex items-center justify-center text-[10px] font-bold">1</span>
                    <span class="font-bold text-xs text-[#114E84]">Pengajuan</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-700 truncate" title="{{ $mutasi->nama_pemohon ?: ($mutasi->pengaju?->nama_lengkap ?? 'User') }}">{{ $mutasi->nama_pemohon ?: ($mutasi->pengaju?->nama_lengkap ?? 'User') }}</p>
                <p class="text-[10px] text-slate-400">{{ $mutasi->created_at->format('d/m/Y H:i') }}</p>
                <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Diajukan</span>
            </div>

            {{-- Step 2: Pengecekan Operator --}}
            <div class="relative p-3.5 rounded-xl border {{ $step2 ? 'bg-blue-50/50 border-blue-200' : 'bg-slate-50 border-slate-200 opacity-60' }}">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full {{ $step2 ? 'bg-[#114E84] text-white' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center text-[10px] font-bold">2</span>
                    <span class="font-bold text-xs {{ $step2 ? 'text-[#114E84]' : 'text-slate-500' }}">Cek Operator</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-700 truncate">{{ $mutasi->operator?->nama_lengkap ?? 'Operator Helpdesk' }}</p>
                <p class="text-[10px] text-slate-400">{{ $mutasi->operator_checked_at ? $mutasi->operator_checked_at->format('d/m/Y H:i') : 'Menunggu Pengecekan' }}</p>
                @if ($mutasi->operator_checked_at)
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Form Lengkap</span>
                @else
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">Antrian Operator</span>
                @endif
            </div>

            {{-- Step 3: Verifikasi Bagian Aset --}}
            @php
                $step3Completed = $step3 || in_array($mutasi->status_hasil, ['Tidak Valid', 'Ditolak']);
                $step3Active = in_array($mutasi->status, ['Diproses', 'Menunggu Approval']);
            @endphp
            <div class="relative p-3.5 rounded-xl border {{ $step3Completed ? ($mutasi->status_hasil === 'Ditolak' ? 'bg-rose-50/60 border-rose-300' : ($mutasi->status_hasil === 'Tidak Valid' ? 'bg-amber-50/60 border-amber-300' : 'bg-purple-50/60 border-purple-300')) : ($step3Active ? 'bg-purple-50/50 border-purple-300 ring-2 ring-purple-300/60' : 'bg-slate-50 border-slate-200 opacity-60') }}">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full {{ $step3Completed ? ($mutasi->status_hasil === 'Ditolak' ? 'bg-rose-600 text-white' : ($mutasi->status_hasil === 'Tidak Valid' ? 'bg-amber-600 text-white' : 'bg-purple-700 text-white')) : ($step3Active ? 'bg-purple-700 text-white' : 'bg-slate-300 text-slate-600') }} flex items-center justify-center text-[10px] font-bold">3</span>
                    <span class="font-bold text-xs {{ $step3Completed ? ($mutasi->status_hasil === 'Ditolak' ? 'text-rose-800' : ($mutasi->status_hasil === 'Tidak Valid' ? 'text-amber-800' : 'text-purple-900')) : ($step3Active ? 'text-purple-800' : 'text-slate-500') }}">Bagian Aset</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-700 truncate">{{ $mutasi->approver?->nama_lengkap ?? $mutasi->verifikator?->nama_lengkap ?? 'Bagian Aset' }}</p>
                <p class="text-[10px] text-slate-400">{{ ($mutasi->approved_at ?: $mutasi->verified_at) ? ($mutasi->approved_at ?: $mutasi->verified_at)->format('d/m/Y H:i') : ($step3Active ? 'Menunggu Verifikasi' : 'Antrian') }}</p>
                @if ($mutasi->status_hasil === 'Ditolak')
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Ditolak</span>
                @elseif ($mutasi->status_hasil === 'Tidak Valid')
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Tidak Valid</span>
                @elseif ($step3)
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">Disetujui</span>
                @elseif ($step3Active)
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 animate-pulse">Perlu Aksi</span>
                @else
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">Antrian</span>
                @endif
            </div>

            {{-- Step 4: Konfirmasi Penutupan (Pengaju) --}}
            <div class="relative p-3.5 rounded-xl border {{ $step4 ? 'bg-emerald-50/70 border-emerald-300' : ($isWaitingConfirm ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-300/60' : 'bg-slate-50 border-slate-200 opacity-60') }}">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-5 rounded-full {{ $step4 ? 'bg-emerald-600 text-white' : ($isWaitingConfirm ? 'bg-amber-500 text-white' : 'bg-slate-300 text-slate-600') }} flex items-center justify-center text-[10px] font-bold">4</span>
                    <span class="font-bold text-xs {{ $step4 ? 'text-emerald-800' : ($isWaitingConfirm ? 'text-amber-800' : 'text-slate-500') }}">Konfirmasi Ditutup</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-700 truncate" title="{{ $mutasi->nama_pemohon ?: ($mutasi->pengaju?->nama_lengkap ?? 'Pengaju') }}">{{ $mutasi->nama_pemohon ?: ($mutasi->pengaju?->nama_lengkap ?? 'Pengaju') }}</p>
                <p class="text-[10px] text-slate-400">{{ $mutasi->confirmed_at ? $mutasi->confirmed_at->format('d/m/Y H:i') : ($isWaitingConfirm ? 'Menunggu Konfirmasi' : 'Belum Ditutup') }}</p>
                @if ($step4)
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Ditutup &amp; Selesai</span>
                @elseif ($isWaitingConfirm)
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">Perlu Konfirmasi</span>
                @else
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">Tahap Akhir</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Result Alert Banner --}}
    @if ($isWaitingConfirm)
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 text-amber-900 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
                </div>
                <div>
                    <p class="font-bold text-sm text-amber-900">Verifikasi Bagian Aset Selesai — Menunggu Konfirmasi Penutupan oleh Pengaju</p>
                    <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">
                        Pengajuan telah <strong>DISETUJUI</strong> oleh Bagian Aset ({{ $mutasi->approver?->nama_lengkap ?? $mutasi->verifikator?->nama_lengkap ?? 'Bagian Aset' }}).
                        Sesuai alur, sistem menunggu konfirmasi akhir dari pemohon (<strong>{{ $mutasi->nama_pemohon ?: ($mutasi->pengaju?->nama_lengkap ?? 'Pengaju') }}</strong>) untuk menutup tiket mutasi dan meresmikan update data master aset.
                    </p>
                    @php
                        $catatanBanner = $mutasi->catatan_approval ?: $mutasi->catatan_verifikasi;
                    @endphp
                    @if ($catatanBanner)
                        <div class="mt-2 text-xs bg-white/80 p-2.5 rounded-lg border border-amber-200">
                            <strong class="font-semibold text-amber-900">Catatan Bagian Aset:</strong> {{ $catatanBanner }}
                        </div>
                    @endif
                </div>
            </div>
            @if ($user->id === $mutasi->pengaju_id || $user->isSuperAdmin())
                <button type="button" onclick="openModal('konfirmasiPengajuModal')"
                        class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-5 py-3 rounded-xl shadow-md transition whitespace-nowrap">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                    Konfirmasi Penerimaan &amp; Tutup
                </button>
            @endif
        </div>
    @elseif ($isClosedApproved)
        <div class="bg-emerald-50 border border-emerald-300 rounded-2xl p-4 text-emerald-900 flex items-start gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
            </div>
            <div>
                <p class="font-bold text-sm text-emerald-800">Pengajuan Mutasi Aset Telah Ditutup &amp; Selesai</p>
                <p class="text-xs text-emerald-700 mt-0.5">
                    Disetujui oleh Bagian Aset (<strong>{{ $mutasi->approver?->nama_lengkap ?? $mutasi->verifikator?->nama_lengkap ?? 'Bagian Aset' }}</strong>) dan telah dikonfirmasi ditutup oleh pemohon (<strong>{{ $mutasi->nama_pemohon ?: ($mutasi->pengaju?->nama_lengkap ?? 'Pengaju') }}</strong>) pada {{ $mutasi->confirmed_at?->translatedFormat('d F Y, H:i') ?? '-' }}.
                    Data lokasi dan penanggung jawab aset telah resmi diperbarui di database inventaris.
                </p>
                @if ($mutasi->catatan_konfirmasi)
                    <div class="mt-2 text-xs bg-white/80 p-2.5 rounded-lg border border-emerald-200">
                        <strong class="font-semibold text-emerald-900">Catatan Konfirmasi Pengaju:</strong> {{ $mutasi->catatan_konfirmasi }}
                    </div>
                @endif
            </div>
        </div>
    @elseif ($mutasi->status_hasil === 'Ditolak')
        <div class="bg-rose-50 border border-rose-300 rounded-2xl p-4 text-rose-900 flex items-start gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-4 h-4'])
            </div>
            <div>
                <p class="font-bold text-sm text-rose-800">Pengajuan Mutasi Aset Ditolak</p>
                <p class="text-xs text-rose-700 mt-0.5">
                    Ditolak oleh Bagian Aset (<strong>{{ $mutasi->approver?->nama_lengkap ?? $mutasi->verifikator?->nama_lengkap ?? 'Bagian Aset' }}</strong>) pada {{ ($mutasi->approved_at ?: $mutasi->verified_at)?->translatedFormat('d F Y, H:i') }}.
                </p>
                @if ($mutasi->alasan_penolakan)
                    <div class="mt-2 text-xs bg-white/80 p-2.5 rounded-lg border border-rose-200">
                        <strong class="font-semibold text-rose-900">Alasan Penolakan:</strong> {{ $mutasi->alasan_penolakan }}
                    </div>
                @endif
            </div>
        </div>
    @elseif ($mutasi->status_hasil === 'Tidak Valid')
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 text-amber-900 flex items-start gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                @include('partials.icon', ['name' => 'alert', 'class' => 'w-5 h-5'])
            </div>
            <div>
                <p class="font-bold text-sm text-amber-800">Data Aset Dinyatakan Tidak Valid &amp; Ditutup</p>
                <p class="text-xs text-amber-700 mt-0.5">
                    Diverifikasi oleh Bagian Aset (<strong>{{ $mutasi->verifikator?->nama_lengkap ?? $mutasi->approver?->nama_lengkap ?? 'Bagian Aset' }}</strong>) pada {{ ($mutasi->verified_at ?: $mutasi->approved_at)?->translatedFormat('d F Y, H:i') }}.
                </p>
                @if ($mutasi->catatan_verifikasi)
                    <div class="mt-2 text-xs bg-white/80 p-2.5 rounded-lg border border-amber-200">
                        <strong class="font-semibold text-amber-900">Catatan Verifikasi:</strong> {{ $mutasi->catatan_verifikasi }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Grid 2 Kolom: Detail Perubahan & Informasi Aset --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Rincian Mutasi (2 Kolom Lebar) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Visual Comparison Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 mb-4 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-[#114E84]'])
                    Rincian Perpindahan Aset
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    {{-- Asal --}}
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Kondisi / Posisi Asal</span>
                        <div class="space-y-2 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Lokasi Semula</span>
                                <span class="font-semibold text-slate-700 text-sm">{{ $mutasi->dari_lokasi }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Penanggung Jawab Semula</span>
                                <span class="font-semibold text-slate-700">{{ $mutasi->dari_penanggung_jawab }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tujuan --}}
                    <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 block mb-2">Tujuan Mutasi Baru</span>
                        <div class="space-y-2 text-xs">
                            <div>
                                <span class="text-blue-500 block text-[11px]">Lokasi Baru</span>
                                <span class="font-bold text-[#114E84] text-sm">{{ $mutasi->ke_lokasi }}</span>
                            </div>
                            <div>
                                <span class="text-blue-500 block text-[11px]">Penanggung Jawab Baru</span>
                                <span class="font-bold text-[#114E84]">{{ $mutasi->ke_penanggung_jawab }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 text-xs border-t border-slate-100 pt-4">
                    <div>
                        <span class="text-slate-400 block font-semibold text-[11px] uppercase tracking-wider mb-1">Alasan Pemindahan</span>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $mutasi->alasan }}
                        </div>
                    </div>

                    {{-- Dokumen Lampiran --}}
                    @if ($mutasi->dokumen)
                        <div>
                            <span class="text-slate-400 block font-semibold text-[11px] uppercase tracking-wider mb-1">Dokumen Pendukung</span>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-[#114E84] flex items-center justify-center">
                                        @include('partials.icon', ['name' => 'file-text', 'class' => 'w-4 h-4'])
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-700 text-xs">Dokumen Lampiran Mutasi</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ basename($mutasi->dokumen) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('mutasi-aset.dokumen', $mutasi) }}" target="_blank"
                                       class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition">
                                        Lihat
                                    </a>
                                    <a href="{{ route('mutasi-aset.dokumen.download', $mutasi) }}"
                                       class="px-3 py-1.5 rounded-lg bg-[#114E84] hover:bg-[#0E4272] text-white font-semibold text-xs transition">
                                        Unduh
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Dynamic Custom Fields Tambahan --}}
                    @if (!empty($mutasi->custom_fields) && is_array($mutasi->custom_fields))
                        @php
                            $dynamicFieldDefs = \App\Models\AsCustomField::where('module_key', 'mutasi')->get()->keyBy('field_name');
                        @endphp
                        <div class="border-t border-slate-100 pt-4">
                            <span class="text-slate-400 block font-semibold text-[11px] uppercase tracking-wider mb-2.5">Informasi Tambahan (Dynamic Fields)</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach ($mutasi->custom_fields as $cfKey => $cfVal)
                                    @php
                                        $cfDef = $dynamicFieldDefs->get($cfKey);
                                        $cfLabel = $cfDef?->label ?? ucwords(str_replace('_', ' ', $cfKey));
                                    @endphp
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                        <span class="text-[11px] text-slate-400 block mb-0.5">{{ $cfLabel }}</span>
                                        <p class="font-semibold text-slate-800 text-xs">
                                            @if (is_bool($cfVal))
                                                {{ $cfVal ? 'Ya' : 'Tidak' }}
                                            @elseif (is_array($cfVal))
                                                {{ implode(', ', $cfVal) }}
                                            @elseif (!empty($cfVal) && str_starts_with((string)$cfVal, 'mutasi_dokumen/'))
                                                <a href="{{ \Illuminate\Support\Facades\Storage::url($cfVal) }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1 font-mono text-[11px]">
                                                    Lihat Dokumen
                                                </a>
                                            @else
                                                {{ $cfVal ?? '-' }}
                                            @endif
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Audit Trail Catatan Peran --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 mb-4 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-[#114E84]'])
                    Jejak Catatan &amp; Verifikasi
                </h2>

                <div class="space-y-3 text-xs">
                    {{-- Catatan Operator --}}
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                Catatan Operator Helpdesk
                            </span>
                            <span class="text-[10px] text-slate-400">
                                {{ $mutasi->operator_checked_at ? $mutasi->operator_checked_at->format('d M Y, H:i') : 'Belum dicek' }}
                            </span>
                        </div>
                        <p class="text-slate-600 pl-3.5">{{ $mutasi->catatan_operator ?? 'Belum ada catatan dari operator.' }}</p>
                    </div>

                    {{-- Catatan & Keputusan Bagian Aset --}}
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                                Catatan &amp; Keputusan Bagian Aset
                                @if ($mutasi->approver || $mutasi->verifikator)
                                    <span class="font-normal text-slate-500">({{ $mutasi->approver?->nama_lengkap ?? $mutasi->verifikator?->nama_lengkap }})</span>
                                @endif
                            </span>
                            <span class="text-[10px] text-slate-400">
                                {{ ($mutasi->approved_at ?: $mutasi->verified_at) ? ($mutasi->approved_at ?: $mutasi->verified_at)->format('d M Y, H:i') : 'Belum ada keputusan' }}
                            </span>
                        </div>
                        @if ($mutasi->alasan_penolakan)
                            <p class="text-rose-700 pl-3.5 font-medium mb-1">Alasan Penolakan: {{ $mutasi->alasan_penolakan }}</p>
                        @endif
                        @php
                            $catatanBagianAset = $mutasi->catatan_approval ?: $mutasi->catatan_verifikasi;
                        @endphp
                        @if ($catatanBagianAset)
                            <p class="text-slate-600 pl-3.5">{{ $catatanBagianAset }}</p>
                        @elseif ($mutasi->status_hasil === 'Tidak Valid')
                            <p class="text-amber-700 pl-3.5 font-medium">Data aset dinyatakan Tidak Valid.</p>
                        @else
                            <p class="text-slate-500 pl-3.5 italic">Belum ada catatan verifikasi Bagian Aset.</p>
                        @endif
                        {{-- Untuk data historis lama yang memiliki catatan verifikasi dan approval terpisah --}}
                        @if ($mutasi->catatan_verifikasi && $mutasi->catatan_approval && $mutasi->catatan_verifikasi !== $mutasi->catatan_approval)
                            <div class="mt-2 pt-2 border-t border-slate-200/80 pl-3.5 space-y-1">
                                <p class="text-[11px] text-slate-500"><strong class="text-slate-700">Verifikasi Teknis:</strong> {{ $mutasi->catatan_verifikasi }}</p>
                                <p class="text-[11px] text-slate-500"><strong class="text-slate-700">Catatan Persetujuan:</strong> {{ $mutasi->catatan_approval }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Catatan Konfirmasi Pengaju --}}
                    @if ($mutasi->confirmed_at || $mutasi->catatan_konfirmasi)
                        <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/50">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="font-bold text-emerald-800 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Konfirmasi Penutupan oleh Pengaju ({{ $mutasi->nama_pemohon ?: ($mutasi->pengaju?->nama_lengkap ?? 'Pemohon') }})
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    {{ $mutasi->confirmed_at ? $mutasi->confirmed_at->format('d M Y, H:i') : '-' }}
                                </span>
                            </div>
                            <p class="text-slate-700 pl-3.5">{{ $mutasi->catatan_konfirmasi ?? 'Fisik aset telah diterima dan mutasi dikonfirmasi selesai.' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Master Aset & Pemohon (1 Kolom) --}}
        <div class="space-y-6">
            {{-- Master Aset Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4 text-[#114E84]'])
                    Informasi Master Aset
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Nama Aset</span>
                        <p class="font-bold text-ink text-sm">{{ $mutasi->aset?->nama_aset ?? '-' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Kode Aset</span>
                            <span class="font-mono font-semibold text-slate-700">{{ $mutasi->aset?->kode_aset ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Kategori</span>
                            <span class="font-semibold text-slate-700">{{ $mutasi->aset?->kategori ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Kondisi Fisik</span>
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                {{ $mutasi->aset?->kondisi ?? 'Baik' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Nilai Perolehan</span>
                            <span class="font-semibold text-slate-700">Rp {{ number_format((float)($mutasi->aset?->nilai_perolehan ?? 0), 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 block text-[11px]">Tanggal Perolehan</span>
                        <span class="font-semibold text-slate-700">
                            {{ $mutasi->aset?->tanggal_perolehan ? $mutasi->aset->tanggal_perolehan->format('d M Y') : '-' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Info Pemohon Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                    @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4 text-[#114E84]'])
                    Informasi Pemohon
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Nama Pemohon (Individu)</span>
                        <p class="font-bold text-ink text-sm">{{ $mutasi->nama_pemohon ?: ($mutasi->pengaju?->nama_lengkap ?? '-') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Jabatan Pemohon</span>
                        <p class="text-slate-700 font-semibold">{{ $mutasi->jabatan_pemohon ?: ($mutasi->pengaju?->jabatan ?? '-') }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Username Sistem</span>
                            <p class="text-slate-700 font-mono font-medium">{{ $mutasi->username_pemohon ?: ($mutasi->pengaju?->username ?? '-') }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Divisi / Unit Kerja</span>
                            <p class="text-slate-700 truncate" title="{{ $mutasi->pengaju?->unitKerja?->nama ?? $mutasi->pengaju?->bagian ?? '-' }}">
                                {{ $mutasi->pengaju?->unitKerja?->nama ?? $mutasi->pengaju?->bagian ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Riwayat Audit Aset Terakhir --}}
            @if ($riwayatAset->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                        @include('partials.icon', ['name' => 'activity', 'class' => 'w-4 h-4 text-[#114E84]'])
                        Audit Trail Pergerakan Aset
                    </h3>

                    <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                        @foreach ($riwayatAset as $h)
                            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 text-[11px]">
                                <div class="flex items-center justify-between text-[10px] text-slate-400 mb-1">
                                    <span class="font-bold text-slate-600">{{ $h->field_changed }}</span>
                                    <span>{{ $h->changed_at->format('d/m/y H:i') }}</span>
                                </div>
                                <p class="text-slate-700 leading-tight">{{ $h->keterangan }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">Oleh: {{ $h->user?->nama_lengkap ?? 'Sistem' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ================= MODAL ACTIONS ================= --}}

{{-- 1. Modal Operator (Pengecekan Form) --}}
<div id="operatorModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5 text-[#114E84]'])
                Pengecekan Formulir oleh Operator
            </h3>
            <button type="button" onclick="closeModal('operatorModal')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="{{ route('mutasi-aset.check-operator', $mutasi) }}" class="space-y-4 text-xs">
            @csrf
            <p class="text-slate-600 leading-relaxed">
                Pastikan data pengajuan mutasi aset <strong>{{ $mutasi->no_mutasi }}</strong> telah lengkap sebelum diteruskan ke Bagian Aset untuk verifikasi fisik/sistem.
            </p>

            <div>
                <label for="catatan_operator" class="block font-semibold text-slate-700 mb-1">Catatan Pengecekan (Opsional)</label>
                <textarea id="catatan_operator" name="catatan_operator" rows="3"
                          placeholder="Formulir telah dicek dan dinyatakan lengkap..."
                          class="w-full p-2.5 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('operatorModal')" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#114E84] hover:bg-[#0E4272] text-white font-semibold shadow-xs">
                    Konfirmasi &amp; Teruskan ke Bagian Aset
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 2. Modal Bagian Aset (Verifikasi & Persetujuan) --}}
<div id="verifikasiBagianAsetModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-5 h-5 text-purple-700'])
                Verifikasi &amp; Persetujuan Bagian Aset
            </h3>
            <button type="button" onclick="closeModal('verifikasiBagianAsetModal')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="{{ route('mutasi-aset.verifikasi-aset', $mutasi) }}" class="space-y-4 text-xs" id="bagianAsetForm">
            @csrf

            <div>
                <label class="block font-semibold text-slate-700 mb-2">Keputusan Bagian Aset <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-3 gap-2.5">
                    <label class="flex flex-col p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/60">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="radio" name="keputusan" value="setujui" required checked onchange="handleBagianAsetKeputusan('setujui')" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="font-bold text-xs text-emerald-900">Setujui</span>
                        </div>
                        <span class="text-[10px] text-slate-500 leading-tight">Lanjut ke konfirmasi pemohon</span>
                    </label>

                    <label class="flex flex-col p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50/60">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="radio" name="keputusan" value="tolak" required onchange="handleBagianAsetKeputusan('tolak')" class="text-rose-600 focus:ring-rose-500">
                            <span class="font-bold text-xs text-rose-900">Tolak</span>
                        </div>
                        <span class="text-[10px] text-slate-500 leading-tight">Tolak &amp; tutup pengajuan</span>
                    </label>

                    <label class="flex flex-col p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50/60">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="radio" name="keputusan" value="tidak_valid" required onchange="handleBagianAsetKeputusan('tidak_valid')" class="text-amber-600 focus:ring-amber-500">
                            <span class="font-bold text-xs text-amber-900">Tidak Valid</span>
                        </div>
                        <span class="text-[10px] text-slate-500 leading-tight">Data tidak valid &amp; tutup</span>
                    </label>
                </div>
            </div>

            {{-- Field Alasan Penolakan (Muncul saat Tolak) --}}
            <div id="fieldTolak" class="hidden">
                <label for="alasan_penolakan" class="block font-semibold text-rose-700 mb-1">
                    Alasan Penolakan <span class="text-rose-500">*</span>
                </label>
                <textarea id="alasan_penolakan" name="alasan_penolakan" rows="3"
                          placeholder="Jelaskan alasan pengajuan mutasi aset ini ditolak..."
                          class="w-full p-2.5 border border-rose-300 rounded-xl focus:ring-1 focus:ring-rose-500 focus:border-rose-500 bg-rose-50/30"></textarea>
            </div>

            {{-- Field Catatan Tidak Valid (Muncul saat Tidak Valid) --}}
            <div id="fieldTidakValid" class="hidden">
                <label for="catatan_verifikasi" class="block font-semibold text-amber-700 mb-1">
                    Catatan Ketidakvalidan Data <span class="text-rose-500">*</span>
                </label>
                <textarea id="catatan_verifikasi" name="catatan_verifikasi" rows="3"
                          placeholder="Jelaskan bagian data atau fisik aset yang tidak valid..."
                          class="w-full p-2.5 border border-amber-300 rounded-xl focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-amber-50/30"></textarea>
            </div>

            {{-- Field Catatan Persetujuan (Muncul saat Setujui) --}}
            <div id="fieldSetujui">
                <label for="catatan_approval" class="block font-semibold text-slate-700 mb-1">
                    Catatan Verifikasi / Instruksi (Opsional)
                </label>
                <textarea id="catatan_approval" name="catatan_approval" rows="3"
                          placeholder="Catatan hasil verifikasi aset atau instruksi pelaksanaan mutasi..."
                          class="w-full p-2.5 border border-slate-300 rounded-xl focus:ring-1 focus:ring-brand focus:border-brand"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('verifikasiBagianAsetModal')" class="px-3.5 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium">Batal</button>
                <button type="submit" id="btnSubmitBagianAset" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-xs">
                    Simpan Keputusan Bagian Aset
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 3. Modal Pengaju (Konfirmasi Akhir & Tutup Mutasi) --}}
<div id="konfirmasiPengajuModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5 text-emerald-600'])
                Konfirmasi Penerimaan Aset &amp; Tutup Pengajuan
            </h3>
            <button type="button" onclick="closeModal('konfirmasiPengajuModal')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="{{ route('mutasi-aset.konfirmasi-pengaju', $mutasi) }}" class="space-y-4 text-xs">
            @csrf

            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 leading-relaxed">
                <p class="font-semibold text-xs mb-1">Konfirmasi Akhir oleh Pengaju:</p>
                <p class="text-[11.5px] text-emerald-800">
                    Dengan mengonfirmasi dan menutup mutasi ini, Anda menyatakan bahwa fisik aset <strong>{{ $mutasi->aset?->nama_aset }}</strong> telah diterima di lokasi tujuan (<strong>{{ $mutasi->ke_lokasi }}</strong>) oleh <strong>{{ $mutasi->ke_penanggung_jawab }}</strong>.
                    Setelah tiket mutasi ditutup, database master aset akan resmi diperbarui.
                </p>
            </div>

            <div>
                <label for="catatan_konfirmasi" class="block font-semibold text-slate-700 mb-1">
                    Catatan Konfirmasi Penerimaan (Opsional)
                </label>
                <textarea id="catatan_konfirmasi" name="catatan_konfirmasi" rows="3"
                          placeholder="Contoh: Fisik aset telah diterima di ruangan baru dalam kondisi baik..."
                          class="w-full p-2.5 border border-slate-300 rounded-xl focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('konfirmasiPengajuModal')" class="px-3.5 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-xs">
                    Ya, Konfirmasi &amp; Tutup Mutasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    function handleBagianAsetKeputusan(keputusan) {
        const fieldSetujui = document.getElementById('fieldSetujui');
        const fieldTolak = document.getElementById('fieldTolak');
        const fieldTidakValid = document.getElementById('fieldTidakValid');
        const inputTolak = document.getElementById('alasan_penolakan');
        const inputTidakValid = document.getElementById('catatan_verifikasi');
        const btnSubmit = document.getElementById('btnSubmitBagianAset');

        fieldSetujui.classList.add('hidden');
        fieldTolak.classList.add('hidden');
        fieldTidakValid.classList.add('hidden');
        inputTolak.removeAttribute('required');
        inputTidakValid.removeAttribute('required');

        if (keputusan === 'tolak') {
            fieldTolak.classList.remove('hidden');
            inputTolak.setAttribute('required', 'required');
            btnSubmit.className = 'px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold shadow-xs';
        } else if (keputusan === 'tidak_valid') {
            fieldTidakValid.classList.remove('hidden');
            inputTidakValid.setAttribute('required', 'required');
            btnSubmit.className = 'px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow-xs';
        } else {
            fieldSetujui.classList.remove('hidden');
            btnSubmit.className = 'px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-xs';
        }
    }
</script>
@endsection
