# IMPLEMENTATION PLAN – FASE 1 (Versi 2)
## Bagian Umum & Rumah Tangga + Layanan & Monitoring (Ticketing)

**Project:** Portum (Laravel)  
**Repository:** https://github.com/alifamarsya/portum  
**Tanggal:** 8 September 2026  
**Status:** Siap dieksekusi (menggantikan plan sebelumnya)

---

## 1. Prinsip Arsitektur yang Disepakati

1. **Ticketing** adalah **satu-satunya pintu masuk** untuk semua pengajuan dari:
   - Cabang
   - Divisi lain di Kantor Pusat
   - Unit internal yang mengajukan ke Divisi Umum
2. Modul internal di tiap Unit Kerja **hanya** untuk pekerjaan operasional internal (bukan untuk menerima pengajuan dari luar).
3. Modul `permintaan_cabang` **dihapus / dinonaktifkan** karena fungsinya sudah digantikan penuh oleh Ticketing.
4. Pemisahan **Permintaan** vs **Permasalahan** **bukan modul**, melainkan **field** di tiket (`jenis_pengajuan`). Operator yang menentukan saat verifikasi.
5. Setiap Unit Kerja hanya boleh mengakses sub-modul sesuai tupoksinya.
6. Kepala Bagian Umum (`kabag_umum`) dapat melihat semua sub-modul di dalam Bagiannya.

---

## 2. Struktur Nama Modul & Sub-Modul

### A. Modul Lintas Bagian (Layanan & Monitoring)

| Nama Modul              | Key / Permission     | Keterangan |
|-------------------------|----------------------|----------|
| **Layanan & Monitoring** | `ticketing`         | Pintu masuk semua pengajuan. Berisi fitur tiket, tracking, disposisi, SLA, dll. |

**Sub-fitur di dalam Layanan & Monitoring:**
- Buat Tiket (oleh User/Pemohon)
- Verifikasi & Klasifikasi (oleh Operator) → termasuk menentukan `jenis_pengajuan`
- Disposisi ke Bagian / Unit Kerja
- Tracking status + history
- Monitoring SLA
- Dashboard tiket per peran

**Field penting di Tiket:**
- `jenis_pengajuan` → `Permintaan` | `Permasalahan` (diisi/diperbarui oleh Operator)
- `category_id` → kategori layanan detail (Sarana & Prasarana, Kebersihan, K3, Dokumen, dll.)
- `department_id`
- `unit_kerja_id`
- `priority`
- `status`
- `ticket_number`

---

### B. Bagian Umum & Rumah Tangga

#### 1. Unit Kerja Umum & Rumah Tangga

**Permission key utama:** `umum_rt`

| No | Nama Sub-Modul                    | Key Modul              | Status        | Keterangan |
|----|-----------------------------------|------------------------|---------------|----------|
| 1  | Kendaraan & Driver                | `kendaraan`            | Sudah ada     | Data kendaraan operasional + driver |
| 2  | Biaya Operasional Harian          | `biaya_harian`         | Sudah ada (perluas) | BBM, Perawatan, Rumah Tangga, Kebersihan, Keamanan, Utilitas |
| 3  | Master Fasilitas Kantor           | `fasilitas_kantor`     | **Baru**      | Daftar fasilitas + kondisi |
| 4  | Pemeliharaan Gedung & Utilitas    | `pemeliharaan_gedung`  | **Baru**      | Jadwal & realisasi pemeliharaan |
| 5  | Checklist Kebersihan & Keamanan   | `checklist_kebersihan` | **Baru**      | Checklist harian/mingguan |
| 6  | Catatan K3 & Lingkungan           | `k3_insiden`           | **Baru**      | Insiden K3, near miss, isu lingkungan & ergonomi |

#### 2. Unit Kerja Pengelolaan Dokumen & Kearsipan

**Permission key utama:** `dokumen_arsip`

| No | Nama Sub-Modul              | Key Modul           | Status              | Keterangan |
|----|-----------------------------|---------------------|---------------------|----------|
| 1  | Surat Masuk                 | `surat_masuk`       | Sudah ada (perkuat) | + lokasi arsip, masa retensi, status arsip |
| 2  | Surat Keluar                | `surat_keluar`      | Sudah ada (perkuat) | Sama |
| 3  | Memo Masuk                  | `memo_masuk`        | Sudah ada (perkuat) | Sama |
| 4  | Memo Keluar                 | `memo_keluar`       | Sudah ada (perkuat) | Sama |
| 5  | Master Arsip Dokumen        | `arsip_dokumen`     | **Baru**            | Pusat data arsip fisik & digital |
| 6  | Dokumen Legalitas           | `dokumen_legalitas` | **Baru**            | Dokumen ber-masa berlaku + reminder |

---

## 3. Perubahan Role

### Role Baru (Unit Kerja)

| ID  | nama (slug)    | Label                                                | Multi-user |
|-----|----------------|------------------------------------------------------|------------|
| 13  | `uk_umum_rt`   | Staf Unit Kerja Umum & Rumah Tangga                  | Ya         |
| 14  | `uk_dokumen`   | Staf Unit Kerja Pengelolaan Dokumen & Kearsipan      | Ya         |

