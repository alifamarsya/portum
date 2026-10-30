<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('as_master_kode_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_master_kode_aset', 'merek')) {
                $table->string('merek', 100)->nullable()->after('jenis');
            }
            if (!Schema::hasColumn('as_master_kode_aset', 'tipe')) {
                $table->string('tipe', 100)->nullable()->after('merek');
            }
            if (!Schema::hasColumn('as_master_kode_aset', 'kapasitas')) {
                $table->string('kapasitas', 100)->nullable()->after('tipe');
            }
        });
    }

    public function down(): void
    {
        Schema::table('as_master_kode_aset', function (Blueprint $table) {
            foreach (['kapasitas', 'tipe', 'merek'] as $col) {
                if (Schema::hasColumn('as_master_kode_aset', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
