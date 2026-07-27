# Checklist Pemenuhan CPMK — Project Portum

Centang satu-satu. Kolom "Cara cek" adalah perintah/langkah nyata, bukan
cuma "menurut saya sudah jalan" — kalau langkahnya belum dicoba, jangan
dicentang dulu.

---

## 1. Manajemen Proyek

Ini murni proses kerja, tidak ada kode yang "menyelesaikan" MK ini —
buktinya ada di cara kalian bekerja, bukan di file.

- [ ] Repo Git dibuat, 3 anggota semua sudah bisa push/pull
- [ ] Ada strategi branch yang disepakati (mis. 1 branch per modul/fitur)
- [ ] Ada papan project (GitHub Projects/Issues) berisi pembagian tugas
      sesuai pembagian yang sudah dibahas (Orang 1/2/3 per modul + fitur CPMK)
- [ ] Risk register tertulis (minimal: risiko migrasi data, risiko
      downtime cutover, risiko field lupa divalidasi — 1 paragraf per risiko)
- [ ] Commit history menunjukkan kontribusi 3 orang (bukan cuma 1 orang commit,
      2 orang lain numpang nama) — cek dengan `git shortlog -sn`
- [ ] Logbook harian per orang mulai diisi (format seperti kating, tapi
      dibagi sesuai fokus modul masing-masing)
- [ ] Ada dokumen retrospective di akhir (bandingkan rencana awal vs
      hasil aktual)

---

## 2. Teknologi Blockchain

| Item | Sudah ada di kode? | Cara cek |
|---|---|---|
| Hash chain di `audit_log` (`prev_hash` + `hash`) | ✅ | `php artisan tinker` → buat data baru → `AuditLog::latest()->first()` → `hash` harus terisi |
| Command verifikasi rantai | ✅ `audit:verify-chain` | Jalankan, harus keluar "valid" dalam kondisi normal |
| Deteksi manipulasi manual | ✅ | Edit 1 baris `audit_log` langsung lewat SQL → jalankan `audit:verify-chain` lagi → harus keluar "anomali" |
| Lock anti-race-condition (`audit_chain_lock`) | ✅ | Sudah diperbaiki minggu ini |
| **Uji race condition nyata** | ⬜ belum dicoba | Jalankan 5 penulisan hampir bersamaan (lihat Bagian B.3 panduan lanjutan) → rantai harus tetap valid |
| Maker-Checker (checker ≠ maker) | ✅ `ApprovalPolicy` | Login user A, buat data → coba approve pakai user A yang sama → harus 403 |
| Maker-Checker approve oleh user lain | ✅ | Login user B → approve data user A → harus berhasil |
| Approve/reject tercatat di audit log | ✅ | Cek Audit Log setelah approve/reject, harus ada baris aksi `APPROVE`/`REJECT` |

**Belum genap kalau:** Bagian "Uji race condition nyata" belum dicoba —
kodenya sudah diperbaiki tapi belum divalidasi jalan di kondisi nyata.

---

## 3. Sistem Komputasi Terdistribusi

| Item | Sudah ada di kode? | Cara cek |
|---|---|---|
| Queue job terpisah dari request web | ✅ 2 job (`CheckJatuhTempoReminderJob`, `GenerateLaporanBiayaBulananJob`) | `php artisan tinker` → dispatch salah satu job → cek `storage/logs/laravel.log` muncul entrinya |
| Broker Redis (bukan tabel database) | ✅ | `redis-cli ping` harus balas `PONG`, `.env` `QUEUE_CONNECTION=redis` |
| Scheduler otomatis | ✅ `routes/console.php` | `php artisan schedule:list` → harus tampil 3 jadwal (reminder, ETL, verify-chain) |
| Retry otomatis kalau job gagal | ✅ (`$tries = 3` di job laporan) | Belum ada cara mudah mengujinya tanpa sengaja bikin error — opsional |
| Log bukti proses (`[distributed-proof]`) | ✅ | Cek `storage/logs/laravel.log` setelah dispatch job, harus ada `hostname` + `pid` |
| **Uji konkurensi 2 worker (1 mesin)** | ⬜ belum dicoba | 2 terminal `queue:work` bersamaan, dispatch 20 job, cek log ada ≥2 PID berbeda |
| **Uji lintas mesin (≥2 komputer)** | ⬜ belum bisa dicoba | Butuh 2 laptop/VM — baru relevan pas tahap deploy, bukan sekarang |