### Role yang Dipertahankan
- `kabag_umum` (id 10) → akses ke **semua** sub-modul di Bagian Umum & Rumah Tangga + ticketing

### Role yang Tidak Dipakai Lagi untuk Staf Baru
- `umum_rt` (id 3) → user lama dipindahkan ke `uk_umum_rt` atau `uk_dokumen`

---

## 4. Database Changes

### Migration yang harus dibuat

**File:** `2026_09_08_000001_fase1_unit_kerja_and_ticketing_update.php`

Isi utama:

1. **Tabel `unit_kerja`**
```php
Schema::create('unit_kerja', function (Blueprint $table) {
    $table->id();
    $table->foreignId('department_id')->constrained('internal_departments')->cascadeOnDelete();
    $table->string('nama');
    $table->string('kode')->unique();
    $table->text('deskripsi')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

2. **Kolom baru di `users`**
```php
$table->foreignId('unit_kerja_id')->nullable()->after('department_id')
      ->constrained('unit_kerja')->nullOnDelete();
```

3. **Kolom baru di `tickets`**
```php
$table->string('jenis_pengajuan')->nullable()->after('priority'); // Permintaan | Permasalahan
$table->foreignId('unit_kerja_id')->nullable()->after('department_id')
      ->constrained('unit_kerja')->nullOnDelete();
