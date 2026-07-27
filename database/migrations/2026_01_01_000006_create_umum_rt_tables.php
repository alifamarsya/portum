<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Kolom maker_id/checker_id/approval_status di bawah ini
    // adalah bagian dari alur Maker-Checker (CPMK Blockchain: akuntabilitas
    // & integritas transaksi) dan akan ditulis ke audit_log berhash-chain.
    public function up(): void
    {
        Schema::create('um_kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('no_polisi');
            $table->string('jenis')->nullable();
            $table->string('merk')->nullable();
            $table->string('tahun')->nullable();
            $table->string('peruntukan')->nullable();
            $table->string('driver')->nullable();
            $table->string('status')->default('Aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('um_biaya_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kategori')->default('BBM');
            $table->string('kendaraan')->nullable();
            $table->string('nama_beban')->nullable();
            $table->string('rekening_debet')->nullable();
            $table->string('rekening_kredit')->nullable();
            $table->text('uraian')->nullable();
            $table->decimal('jumlah', 18, 2)->default(0);
            $table->string('no_nota')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Draft');
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status')->default('Diajukan');
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->string('dibuat_oleh')->nullable();
            $table->timestamps();
        });

        Schema::create('um_permintaan_cabang', function (Blueprint $table) {
            $table->id();
            $table->string('no_permintaan')->nullable();
            $table->date('tanggal');
            $table->string('unit_kerja')->nullable();
            $table->string('jenis')->default('ATK');
            $table->text('uraian')->nullable();
            $table->string('jumlah')->nullable();
            $table->string('satuan')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Diajukan');
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status')->default('Diajukan');
            $table->timestamp('approved_at')->nullable();
            $table->string('petugas')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('um_generate_log', function (Blueprint $table) {
            $table->id();
            $table->date('periode_awal')->nullable();
            $table->date('periode_akhir')->nullable();
            $table->string('kategori')->nullable();
            $table->integer('jumlah_item')->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->string('dibuat_oleh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('um_generate_log');
        Schema::dropIfExists('um_permintaan_cabang');
        Schema::dropIfExists('um_biaya_harian');
        Schema::dropIfExists('um_kendaraan');
    }
};
