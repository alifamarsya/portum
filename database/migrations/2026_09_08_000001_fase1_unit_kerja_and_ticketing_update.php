<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fase 1 patch: tambah field jenis_pengajuan di tickets
     * (unit_kerja table + unit_kerja_id di users & tickets sudah ada sebelumnya)
     */
    public function up(): void
    {
        // Tambah jenis_pengajuan jika belum ada
        if (!Schema::hasColumn('tickets', 'jenis_pengajuan')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('jenis_pengajuan')->nullable()->after('priority')
                    ->comment('Permintaan | Permasalahan — diisi oleh Operator saat verifikasi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('jenis_pengajuan');
        });
    }
};
