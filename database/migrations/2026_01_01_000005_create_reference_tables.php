<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panduan', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori')->default('Umum');
            $table->longText('konten')->nullable();
            $table->integer('urutan')->default(0);
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('risalah_rapat', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->string('judul');
            $table->date('tanggal');
            $table->string('waktu')->nullable();
            $table->string('tempat')->nullable();
            $table->string('pemimpin')->nullable();
            $table->text('peserta')->nullable();
            $table->text('agenda')->nullable();
            $table->text('pembahasan')->nullable();
            $table->text('keputusan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('lampiran')->nullable();
            $table->string('dibuat_oleh')->nullable();
            $table->timestamps();
        });

        Schema::create('ref_akun', function (Blueprint $table) {
            $table->id();
            $table->string('nama_beban');
            $table->string('rekening_debet')->nullable();
            $table->text('contoh_keterangan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_akun');
        Schema::dropIfExists('risalah_rapat');
        Schema::dropIfExists('panduan');
    }
};
