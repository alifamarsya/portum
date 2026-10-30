<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('as_master_kode_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_master_kode_aset', 'jenis')) {
                $table->string('jenis', 100)->nullable()->after('nama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('as_master_kode_aset', function (Blueprint $table) {
            if (Schema::hasColumn('as_master_kode_aset', 'jenis')) {
                $table->dropColumn('jenis');
            }
        });
    }
};
