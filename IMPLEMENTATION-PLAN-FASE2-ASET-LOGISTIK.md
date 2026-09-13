# IMPLEMENTATION PLAN – FASE 2
## Bagian Aset/Inventaris & Logistik

**Project:** Portum (Laravel)  
**Repository:** https://github.com/alifamarsya/portum  
**Tanggal:** 8 September 2026  
**Prinsip:** Sama dengan Fase 1  
- Ticketing = satu-satunya pintu masuk pengajuan dari luar  
- Modul internal hanya untuk pekerjaan operasional Unit Kerja  
- Role staf dipecah per Unit Kerja (multi-user)  
- Kepala Bagian melihat semua sub-modul di Bagiannya

---

## 1. Struktur Organisasi (dari Tupoksi)

```
Bagian Aset/Inventaris & Logistik
├── Unit Kerja Administrasi Aset & Inventaris
│   ├── Inventarisasi, kodefikasi & pelabelan aset
│   ├── Rekonsiliasi data aset & inventaris
│   ├── Perhitungan penyusutan (depresiasi/amortisasi)
│   ├── Reklasifikasi & pelaporan kondisi aset
│   └── Penghapusan aset (asset disposal)
│
└── Unit Kerja Logistik & Pelaporan
    ├── Pengelolaan logistik operasional
    ├── Koordinasi penerimaan & distribusi barang/jasa
    ├── Penyusunan laporan aset, inventaris & logistik
    └── Administrasi pembayaran tagihan terkait aset & operasional
```

---

## 2. Nama Modul & Sub-Modul Final

### A. Unit Kerja Administrasi Aset & Inventaris

**Permission key utama:** `administrasi_aset`

| No | Nama Sub-Modul                      | Key Modul            | Status              | Keterangan |
|----|-------------------------------------|----------------------|---------------------|----------|
| 1  | Inventarisasi Aset                  | `aset`               | Sudah ada (perkuat) | Master data aset + kode unik + kondisi + lokasi + penanggung jawab |
| 2  | Amortisasi / Penyusutan             | `amortisasi`         | Sudah ada           | Perhitungan nilai buku & akumulasi penyusutan |
| 3  | Riwayat Pergerakan Aset             | `aset_history`       | **Baru**            | Tracking perubahan lokasi, penanggung jawab, kondisi |
| 4  | Mutasi Aset                         | `mutasi_aset`        | **Baru**            | Permohonan pindah lokasi/user + approval |
| 5  | Penghapusan Aset (Disposal)         | `disposal_aset`      | **Baru**            | Proses penghapusan aset dari inventaris |
| 6  | Rekonsiliasi & Reklasifikasi Aset   | `rekonsiliasi_aset`  | **Baru**            | Rekonsiliasi data + perubahan kategori/kondisi |
| 7  | Tindak Lanjut Temuan                | `temuan`             | Sudah ada           | Temuan audit terkait aset |

### B. Unit Kerja Logistik & Pelaporan

**Permission key utama:** `logistik_pelaporan`

| No | Nama Sub-Modul                          | Key Modul              | Status              | Keterangan |
|----|-----------------------------------------|------------------------|---------------------|----------|
| 1  | Tagihan / Invoice Sewa                  | `invoice_sewa`         | Sudah ada           | Invoice sewa aset/perangkat |
| 2  | PKS & Jatuh Tempo                       | `pks`                  | Sudah ada           | Perjanjian Kerja Sama + reminder |
| 3  | Memo Sewa Cabang                        | `memo_sewa_cabang`     | Sudah ada           | Memo terkait sewa di cabang |
| 4  | Penerimaan Barang / Jasa                | `penerimaan_barang`    | **Baru**            | Pencatatan barang/jasa yang diterima |
| 5  | Distribusi Barang / Jasa                | `distribusi_barang`    | **Baru**            | Distribusi ke unit/cabang pemohon |
| 6  | Administrasi Pembayaran Tagihan         | `pembayaran_tagihan`   | **Baru**            | Tracking pembayaran terkait aset & operasional |
| 7  | Laporan Aset, Inventaris & Logistik     | (menggunakan Dashboard + Data Warehouse) | Sudah ada (perkuat) | Pelaporan periodik |

---

## 3. Perubahan Role

### Role Baru (Unit Kerja)

