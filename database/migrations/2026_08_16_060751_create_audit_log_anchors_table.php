<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('audit_log_anchors', function (Blueprint $table) {
        $table->id();
        $table->string('root_hash', 66);      // 0x + 64 karakter hex
        $table->string('tx_hash', 66)->nullable();
        $table->unsignedBigInteger('block_number')->nullable();
        $table->date('periode_awal');
        $table->date('periode_akhir');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_log_anchors');
    }
};
