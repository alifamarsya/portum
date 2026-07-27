<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('as_invoice_sewa', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->nullable();
            $table->string('vendor')->nullable();
            $table->string('jenis_sewa')->nullable();
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->date('jatuh_tempo')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Belum Bayar');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('as_aset', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset')->nullable();
            $table->string('nama_aset');
            $table->string('kategori')->nullable();
            $table->string('lokasi')->nullable();
            $table->date('tanggal_perolehan')->nullable();
            $table->decimal('nilai_perolehan', 18, 2)->default(0);
            $table->integer('umur_ekonomis')->default(48);
            $table->string('kondisi')->default('Baik');
            $table->string('penanggung_jawab')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('as_amortisasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_biaya');
            $table->decimal('nilai_perolehan', 18, 2)->default(0);
            $table->date('tanggal_mulai')->nullable();
            $table->integer('umur_bulan')->default(12);
            $table->decimal('nilai_per_bulan', 18, 2)->default(0);
            $table->decimal('akumulasi', 18, 2)->default(0);
            $table->decimal('nilai_buku', 18, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('as_pks', function (Blueprint $table) {
            $table->id();
            $table->string('no_pks')->nullable();
            $table->string('judul');
            $table->string('vendor')->nullable();
            $table->string('div_owner')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('jatuh_tempo')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Aktif');
            $table->boolean('memo_dibuat')->default(false);
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status')->default('Diajukan');
            $table->timestamp('approved_at')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('as_memo_sewa_cabang', function (Blueprint $table) {
            $table->id();
            $table->string('no_memo')->nullable();
            $table->string('cabang')->nullable();
            $table->string('jenis')->nullable();
            $table->date('tanggal')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->string('dokumen')->nullable();
            $table->string('status_persetujuan')->default('Diajukan');
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('as_temuan', function (Blueprint $table) {
            $table->id();
            $table->string('no_temuan')->nullable();
            $table->string('sumber')->nullable();
            $table->text('uraian')->nullable();
            $table->date('tanggal_temuan')->nullable();
            $table->date('batas_tindak_lanjut')->nullable();
            $table->string('status')->default('Open');
            $table->string('penanggung_jawab')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('dokumen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('as_temuan');
        Schema::dropIfExists('as_memo_sewa_cabang');
        Schema::dropIfExists('as_pks');
        Schema::dropIfExists('as_amortisasi');
        Schema::dropIfExists('as_aset');
        Schema::dropIfExists('as_invoice_sewa');
    }
};
