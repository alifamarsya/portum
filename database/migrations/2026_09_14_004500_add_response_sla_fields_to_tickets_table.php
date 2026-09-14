<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('sla_response_start_at')->nullable()->after('status')
                ->comment('Waktu mulai perhitungan SLA (pukul 08:00 hari kerja jika submit di luar jam kerja)');
            $table->timestamp('sla_response_due_at')->nullable()->after('sla_response_start_at')
                ->comment('Batas akhir respon Operator (maksimal 2 jam kerja sejak start)');
            $table->timestamp('verified_at')->nullable()->after('sla_response_due_at')
                ->comment('Waktu tiket diverifikasi oleh Operator');
            $table->foreignId('verified_by')->nullable()->after('verified_at')
                ->constrained('users')->nullOnDelete();
            $table->integer('sla_response_time_minutes')->nullable()->after('verified_by')
                ->comment('Durasi respon kerja dalam menit');
            $table->string('sla_response_status')->default('Menunggu')->after('sla_response_time_minutes')
                ->comment('Menunggu | Tepat Waktu | Terlambat');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'sla_response_start_at',
                'sla_response_due_at',
                'verified_at',
                'verified_by',
                'sla_response_time_minutes',
                'sla_response_status',
            ]);
        });
    }
};