| ID  | nama (slug)              | Label                                              | Multi-user |
|-----|--------------------------|----------------------------------------------------|------------|
| 15  | `uk_administrasi_aset`   | Staf Unit Kerja Administrasi Aset & Inventaris     | Ya         |
| 16  | `uk_logistik`            | Staf Unit Kerja Logistik & Pelaporan               | Ya         |

### Role yang Dipertahankan
- `kabag_aset` (id 11) → akses ke **semua** sub-modul di Bagian Aset/Inventaris & Logistik + ticketing

### Role Lama
- `aset` (id 4) → **jangan dipakai lagi** untuk staf baru. User lama dipindahkan ke `uk_administrasi_aset` atau `uk_logistik`.

---

## 4. Database Changes

### Migration yang harus dibuat

**File usulan:** `2026_09_08_000010_fase2_aset_logistik_unit_kerja.php`

Isi utama:

1. **Pastikan tabel `unit_kerja` sudah ada** (dari Fase 1). Jika belum, buat ulang.

2. **Seed Unit Kerja baru** (di bawah `department_id = 2`):
   - UK-ADM-ASET → Unit Kerja Administrasi Aset & Inventaris
   - UK-LOGISTIK → Unit Kerja Logistik & Pelaporan

3. **Kolom di `users`** dan `tickets`** sudah ada dari Fase 1 (`unit_kerja_id`). Tidak perlu dibuat ulang.

4. **Tabel baru yang dibutuhkan:**

#### a. `as_aset_histories` (Riwayat Pergerakan Aset)
```php
Schema::create('as_aset_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('aset_id')->constrained('as_aset')->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('field_changed'); // lokasi, penanggung_jawab, kondisi, dll
    $table->text('old_value')->nullable();
    $table->text('new_value')->nullable();
    $table->text('keterangan')->nullable();
    $table->timestamp('changed_at');
    $table->timestamps();
});
```

#### b. `as_mutasi_aset` (Mutasi Aset)
```php
Schema::create('as_mutasi_aset', function (Blueprint $table) {
    $table->id();
    $table->string('no_mutasi')->unique();
    $table->foreignId('aset_id')->constrained('as_aset')->cascadeOnDelete();
    $table->string('dari_lokasi')->nullable();
    $table->string('ke_lokasi')->nullable();
    $table->string('dari_penanggung_jawab')->nullable();
    $table->string('ke_penanggung_jawab')->nullable();
    $table->text('alasan')->nullable();
    $table->string('status')->default('Diajukan'); // Diajukan, Disetujui, Ditolak, Selesai
    $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->text('keterangan')->nullable();
    $table->timestamps();
});
```

#### c. `as_disposal_aset` (Penghapusan Aset)
```php
Schema::create('as_disposal_aset', function (Blueprint $table) {
    $table->id();
    $table->string('no_disposal')->unique();
    $table->foreignId('aset_id')->constrained('as_aset')->cascadeOnDelete();
    $table->date('tanggal_pengajuan')->nullable();
    $table->string('alasan_penghapusan')->nullable();
    $table->string('metode')->nullable(); // Dihibahkan, Dijual, Dimusnahkan, dll
    $table->decimal('nilai_buku_terakhir', 18, 2)->nullable();
    $table->string('status')->default('Diajukan');
    $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->text('keterangan')->nullable();
    $table->string('dokumen')->nullable();
    $table->timestamps();
});
```

#### d. Tabel untuk Logistik (contoh)
- `as_penerimaan_barang`
- `as_distribusi_barang`
- `as_pembayaran_tagihan`

(Struktur field mengikuti pola modul existing: no_dokumen, tanggal, vendor/pihak, nilai, status, maker_id, checker_id, dll.)

5. **Perkuat tabel `as_aset`** (jika belum ada):
   - Pastikan ada `kode_aset` (unik)
   - Field `lokasi`, `penanggung_jawab`, `kondisi` tetap ada
   - Tambah trigger/observer untuk otomatis menulis ke `as_aset_histories` setiap kali lokasi/penanggung_jawab/kondisi berubah

---

## 5. Model yang Harus Dibuat / Diubah

1. **Model baru:**
   - `AsAsetHistory`
   - `AsMutasiAset`
   - `AsDisposalAset`
   - `AsPenerimaanBarang` (jika dibuat)
   - `AsDistribusiBarang`
   - `AsPembayaranTagihan`

2. **Update `AsAset`**
   - Relasi `histories()`, `mutasi()`, `disposal()`
   - Observer / boot method untuk mencatat history otomatis

