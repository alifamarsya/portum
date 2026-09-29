@extends('layouts.app')
@section('title', 'Persetujuan Penghapusan Aset')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-1">Otorisasi &amp; Persetujuan</p>
            <h1 class="text-2xl font-bold text-ink flex items-center gap-2.5">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-7 h-7 text-[#114E84]'])
                <span>Antrian Persetujuan Penghapusan Aset</span>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">
                    Khusus Kepala Divisi
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Daftar permohonan penghapusan aset dari Staff/Kabag yang menunggu keputusan Kepala Divisi.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('disposal-aset.riwayat') }}"
               class="inline-flex items-center gap-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl hover:bg-slate-50 transition shadow-2xs">
                @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4 text-slate-500'])
                Lihat Riwayat Aset Terhapus
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('status'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-emerald-600'])
            {{ session('status') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
            @include('partials.icon', ['name' => 'alert-triangle', 'class' => 'w-4 h-4 text-rose-600'])
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter & Search Form --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-card">
        <form method="GET" action="{{ route('disposal-aset.approval') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                </span>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Cari No. Pengajuan, Nama Aset, Kode Aset, Pemohon, atau Alasan..."
                       class="w-full text-xs pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#114E84] focus:ring focus:ring-blue-100 placeholder:text-slate-400">
            </div>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition">
                Cari
            </button>
            @if(request('q'))
                <a href="{{ route('disposal-aset.approval') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table List Pengajuan --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="px-4 py-3.5">No. Pengajuan</th>
                        <th class="px-4 py-3.5">Aset Inventaris</th>
                        <th class="px-4 py-3.5">Pemohon</th>
                        <th class="px-4 py-3.5">Alasan Penghapusan</th>
                        <th class="px-4 py-3.5">Metode</th>
                        <th class="px-4 py-3.5">Dokumen</th>
                        <th class="px-4 py-3.5 text-right">Keputusan Kadiv</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($items as $item)
                        @php $aset = $item->aset; @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            {{-- No & Tanggal --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block font-mono text-[11px]">{{ $item->no_disposal }}</span>
                                <span class="text-[11px] text-slate-400">
                                    {{ $item->tanggal_pengajuan ? \Carbon\Carbon::parse($item->tanggal_pengajuan)->isoFormat('D MMM Y') : '-' }}
                                </span>
                            </td>

                            {{-- Aset --}}
                            <td class="px-4 py-3.5 min-w-[200px]">
                                <span class="font-bold text-slate-900 block text-xs">{{ $aset?->nama_aset ?? '(Aset tidak ditemukan)' }}</span>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                    <span>Kode: <strong class="text-slate-700">{{ $aset?->kode_aset ?? '-' }}</strong></span>
                                    <span>•</span>
                                    <span>{{ $aset?->kategori ?? '-' }}</span>
                                </div>
                                <div class="text-[10.5px] text-slate-500 mt-1">
                                    Nilai Buku: <strong class="text-slate-800">Rp {{ number_format((float) ($item->nilai_buku_terakhir ?? 0), 0, ',', '.') }}</strong>
                                    • Kondisi: <span class="font-semibold">{{ $aset?->kondisi ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Pemohon --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-semibold text-slate-900 block">{{ $item->maker?->nama_lengkap ?? 'Staff / Kabag' }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $item->maker?->jabatan ?? $item->maker?->bagian ?? '-' }}</span>
                            </td>

                            {{-- Alasan Penghapusan --}}
                            <td class="px-4 py-3.5 max-w-xs">
                                <p class="text-slate-800 line-clamp-3 leading-relaxed text-xs">
                                    {{ $item->alasan_penghapusan }}
                                </p>
                                @if($item->keterangan)
                                    <p class="text-[11px] text-slate-400 mt-1 italic">
                                        Catatan: {{ $item->keterangan }}
                                    </p>
                                @endif
                            </td>

                            {{-- Metode --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $item->metode ?? 'Dimusnahkan' }}
                                </span>
                            </td>

                            {{-- Dokumen --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($item->dokumen)
                                    <a href="{{ route('disposal-aset.dokumen.download', $item->id) }}"
                                       class="inline-flex items-center gap-1.5 text-xs text-[#114E84] hover:underline font-medium">
                                        @include('partials.icon', ['name' => 'file-text', 'class' => 'w-3.5 h-3.5'])
                                        Unduh
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            {{-- Aksi Kadiv --}}
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Tombol Setujui --}}
                                    <form method="POST" action="{{ route('disposal-aset.approve', $item->id) }}"
                                          onsubmit="return confirm('SETUJUI penghapusan aset {{ addslashes($aset?->nama_aset) }}? Aset akan ditandai terhapus dan dipindahkan ke Riwayat Aset Terhapus.')">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition">
                                            @include('partials.icon', ['name' => 'check', 'class' => 'w-3.5 h-3.5'])
                                            Setujui
                                        </button>
                                    </form>

                                    {{-- Tombol Tolak (Buka Modal) --}}
                                    <button type="button"
                                            onclick="openRejectModal({{ $item->id }}, '{{ addslashes($aset?->nama_aset) }}', '{{ $item->no_disposal }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition">
                                        @include('partials.icon', ['name' => 'x', 'class' => 'w-3.5 h-3.5'])
                                        Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-14 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6 text-emerald-500'])
                                </div>
                                <p class="text-slate-700 font-bold text-sm mb-1">Semua Pengajuan Selesai</p>
                                <p class="text-slate-400 text-xs">Tidak ada antrian pengajuan penghapusan aset yang menunggu persetujuan saat ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="p-4 border-t border-slate-100">{{ $items->links() }}</div>
        @endif
    </div>
</div>

{{-- Modal Penolakan --}}
<div id="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                @include('partials.icon', ['name' => 'alert-circle', 'class' => 'w-5 h-5 text-rose-600'])
                Tolak Pengajuan Penghapusan Aset
            </h3>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <p class="text-xs text-slate-600">
            Anda akan menolak pengajuan <strong id="modalNoDisposal" class="text-slate-900"></strong> untuk aset <strong id="modalNamaAset" class="text-slate-900"></strong>. Aset akan tetap aktif di inventaris.
        </p>

        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="space-y-2">
                <label for="alasan_penolakan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Alasan Penolakan <span class="text-rose-500">*</span>
                </label>
                <textarea id="alasan_penolakan" name="alasan_penolakan" rows="3" required
                          placeholder="Tuliskan alasan penolakan secara jelas untuk pemohon (contoh: Aset masih layak pakai setelah perbaikan minor)..."
                          class="w-full text-xs rounded-xl border-slate-300 focus:border-rose-500 focus:ring focus:ring-rose-100 p-3"></textarea>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition">
                    Konfirmasi Tolak Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(disposalId, namaAset, noDisposal) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/disposal-aset/${disposalId}/reject`;
    document.getElementById('modalNamaAset').textContent = namaAset;
    document.getElementById('modalNoDisposal').textContent = noDisposal;
    document.getElementById('alasan_penolakan').value = '';
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endsection
