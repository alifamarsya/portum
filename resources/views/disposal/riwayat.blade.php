@extends('layouts.app')
@section('title', 'Riwayat Aset Terhapus')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1.5">
                <a href="{{ route('modul.index', 'aset') }}" class="hover:text-brand transition">Inventarisasi Aset</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Riwayat Aset Terhapus</span>
            </nav>
            <h1 class="text-2xl font-bold text-ink flex items-center gap-2.5">
                @include('partials.icon', ['name' => 'archive', 'class' => 'w-7 h-7 text-[#114E84]'])
                <span>Riwayat Aset Terhapus</span>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                    Audit Trail Inventaris
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Daftar seluruh data aset yang telah disetujui untuk dihapus dari inventaris aktif beserta riwayat otorisasi Kepala Divisi.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('modul.index', 'aset') }}"
               class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md transition">
                @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4'])
                Lihat Inventaris Aset Aktif
            </a>
            @if(auth()->user()->isKepalaDivisi() || auth()->user()->hasRole(['pimpinan', 'kepala_divisi']) || auth()->user()->isSuperAdmin())
                <a href="{{ route('disposal-aset.approval') }}"
                   class="inline-flex items-center gap-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-semibold px-4 py-2.5 rounded-xl transition">
                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4 text-amber-600'])
                    Antrian Persetujuan Kadiv
                </a>
            @endif
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-card">
        <form method="GET" action="{{ route('disposal-aset.riwayat') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                </span>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Cari No. Disposal, Nama Aset, Kode Aset, Pemohon, atau Kadiv..."
                       class="w-full text-xs pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#114E84] focus:ring focus:ring-blue-100 placeholder:text-slate-400">
            </div>

            <div class="w-full sm:w-48">
                <select name="metode" onchange="this.form.submit()"
                        class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 focus:border-[#114E84] focus:ring focus:ring-blue-100 text-slate-700">
                    <option value="">Semua Metode</option>
                    <option value="Dimusnahkan" {{ request('metode') === 'Dimusnahkan' ? 'selected' : '' }}>Dimusnahkan</option>
                    <option value="Dihapusbukukan" {{ request('metode') === 'Dihapusbukukan' ? 'selected' : '' }}>Dihapusbukukan</option>
                    <option value="Dihibahkan" {{ request('metode') === 'Dihibahkan' ? 'selected' : '' }}>Dihibahkan</option>
                    <option value="Dijual" {{ request('metode') === 'Dijual' ? 'selected' : '' }}>Dijual</option>
                </select>
            </div>

            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition">
                Filter
            </button>

            @if(request('q') || request('metode'))
                <a href="{{ route('disposal-aset.riwayat') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table Riwayat Aset Terhapus --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="px-4 py-3.5">No. Disposal</th>
                        <th class="px-4 py-3.5">Data Aset (Terhapus)</th>
                        <th class="px-4 py-3.5">Diajukan Oleh</th>
                        <th class="px-4 py-3.5">Alasan Penghapusan</th>
                        <th class="px-4 py-3.5">Disetujui Kadiv</th>
                        <th class="px-4 py-3.5">Metode</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($items as $item)
                        @php $aset = $item->aset; @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            {{-- No Disposal & Tanggal Disetujui --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block font-mono text-[11px]">{{ $item->no_disposal }}</span>
                                <span class="text-[11px] text-emerald-700 font-medium flex items-center gap-1 mt-0.5">
                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3 h-3'])
                                    {{ $item->approved_at ? \Carbon\Carbon::parse($item->approved_at)->isoFormat('D MMM Y, HH:mm') : '-' }}
                                </span>
                            </td>

                            {{-- Data Aset --}}
                            <td class="px-4 py-3.5 min-w-[200px]">
                                <span class="font-bold text-slate-900 block text-xs">{{ $aset?->nama_aset ?? '(Data aset)' }}</span>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                    <span>Kode: <strong class="text-slate-700">{{ $aset?->kode_aset ?? '-' }}</strong></span>
                                    <span>•</span>
                                    <span>{{ $aset?->kategori ?? '-' }}</span>
                                </div>
                                <div class="text-[10.5px] text-slate-500 mt-1">
                                    Nilai Buku: <strong class="text-slate-800">Rp {{ number_format((float) ($item->nilai_buku_terakhir ?? 0), 0, ',', '.') }}</strong>
                                    • Lokasi: {{ $aset?->lokasi ?? '-' }}
                                </div>
                            </td>

                            {{-- Diajukan Oleh (Maker) --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-semibold text-slate-900 block">{{ $item->maker?->nama_lengkap ?? 'Staff / Kabag' }}</span>
                                <span class="text-[11px] text-slate-400 block">
                                    {{ $item->tanggal_pengajuan ? \Carbon\Carbon::parse($item->tanggal_pengajuan)->isoFormat('D MMM Y') : '-' }}
                                </span>
                            </td>

                            {{-- Alasan Penghapusan --}}
                            <td class="px-4 py-3.5 max-w-xs">
                                <p class="text-slate-800 line-clamp-3 leading-relaxed text-xs">
                                    {{ $item->alasan_penghapusan }}
                                </p>
                            </td>

                            {{-- Disetujui Kadiv (Checker) --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block flex items-center gap-1.5">
                                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5 text-[#114E84]'])
                                    {{ $item->checker?->nama_lengkap ?? 'Kepala Divisi' }}
                                </span>
                                <span class="text-[10.5px] text-slate-400 block">
                                    {{ $item->checker?->jabatan ?? 'Pimpinan Divisi' }}
                                </span>
                            </td>

                            {{-- Metode --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $item->metode ?? 'Dimusnahkan' }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <button type="button"
                                        onclick="showDetailModal({{ json_encode([
                                            'no_disposal' => $item->no_disposal,
                                            'nama_aset' => $aset?->nama_aset ?? '-',
                                            'kode_aset' => $aset?->kode_aset ?? '-',
                                            'kategori' => $aset?->kategori ?? '-',
                                            'lokasi' => $aset?->lokasi ?? '-',
                                            'kondisi' => $aset?->kondisi ?? '-',
                                            'nilai_perolehan' => 'Rp ' . number_format((float) ($aset?->nilai_perolehan ?? 0), 0, ',', '.'),
                                            'nilai_buku' => 'Rp ' . number_format((float) ($item->nilai_buku_terakhir ?? 0), 0, ',', '.'),
                                            'pemohon' => $item->maker?->nama_lengkap ?? '-',
                                            'jabatan_pemohon' => $item->maker?->jabatan ?? $item->maker?->bagian ?? '-',
                                            'tanggal_pengajuan' => $item->tanggal_pengajuan ? \Carbon\Carbon::parse($item->tanggal_pengajuan)->isoFormat('D MMMM Y') : '-',
                                            'alasan' => $item->alasan_penghapusan,
                                            'kadiv' => $item->checker?->nama_lengkap ?? 'Kepala Divisi',
                                            'jabatan_kadiv' => $item->checker?->jabatan ?? 'Pimpinan Divisi',
                                            'tanggal_setuju' => $item->approved_at ? \Carbon\Carbon::parse($item->approved_at)->isoFormat('D MMMM Y, HH:mm') : '-',
                                            'catatan_approval' => $item->catatan_approval ?? '-',
                                            'metode' => $item->metode ?? 'Dimusnahkan',
                                            'keterangan' => $item->keterangan ?? '-',
                                            'dokumen_url' => $item->dokumen ? route('disposal-aset.dokumen.download', $item->id) : null,
                                        ]) }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                    @include('partials.icon', ['name' => 'eye', 'class' => 'w-3.5 h-3.5 text-slate-500'])
                                    Detail Audit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-14 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-6 h-6'])
                                </div>
                                <p class="text-slate-700 font-bold text-sm mb-1">Belum Ada Riwayat Aset Terhapus</p>
                                <p class="text-slate-400 text-xs">Belum ada aset yang disetujui penghapusannya oleh Kepala Divisi.</p>
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

{{-- Modal Detail Audit Aset Terhapus --}}
<div id="detailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600 mb-0.5">Status: Terhapus (Disetujui Kadiv)</p>
                <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-5 h-5 text-[#114E84]'])
                    <span id="dNamaAset">-</span>
                </h3>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
        </div>

        {{-- Meta Otorisasi Kadiv & Pemohon --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50/60 p-4 rounded-xl border border-blue-100">
                <span class="block text-[11px] text-blue-700 font-bold uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    @include('partials.icon', ['name' => 'user', 'class' => 'w-3.5 h-3.5'])
                    Diajukan Oleh (Pemohon)
                </span>
                <p class="text-xs font-bold text-slate-900" id="dPemohon">-</p>
                <p class="text-[11px] text-slate-500" id="dJabatanPemohon">-</p>
                <p class="text-[11px] text-slate-500 mt-1">Tanggal Pengajuan: <strong id="dTglPengajuan" class="text-slate-700">-</strong></p>
            </div>

            <div class="bg-emerald-50/60 p-4 rounded-xl border border-emerald-100">
                <span class="block text-[11px] text-emerald-700 font-bold uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5'])
                    Disetujui Oleh (Kepala Divisi)
                </span>
                <p class="text-xs font-bold text-slate-900" id="dKadiv">-</p>
                <p class="text-[11px] text-slate-500" id="dJabatanKadiv">-</p>
                <p class="text-[11px] text-slate-500 mt-1">Tanggal Persetujuan: <strong id="dTglSetuju" class="text-slate-700">-</strong></p>
            </div>
        </div>

        {{-- Rincian Aset --}}
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">Spesifikasi Aset</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">No. Disposal:</span>
                    <strong id="dNoDisposal" class="font-mono text-slate-800">-</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Kode Aset:</span>
                    <strong id="dKodeAset" class="text-slate-800">-</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Kategori:</span>
                    <strong id="dKategori" class="text-slate-800">-</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Lokasi Terakhir:</span>
                    <strong id="dLokasi" class="text-slate-800">-</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Nilai Perolehan:</span>
                    <strong id="dNilaiPerolehan" class="text-slate-800">-</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Metode:</span>
                    <strong id="dMetode" class="text-slate-800">-</strong>
                </div>
            </div>
        </div>

        {{-- Alasan Penghapusan & Catatan --}}
        <div class="space-y-3 text-xs">
            <div>
                <span class="text-slate-500 font-bold block mb-1">Alasan Penghapusan:</span>
                <p id="dAlasan" class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-800 leading-relaxed"></p>
            </div>
            <div id="dCatatanWrap">
                <span class="text-slate-500 font-bold block mb-1">Catatan Kepala Divisi:</span>
                <p id="dCatatanApproval" class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-800 italic"></p>
            </div>
            <div id="dDokumenWrap">
                <span class="text-slate-500 font-bold block mb-1">Dokumen Lampiran:</span>
                <a id="dDokumenLink" href="#" target="_blank"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-[#114E84] hover:underline p-2 bg-blue-50/50 rounded-lg border border-blue-100">
                    @include('partials.icon', ['name' => 'file-text', 'class' => 'w-4 h-4 text-[#114E84]'])
                    Unduh Berita Acara / Lampiran Penghapusan
                </a>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeDetailModal()"
                    class="px-5 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
function showDetailModal(data) {
    document.getElementById('dNamaAset').textContent = data.nama_aset;
    document.getElementById('dNoDisposal').textContent = data.no_disposal;
    document.getElementById('dKodeAset').textContent = data.kode_aset;
    document.getElementById('dKategori').textContent = data.kategori;
    document.getElementById('dLokasi').textContent = data.lokasi;
    document.getElementById('dNilaiPerolehan').textContent = data.nilai_perolehan;
    document.getElementById('dMetode').textContent = data.metode;
    document.getElementById('dPemohon').textContent = data.pemohon;
    document.getElementById('dJabatanPemohon').textContent = data.jabatan_pemohon;
    document.getElementById('dTglPengajuan').textContent = data.tanggal_pengajuan;
    document.getElementById('dKadiv').textContent = data.kadiv;
    document.getElementById('dJabatanKadiv').textContent = data.jabatan_kadiv;
    document.getElementById('dTglSetuju').textContent = data.tanggal_setuju;
    document.getElementById('dAlasan').textContent = data.alasan;
    document.getElementById('dCatatanApproval').textContent = data.catatan_approval;

    const dokWrap = document.getElementById('dDokumenWrap');
    if (data.dokumen_url) {
        dokWrap.classList.remove('hidden');
        document.getElementById('dDokumenLink').href = data.dokumen_url;
    } else {
        dokWrap.classList.add('hidden');
    }

    document.getElementById('detailModal').classList.remove('hidden');
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}
</script>
@endsection
