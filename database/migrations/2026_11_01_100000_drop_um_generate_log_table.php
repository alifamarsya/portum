<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus tabel um_generate_log yang sudah yatim: model UmGenerateLog
     * tidak dirujuk di mana pun dan tabel ini tidak pernah dibaca/ditulis
     * oleh kode mana pun (hanya dibuat oleh migration awal).
     */
    public function up(): void
    {
        Schema::dropIfExists('um_generate_log');
    }

    /**
     * Kembalikan tabel persis seperti dibuat migration
     * 2026_01_01_000006_create_umum_rt_tables.php.
     */
    public function down(): void
    {
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
};
