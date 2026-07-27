<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pg_memo_internal', function (Blueprint $table) {
            $table->id();
            $table->string('no_memo')->nullable();
            $table->string('dari_unit')->nullable();
            $table->string('ke_unit')->nullable();
            $table->string('perihal')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('jenis')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Masuk');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_penawaran', function (Blueprint $table) {
            $table->id();
            $table->string('no_penawaran')->nullable();
            $table->string('vendor');
            $table->text('barang_jasa')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->date('tanggal')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Diterima');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_negosiasi', function (Blueprint $table) {
            $table->id();
            $table->string('no_berita_acara')->nullable();
            $table->string('vendor');
            $table->text('barang_jasa')->nullable();
            $table->decimal('nilai_awal', 18, 2)->default(0);
            $table->decimal('nilai_nego', 18, 2)->default(0);
            $table->date('tanggal')->nullable();
            $table->text('hasil')->nullable();
            $table->string('dokumen')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_draft_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('jenis')->default('PKS');
            $table->string('no_dokumen')->nullable();
            $table->string('judul');
            $table->string('vendor')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status')->default('Draft');
            $table->string('file')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_spk', function (Blueprint $table) {
            $table->id();
            $table->string('no_spk')->nullable();
            $table->string('vendor');
            $table->text('pekerjaan')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->date('tanggal_terbit')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Berjalan');
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status')->default('Diajukan');
            $table->timestamp('approved_at')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_reminder', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori')->nullable();
            $table->date('tanggal_jatuh_tempo');
            $table->string('status')->default('Aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pg_reminder');
        Schema::dropIfExists('pg_spk');
        Schema::dropIfExists('pg_draft_dokumen');
        Schema::dropIfExists('pg_negosiasi');
        Schema::dropIfExists('pg_penawaran');
        Schema::dropIfExists('pg_memo_internal');
    }
};
