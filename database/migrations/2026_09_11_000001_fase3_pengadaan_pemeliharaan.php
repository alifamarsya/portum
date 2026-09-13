<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fase 3: Bagian Pengadaan & Pemeliharaan Aset & Inventaris
     * Tabel-tabel baru:
     * - pm_jadwal_pemeliharaan  (Jadwal Pemeliharaan Rutin)
     * - pm_monitoring_kondisi   (Monitoring Kondisi Fisik Aset)
     * - pm_pengawasan_penggunaan (Pengawasan Penggunaan Aset)
     * - pm_tindak_lanjut_perbaikan (Tindak Lanjut Perbaikan)
     * - pm_perencanaan_kebutuhan  (Perencanaan Kebutuhan Barang/Jasa)
     */
    public function up(): void
    {
        // 1. Jadwal Pemeliharaan Rutin
        if (!Schema::hasTable('pm_jadwal_pemeliharaan')) {
            Schema::create('pm_jadwal_pemeliharaan', function (Blueprint $table) {
                $table->id();
                $table->string('no_jadwal')->nullable();
                $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
                $table->string('nama_aset')->nullable(); // cache nama aset jika aset dihapus
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
        }

        // 2. Monitoring Kondisi Fisik Aset
        if (!Schema::hasTable('pm_monitoring_kondisi')) {
            Schema::create('pm_monitoring_kondisi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
                $table->string('nama_aset')->nullable();
                $table->date('tanggal_inspeksi')->nullable();
                $table->string('kondisi')->nullable(); // Baik, Rusak Ringan, Rusak Berat
                $table->text('temuan')->nullable();
                $table->text('rekomendasi')->nullable();
                $table->string('petugas')->nullable();
                $table->string('status')->default('Selesai'); // Selesai, Perlu Tindak Lanjut
                $table->string('dokumen')->nullable();
                $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 3. Pengawasan Penggunaan Aset
        if (!Schema::hasTable('pm_pengawasan_penggunaan')) {
            Schema::create('pm_pengawasan_penggunaan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
                $table->string('nama_aset')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('pengguna')->nullable(); // Unit/Divisi/Orang yang menggunakan
                $table->text('uraian_penggunaan')->nullable();
                $table->string('kesesuaian')->nullable(); // Sesuai / Tidak Sesuai / Perlu Evaluasi
                $table->text('catatan')->nullable();
                $table->string('petugas')->nullable();
                $table->string('dokumen')->nullable();
                $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Tindak Lanjut Perbaikan
        if (!Schema::hasTable('pm_tindak_lanjut_perbaikan')) {
            Schema::create('pm_tindak_lanjut_perbaikan', function (Blueprint $table) {
                $table->id();
                $table->string('no_tindak_lanjut')->nullable();
                $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
                $table->string('nama_aset')->nullable();
                $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete(); // opsional, dari tiket
                $table->string('sumber')->nullable(); // Tiket / Inspeksi Mandiri / Pengawasan
                $table->text('uraian_kerusakan')->nullable();
                $table->date('tanggal_laporan')->nullable();
                $table->date('tanggal_perbaikan')->nullable();
                $table->string('teknisi')->nullable();
                $table->text('hasil_perbaikan')->nullable();
                $table->string('status')->default('Dilaporkan'); // Dilaporkan, Dalam Proses, Selesai, Ditangguhkan
                $table->string('dokumen')->nullable();
                $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 5. Perencanaan Kebutuhan Barang/Jasa/Aset (opsional Pengadaan)
        if (!Schema::hasTable('pm_perencanaan_kebutuhan')) {
            Schema::create('pm_perencanaan_kebutuhan', function (Blueprint $table) {
                $table->id();
                $table->string('no_rencana')->nullable();
                $table->string('periode')->nullable(); // cth: Q1 2026 / Semester I 2026
                $table->string('jenis_kebutuhan')->nullable(); // Barang / Jasa / Aset
                $table->string('nama_item');
                $table->integer('jumlah')->default(1);
                $table->string('satuan')->nullable()->default('Unit');
                $table->text('spesifikasi')->nullable();
                $table->decimal('estimasi_harga', 18, 2)->nullable();
                $table->string('prioritas')->default('Normal'); // Darurat, Tinggi, Normal, Rendah
                $table->string('status')->default('Draft'); // Draft, Diajukan, Disetujui, Ditolak, Selesai
                $table->text('keterangan')->nullable();
                $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_perencanaan_kebutuhan');
        Schema::dropIfExists('pm_tindak_lanjut_perbaikan');
        Schema::dropIfExists('pm_pengawasan_penggunaan');
        Schema::dropIfExists('pm_monitoring_kondisi');
        Schema::dropIfExists('pm_jadwal_pemeliharaan');
    }
};
