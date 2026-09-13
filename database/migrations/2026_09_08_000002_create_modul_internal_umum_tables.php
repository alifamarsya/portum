<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel untuk modul internal baru:
     * - um_fasilitas_kantor
     * - um_pemeliharaan_gedung
     * - um_checklist_kebersihan
     * - um_k3_insiden
     * - dk_arsip_dokumen
     * - dk_dokumen_legalitas
     */
    public function up(): void
    {
        // ── 1. Master Fasilitas Kantor ──────────────────────────────────────────────
        if (!Schema::hasTable('um_fasilitas_kantor')) {
            Schema::create('um_fasilitas_kantor', function (Blueprint $table) {
                $table->id();
                $table->string('nama_fasilitas');
                $table->string('kode')->nullable();
                $table->string('kategori')->nullable();
                $table->string('lokasi')->nullable();
                $table->string('kondisi')->nullable();
                $table->date('tanggal_perolehan')->nullable();
                $table->string('penanggung_jawab')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // ── 2. Pemeliharaan Gedung & Utilitas ──────────────────────────────────────
        if (!Schema::hasTable('um_pemeliharaan_gedung')) {
            Schema::create('um_pemeliharaan_gedung', function (Blueprint $table) {
                $table->id();
                $table->string('jenis_pekerjaan');
                $table->text('uraian');
                $table->string('lokasi')->nullable();
                $table->date('tanggal_rencana')->nullable();
                $table->date('tanggal_realisasi')->nullable();
                $table->string('vendor')->nullable();
                $table->decimal('biaya', 15, 2)->nullable();
                $table->string('status')->nullable()->default('Dijadwalkan');
                $table->string('dokumen')->nullable(); // file path
                // maker-checker fields
                $table->string('dibuat_oleh')->nullable();
                $table->string('disetujui_oleh')->nullable();
                $table->string('status_approval')->nullable();
                $table->timestamps();
            });
        }

        // ── 3. Checklist Kebersihan & Keamanan ─────────────────────────────────────
        if (!Schema::hasTable('um_checklist_kebersihan')) {
            Schema::create('um_checklist_kebersihan', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('jenis');
                $table->string('area')->nullable();
                $table->string('petugas')->nullable();
                $table->string('status')->nullable()->default('Belum Dilakukan');
                $table->text('catatan')->nullable();
                $table->string('dibuat_oleh')->nullable();
                $table->timestamps();
            });
        }

        // ── 4. Catatan K3 & Lingkungan ─────────────────────────────────────────────
        if (!Schema::hasTable('um_k3_insiden')) {
            Schema::create('um_k3_insiden', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('jenis');
                $table->string('lokasi')->nullable();
                $table->text('uraian');
                $table->string('korban')->nullable();
                $table->text('tindak_lanjut')->nullable();
                $table->string('status')->nullable()->default('Open');
                $table->string('dokumen')->nullable(); // file path
                $table->string('dibuat_oleh')->nullable();
                $table->timestamps();
            });
        }

        // ── 5. Master Arsip Dokumen ─────────────────────────────────────────────────
        if (!Schema::hasTable('dk_arsip_dokumen')) {
            Schema::create('dk_arsip_dokumen', function (Blueprint $table) {
                $table->id();
                $table->string('kode_arsip')->nullable();
                $table->string('judul');
                $table->string('jenis')->nullable();
                $table->string('kategori')->nullable();
                $table->date('tanggal_dokumen')->nullable();
                $table->string('lokasi_arsip')->nullable();
                $table->unsignedInteger('masa_retensi')->nullable()->comment('dalam tahun');
                $table->string('status_arsip')->nullable()->default('Aktif');
                $table->text('keterangan')->nullable();
                $table->string('lampiran')->nullable(); // file path
                $table->string('dibuat_oleh')->nullable();
                $table->timestamps();
            });
        }

        // ── 6. Dokumen Legalitas ────────────────────────────────────────────────────
        if (!Schema::hasTable('dk_dokumen_legalitas')) {
            Schema::create('dk_dokumen_legalitas', function (Blueprint $table) {
                $table->id();
                $table->string('nama_dokumen');
                $table->string('jenis')->nullable();
                $table->string('no_dokumen')->nullable();
                $table->string('penerbit')->nullable();
                $table->date('tanggal_terbit')->nullable();
                $table->date('tanggal_berlaku')->nullable();
                $table->string('status')->nullable()->default('Aktif');
                $table->text('keterangan')->nullable();
                $table->string('dokumen')->nullable(); // file path
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dk_dokumen_legalitas');
        Schema::dropIfExists('dk_arsip_dokumen');
        Schema::dropIfExists('um_k3_insiden');
        Schema::dropIfExists('um_checklist_kebersihan');
        Schema::dropIfExists('um_pemeliharaan_gedung');
        Schema::dropIfExists('um_fasilitas_kantor');
    }
};