**Belum genap kalau:** Baru sebatas kode siap, dua baris terakhir di
tabel di atas ("Uji konkurensi" dan "Uji lintas mesin") **belum benar-benar
dijalankan**. Kalau di laporan ditulis "sudah terdistribusi" tanpa dua
bukti ini, itu klaim yang lebih besar dari yang sudah teruji.

---

## 4. Data Warehouse

| Item | Sudah ada di kode? | Cara cek |
|---|---|---|
| Star schema (3 fact + 4 dim table) | ✅ | Cek tabel `fact_biaya_bulanan`, `fact_amortisasi_aset`, `fact_pengadaan`, `dim_waktu`, `dim_kategori`, `dim_vendor`, `dim_unit_kerja` ada di database |
| Command ETL | ✅ `dw:etl` (bug timestamp sudah diperbaiki) | `php artisan dw:etl --bulan=<n> --tahun=<yyyy>` → harus keluar "ETL selesai." tanpa error |
| ETL terjadwal otomatis | ✅ | `php artisan schedule:list` → cek ada jadwal `dw:etl` tanggal 1 tiap bulan |
| Dashboard analitik (bukan cuma tabel OLTP) | ✅ halaman `/analitik` | Buka menu **Analitik & Tren**, 4 chart harus terisi |
| **Akurasi data ETL** | ⬜ belum diverifikasi | Bandingkan manual: `UmBiayaHarian::whereMonth(...)->sum('jumlah')` vs `FactBiayaBulanan::...->sum('total_biaya')` — angkanya harus sama persis (lihat Bagian C panduan lanjutan) |

**Belum genap kalau:** ETL sudah jalan tanpa error, tapi **belum pernah
dicek angkanya benar** — "tidak error" ≠ "hasilnya akurat". Ini yang
paling penting divalidasi sebelum diklaim selesai.

---

## 5. Kerja Praktek

Sama seperti Manajemen Proyek — muara dari kerja nyata, bukan kode.

- [ ] Bukti komunikasi dengan mentor/pembimbing lapangan (notulen,
      screenshot chat, atau catatan bimbingan)
- [ ] Adaptasi terhadap standar kerja institusi (mis. penerapan RBAC dan
      audit trail yang memang relevan untuk konteks perbankan — ini sudah
      ada di sistemnya, tinggal dituliskan sebagai bentuk adaptasi)
- [ ] Laporan disusun sistematis mengikuti format kampus

---

## Ringkasan prioritas

Urutan yang paling masuk akal untuk dikerjakan/diuji sekarang:

1. **Selesaikan dulu Bagian B (Blockchain)** yang sedang berjalan — tinggal
   B.2, B.3, B.4
2. **Bagian C (Data Warehouse)** — verifikasi akurasi angka ETL, karena
   ini belum pernah dicek sama sekali
3. **Bagian D (Sistem Terdistribusi)** — minimal uji 2 worker di 1 mesin
4. Manajemen Proyek & Kerja Praktek berjalan paralel selama proses di atas
   (mulai isi logbook & papan GitHub dari sekarang, jangan ditunda sampai
   semua kode selesai)

Kolom "⬜ belum dicoba/diverifikasi" di atas adalah yang paling menentukan
nilai kalian — kode yang benar tapi belum pernah dibuktikan jalan itu
risikonya sama dengan kode yang salah kalau ditanya dosen penguji.