3. **Update `User`**
   - Helper: `isUkAdministrasiAset()`, `isUkLogistik()`

4. **Update `Ticket`**
   - Pastikan `visibleTo` sudah mendukung Unit Kerja di department_id = 2

---

## 6. Seeder

### 6.1 Update `UnitKerjaSeeder`
Tambah 2 Unit Kerja di bawah `department_id = 2`:
- UK-ADM-ASET
- UK-LOGISTIK

### 6.2 Update `RolePermissionSeeder`
Tambah role:
```php
['id' => 15, 'nama' => 'uk_administrasi_aset', 'label' => 'Staf Unit Kerja Administrasi Aset & Inventaris', ...],
['id' => 16, 'nama' => 'uk_logistik', 'label' => 'Staf Unit Kerja Logistik & Pelaporan', ...],
```

**Permission:**
- `uk_administrasi_aset` → `administrasi_aset` (write), `ticketing` (write), `dashboard`
- `uk_logistik` → `logistik_pelaporan` (write), `ticketing` (write), `dashboard`
- `kabag_aset` → keduanya + ticketing + dashboard

### 6.3 Update User Seeder
- Pindahkan user lama `role_id = 4` (`aset`) ke salah satu Unit Kerja
- Buat minimal 2 user aktif per Unit Kerja

### 6.4 Ticket Category (tambahan jika perlu)
Contoh kategori yang mengarah ke Bagian Aset:
- Perbaikan & Pemeliharaan Aset → bisa ke UK Administrasi Aset atau Logistik (tergantung deskripsi)
- Pengadaan terkait aset (setelah barang datang) → Logistik
- Mutasi / Pindah Aset → Administrasi Aset

---

## 7. Perubahan di Ticketing (Layanan & Monitoring)

Tidak ada perubahan besar di struktur Ticketing. Hanya pastikan:

1. Operator / Kabag Aset bisa mengarahkan tiket ke:
   - Unit Kerja Administrasi Aset & Inventaris
   - Unit Kerja Logistik & Pelaporan
2. Scope `visibleTo` sudah benar untuk department_id = 2 dan unit_kerja_id masing-masing.
3. Kategori tiket yang relevan dengan aset otomatis disarankan ke Bagian Aset.

---

## 8. Detail Sub-Modul (Penjelasan Isi)

### Unit Kerja Administrasi Aset & Inventaris

| Sub-Modul              | Fungsi Utama | Field / Fitur Penting |
|------------------------|--------------|-----------------------|
| Inventarisasi Aset     | Master data aset | kode_aset (unik), nama, kategori, lokasi, penanggung_jawab, kondisi, nilai_perolehan, umur_ekonomis |
| Amortisasi             | Hitung penyusutan | nilai_per_bulan, akumulasi, nilai_buku (otomatis) |
| Riwayat Pergerakan     | Audit trail aset | otomatis tercatat setiap perubahan lokasi/PJ/kondisi |
| Mutasi Aset            | Pindah aset antar lokasi/user | no_mutasi, dari-ke, alasan, approval Kabag |
| Disposal Aset          | Hapus aset dari inventaris | alasan, metode, nilai buku terakhir, approval |
| Rekonsiliasi & Reklasifikasi | Sesuaikan data & ubah kategori/kondisi | periode rekonsiliasi, hasil, dokumentasi |
| Temuan                 | Tindak lanjut temuan audit | sumber, uraian, batas waktu, status |

### Unit Kerja Logistik & Pelaporan

| Sub-Modul                  | Fungsi Utama | Field / Fitur Penting |
|----------------------------|--------------|-----------------------|
| Invoice Sewa               | Tagihan sewa | no_invoice, vendor, periode, nilai, jatuh_tempo, status bayar |
| PKS & Jatuh Tempo          | Perjanjian + reminder | no_pks, vendor, jatuh_tempo, status, memo ke owner |
| Memo Sewa Cabang           | Memo sewa di cabang | cabang, jenis sewa, nilai, status persetujuan |
| Penerimaan Barang/Jasa     | Barang masuk | no_penerimaan, vendor, item, jumlah, kondisi, dokumen |
| Distribusi Barang/Jasa     | Barang keluar ke pemohon | no_distribusi, tujuan, item, tanggal, penerima |
| Administrasi Pembayaran    | Tracking bayar tagihan | no_tagihan, nilai, status bayar, tanggal bayar |
| Laporan                    | Dashboard + export | memanfaatkan Data Warehouse yang sudah ada |