```

4. **Nonaktifkan / hapus referensi modul `permintaan_cabang`**
   - Hapus dari `config/modules.php`
   - Hapus permission terkait (jika ada)
   - Jangan hard-delete tabel jika masih ada data (bisa dibiarkan atau di-soft-delete)

5. **Data awal Unit Kerja**
   - UK-URT → Unit Kerja Umum & Rumah Tangga (department_id = 1)
   - UK-DOK → Unit Kerja Pengelolaan Dokumen & Kearsipan (department_id = 1)

---

## 5. Model yang Harus Dibuat / Diubah

1. **Model baru:** `app/Models/UnitKerja.php`
2. **Update `User`**
   - Relasi `unitKerja()`
   - Helper: `isUkUmumRt()`, `isUkDokumen()`, `effectiveUnitKerjaId()`
3. **Update `Ticket`**
   - Relasi `unitKerja()`
   - Field `jenis_pengajuan` masuk `$fillable`
   - Update scope `visibleTo()` agar mempertimbangkan `unit_kerja_id`

---

## 6. Seeder

### 6.1 `UnitKerjaSeeder`
- Insert 2 Unit Kerja di bawah Bagian Umum & Rumah Tangga

### 6.2 Update `RolePermissionSeeder`
- Tambah role id 13 (`uk_umum_rt`) dan 14 (`uk_dokumen`)
- Permission:
  - `uk_umum_rt` → `umum_rt` (write), `ticketing` (write), `dashboard`
  - `uk_dokumen` → `dokumen_arsip` (write), `ticketing` (write), `dashboard`
  - `kabag_umum` → `umum_rt` + `dokumen_arsip` + `ticketing` + `dashboard`

### 6.3 Update User Seeder
- Pindahkan user lama `role_id = 3` ke role Unit Kerja + isi `unit_kerja_id`
- Buat minimal 2–3 user aktif per Unit Kerja (multi-user)

### 6.4 Update Ticket Category Seeder
Contoh kategori yang disarankan:

| Nama Kategori                      | Default Unit Kerja | default_sla_hours |
|------------------------------------|--------------------|-------------------|
| Permintaan Sarana & Prasarana      | UK-URT             | 24                |
| Layanan Umum & Kebersihan          | UK-URT             | 24                |
| Keamanan & K3                      | UK-URT             | 12                |
| Pemeliharaan Gedung & Utilitas     | UK-URT             | 48                |
| Pengelolaan Dokumen & Arsip        | UK-DOK             | 24                |

---

## 7. Perubahan di Ticketing (Layanan & Monitoring)

1. Tambah field `jenis_pengajuan` di form verifikasi/update tiket (hanya Operator & Kabag yang boleh mengubah).
2. Saat Operator verifikasi:
   - Wajib / sangat disarankan mengisi `jenis_pengajuan` (Permintaan / Permasalahan)
   - Bisa langsung assign `department_id` + `unit_kerja_id`
3. Update filter list tiket: bisa filter berdasarkan `jenis_pengajuan`.
4. Update tampilan detail tiket: tampilkan badge “Permintaan” atau “Permasalahan”.
5. Logic `visibleTo`:
   - Staf Unit Kerja hanya melihat tiket yang di-assign ke unit kerjanya (atau department-nya yang sudah diverifikasi)
   - Kabag Umum melihat semua tiket department 1

---

## 8. Modul Internal yang Harus Dikerjakan

### Unit Kerja Umum & Rumah Tangga (`perm = umum_rt`)

**Yang sudah ada (perbaiki):**
- `kendaraan`
- `biaya_harian` → perluas opsi kategori menjadi:  
  `BBM`, `Perawatan`, `Rumah Tangga`, `Kebersihan`, `Keamanan`, `Utilitas`, `Lainnya`

**Yang harus dibuat baru:**
- `fasilitas_kantor`
- `pemeliharaan_gedung`
- `checklist_kebersihan`
- `k3_insiden`

### Unit Kerja Pengelolaan Dokumen & Kearsipan (`perm = dokumen_arsip`)

**Yang sudah ada (perkuat):**
- `surat_masuk`, `surat_keluar`, `memo_masuk`, `memo_keluar`  
  → tambah field: `lokasi_arsip`, `masa_retensi`, `status_arsip` (Aktif / Arsip / Musnah)

**Yang harus dibuat baru:**
- `arsip_dokumen`
- `dokumen_legalitas`

---

## 9. Menu / Sidebar Rules

| Role            | Menu yang tampil |
|-----------------|------------------|
| `uk_umum_rt`    | Sub-modul Umum & Rumah Tangga + Ticketing |
| `uk_dokumen`    | Sub-modul Dokumen & Kearsipan + Ticketing |
| `kabag_umum`    | Semua sub-modul Bagian Umum & Rumah Tangga + Ticketing |
| `operator`      | Ticketing (full) + monitoring |

---

## 10. Urutan Eksekusi (Wajib Berurutan)

1. Buat migration (tabel `unit_kerja` + kolom di `users` & `tickets` + field `jenis_pengajuan`)
2. Jalankan migration
3. Buat model `UnitKerja`
4. Buat & jalankan `UnitKerjaSeeder`
5. Update `RolePermissionSeeder` (role 13 & 14 + permission)
6. Update User Seeder (pindahkan user lama + buat multi-user)
7. Update model `User` dan `Ticket`
8. Hapus / nonaktifkan modul `permintaan_cabang` dari `config/modules.php` dan menu
9. Update `config/modules.php` untuk modul baru + permission `dokumen_arsip`
10. Buat migration tabel modul baru (jika diperlukan)
11. Update form & logic Ticketing (jenis_pengajuan + unit_kerja_id)
12. Update scope `visibleTo` di Ticket
13. Update sidebar / menu filtering
14. Update Ticket Category seeder
15. Testing login per role

---

## 11. Acceptance Criteria

- [ ] Modul `permintaan_cabang` sudah tidak muncul di menu dan config
- [ ] Field `jenis_pengajuan` ada di tiket dan bisa diisi/diubah oleh Operator
- [ ] Login `uk_umum_rt` hanya melihat sub-modul Umum & Rumah Tangga + tiket yang relevan
- [ ] Login `uk_dokumen` hanya melihat sub-modul Dokumen & Kearsipan + tiket yang relevan
- [ ] Login `kabag_umum` melihat semua sub-modul Bagian Umum + semua tiket department 1
- [ ] Operator dapat menentukan jenis pengajuan (Permintaan/Permasalahan) dan mengarahkan ke Unit Kerja
- [ ] Minimal 2 user aktif per Unit Kerja
- [ ] `php artisan migrate` dan `db:seed` berjalan tanpa error

---

## 12. File yang Akan Dibuat / Diubah

```
database/migrations/2026_09_08_000001_fase1_unit_kerja_and_ticketing_update.php
database/migrations/2026_09_08_000002_create_modul_internal_umum_tables.php  (jika perlu)
app/Models/UnitKerja.php
app/Models/User.php
app/Models/Ticket.php
config/modules.php
database/seeders/UnitKerjaSeeder.php
database/seeders/RolePermissionSeeder.php
database/seeders/UserSeeder.php
database/seeders/TicketRoleSeeder.php
app/Http/Controllers/TicketController.php
app/Services/TicketService.php
resources/views/... (form tiket, sidebar, detail tiket)
```

---

## 13. Ringkasan Nama Modul Final (Fase 1)

```
Layanan & Monitoring
└── Ticketing
    ├── jenis_pengajuan : Permintaan | Permasalahan
    └── kategori layanan detail

Bagian Umum & Rumah Tangga
├── Unit Kerja Umum & Rumah Tangga
│   ├── Kendaraan & Driver
│   ├── Biaya Operasional Harian
│   ├── Master Fasilitas Kantor
│   ├── Pemeliharaan Gedung & Utilitas
│   ├── Checklist Kebersihan & Keamanan
│   └── Catatan K3 & Lingkungan
│
└── Unit Kerja Pengelolaan Dokumen & Kearsipan
    ├── Surat Masuk
    ├── Surat Keluar
    ├── Memo Masuk
    ├── Memo Keluar
    ├── Master Arsip Dokumen
    └── Dokumen Legalitas
```

---

**Catatan untuk Agent:**
- Jangan menghapus tabel `um_permintaan_cabang` secara hard-delete jika masih ada data. Cukup hilangkan dari config dan menu.
- Gunakan `updateOrCreate` di semua seeder.
- Setelah selesai, pastikan semua acceptance criteria terpenuhi sebelum lanjut ke Fase 2.
