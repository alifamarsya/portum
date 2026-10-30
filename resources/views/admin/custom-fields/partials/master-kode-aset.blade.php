{{-- Master Kode Aset (Inventarisasi Aset) --}}
<div class="space-y-6">
    {{-- Statistics & Actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'layers', 'class' => 'w-5 h-5 text-[#114E84]'])
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Master Kode Aset</p>
                <p class="text-xl font-bold text-ink">{{ $masterKodeAset->count() }} <span class="text-xs font-normal text-slate-400">kode</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5 text-emerald-600'])
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Kode Aktif</p>
                <p class="text-xl font-bold text-ink">{{ $masterKodeAset->where('is_active', true)->count() }} <span class="text-xs font-normal text-slate-400">kode</span></p>
            </div>
        </div>

        <div class="sm:col-span-2 flex items-center justify-end gap-2.5">
            <button type="button" onclick="openModalCustom('modalTambahKodeAset')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] transition shadow-xs">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-white']})
                <span>Tambah Kode Aset</span>
            </button>
        </div>
    </div>

    <p class="text-xs text-slate-500 leading-relaxed">
        Master ini berisi data general produk (misalnya kode untuk "Laptop Asus TUF"). Bagian Aset menginisialisasi kode di sini
        <strong>sebelum</strong> mendaftarkan rincian unit aset — satu kode general dipakai banyak unit. Kode yang sudah dipakai
        unit aset tidak bisa dihapus, tapi bisa dinonaktifkan.
    </p>

    {{-- Daftar Master Kode Aset --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-sm text-ink flex items-center gap-2">
                    @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-[#114E84]'])
                    Master Kode Aset
                </h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Kode general yang menjadi pilihan dropdown di form Tambah Inventarisasi Aset.</p>
            </div>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                {{ $masterKodeAset->count() }} Kode
            </span>
        </div>

        <div class="p-3">
            <input type="text" id="searchKodeAsetInput" onkeyup="filterKodeAsetList()"
                   placeholder="Cari kode atau nama produk..."
                   class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84] transition">
        </div>

        <div class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto" id="kodeAsetListContainer">
            @forelse ($masterKodeAset as $ka)
                <div class="kode-aset-item p-3.5 hover:bg-slate-50/80 transition flex items-center justify-between gap-3"
                     data-nama="{{ strtolower($ka->kode . ' ' . $ka->nama) }}">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="font-bold text-xs text-ink truncate">{{ $ka->kode }}</span>
                            @if (!$ka->is_active)
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-200">Nonaktif</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-400 truncate">{{ $ka->nama }}</p>
                        @if ($ka->jenis)
                            <p class="text-[11px] text-slate-400 truncate">Jenis: {{ $ka->jenis }}</p>
                        @endif
                        @if ($ka->merek || $ka->tipe || $ka->kapasitas)
                            <p class="text-[11px] text-slate-400 truncate">{{ collect([$ka->merek, $ka->tipe, $ka->kapasitas])->filter()->join(' • ') }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" onclick="editKodeAset({{ json_encode($ka) }})"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-[#114E84] hover:bg-blue-50 transition"
                                title="Edit Master Kode Aset">
                            @include('partials.icon', ['name' => 'edit', 'class' => 'w-3.5 h-3.5']})
                        </button>

                        <form method="POST" action="{{ route('konfigurasi.field-aset.kode-aset.toggle', $ka) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="p-1.5 rounded-lg {{ $ka->is_active ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-100' }} transition"
                                    title="{{ $ka->is_active ? 'Nonaktifkan Kode Aset' : 'Aktifkan Kode Aset' }}">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5']})
                            </button>
                        </form>

                        <form method="POST" action="{{ route('konfigurasi.field-aset.kode-aset.destroy', $ka) }}"
                              onsubmit="return confirm('Hapus master kode aset {{ addslashes($ka->kode) }}?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                    title="Hapus Master Kode Aset">
                                @include('partials.icon', ['name' => 'trash', 'class' => 'w-3.5 h-3.5']})
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-slate-400 text-xs">
                    Belum ada master kode aset. Silakan tambah kode aset baru.
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- MODAL: TAMBAH KODE ASET --}}
<div id="modalTambahKodeAset" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-[#114E84]'])
                Tambah Master Kode Aset
            </h3>
            <button type="button" onclick="closeModalCustom('modalTambahKodeAset')" class="text-slate-400 hover:text-ink text-lg">&times;</button>
        </div>
        <form method="POST" action="{{ route('konfigurasi.field-aset.kode-aset.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Aset <span class="text-rose-500">*</span></label>
                <input type="text" name="kode" required maxlength="50" pattern="[A-Za-z0-9\-_]+"
                       placeholder="Contoh: AST-LAP-ASUSTUF"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
                <p class="text-[11px] text-slate-400 mt-1">Hanya huruf, angka, strip (-), dan underscore (_). Kode ini dipakai bersama oleh banyak unit.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" required maxlength="150" placeholder="Contoh: Laptop Asus TUF Gaming"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis <span class="text-rose-500">*</span></label>
                <input type="text" name="jenis" required maxlength="100" placeholder="Contoh: Laptop / PC / AC"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Merek (Opsional)</label>
                <input type="text" name="merek" maxlength="100" placeholder="Contoh: Asus"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe (Opsional)</label>
                <input type="text" name="tipe" maxlength="100" placeholder="Contoh: TUF Gaming F15"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kapasitas (Opsional)</label>
                <input type="text" name="kapasitas" maxlength="100" placeholder="Contoh: 16 GB / 5 PK"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalCustom('modalTambahKodeAset')" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] rounded-xl shadow-xs">Simpan Kode Aset</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: EDIT KODE ASET --}}
