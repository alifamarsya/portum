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
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->enum('jenis_pengajuan', ['Permintaan', 'Permasalahan'])->default('Permintaan')->after('name');
            $table->integer('sla_resolution_hours')->default(24)->after('jenis_pengajuan');
            $table->boolean('is_active')->default(true)->after('sla_resolution_hours');
            $table->foreignId('department_id')->nullable()->after('is_active')->constrained('internal_departments')->nullOnDelete();
            $table->integer('sort_order')->default(0)->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'jenis_pengajuan',
                'sla_resolution_hours',
                'is_active',
                'department_id',
                'sort_order',
            ]);
        });
    }
};
