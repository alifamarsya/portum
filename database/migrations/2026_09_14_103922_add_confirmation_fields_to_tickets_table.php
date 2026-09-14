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
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('sla_resolution_status')
                ->comment('Waktu saat staf menyelesaikan pekerjaan dan mengubah status menjadi Selesai');
            $table->timestamp('confirmation_deadline')->nullable()->after('completed_at')
                ->comment('Batas waktu konfirmasi pemohon (2x24 jam kerja dari status Selesai)');
            $table->timestamp('closed_at')->nullable()->after('confirmation_deadline')
                ->comment('Waktu penutupan tiket (baik konfirmasi pemohon atau auto-close)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['completed_at', 'confirmation_deadline', 'closed_at']);
        });
    }
};
