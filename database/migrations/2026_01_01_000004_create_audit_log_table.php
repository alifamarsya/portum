<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabel ini memenuhi CPMK Teknologi Blockchain: prev_hash + hash
    // membentuk rantai hash sederhana pada audit trail, sehingga
    // perubahan pada baris lama akan merusak rantai dan bisa dideteksi
    // lewat proses verifikasi ulang (recompute hash tiap baris).
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('username')->nullable();
            $table->string('aksi');
            $table->string('modul')->nullable();
            $table->string('entitas')->nullable();
            $table->string('entitas_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('prev_hash', 64)->nullable();
            $table->string('hash', 64);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
