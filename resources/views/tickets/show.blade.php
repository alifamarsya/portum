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

            {{-- Process / Edit Button only for Operator and Internal Staff, NOT for User --}}
            @if ((auth()->user()->isOperator() || !is_null(auth()->user()->department_id)) && !auth()->user()->isKepalaDivisi())
                <a href="{{ route('tickets.edit', $ticket) }}"
                   class="inline-flex items-center gap-1.5 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow transition">
                    @include('partials.icon', ['name' => 'pencil', 'class' => 'w-3.5 h-3.5'])
                    Proses / Ubah Tiket
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Informasi Utama Permintaan --}}
        <div class="lg:col-span-2 space-y-6">
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
                        <span class="font-semibold text-slate-700">{{ $ticket->created_at->format('d F Y, H:i') }} WIB</span>
                        <span class="text-[11px] text-slate-400 block">({{ $ticket->created_at->diffForHumans() }})</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Nama Pemohon</span>
                        <span class="font-bold text-ink">{{ $ticket->user?->nama_lengkap ?? '-' }}</span>
                        <span class="text-xs text-slate-500 block">{{ $ticket->user?->bagian ?? '-' }} ({{ $ticket->user?->jabatan ?? '-' }})</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Kategori Layanan</span>
                        <span class="font-semibold text-slate-800">{{ $ticket->category?->name ?? '-' }}</span>
                        <span class="text-xs text-slate-500 block">Target SLA: {{ $ticket->category?->default_sla_hours ?? 24 }} Jam Kerja</span>
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

                    {{-- Jenis Pengajuan & Unit Kerja (hanya tampil jika sudah diisi oleh Operator) --}}
                    @if ($ticket->jenis_pengajuan || $ticket->unit_kerja_id)
                        <div class="sm:col-span-2 flex flex-wrap gap-2 items-center">
                            @if ($ticket->jenis_pengajuan)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $ticket->jenis_badge }}">
                                    {{ $ticket->jenis_pengajuan }}
                                </span>
                            @endif
                            @if ($ticket->unitKerja)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 border border-violet-200">
                                    @include('partials.icon', ['name' => 'briefcase', 'class' => 'w-3 h-3'])
                                    {{ $ticket->unitKerja->nama }}
                                </span>
                            @endif
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
                    <div>
                        <span class="text-xs text-slate-400 block font-medium mb-1.5">Berkas / Dokumen Lampiran:</span>
                        <a href="{{ asset('storage/' . $ticket->attachment_path) }}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-[#114E84] text-xs font-semibold transition border border-slate-200 shadow-2xs">
                            @include('partials.icon', ['name' => 'link', 'class' => 'w-4 h-4'])
                            Buka / Unduh Lampiran Tiket
                        </a>
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

                    {{-- Form Disposisi ke Staf --}}
                    <form method="POST" action="{{ route('tickets.dispose', $ticket) }}" class="space-y-4">
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
                <div class="bg-emerald-50 border-2 border-emerald-300 rounded-2xl p-6 shadow-card">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5'])
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-bold text-emerald-900 mb-1">Kendala Anda Telah Diselesaikan</h3>
                            <p class="text-sm text-emerald-700 mb-4">
                                Bagian yang bertanggung jawab telah menandai tiket ini sebagai <strong>Selesai</strong>.
                                Jika kendala Anda sudah benar-benar terselesaikan, silakan konfirmasi di bawah ini untuk menutup tiket secara resmi.
                            </p>

                            @error('error')
                                <div class="bg-rose-50 border border-rose-200 rounded-lg px-4 py-2.5 mb-4">
                                    <p class="text-xs text-rose-700 font-medium">{{ $message }}</p>
                                </div>
                            @enderror

                            <form method="POST" action="{{ route('tickets.confirm-close', $ticket) }}"
                                  onsubmit="return confirm('Konfirmasi bahwa masalah Anda sudah benar-benar selesai? Tiket akan ditutup dan tidak bisa dibuka kembali.')"
                            >
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-6 py-2.5 rounded-xl shadow transition">
                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                                    Ya, Masalah Sudah Selesai — Tutup Tiket
                                </button>
                                <p class="text-[11px] text-emerald-600 mt-2.5">
                                    * Aksi ini akan dicatat ke Audit Log sistem secara permanen.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Quick Process Panel (for Operator & Bagian Internal only, after disposed) --}}
            @if ((auth()->user()->isOperator() || (!is_null(auth()->user()->effectiveDepartmentId()) && !auth()->user()->isKabag())) && !auth()->user()->isKepalaDivisi())
                <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6">
                    <h2 class="text-base font-bold text-ink mb-3 pb-2 border-b border-slate-100 flex items-center gap-2">
                        @include('partials.icon', ['name' => 'sliders', 'class' => 'w-4 h-4 text-brand'])
                        Perbarui Status &amp; Delegasi Tiket
                    </h2>

                    @if ($ticket->isAwaitingKabagDisposition())
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>Tiket ini sedang menunggu verifikasi RBB &amp; disposisi dari Kepala Bagian sebelum dikerjakan oleh staf.</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-xs font-bold text-slate-700 mb-1">Status Tiket</label>
                                @php
                                    $quickStatuses = ['Diverifikasi', 'Didistribusikan', 'Dalam Proses', 'Selesai', 'Ditolak'];
                                    $currentQuickStatus = old('status', $ticket->status === 'Menunggu Verifikasi' ? 'Diverifikasi' : $ticket->status);
                                @endphp
                                <select name="status" id="status" required
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-brand focus:border-brand">
                                    @foreach ($quickStatuses as $st)
                                        <option value="{{ $st }}" {{ $currentQuickStatus === $st ? 'selected' : '' }}>
                                            {{ $st }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if (auth()->user()->isOperator())
                                <div>
                                    <label for="department_id" class="block text-xs font-bold text-slate-700 mb-1">Alokasikan ke Bagian (Kepala Bagian)</label>
                                    <select name="department_id" id="department_id"
                                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-brand focus:border-brand">
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id', $ticket->department_id) == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-bold text-slate-700 mb-1">Catatan Tindak Lanjut / Keterangan</label>
                            <textarea name="notes" id="notes" rows="2"
                                      placeholder="Tambahkan catatan untuk pemohon atau catatan internal..."
                                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-brand focus:border-brand"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-5 py-2.5 rounded-xl shadow transition">
                                Simpan Perubahan &amp; Catat Riwayat
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

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
