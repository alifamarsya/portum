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
        if (!Schema::hasTable('unit_kerja')) {
            Schema::create('unit_kerja', function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->nullable()->constrained('internal_departments')->nullOnDelete();
                $table->string('nama');
                $table->string('kode')->nullable();
                $table->text('deskripsi')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'unit_kerja_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('unit_kerja_id')->nullable()->after('department_id')->constrained('unit_kerja')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'unit_kerja_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('unit_kerja_id');
            });
        }

        Schema::dropIfExists('unit_kerja');
    }
};
