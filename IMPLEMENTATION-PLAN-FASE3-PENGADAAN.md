# IMPLEMENTATION PLAN – FASE 3
## Bagian Pengadaan & Pemeliharaan Aset & Inventaris

**Project:** Portum (Laravel)  
**Repository:** https://github.com/alifamarsya/portum  
**Tanggal:** 11 September 2026  

**Prinsip (konsisten dengan Fase 1 & 2):**
- Ticketing / Mutasi Aset = pintu masuk pengajuan dari luar (modul **Pengajuan dan Monitoring**)
- Modul di Bagian Pengadaan = **pencatatan operasional internal** saja
- Role staf dipecah per Unit Kerja (multi-user)
- Kepala Bagian melihat semua sub-modul di Bagiannya

> Catatan: Alur Mutasi Aset masih dalam diskusi dengan mentor. Fase 3 ini fokus ke modul internal Pengadaan & Pemeliharaan.

---

## 1. Struktur Organisasi (dari Tupoksi)

```
Bagian Pengadaan & Pemeliharaan Aset & Inventaris
├── Unit Kerja Pengadaan Aset & Inventaris
│   ├── Perencanaan kebutuhan barang/jasa & aset
│   ├── Seleksi vendor & negosiasi harga
│   ├── Proses pengadaan sesuai SOP & regulasi
│   └── Administrasi kontrak & dokumen pengadaan
│
└── Unit Kerja Pemeliharaan & Pengawasan Aset/Inventaris
    ├── Pemeliharaan rutin aset & inventaris
    ├── Monitoring kondisi fisik aset
    ├── Pengawasan penggunaan aset
    └── Tindak lanjut perbaikan & penghapusan aset
```

---

## 2. Nama Modul & Sub-Modul Final (Internal Only)

### A. Unit Kerja Pengadaan Aset & Inventaris

**Permission key utama:** `pengadaan`

| No | Nama Sub-Modul                    | Key Modul          | Status              | Keterangan |
|----|-----------------------------------|--------------------|---------------------|----------|
| 1  | Memo Internal                     | `memo_internal`    | Sudah ada           | Memo permintaan/instruksi internal terkait pengadaan |
| 2  | Penawaran Vendor                  | `penawaran`        | Sudah ada           | Catat penawaran dari vendor |
| 3  | Negosiasi                         | `negosiasi`        | Sudah ada           | Proses & hasil negosiasi harga |
| 4  | Draft Dokumen                     | `draft_dokumen`    | Sudah ada           | Draft kontrak / dokumen pengadaan |
| 5  | SPK                               | `spk`              | Sudah ada           | Surat Perintah Kerja |
| 6  | Reminder                          | `reminder`         | Sudah ada           | Pengingat jatuh tempo / tindak lanjut pengadaan |
| 7  | Perencanaan Kebutuhan             | `perencanaan_kebutuhan` | **Baru** (opsional) | Rencana kebutuhan barang/jasa/aset |

### B. Unit Kerja Pemeliharaan & Pengawasan Aset/Inventaris

**Permission key utama:** `pemeliharaan_pengawasan`

| No | Nama Sub-Modul                        | Key Modul                 | Status     | Keterangan |
|----|---------------------------------------|---------------------------|------------|----------|
| 1  | Jadwal Pemeliharaan Rutin             | `jadwal_pemeliharaan`     | **Baru**   | Schedule maintenance berkala |
| 2  | Monitoring Kondisi Fisik Aset         | `monitoring_kondisi`      | **Baru**   | Catatan hasil inspeksi kondisi aset |
| 3  | Pengawasan Penggunaan Aset            | `pengawasan_penggunaan`   | **Baru**   | Catatan pengawasan pemakaian aset |
| 4  | Tindak Lanjut Perbaikan               | `tindak_lanjut_perbaikan` | **Baru**   | Follow-up perbaikan (bisa terhubung hasil tiket) |
| 5  | Tindak Lanjut Penghapusan Aset        | (bisa pakai `disposal_aset` di Aset atau modul terpisah) | Opsional | Jika bagian ini juga terlibat disposal |

