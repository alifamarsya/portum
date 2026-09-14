# IMPLEMENTATION PLAN – FASE 2 (Revisi)
## Bagian Aset/Inventaris & Logistik + Sub-Modul Mutasi Aset

**Project:** Portum (Laravel)  
**Repository:** https://github.com/alifamarsya/portum  
**Tanggal Revisi:** 9 September 2026  

**Perubahan Utama dari Versi Sebelumnya:**
- Nama grup menu **Layanan & Monitoring** diubah menjadi **Pengajuan dan Monitoring**
- Di dalam **Pengajuan dan Monitoring** terdapat 3 sub-modul:
  1. Dashboard (sesuai role)
  2. Sistem Tiket
  3. Mutasi Aset
- Modul internal di Operasional → Aset & Logistik **tidak lagi** berisi proses Mutasi Aset
- Alur Mutasi Aset mengikuti flowchart yang disepakati

---

## 1. Prinsip Arsitektur (Revisi)

1. **Pengajuan dan Monitoring** memiliki **3 sub-modul**:
   - **Dashboard** → sesuai role masing-masing pengguna
   - **Sistem Tiket** → untuk Permintaan & Permasalahan
   - **Mutasi Aset** → untuk tracking pemindahan aset (lokasi & penanggung jawab)
2. **Modul internal** di Operasional → Aset & Logistik bersifat **pencatatan operasional saja** (tidak menangani alur pengajuan mutasi).
3. Role staf dipecah per Unit Kerja (multi-user).
4. Kepala Bagian Aset melihat semua sub-modul internal di Bagiannya + dapat memproses Mutasi Aset.

---

## 2. Struktur Menu yang Diharapkan

```
PENGAJUAN DAN MONITORING
├── Dashboard             ← sesuai role (Admin / Operator / Kabag / Staf / User / Pimpinan)
├── Sistem Tiket          ← Permintaan / Permasalahan
└── Mutasi Aset           ← Sub-modul khusus tracking mutasi aset

OPERASIONAL
├── Umum & Rumah Tangga
├── Aset & Logistik
│   ├── Unit Kerja Administrasi Aset & Inventaris
│   │   ├── Inventarisasi Aset
│   │   ├── Amortisasi / Penyusutan
│   │   ├── Riwayat Pergerakan Aset (view only / history)
│   │   ├── Penghapusan Aset (Disposal)
│   │   ├── Rekonsiliasi & Reklasifikasi Aset
│   │   └── Tindak Lanjut Temuan
│   │
│   └── Unit Kerja Logistik & Pelaporan
│       ├── Tagihan / Invoice Sewa
│       ├── PKS & Jatuh Tempo
│       ├── Memo Sewa Cabang
│       ├── Penerimaan Barang / Jasa
│       ├── Distribusi Barang / Jasa
│       ├── Administrasi Pembayaran Tagihan
│       └── Laporan Aset & Logistik
│
└── Pengadaan & Pemeliharaan
```

---

## 3. Sub-Modul Mutasi Aset (di dalam Pengajuan dan Monitoring)

### 3.1 Tujuan
Menyediakan alur khusus untuk pengajuan, verifikasi, approval, dan pencatatan pemindahan aset (perubahan lokasi dan/atau penanggung jawab), lengkap dengan nomor mutasi dan riwayat.

### 3.2 Alur (sesuai flowchart)

```
Mulai
  │
  ▼
Pengajuan dan Monitoring → Pilih Sub-Modul: Mutasi Aset
  │
  ▼
Pengaju membuat Pengajuan Mutasi Aset
(Pilih Aset + Lokasi Tujuan + Penanggung Jawab Baru)
  │
  ▼
Sistem generate No. Mutasi: MUT-YYYYMMDD-XXXX
Status: Diajukan
Simpan ke tabel as_mutasi_aset
  │
  ▼
Operator / Staf Aset Verifikasi data aset
  │
  ├── Data Tidak Valid ──► Minta Klarifikasi ──► kembali ke verifikasi
  │
  └── Data Valid
        │
        ▼
      Kabag Aset Review Pengajuan
        │
        ├── Tolak ──► Status: Ditolak ──► Selesai (Ditolak)
        │
        └── Setujui
              │
              ▼
            Update otomatis tabel as_aset
            (Lokasi + Penanggung Jawab)
              │
              ▼
            Catat ke as_aset_history
              │
              ▼
            Status: Selesai
              │
              ▼
            Notifikasi ke Pengaju & Penerima
              │
              ▼
            Selesai - Mutasi Aset
```

### 3.3 Tabel Database untuk Mutasi Aset

**Tabel: `as_mutasi_aset`**
```php
Schema::create('as_mutasi_aset', function (Blueprint $table) {
    $table->id();
    $table->string('no_mutasi')->unique();          // MUT-YYYYMMDD-XXXX
    $table->foreignId('aset_id')->constrained('as_aset')->cascadeOnDelete();
    $table->string('dari_lokasi')->nullable();
    $table->string('ke_lokasi')->nullable();
    $table->string('dari_penanggung_jawab')->nullable();
    $table->string('ke_penanggung_jawab')->nullable();
    $table->text('alasan')->nullable();
    $table->string('status')->default('Diajukan');  // Diajukan, Menunggu Klarifikasi, Diverifikasi, Disetujui, Ditolak, Selesai
    $table->foreignId('pengaju_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('verifikator_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete(); // Kabag Aset
    $table->text('catatan_verifikasi')->nullable();
    $table->text('catatan_approval')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->string('dokumen')->nullable();
    $table->timestamps();
});
```

