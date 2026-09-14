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
        // 1. Tambah field pemohon & konfirmasi di as_mutasi_aset
        Schema::table('as_mutasi_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_mutasi_aset', 'nama_pemohon')) {
                $table->string('nama_pemohon')->nullable()->after('pengaju_id');
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'jabatan_pemohon')) {
                $table->string('jabatan_pemohon')->nullable()->after('nama_pemohon');
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'username_pemohon')) {
                $table->string('username_pemohon')->nullable()->after('jabatan_pemohon');
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'catatan_konfirmasi')) {
                $table->text('catatan_konfirmasi')->nullable()->after('alasan_penolakan');
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('catatan_konfirmasi');
            }
        });

        // 2. Tambah kolom custom_fields (JSON) pada as_aset
        Schema::table('as_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_aset', 'custom_fields')) {
                $table->json('custom_fields')->nullable()->after('keterangan');
            }
        });

        // 3. Tambah kolom custom_fields (JSON) pada as_aset_histories
        Schema::table('as_aset_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('as_aset_histories', 'custom_fields')) {
                $table->json('custom_fields')->nullable()->after('keterangan');
            }
        });

        // 4. Buat tabel as_custom_fields untuk dynamic fields
        if (!Schema::hasTable('as_custom_fields')) {
            Schema::create('as_custom_fields', function (Blueprint $table) {
                $table->id();
                $table->string('module_key')->index(); // 'aset', 'aset_history'
                $table->string('field_name');           // e.g. nomor_seri, merk_tipe
                $table->string('label');                // e.g. Nomor Seri / IMEI
                $table->string('field_type')->default('text'); // text, number, money, date, select, textarea, checkbox
                $table->json('options')->nullable();    // Opsi untuk type select
                $table->boolean('is_required')->default(false);
                $table->boolean('show_in_list')->default(true);
                $table->integer('sort_order')->default(0);
                $table->string('help_text')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('as_custom_fields');

        Schema::table('as_aset_histories', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });

        Schema::table('as_aset', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });

        Schema::table('as_mutasi_aset', function (Blueprint $table) {
            $table->dropColumn([
                'nama_pemohon',
                'jabatan_pemohon',
                'username_pemohon',
                'catatan_konfirmasi',
                'confirmed_at',
            ]);
        });
    }
};
