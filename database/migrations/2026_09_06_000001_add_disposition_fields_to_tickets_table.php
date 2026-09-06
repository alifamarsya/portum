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
            $table->foreignId('assigned_to')->nullable()->after('department_id')->constrained('users')->nullOnDelete();
            $table->foreignId('disposed_by')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
            $table->text('disposition_notes')->nullable()->after('description');
            $table->timestamp('disposed_at')->nullable()->after('disposition_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['disposed_by']);
            $table->dropColumn(['assigned_to', 'disposed_by', 'disposition_notes', 'disposed_at']);
        });
    }
};