> Catatan: Bagian Pemeliharaan sering menerima pekerjaan dari **Sistem Tiket** (kategori Perbaikan & Pemeliharaan Aset). Setelah tiket diarahkan ke Unit Kerja ini, pencatatan detail dikerjakan di modul internal di atas.

---

## 3. Perubahan Role

### Role Baru (Unit Kerja)

| ID  | nama (slug)                 | Label                                                    | Multi-user |
|-----|-----------------------------|----------------------------------------------------------|------------|
| 17  | `uk_pengadaan`              | Staf Unit Kerja Pengadaan Aset & Inventaris              | Ya         |
| 18  | `uk_pemeliharaan`           | Staf Unit Kerja Pemeliharaan & Pengawasan Aset/Inventaris| Ya         |

### Role yang Dipertahankan
- `kabag_pengadaan` (id 12) → akses **semua** sub-modul di Bagian Pengadaan & Pemeliharaan + ticketing

### Role Lama
- `pengadaan` (id 5) → jangan dipakai lagi untuk staf baru. User lama dipindahkan ke `uk_pengadaan` atau `uk_pemeliharaan`.

---

## 4. Database Changes

### 4.1 Unit Kerja
Seed 2 Unit Kerja di bawah `department_id = 3`:
- UK-PENGADAAN → Unit Kerja Pengadaan Aset & Inventaris
- UK-PEMELIHARAAN → Unit Kerja Pemeliharaan & Pengawasan Aset/Inventaris

### 4.2 Tabel baru (untuk modul Pemeliharaan)

**Contoh `jadwal_pemeliharaan`:**
```php
Schema::create('pm_jadwal_pemeliharaan', function (Blueprint $table) {
    $table->id();
    $table->string('no_jadwal')->nullable();
    $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
    $table->string('jenis_pemeliharaan')->nullable(); // Rutin / Insidental
    $table->date('tanggal_rencana')->nullable();
    $table->date('tanggal_realisasi')->nullable();
    $table->string('pelaksana')->nullable();
    $table->string('status')->default('Direncanakan'); // Direncanakan, Dikerjakan, Selesai, Dibatalkan
    $table->text('keterangan')->nullable();
    $table->string('dokumen')->nullable();
    $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

**Contoh `monitoring_kondisi`:**
```php
Schema::create('pm_monitoring_kondisi', function (Blueprint $table) {
    $table->id();
    $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
    $table->date('tanggal_inspeksi')->nullable();
    $table->string('kondisi')->nullable(); // Baik, Rusak Ringan, Rusak Berat
    $table->text('temuan')->nullable();
    $table->text('rekomendasi')->nullable();
    $table->string('petugas')->nullable();
    $table->string('dokumen')->nullable();
    $table->timestamps();
});
```

**Contoh `pengawasan_penggunaan` & `tindak_lanjut_perbaikan`:**  
Ikuti pola serupa (tanggal, aset, uraian, status, petugas, dokumen).

### 4.3 Modul Pengadaan yang sudah ada
Modul `memo_internal`, `penawaran`, `negosiasi`, `draft_dokumen`, `spk`, `reminder` **dipertahankan**. Cukup pastikan permission-nya mengarah ke role Unit Kerja Pengadaan.

---

## 5. Model & Config

1. Buat model untuk modul baru Pemeliharaan (`PmJadwalPemeliharaan`, dll.).
2. Daftarkan di `config/modules.php` dengan:
   - Unit Kerja Pengadaan → `perm` = `pengadaan`
   - Unit Kerja Pemeliharaan → `perm` = `pemeliharaan_pengawasan`
3. Update `User` helper: `isUkPengadaan()`, `isUkPemeliharaan()`
4. Update sidebar: filter menu sesuai Unit Kerja.

---

## 6. Seeder

1. Update `UnitKerjaSeeder` → tambah UK-PENGADAAN & UK-PEMELIHARAAN (department_id = 3)
2. Update `RolePermissionSeeder`:
   - Role id 17 (`uk_pengadaan`) → permission `pengadaan` + `ticketing` + `dashboard`
   - Role id 18 (`uk_pemeliharaan`) → permission `pemeliharaan_pengawasan` + `ticketing` + `dashboard`
   - `kabag_pengadaan` → keduanya + ticketing
3. Update User Seeder:
   - Pindahkan user lama `role_id = 5` ke salah satu Unit Kerja
   - Buat minimal 2 user aktif per Unit Kerja

---

## 7. Hubungan dengan Pengajuan dan Monitoring

```
Cabang / Divisi lain
        │
        ▼
