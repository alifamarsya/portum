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
        // 1. Tambahkan soft deletes dan flag status_penghapusan ke tabel as_aset
        Schema::table('as_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_aset', 'deleted_at')) {
                $table->softDeletes()->after('custom_fields');
            }
            if (!Schema::hasColumn('as_aset', 'status_penghapusan')) {
                $table->string('status_penghapusan', 50)->default('aktif')->after('kondisi');
            }
        });

        // 2. Tambahkan kolom alasan_penolakan dan catatan_approval ke tabel as_disposal_aset
        Schema::table('as_disposal_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_disposal_aset', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('as_disposal_aset', 'catatan_approval')) {
                $table->text('catatan_approval')->nullable()->after('alasan_penolakan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('as_aset', function (Blueprint $table) {
            if (Schema::hasColumn('as_aset', 'status_penghapusan')) {
                $table->dropColumn('status_penghapusan');
            }
            if (Schema::hasColumn('as_aset', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('as_disposal_aset', function (Blueprint $table) {
            if (Schema::hasColumn('as_disposal_aset', 'alasan_penolakan')) {
                $table->dropColumn('alasan_penolakan');
            }
            if (Schema::hasColumn('as_disposal_aset', 'catatan_approval')) {
                $table->dropColumn('catatan_approval');
            }
        });
    }
};