<div id="modalEditKodeAset" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'edit', 'class' => 'w-4 h-4 text-[#114E84]'])
                Edit Master Kode Aset
            </h3>
            <button type="button" onclick="closeModalCustom('modalEditKodeAset')" class="text-slate-400 hover:text-ink text-lg">&times;</button>
        </div>
        <form id="formEditKodeAset" method="POST" action="" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Aset <span class="text-rose-500">*</span></label>
                <input type="text" id="editKodeKodeAset" name="kode" required maxlength="50" pattern="[A-Za-z0-9\-_]+"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                <input type="text" id="editNamaKodeAset" name="nama" required maxlength="150"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis <span class="text-rose-500">*</span></label>
                <input type="text" id="editJenisKodeAset" name="jenis" required maxlength="100" placeholder="Contoh: Laptop / PC / AC"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Merek (Opsional)</label>
                <input type="text" id="editMerekKodeAset" name="merek" maxlength="100"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe (Opsional)</label>
                <input type="text" id="editTipeKodeAset" name="tipe" maxlength="100"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kapasitas (Opsional)</label>
                <input type="text" id="editKapasitasKodeAset" name="kapasitas" maxlength="100"
                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#114E84]/20 focus:border-[#114E84]">
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalCustom('modalEditKodeAset')" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] rounded-xl shadow-xs">Simpan Perubahan</button>
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
    function editKodeAset(kodeAset) {
        document.getElementById('formEditKodeAset').action = `/konfigurasi/field-aset/kode-aset/${kodeAset.id}`;
        document.getElementById('editKodeKodeAset').value = kodeAset.kode ?? '';
        document.getElementById('editNamaKodeAset').value = kodeAset.nama ?? '';
        document.getElementById('editJenisKodeAset').value = kodeAset.jenis ?? '';
        document.getElementById('editMerekKodeAset').value = kodeAset.merek ?? '';
        document.getElementById('editTipeKodeAset').value = kodeAset.tipe ?? '';
        document.getElementById('editKapasitasKodeAset').value = kodeAset.kapasitas ?? '';
        openModalCustom('modalEditKodeAset');
    }
    function filterKodeAsetList() {
        const q = (document.getElementById('searchKodeAsetInput').value || '').toLowerCase();
        document.querySelectorAll('.kode-aset-item').forEach(function (el) {
            el.style.display = (el.dataset.nama || '').includes(q) ? '' : 'none';
        });
    }
</script>
