<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fase 2: Bagian Aset/Inventaris & Logistik
     * Tabel-tabel:
     * - as_aset_histories (Riwayat Pergerakan Aset)
     * - as_mutasi_aset (Mutasi Aset)
     * - as_disposal_aset (Penghapusan Aset / Disposal)
     * - as_rekonsiliasi_aset (Rekonsiliasi & Reklasifikasi Aset)
     * - as_penerimaan_barang (Penerimaan Barang/Jasa)
     * - as_distribusi_barang (Distribusi Barang/Jasa)
     * - as_pembayaran_tagihan (Administrasi Pembayaran Tagihan)
     */
    public function up(): void
    {
        // 1. Riwayat Pergerakan Aset (Audit Trail Perubahan Aset)
        if (!Schema::hasTable('as_aset_histories')) {
            Schema::create('as_aset_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aset_id')->constrained('as_aset')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('field_changed'); // lokasi, penanggung_jawab, kondisi, status, dll
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamp('changed_at')->useCurrent();
                $table->timestamps();
            });
        }

        // 2. Mutasi Aset (Permohonan pindah lokasi/user + approval)
        if (!Schema::hasTable('as_mutasi_aset')) {
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
                $table->string('approval_status')->default('Diajukan');
                $table->timestamp('approved_at')->nullable();
                $table->text('keterangan')->nullable();
                $table->string('dokumen')->nullable();
                $table->timestamps();
            });
        }

        // 3. Penghapusan Aset / Disposal (Proses penghapusan dari inventaris)
        if (!Schema::hasTable('as_disposal_aset')) {
            Schema::create('as_disposal_aset', function (Blueprint $table) {
                $table->id();
                $table->string('no_disposal')->unique();
                $table->foreignId('aset_id')->constrained('as_aset')->cascadeOnDelete();
                $table->date('tanggal_pengajuan')->nullable();
                $table->string('alasan_penghapusan')->nullable();
                $table->string('metode')->nullable(); // Dihibahkan, Dijual, Dimusnahkan, Dihapusbukukan
                $table->decimal('nilai_buku_terakhir', 18, 2)->nullable();
                $table->string('status')->default('Diajukan'); // Diajukan, Disetujui, Ditolak, Selesai
                $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('approval_status')->default('Diajukan');
                $table->timestamp('approved_at')->nullable();
                $table->text('keterangan')->nullable();
                $table->string('dokumen')->nullable();
                $table->timestamps();
            });
        }

        // 4. Rekonsiliasi & Reklasifikasi Aset
        if (!Schema::hasTable('as_rekonsiliasi_aset')) {
            Schema::create('as_rekonsiliasi_aset', function (Blueprint $table) {
                $table->id();
                $table->string('no_rekonsiliasi')->unique();
                $table->string('periode')->nullable(); // e.g. Semester I 2026
                $table->date('tanggal')->nullable();
                $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
                $table->string('jenis')->default('Rekonsiliasi'); // Rekonsiliasi, Reklasifikasi
                $table->string('kategori_awal')->nullable();
                $table->string('kategori_baru')->nullable();
                $table->string('kondisi_awal')->nullable();
                $table->string('kondisi_baru')->nullable();
                $table->text('hasil_rekonsiliasi')->nullable();
                $table->string('status')->default('Selesai');
                $table->string('petugas')->nullable();
                $table->string('dokumen')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 5. Penerimaan Barang / Jasa (Logistik)
        if (!Schema::hasTable('as_penerimaan_barang')) {
            Schema::create('as_penerimaan_barang', function (Blueprint $table) {
                $table->id();
                $table->string('no_penerimaan')->unique();
                $table->date('tanggal')->nullable();
                $table->string('vendor')->nullable();
                $table->string('nama_barang');
                $table->integer('jumlah')->default(1);
                $table->string('satuan')->nullable()->default('Unit');
                $table->string('kondisi')->default('Baik'); // Baik, Rusak, Tidak Sesuai
                $table->string('penerima')->nullable();
                $table->string('status')->default('Diterima'); // Diterima, Diperiksa, Disimpan
                $table->string('dokumen')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 6. Distribusi Barang / Jasa (Logistik)
        if (!Schema::hasTable('as_distribusi_barang')) {
            Schema::create('as_distribusi_barang', function (Blueprint $table) {
                $table->id();
                $table->string('no_distribusi')->unique();
                $table->date('tanggal')->nullable();
                $table->string('nama_barang');
                $table->integer('jumlah')->default(1);
                $table->string('satuan')->nullable()->default('Unit');
                $table->string('tujuan_unit')->nullable(); // Divisi / Kantor Cabang
                $table->string('penerima')->nullable();
                $table->string('status')->default('Terkirim'); // Disiapkan, Terkirim, Diterima
                $table->string('dokumen')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 7. Administrasi Pembayaran Tagihan (Logistik)
        if (!Schema::hasTable('as_pembayaran_tagihan')) {
            Schema::create('as_pembayaran_tagihan', function (Blueprint $table) {
                $table->id();
                $table->string('no_tagihan')->unique();
                $table->date('tanggal_tagihan')->nullable();
                $table->date('tanggal_bayar')->nullable();
                $table->string('vendor')->nullable();
                $table->text('uraian')->nullable();
                $table->decimal('nilai', 18, 2)->default(0);
                $table->string('status')->default('Belum Bayar'); // Belum Bayar, Proses, Lunas
                $table->string('no_rekening')->nullable();
                $table->string('dokumen')->nullable();
                $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('approval_status')->default('Diajukan');
                $table->timestamp('approved_at')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('as_pembayaran_tagihan');
        Schema::dropIfExists('as_distribusi_barang');
        Schema::dropIfExists('as_penerimaan_barang');
        Schema::dropIfExists('as_rekonsiliasi_aset');
        Schema::dropIfExists('as_disposal_aset');
        Schema::dropIfExists('as_mutasi_aset');
        Schema::dropIfExists('as_aset_histories');
    }
};
