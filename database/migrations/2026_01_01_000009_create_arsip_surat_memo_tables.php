<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sr_surat_masuk', 'sr_surat_keluar', 'sr_memo_masuk', 'sr_memo_keluar'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->integer('nomor_agenda')->nullable();
                $t->string('no_surat')->nullable();
                $t->string('pengirim')->nullable();
                $t->string('perihal');
                $t->date('tanggal');
                $t->string('penerima')->nullable();
                $t->string('lokasi_arsip')->nullable();
                $t->string('lampiran')->nullable();
                $t->string('dibuat_oleh')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['sr_memo_keluar', 'sr_memo_masuk', 'sr_surat_keluar', 'sr_surat_masuk'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
