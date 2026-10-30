<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('as_master_kode_aset')) {
            Schema::create('as_master_kode_aset', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 50)->unique();
                $table->string('nama', 150);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        Schema::table('as_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_aset', 'kode_aset_id')) {
                $table->foreignId('kode_aset_id')->nullable()->after('kode_aset')->constrained('as_master_kode_aset')->nullOnDelete();
            }
            if (!Schema::hasColumn('as_aset', 'lokasi_id')) {
                $table->foreignId('lokasi_id')->nullable()->after('lokasi')->constrained('as_master_lokasi')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('as_aset', function (Blueprint $table) {
            if (Schema::hasColumn('as_aset', 'lokasi_id')) {
                $table->dropForeign(['lokasi_id']);
                $table->dropColumn('lokasi_id');
            }
            if (Schema::hasColumn('as_aset', 'kode_aset_id')) {
                $table->dropForeign(['kode_aset_id']);
                $table->dropColumn('kode_aset_id');
            }
        });

        Schema::dropIfExists('as_master_kode_aset');
    }
};