**Tabel: `as_aset_histories`** (jika belum ada)
```php
Schema::create('as_aset_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('aset_id')->constrained('as_aset')->cascadeOnDelete();
    $table->foreignId('mutasi_id')->nullable()->constrained('as_mutasi_aset')->nullOnDelete();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('field_changed'); // lokasi, penanggung_jawab, kondisi, dll
    $table->text('old_value')->nullable();
    $table->text('new_value')->nullable();
    $table->text('keterangan')->nullable();
    $table->timestamp('changed_at');
    $table->timestamps();
});
```

### 3.4 Fitur yang Harus Ada di Sub-Modul Mutasi Aset

- Form pengajuan (pilih aset, lokasi tujuan, penanggung jawab baru, alasan, lampiran)
- Generate nomor otomatis `MUT-YYYYMMDD-XXXX`
- List pengajuan dengan filter status
- Halaman verifikasi (Operator / Staf Aset)
- Halaman approval (Kabag Aset)
- Tombol Minta Klarifikasi
- Saat disetujui: otomatis update `as_aset` + insert ke `as_aset_history`
- Notifikasi ke pengaju dan penerima (penanggung jawab baru)
- Detail + timeline status

### 3.5 Permission untuk Mutasi Aset

| Role                        | Hak Akses Mutasi Aset                          |
|-----------------------------|------------------------------------------------|
| User / Pengaju              | Buat pengajuan + lihat milik sendiri           |
| Operator                    | Verifikasi data + minta klarifikasi            |
| Staf Aset (`uk_administrasi_aset`) | Verifikasi data + minta klarifikasi     |
| Kabag Aset                  | Review + Approve / Reject                      |
| Pimpinan / Admin            | Lihat semua                                    |

---

## 4. Modul Internal Operasional – Aset & Logistik

> Modul-modul di bawah ini **tidak** menangani alur pengajuan Mutasi Aset.  
> Mutasi Aset sudah dipindah ke Pengajuan dan Monitoring.

### A. Unit Kerja Administrasi Aset & Inventaris  
**Permission key:** `administrasi_aset`

| No | Nama Sub-Modul                      | Key Modul            | Status              | Keterangan |
|----|-------------------------------------|----------------------|---------------------|----------|
| 1  | Inventarisasi Aset                  | `aset`               | Sudah ada (perkuat) | Master data aset (kode unik, lokasi, penanggung jawab, kondisi, nilai, dll.) |
| 2  | Amortisasi / Penyusutan             | `amortisasi`         | Sudah ada           | Perhitungan nilai buku |
| 3  | Riwayat Pergerakan Aset             | `aset_history`       | **Baru** (view)     | Hanya melihat history (data diisi otomatis dari proses Mutasi Aset / perubahan manual) |
| 4  | Penghapusan Aset (Disposal)         | `disposal_aset`      | **Baru**            | Proses internal penghapusan aset |
| 5  | Rekonsiliasi & Reklasifikasi Aset   | `rekonsiliasi_aset`  | **Baru**            | Koreksi data & perubahan kategori/kondisi |
| 6  | Tindak Lanjut Temuan                | `temuan`             | Sudah ada           | Temuan audit terkait aset |

### B. Unit Kerja Logistik & Pelaporan  
**Permission key:** `logistik_pelaporan`

| No | Nama Sub-Modul                      | Key Modul              | Status              | Keterangan |
|----|-------------------------------------|------------------------|---------------------|----------|
| 1  | Tagihan / Invoice Sewa              | `invoice_sewa`         | Sudah ada           | |
| 2  | PKS & Jatuh Tempo                   | `pks`                  | Sudah ada           | |
| 3  | Memo Sewa Cabang                    | `memo_sewa_cabang`     | Sudah ada           | |
| 4  | Penerimaan Barang / Jasa            | `penerimaan_barang`    | **Baru**            | |
| 5  | Distribusi Barang / Jasa            | `distribusi_barang`    | **Baru**            | |
| 6  | Administrasi Pembayaran Tagihan     | `pembayaran_tagihan`   | **Baru**            | |
| 7  | Laporan Aset & Logistik             | Dashboard + DW         | Sudah ada (perkuat) | |

---

## 5. Perubahan Role

### Role Baru (Unit Kerja)

| ID  | nama (slug)              | Label                                              | Multi-user |
|-----|--------------------------|----------------------------------------------------|------------|
| 15  | `uk_administrasi_aset`   | Staf Unit Kerja Administrasi Aset & Inventaris     | Ya         |
| 16  | `uk_logistik`            | Staf Unit Kerja Logistik & Pelaporan               | Ya         |

