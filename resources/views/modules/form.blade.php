@extends('layouts.app')
@section('title', ($item ? 'Ubah' : 'Tambah') . ' — ' . $cfg['judul'])
@section('content')
    <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">{{ $cfg['modul'] }}</p>
    <h1 class="text-xl font-bold text-ink mb-5">{{ $item ? 'Ubah' : 'Tambah' }} {{ $cfg['judul'] }}</h1>

    <form method="POST"
          action="{{ $item ? route('modul.update', [$key, $item->id]) : route('modul.store', $key) }}"
          class="bg-white rounded-xl border border-slate-200 shadow-card p-6 max-w-3xl">
        @csrf
        @if ($item) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
            @foreach ($cfg['fields'] as $field => $meta)
                @php
                    $opPerms = [
                        'umum_rt', 'dokumen_arsip', 'administrasi_aset',
                        'logistik_pelaporan', 'pengadaan', 'pemeliharaan_pengawasan'
                    ];
                    $isOperational = in_array($cfg['perm'] ?? '', $opPerms);
                    $isStatusField = $isOperational && (
                        in_array($field, ['status', 'status_persetujuan', 'status_arsip', 'status_pembayaran'])
                        || stripos($field, 'status') !== false
                        || stripos($meta['label'] ?? '', 'status') !== false
                    );
                    $techFields = ['maker_id', 'checker_id', 'approval_status', 'approved_at', 'catatan_approval', 'dibuat_oleh'];
                @endphp
                @continue($isStatusField || in_array($field, $techFields) || ($meta['type'] ?? '') === 'auto_user' || ($meta['type'] ?? '') === 'file')
                @php
                    $isWide = in_array($meta['type'] ?? '', ['textarea']);
                    $isReq = (bool) ($meta['req'] ?? false);
                @endphp
                <div class="{{ $isWide ? 'md:col-span-2' : '' }}">
                    <label class="block text-[13px] font-medium mb-1.5 text-slate-700">
                        {{ $meta['label'] }} @if($isReq)<span class="text-red-500 font-bold">*</span>@endif
                    </label>

                    @if (($meta['type'] ?? '') === 'textarea')
                        <textarea name="{{ $field }}" rows="3" {{ $isReq ? 'required' : '' }}
                            class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm focus:ring-1 transition">{{ old($field, $item?->$field) }}</textarea>
                    @elseif (($meta['type'] ?? '') === 'select')
                        <select name="{{ $field }}" {{ $isReq ? 'required' : '' }}
                            class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm bg-white focus:ring-1 transition">
                            <option value="">— pilih {{ strtolower($meta['label'] ?? '') }} —</option>
                            @if (!empty($meta['opts']) && is_array($meta['opts']))
                                @foreach ($meta['opts'] as $opt)
                                    <option value="{{ $opt }}" @selected(old($field, $item?->$field) === $opt)>{{ $opt }}</option>
                                @endforeach
                            @endif
                        </select>
                    @elseif (($meta['type'] ?? '') === 'checkbox')
                        <label class="inline-flex items-center gap-2 mt-1">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $item?->$field)) class="rounded border-slate-300 text-brand focus:ring-brand">
                            <span class="text-sm text-slate-500">Ya</span>
                        </label>
                    @elseif (($meta['type'] ?? '') === 'date')
                        @php
                            $dateVal = old($field, $item?->$field);
                            if ($dateVal instanceof \Illuminate\Support\Carbon) {
                                $dateVal = $dateVal->format('Y-m-d');
                            } elseif (is_string($dateVal)) {
                                $dateVal = substr($dateVal, 0, 10);
                            }
                        @endphp
                        <input type="date" name="{{ $field }}" value="{{ $dateVal }}" {{ $isReq ? 'required' : '' }}
                               class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm focus:ring-1 transition">
                    @elseif (($meta['type'] ?? '') === 'money')
                        <input type="text" inputmode="numeric" data-money-input name="{{ $field }}" value="{{ old($field, $item?->$field) }}" {{ $isReq ? 'required' : '' }}
                               class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm font-mono focus:ring-1 transition">
                        <p class="text-[12px] text-slate-400 mt-1">Pisah ribuan otomatis (1.000.000); desimal pakai koma.</p>
                    @elseif (($meta['type'] ?? '') === 'number')
                        <input type="number" step="0.01" name="{{ $field }}" value="{{ old($field, $item?->$field) }}" {{ $isReq ? 'required' : '' }}
                               class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm font-mono focus:ring-1 transition">
                    @else
                        <input type="text" name="{{ $field }}" value="{{ old($field, $item?->$field) }}" {{ $isReq ? 'required' : '' }}
                               class="w-full border @error($field) border-rose-400 focus:border-rose-500 focus:ring-rose-200 @else border-slate-300 focus:border-brand focus:ring-brand @enderror rounded-lg px-3.5 py-2.5 text-sm focus:ring-1 transition">
                    @endif

                    @if ($field === 'kode_aset' && ($key ?? '') === 'aset' && isset($masterKodeAset))
                        @if ($masterKodeAset->isEmpty())
                            <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                Belum ada master kode aset aktif. Inisialisasi dulu di
                                <a href="{{ route('konfigurasi.field-aset.index', ['module' => 'kode_aset']) }}" class="font-semibold underline">Master Kode Aset</a>
                                sebelum mendaftarkan unit aset.
                            </div>
                        @else
                            @php
                                $selectedKode = old($field, $item?->kode_aset);
                                $selectedMaster = $selectedKode
                                    ? $masterKodeAset->firstWhere('kode', $selectedKode)
                                    : null;
                                $hiddenMasterAttrs = ['id', 'kode', 'is_active', 'sort_order', 'created_at', 'updated_at'];
                            @endphp
                            <div id="master-kode-info" class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                @if ($selectedMaster)
                                    <p class="font-semibold text-slate-700 mb-1">{{ $selectedMaster->nama }}</p>
                                    @foreach ($selectedMaster->getAttributes() as $attr => $val)
                                        @if (!in_array($attr, $hiddenMasterAttrs) && $attr !== 'nama' && $val !== null && $val !== '')
                                            <p><span class="text-slate-400">{{ ucwords(str_replace('_', ' ', $attr)) }}:</span> {{ $val }}</p>
                                        @endif
                                    @endforeach
                                @else
                                    <p>Pilih kode aset untuk melihat detail master.</p>
                                @endif
                            </div>
                        @endif
                    @endif

                    @error($field)
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror

                    @if (!empty($meta['help']))
                        <p class="text-[12px] text-slate-400 mt-1">{{ $meta['help'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        @if (($key ?? '') === 'aset' && isset($masterKodeAset) && $masterKodeAset->isNotEmpty())
            <script>
                (function () {
                    const masters = @json($masterKodeAset->map(fn($m) => $m->getAttributes())->values());
                    const hidden = ['id', 'kode', 'is_active', 'sort_order', 'created_at', 'updated_at', 'nama'];
                    const infoBox = document.getElementById('master-kode-info');
                    const select = document.querySelector('select[name="kode_aset"]');
                    if (!infoBox || !select) return;
                    function labelFor(attr) {
                        return attr.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
                    }
                    function render(kode) {
                        const master = masters.find(function (m) { return m.kode === kode; });
                        infoBox.replaceChildren();
                        if (!master) {
                            const p = document.createElement('p');
                            p.textContent = 'Pilih kode aset untuk melihat detail master.';
                            infoBox.appendChild(p);
                            return;
                        }
                        const title = document.createElement('p');
                        title.className = 'font-semibold text-slate-700 mb-1';
                        title.textContent = master.nama || '';
                        infoBox.appendChild(title);
                        Object.keys(master).forEach(function (attr) {
                            if (hidden.includes(attr)) return;
                            const val = master[attr];
                            if (val === null || val === '') return;
                            const p = document.createElement('p');
                            const lbl = document.createElement('span');
                            lbl.className = 'text-slate-400';
                            lbl.textContent = labelFor(attr) + ': ';
                            p.appendChild(lbl);
                            p.appendChild(document.createTextNode(String(val)));
                            infoBox.appendChild(p);
                        });
                    }
                    select.addEventListener('change', function () { render(select.value); });
                    render(select.value);
                })();
            </script>
        @endif

        {{-- Cascading Lokasi -> Penanggung Jawab (modul aset saja, pola yang
             sama dengan form mutasi): PJ hanya berisi personel aktif dari
             lokasi yang dipilih, dan dinonaktifkan sampai lokasi dipilih. --}}
        @if (($key ?? '') === 'aset' && isset($personelByLokasi))
            <script>
                (function () {
                    const personelByLokasi = @json($personelByLokasi);
                    const selectLokasi = document.querySelector('select[name="lokasi"]');
                    const selectPj = document.querySelector('select[name="penanggung_jawab"]');
                    if (!selectLokasi || !selectPj) return;
                    const defaultPj = @json(old('penanggung_jawab', $item?->penanggung_jawab ?? ''));
                    function setDisabled(placeholder) {
                        selectPj.disabled = true;
                        selectPj.classList.add('bg-slate-50', 'opacity-60', 'cursor-not-allowed');
                        selectPj.replaceChildren();
                        const opt = document.createElement('option');
                        opt.value = '';
                        opt.textContent = placeholder;
                        selectPj.appendChild(opt);
                    }
                    function updatePjDropdown(lokasiNama, defaultVal) {
                        if (!lokasiNama) {
                            setDisabled('-- Pilih lokasi terlebih dahulu --');
                            return;
                        }
                        const daftar = personelByLokasi[lokasiNama] || [];
                        if (daftar.length === 0) {
                            setDisabled('-- Belum ada personel terdaftar di lokasi ini --');
                            return;
                        }
                        selectPj.disabled = false;
                        selectPj.classList.remove('bg-slate-50', 'opacity-60', 'cursor-not-allowed');
                        selectPj.replaceChildren();
                        const placeholder = document.createElement('option');
                        placeholder.value = '';
                        placeholder.textContent = '-- Pilih penanggung jawab --';
                        selectPj.appendChild(placeholder);
                        daftar.forEach(function (nama) {
                            const opt = document.createElement('option');
                            opt.value = nama;
                            opt.textContent = nama;
                            if (defaultVal && defaultVal === nama) opt.selected = true;
                            selectPj.appendChild(opt);
                        });
                    }
                    selectLokasi.addEventListener('change', function () { updatePjDropdown(selectLokasi.value, null); });
                    updatePjDropdown(selectLokasi.value, defaultPj);
                })();
            </script>
        @endif

        {{-- Format ribuan otomatis untuk field money: tampil bertitik
             (1.000.000), simpan angka polos (1000000). Titik selalu dianggap
             pemisah ribuan; desimal gaya Indonesia memakai koma. --}}
        <script>
            (function () {
                const inputs = document.querySelectorAll('input[data-money-input]');
                if (inputs.length === 0) return;

                function grupRibuan(digit) {
                    return digit.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }

                // Nilai dari server (angka polos, desimal pakai titik):
                // "1000000" -> "1.000.000"; "1500000.50" -> "1.500.000,50"
                function formatDariServer(nilai) {
                    if (nilai === null || nilai === undefined) return '';
                    const teks = String(nilai).trim();
                    if (teks === '') return '';
                    const bersih = teks.replace(/[^0-9.]/g, '');
                    const titik = bersih.lastIndexOf('.');
                    let bulat = bersih;
                    let desimal = '';
                    if (titik !== -1) {
                        const pecahan = bersih.slice(titik + 1);
                        if (pecahan.length <= 2) {
                            bulat = bersih.slice(0, titik);
                            desimal = pecahan;
                        }
                    }
                    bulat = bulat.replace(/[^0-9]/g, '');
                    if (bulat === '') return '';
                    let hasil = grupRibuan(bulat);
                    if (desimal !== '' && desimal !== '00' && desimal !== '0') {
                        hasil += ',' + desimal;
                    }
                    return hasil;
                }

                // Nilai ketikan pengguna (titik = pemisah ribuan, koma = desimal):
                // "1.000.000" -> "1.000.000"; "1.500.000,50" -> "1.500.000,50"
                function formatDariKetik(tampilan) {
                    if (tampilan === null || tampilan === undefined) return '';
                    const teks = String(tampilan);
                    if (teks === '') return '';
                    const koma = teks.indexOf(',');
                    let bulat = teks;
                    let desimal = '';
                    if (koma !== -1) {
                        desimal = teks.slice(koma + 1).replace(/[^0-9]/g, '').slice(0, 2);
                        bulat = teks.slice(0, koma);
                    }
                    bulat = bulat.replace(/[^0-9]/g, '');
                    if (bulat === '') return desimal ? '0,' + desimal : '';
                    let hasil = grupRibuan(bulat);
                    if (desimal !== '') hasil += ',' + desimal;
                    return hasil;
                }

                // Tampilan -> angka polos untuk server: "1.000.000,50" -> "1000000.50"
                function keAngkaPolos(tampilan) {
                    if (tampilan === null || tampilan === undefined) return '';
                    const teks = String(tampilan);
                    if (teks === '') return '';
                    const koma = teks.indexOf(',');
                    let bulat = teks;
                    let desimal = '';
                    if (koma !== -1) {
                        desimal = teks.slice(koma + 1).replace(/[^0-9]/g, '').slice(0, 2);
                        bulat = teks.slice(0, koma);
                    }
                    bulat = bulat.replace(/[^0-9]/g, '');
                    if (bulat === '') return '';
                    return desimal ? bulat + '.' + desimal : bulat;
                }

                function formatSaatKetik(input) {
                    const sebelum = input.value;
                    const posKursor = input.selectionStart === null ? sebelum.length : input.selectionStart;
                    let digitSebelumKursor = 0;
                    for (let i = 0; i < posKursor; i++) {
                        if (sebelum[i] >= '0' && sebelum[i] <= '9') digitSebelumKursor++;
                    }
                    const sesudah = formatDariKetik(sebelum);
                    input.value = sesudah;
                    let hitung = 0;
                    let posBaru = sesudah.length;
                    for (let i = 0; i < sesudah.length; i++) {
                        if (sesudah[i] >= '0' && sesudah[i] <= '9') hitung++;
                        if (hitung === digitSebelumKursor) { posBaru = i + 1; break; }
                    }
                    if (digitSebelumKursor === 0) posBaru = 0;
                    try { input.setSelectionRange(posBaru, posBaru); } catch (e) {}
                }

                inputs.forEach(function (input) {
                    input.value = formatDariServer(input.value);
                    input.addEventListener('input', function () { formatSaatKetik(input); });
                    input.addEventListener('blur', function () { input.value = formatDariKetik(input.value); });
                    const form = input.closest('form');
                    if (form && !form.dataset.moneyFormatBound) {
                        form.dataset.moneyFormatBound = '1';
                        form.addEventListener('submit', function () {
                            inputs.forEach(function (el) { el.value = keAngkaPolos(el.value); });
                        });
                    }
                });
            })();
        </script>

        <div class="pt-6 mt-6 border-t border-slate-100 flex gap-3">
            <button type="submit" class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-light transition">Simpan</button>
            <a href="{{ route('modul.index', $key) }}" class="text-sm text-slate-500 px-5 py-2.5 hover:text-ink transition">Batal</a>
        </div>
    </form>
@endsection