---

## 9. Menu / Sidebar Rules

| Role                     | Menu yang tampil |
|--------------------------|------------------|
| `uk_administrasi_aset`   | Sub-modul Administrasi Aset + Ticketing |
| `uk_logistik`            | Sub-modul Logistik & Pelaporan + Ticketing |
| `kabag_aset`             | Semua sub-modul Bagian Aset + Ticketing |
| `operator`               | Ticketing (full) |

---

## 10. Urutan Eksekusi (Wajib Berurutan)

1. Pastikan Fase 1 (Unit Kerja + role framework) sudah selesai dan stabil.
2. Seed 2 Unit Kerja baru di bawah department_id = 2.
3. Tambah role 15 & 16 + permission matrix.
4. Update User Seeder (pindahkan user lama + multi-user).
5. Buat migration tabel baru (`as_aset_histories`, `as_mutasi_aset`, `as_disposal_aset`, dll.).
6. Buat model + relasi.
7. Update model `AsAset` agar otomatis mencatat history.
8. Daftarkan semua sub-modul baru di `config/modules.php` dengan permission yang benar.
9. Update sidebar filtering.
10. Update logic Ticketing (visibleTo + pilihan Unit Kerja).
11. Testing login per role Unit Kerja + Kabag Aset.

---

## 11. Acceptance Criteria

- [ ] Role `uk_administrasi_aset` dan `uk_logistik` aktif dan multi-user
- [ ] Login `uk_administrasi_aset` hanya melihat sub-modul Administrasi Aset + tiket relevan
- [ ] Login `uk_logistik` hanya melihat sub-modul Logistik + tiket relevan
- [ ] Login `kabag_aset` melihat semua sub-modul Bagian Aset
- [ ] Setiap perubahan lokasi / penanggung jawab / kondisi aset otomatis tercatat di history
- [ ] Alur Mutasi Aset dan Disposal Aset berjalan dengan approval
- [ ] Operator dapat mengarahkan tiket ke Unit Kerja yang benar di Bagian Aset
- [ ] `php artisan migrate` dan `db:seed` sukses tanpa error

---

## 12. File yang Akan Dibuat / Diubah

```
database/migrations/2026_09_08_000010_fase2_aset_logistik_unit_kerja.php
database/migrations/2026_09_08_000011_create_aset_history_mutasi_disposal_tables.php
app/Models/UnitKerja.php                    (update seed data)
app/Models/AsAset.php                       (update + observer)
app/Models/AsAsetHistory.php                (baru)
app/Models/AsMutasiAset.php                 (baru)
app/Models/AsDisposalAset.php               (baru)
app/Models/User.php                         (helper baru)
app/Models/Ticket.php                       (visibleTo)
config/modules.php
database/seeders/UnitKerjaSeeder.php
database/seeders/RolePermissionSeeder.php
database/seeders/UserSeeder.php
resources/views/... (sidebar, form modul baru)
```

---

## 13. Ringkasan Nama Modul Final (Fase 2)

```
Bagian Aset/Inventaris & Logistik
├── Unit Kerja Administrasi Aset & Inventaris
│   ├── Inventarisasi Aset
│   ├── Amortisasi / Penyusutan
│   ├── Riwayat Pergerakan Aset
│   ├── Mutasi Aset
│   ├── Penghapusan Aset (Disposal)
│   ├── Rekonsiliasi & Reklasifikasi Aset
│   └── Tindak Lanjut Temuan
│
└── Unit Kerja Logistik & Pelaporan
    ├── Tagihan / Invoice Sewa
    ├── PKS & Jatuh Tempo
    ├── Memo Sewa Cabang
    ├── Penerimaan Barang / Jasa
    ├── Distribusi Barang / Jasa
    ├── Administrasi Pembayaran Tagihan
    └── Laporan Aset, Inventaris & Logistik
```

---

**Catatan untuk Agent:**
- Jangan menghapus role `aset` (id 4) secara hard-delete. Cukup jangan dipakai untuk user baru.
- Gunakan `updateOrCreate` di seeder.
- History aset harus otomatis (observer/model event), jangan mengandalkan input manual saja.
- Setelah Fase 2 selesai dan lulus acceptance criteria, baru lanjut ke Fase 3 (Bagian Pengadaan & Pemeliharaan).