### Role yang Dipertahankan
- `kabag_aset` (id 11) → akses semua modul internal Aset + approval Mutasi Aset + ticketing

### Role Lama
- `aset` (id 4) → jangan dipakai lagi untuk staf baru. User lama dipindahkan ke `uk_administrasi_aset` atau `uk_logistik`.

---

## 6. Database Changes (Ringkasan)

1. Pastikan tabel `unit_kerja` sudah ada (dari Fase 1).
2. Seed Unit Kerja:
   - UK-ADM-ASET (department_id = 2)
   - UK-LOGISTIK (department_id = 2)
3. Buat tabel `as_mutasi_aset`
4. Buat tabel `as_aset_histories` (jika belum ada)
5. Buat tabel lain yang diperlukan untuk modul internal baru (`disposal_aset`, `penerimaan_barang`, dll.)
6. Pastikan `as_aset` memiliki field `lokasi` dan `penanggung_jawab` yang bisa di-update otomatis.

---

## 7. Model yang Harus Dibuat / Diubah

**Baru:**
- `AsMutasiAset`
- `AsAsetHistory`
- `AsDisposalAset` (jika dibuat)
- Model untuk modul logistik baru

**Update:**
- `AsAset` → relasi ke histories & mutasi; method untuk update lokasi/penanggung jawab
- `User` → helper `isUkAdministrasiAset()`, `isUkLogistik()`
- Sidebar / menu → tampilkan **Mutasi Aset** di grup Pengajuan dan Monitoring

---

## 8. Urutan Eksekusi

1. Pastikan Fase 1 sudah stabil (unit_kerja + role framework).
2. Seed 2 Unit Kerja di bawah department_id = 2.
3. Tambah role 15 & 16 + permission matrix.
4. Update User Seeder (pindahkan user lama + multi-user).
5. Buat migration tabel `as_mutasi_aset` dan `as_aset_histories`.
6. Buat model + controller + service untuk **Mutasi Aset**.
7. Daftarkan menu **Mutasi Aset** di bawah Pengajuan dan Monitoring.
8. Implementasikan alur lengkap sesuai flowchart (pengajuan → verifikasi → approval → update otomatis + history + notifikasi).
9. Buat/perkuat modul internal di Operasional → Aset & Logistik (tanpa Mutasi Aset).
10. Update sidebar filtering sesuai role.
11. Testing end-to-end:
    - Pengajuan Mutasi Aset
    - Verifikasi oleh Staf/Operator
    - Approval oleh Kabag Aset
    - Cek data `as_aset` dan `as_aset_history` ter-update
    - Notifikasi terkirim

---

## 9. Acceptance Criteria

- [ ] Menu **Mutasi Aset** muncul di bawah **Pengajuan dan Monitoring** (sejajar Sistem Tiket)
- [ ] Pengaju dapat membuat pengajuan mutasi dan mendapat nomor `MUT-YYYYMMDD-XXXX`
- [ ] Operator / Staf Aset dapat verifikasi dan minta klarifikasi
- [ ] Kabag Aset dapat Approve / Reject
- [ ] Saat disetujui: `as_aset` ter-update otomatis + history tercatat
- [ ] Notifikasi terkirim ke pengaju dan penerima
- [ ] Modul internal di Operasional → Aset & Logistik **tidak** berisi form pengajuan Mutasi Aset
- [ ] Role `uk_administrasi_aset` dan `uk_logistik` aktif (multi-user)
- [ ] `php artisan migrate` dan `db:seed` sukses

---

## 10. Ringkasan Nama Modul Final (Fase 2)

```
PENGAJUAN DAN MONITORING
├── Dashboard                      ← sesuai role
├── Sistem Tiket
│   └── jenis_pengajuan: Permintaan | Permasalahan
└── Mutasi Aset                    ← Sub-modul khusus tracking mutasi
    └── Alur: Pengajuan → Verifikasi → Approval Kabag → Update Aset + History

OPERASIONAL → Aset & Logistik
├── Unit Kerja Administrasi Aset & Inventaris
│   ├── Inventarisasi Aset
│   ├── Amortisasi / Penyusutan
│   ├── Riwayat Pergerakan Aset (view)
│   ├── Penghapusan Aset (Disposal)
│   ├── Rekonsiliasi & Reklasifikasi
│   └── Tindak Lanjut Temuan
│
└── Unit Kerja Logistik & Pelaporan
    ├── Invoice Sewa
    ├── PKS & Jatuh Tempo
    ├── Memo Sewa Cabang
    ├── Penerimaan Barang / Jasa
    ├── Distribusi Barang / Jasa
    ├── Administrasi Pembayaran Tagihan
    └── Laporan
```

---

**Catatan untuk Agent:**
- Mutasi Aset adalah **sub-modul layanan**, bukan modul internal operasional.
- Jangan taruh form pengajuan Mutasi Aset di dalam menu Operasional → Aset & Logistik.
- Saat approval, wajib ada transaksi database yang meng-update `as_aset` dan menulis `as_aset_histories` dalam satu DB::transaction.
- Gunakan pola nomor yang konsisten: `MUT-YYYYMMDD-XXXX`.
