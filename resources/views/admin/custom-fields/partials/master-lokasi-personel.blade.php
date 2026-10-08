{{-- Master Lokasi & Personel (Mutasi Aset) --}}
<div class="space-y-6">
    {{-- Statistics & Actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'building', 'class' => 'w-5 h-5 text-[#114E84]'])
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Divisi / Cabang</p>
                <p class="text-xl font-bold text-ink">{{ $masterLokasi->count() }} <span class="text-xs font-normal text-slate-400">lokasi</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'users', 'class' => 'w-5 h-5 text-emerald-600'])
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Personel Aktif</p>
                @php
                    $totalPersonel = $masterLokasi->sum(fn($l) => $l->personels->where('is_active', true)->count());
                @endphp
                <p class="text-xl font-bold text-ink">{{ $totalPersonel }} <span class="text-xs font-normal text-slate-400">orang</span></p>
            </div>
        </div>

        <div class="sm:col-span-2 flex items-center justify-end gap-2.5">
            <button type="button" onclick="openLokasiModal()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-[#114E84]'])
                <span>Tambah Lokasi / Divisi</span>
            </button>
            <button type="button" onclick="openPersonelModal()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] transition shadow-xs">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-white'])
                <span>Tambah Personel</span>
            </button>
        </div>
    </div>

    {{-- Main Content Grid: Lokasi (Kiri) & Personel (Kanan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Sisi Kiri: Daftar Lokasi / Divisi (5 Cols) --}}
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-sm text-ink flex items-center gap-2">
                            @include('partials.icon', ['name' => 'building', 'class' => 'w-4 h-4 text-[#114E84]'])
                            Master Divisi &amp; Cabang
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Daftar lokasi asal dan tujuan mutasi aset.</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                        {{ $masterLokasi->count() }} Lokasi
                    </span>
                </div>

                <div class="p-3">
                    <input type="text" id="searchLokasiInput" onkeyup="filterLokasiList()"
                           placeholder="Cari divisi atau cabang..."
                           class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84] transition">
                </div>

                <div class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto" id="lokasiListContainer">
                    @forelse ($masterLokasi as $lok)
                        <div class="lokasi-item p-3.5 hover:bg-slate-50/80 transition flex items-center justify-between gap-3"
                             data-nama="{{ strtolower($lok->nama_lokasi) }}">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="font-bold text-xs text-ink truncate">{{ $lok->nama_lokasi }}</span>
                                    @if (!$lok->is_active)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-200">Nonaktif</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                    <span class="bg-slate-100 px-1.5 py-0.2 rounded text-[10px] text-slate-600 font-medium">{{ $lok->tipe ?? 'Divisi' }}</span>
                                    <span>•</span>
                                    <span>{{ $lok->personels->count() }} personel ({{ $lok->personels->where('is_active', true)->count() }} aktif)</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 flex-shrink-0">
                                {{-- Tombol Edit --}}
                                <button type="button" onclick="editLokasi({{ json_encode($lok) }})"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#114E84] hover:bg-blue-50 transition"
                                        title="Edit Lokasi">
                                    @include('partials.icon', ['name' => 'edit', 'class' => 'w-3.5 h-3.5'])
                                </button>

                                {{-- Toggle Aktif/Nonaktif --}}
                                <form method="POST" action="{{ route('konfigurasi.field-aset.lokasi.toggle', $lok) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg {{ $lok->is_active ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-100' }} transition"
                                            title="{{ $lok->is_active ? 'Nonaktifkan Lokasi' : 'Aktifkan Lokasi' }}">
                                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5'])
                                    </button>
                                </form>

                                {{-- Hapus Lokasi --}}
                                <form method="POST" action="{{ route('konfigurasi.field-aset.lokasi.destroy', $lok) }}"
                                      onsubmit="return confirm('Hapus lokasi {{ addslashes($lok->nama_lokasi) }} beserta seluruh personel di dalamnya?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Hapus Lokasi">
                                        @include('partials.icon', ['name' => 'trash', 'class' => 'w-3.5 h-3.5'])
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-400 text-xs">
                            Belum ada master lokasi/divisi. Silakan tambah lokasi baru.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Daftar Personel per Divisi (7 Cols) --}}
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-sm text-ink flex items-center gap-2">
                            @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4 text-[#114E84]'])
                            Daftar Personel per Divisi / Cabang
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Nama pemohon dan penanggung jawab aset.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <select id="filterLokasiSelect" onchange="filterPersonelTable()"
                                class="py-1.5 px-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84] transition bg-white">
                            <option value="">Semua Lokasi ({{ $totalPersonel }} Personel)</option>
                            @foreach ($masterLokasi as $lok)
                                <option value="{{ $lok->id }}">{{ $lok->nama_lokasi }} ({{ $lok->personels->count() }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-3 border-b border-slate-100 bg-slate-50/50">
                    <input type="text" id="searchPersonelInput" onkeyup="filterPersonelTable()"
                           placeholder="Cari nama personel..."
                           class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84] transition bg-white">
                </div>

                <div class="overflow-x-auto max-h-[500px]">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100 sticky top-0">
                            <tr>
                                <th class="px-4 py-3">Nama Personel</th>
                                <th class="px-4 py-3">Lokasi / Divisi</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="personelTableBody">
                            @php
                                $allPersonels = $masterLokasi->flatMap->personels->sortBy('nama_personel');
                            @endphp
                            @forelse ($allPersonels as $per)
                                <tr class="personel-row hover:bg-slate-50/60 transition"
                                    data-lokasi-id="{{ $per->lokasi_id }}"
                                    data-nama="{{ strtolower($per->nama_personel) }}"
                                    data-lokasi-nama="{{ strtolower($per->lokasi?->nama_lokasi ?? '') }}">
                                    <td class="px-4 py-3 font-semibold text-ink">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-[#114E84]/10 text-[#114E84] flex items-center justify-center font-bold text-[11px] flex-shrink-0">
                                                {{ strtoupper(substr($per->nama_personel, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span>{{ $per->nama_personel }}</span>
                                                @if ($per->jabatan)
                                                    <span class="block text-[10px] text-slate-400 font-normal">{{ $per->jabatan }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-blue-50 text-[#114E84] border border-blue-100">
                                            @include('partials.icon', ['name' => 'building', 'class' => 'w-3 h-3 text-[#114E84]'])
                                            {{ $per->lokasi?->nama_lokasi ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($per->is_active)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            {{-- Edit Personel --}}
                                            <button type="button" onclick="editPersonel({{ json_encode($per) }})"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-[#114E84] hover:bg-blue-50 transition"
                                                    title="Edit Personel">
                                                @include('partials.icon', ['name' => 'edit', 'class' => 'w-3.5 h-3.5'])
                                            </button>

                                            {{-- Toggle Status --}}
                                            <form method="POST" action="{{ route('konfigurasi.field-aset.personel.toggle', $per) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg {{ $per->is_active ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-100' }} transition"
                                                        title="{{ $per->is_active ? 'Nonaktifkan Personel' : 'Aktifkan Personel' }}">
                                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5'])
                                                </button>
                                            </form>

                                            {{-- Hapus Personel --}}
                                            <form method="POST" action="{{ route('konfigurasi.field-aset.personel.destroy', $per) }}"
                                                  onsubmit="return confirm('Hapus personel {{ addslashes($per->nama_personel) }}?')"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                        title="Hapus Personel">
                                                    @include('partials.icon', ['name' => 'trash', 'class' => 'w-3.5 h-3.5'])
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-12 text-center text-slate-400 text-xs">
                                        Belum ada personel terdaftar di master.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: TAMBAH LOKASI --}}
<div id="modalTambahLokasi" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'building', 'class' => 'w-4 h-4 text-[#114E84]'])
                Tambah Lokasi / Divisi Baru
            </h3>
            <button type="button" onclick="closeModalCustom('modalTambahLokasi')" class="text-slate-400 hover:text-ink text-lg">&times;</button>
        </div>
        <form method="POST" action="{{ route('konfigurasi.field-aset.lokasi.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lokasi / Divisi <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_lokasi" required placeholder="Contoh: Divisi IT / Divisi Cyber / Cabang Palu"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Lokasi</label>
                <select name="tipe" class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84] bg-white">
                    <option value="Divisi">Divisi (Kantor Pusat)</option>
                    <option value="Cabang">Kantor Cabang</option>
                    <option value="Unit Kerja">Unit Kerja</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Lokasi (Opsional)</label>
                <input type="text" name="kode_lokasi" placeholder="Contoh: DIV-CYBER / CBG-PLU"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalCustom('modalTambahLokasi')" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] rounded-xl shadow-xs">Simpan Lokasi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: EDIT LOKASI --}}
<div id="modalEditLokasi" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'edit', 'class' => 'w-4 h-4 text-[#114E84]'])
                Edit Lokasi / Divisi
            </h3>
            <button type="button" onclick="closeModalCustom('modalEditLokasi')" class="text-slate-400 hover:text-ink text-lg">&times;</button>
        </div>
        <form id="formEditLokasi" method="POST" action="" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lokasi / Divisi <span class="text-rose-500">*</span></label>
                <input type="text" id="editNamaLokasi" name="nama_lokasi" required
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Lokasi</label>
                <select id="editTipeLokasi" name="tipe" class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84] bg-white">
                    <option value="Divisi">Divisi (Kantor Pusat)</option>
                    <option value="Cabang">Kantor Cabang</option>
                    <option value="Unit Kerja">Unit Kerja</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Lokasi (Opsional)</label>
                <input type="text" id="editKodeLokasi" name="kode_lokasi"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalCustom('modalEditLokasi')" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] rounded-xl shadow-xs">Perbarui Lokasi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: TAMBAH PERSONEL --}}