Pengajuan dan Monitoring
├── Sistem Tiket (Permintaan / Permasalahan)
└── Mutasi Aset
        │
        ▼
Operator alihkan ke Bagian Pengadaan
        │
        ├── Unit Kerja Pengadaan      → kerjakan di modul internal pengadaan
        └── Unit Kerja Pemeliharaan   → kerjakan di modul internal pemeliharaan
```

Modul internal **tidak** menjadi channel pengajuan. Semua pengajuan masuk lewat Sistem Tiket (atau Mutasi Aset).

---

## 8. Menu / Sidebar Rules

| Role                 | Menu yang tampil |
|----------------------|------------------|
| `uk_pengadaan`       | Sub-modul Pengadaan + Ticketing (+ Dashboard) |
| `uk_pemeliharaan`    | Sub-modul Pemeliharaan & Pengawasan + Ticketing (+ Dashboard) |
| `kabag_pengadaan`    | Semua sub-modul Bagian Pengadaan + Ticketing |
| `operator`           | Pengajuan dan Monitoring (full) |

---

## 9. Urutan Eksekusi

1. Pastikan Fase 1 & kerangka Unit Kerja sudah ada.
2. Seed 2 Unit Kerja di bawah department_id = 3.
3. Tambah role 17 & 18 + permission matrix.
4. Update User Seeder (pindahkan user lama + multi-user).
5. Pastikan modul pengadaan existing (`memo_internal`, `penawaran`, `negosiasi`, `draft_dokumen`, `spk`, `reminder`) permission-nya benar.
6. Buat migration + model untuk modul Pemeliharaan (jadwal, monitoring, pengawasan, tindak lanjut).
7. Daftarkan di `config/modules.php`.
8. Update sidebar filtering.
9. Testing login per role Unit Kerja + Kabag Pengadaan.

---

## 10. Acceptance Criteria

- [ ] Role `uk_pengadaan` dan `uk_pemeliharaan` aktif (multi-user)
- [ ] Login `uk_pengadaan` hanya melihat sub-modul Pengadaan + tiket relevan
- [ ] Login `uk_pemeliharaan` hanya melihat sub-modul Pemeliharaan + tiket relevan
- [ ] Login `kabag_pengadaan` melihat semua sub-modul Bagian Pengadaan
- [ ] Modul internal tidak berisi form pengajuan dari luar (semua lewat Sistem Tiket)
- [ ] `php artisan migrate` dan `db:seed` sukses

---

## 11. Ringkasan Nama Modul Final (Fase 3)

```
OPERASIONAL → Pengadaan & Pemeliharaan
├── Unit Kerja Pengadaan Aset & Inventaris
│   ├── Memo Internal
│   ├── Penawaran Vendor
│   ├── Negosiasi
│   ├── Draft Dokumen
│   ├── SPK
│   ├── Reminder
│   └── Perencanaan Kebutuhan (opsional)
│
└── Unit Kerja Pemeliharaan & Pengawasan Aset/Inventaris
    ├── Jadwal Pemeliharaan Rutin
    ├── Monitoring Kondisi Fisik Aset
    ├── Pengawasan Penggunaan Aset
    └── Tindak Lanjut Perbaikan
```

---

**Catatan untuk Agent:**
- Jangan menghapus role `pengadaan` (id 5) secara hard-delete.
- Fokus Fase 3: pecah role + pastikan modul existing Pengadaan ter-assign benar + bangun modul dasar Pemeliharaan.
- Mutasi Aset **bukan** bagian dari Fase 3 (masih dalam diskusi mentor).
- Gunakan `updateOrCreate` di seeder.