<div id="modalTambahPersonel" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4 text-[#114E84]'])
                Tambah Personel Baru
            </h3>
            <button type="button" onclick="closeModalCustom('modalTambahPersonel')" class="text-slate-400 hover:text-ink text-lg">&times;</button>
        </div>
        <form method="POST" action="{{ route('konfigurasi.field-aset.personel.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Divisi / Lokasi Penempatan <span class="text-rose-500">*</span></label>
                <select name="lokasi_id" required class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84] bg-white">
                    <option value="">-- Pilih Lokasi / Divisi --</option>
                    @foreach ($masterLokasi as $lok)
                        <option value="{{ $lok->id }}">{{ $lok->nama_lokasi }} ({{ $lok->tipe ?? 'Divisi' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Personel <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_personel" required placeholder="Contoh: Dirli / Marsya / Zahra"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan (Opsional)</label>
                <input type="text" name="jabatan" placeholder="Contoh: Staf Cyber Security / Operator IT"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">NIP (Opsional)</label>
                <input type="text" name="nip" placeholder="Nomor Induk Pegawai"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalCustom('modalTambahPersonel')" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] rounded-xl shadow-xs">Simpan Personel</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: EDIT PERSONEL --}}
<div id="modalEditPersonel" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'edit', 'class' => 'w-4 h-4 text-[#114E84]'])
                Edit Data Personel
            </h3>
            <button type="button" onclick="closeModalCustom('modalEditPersonel')" class="text-slate-400 hover:text-ink text-lg">&times;</button>
        </div>
        <form id="formEditPersonel" method="POST" action="" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Divisi / Lokasi Penempatan <span class="text-rose-500">*</span></label>
                <select id="editPersonelLokasiId" name="lokasi_id" required class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84] bg-white">
                    @foreach ($masterLokasi as $lok)
                        <option value="{{ $lok->id }}">{{ $lok->nama_lokasi }} ({{ $lok->tipe ?? 'Divisi' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Personel <span class="text-rose-500">*</span></label>
                <input type="text" id="editNamaPersonel" name="nama_personel" required
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan (Opsional)</label>
                <input type="text" id="editPersonelJabatan" name="jabatan"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">NIP (Opsional)</label>
                <input type="text" id="editPersonelNip" name="nip"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalCustom('modalEditPersonel')" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] rounded-xl shadow-xs">Perbarui Personel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModalCustom(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModalCustom(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function openLokasiModal() {
        openModalCustom('modalTambahLokasi');
    }

    function openPersonelModal() {
        openModalCustom('modalTambahPersonel');
    }

    function editLokasi(lokasi) {
        const form = document.getElementById('formEditLokasi');
        form.action = `/konfigurasi/field-aset/lokasi/${lokasi.id}`;
        document.getElementById('editNamaLokasi').value = lokasi.nama_lokasi || '';
        document.getElementById('editTipeLokasi').value = lokasi.tipe || 'Divisi';
        document.getElementById('editKodeLokasi').value = lokasi.kode_lokasi || '';
        openModalCustom('modalEditLokasi');
    }

    function editPersonel(personel) {
        const form = document.getElementById('formEditPersonel');
        form.action = `/konfigurasi/field-aset/personel/${personel.id}`;
        document.getElementById('editPersonelLokasiId').value = personel.lokasi_id || '';
        document.getElementById('editNamaPersonel').value = personel.nama_personel || '';
        document.getElementById('editPersonelJabatan').value = personel.jabatan || '';
        document.getElementById('editPersonelNip').value = personel.nip || '';
        openModalCustom('modalEditPersonel');
    }

    function filterLokasiList() {
        const query = document.getElementById('searchLokasiInput').value.toLowerCase().trim();
        const items = document.querySelectorAll('.lokasi-item');
        items.forEach(el => {
            const nama = el.getAttribute('data-nama') || '';
            el.style.display = nama.includes(query) ? '' : 'none';
        });
    }

    function filterPersonelTable() {
        const lokasiId = document.getElementById('filterLokasiSelect').value;
        const query = document.getElementById('searchPersonelInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.personel-row');

        rows.forEach(row => {
            const rowLokasiId = row.getAttribute('data-lokasi-id') || '';
            const rowNama = row.getAttribute('data-nama') || '';
            const rowLokasiNama = row.getAttribute('data-lokasi-nama') || '';

            const matchLokasi = !lokasiId || rowLokasiId === lokasiId;
            const matchSearch = !query || rowNama.includes(query) || rowLokasiNama.includes(query);

            row.style.display = (matchLokasi && matchSearch) ? '' : 'none';
        });
    }
</script>
