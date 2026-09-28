<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom custom_fields (JSON) pada as_mutasi_aset jika belum ada
        Schema::table('as_mutasi_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_mutasi_aset', 'custom_fields')) {
                $table->json('custom_fields')->nullable()->after('keterangan');
            }
        });

        // 2. Pastikan nilai default kolom di as_custom_fields aman
        Schema::table('as_custom_fields', function (Blueprint $table) {
            if (Schema::hasColumn('as_custom_fields', 'field_type')) {
                $table->string('field_type')->default('text')->change();
            }
            if (Schema::hasColumn('as_custom_fields', 'sort_order')) {
                $table->integer('sort_order')->default(0)->change();
            }
        });

        // 3. Seed field awal untuk konteks form mutasi aset ('mutasi')
        $mutasiFields = [
            [
                'module_key'   => 'mutasi',
                'field_name'   => 'ke_lokasi',
                'label'        => 'Lokasi Tujuan',
                'field_type'   => 'text',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => true,
                'show_in_list' => true,
                'sort_order'   => 1,
                'help_text'    => 'Tentukan cabang, unit kerja, atau ruangan tujuan aset.',
                'is_active'    => true,
            ],
            [
                'module_key'   => 'mutasi',
                'field_name'   => 'ke_penanggung_jawab',
                'label'        => 'Penanggung Jawab Baru',
                'field_type'   => 'text',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => true,
                'show_in_list' => true,
                'sort_order'   => 2,
                'help_text'    => 'Nama pejabat, staf, atau unit kerja yang menerima tanggung jawab aset.',
                'is_active'    => true,
            ],
            [
                'module_key'   => 'mutasi',
                'field_name'   => 'alasan',
                'label'        => 'Alasan Mutasi',
                'field_type'   => 'textarea',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => true,
                'show_in_list' => false,
                'sort_order'   => 3,
                'help_text'    => 'Jelaskan secara rinci alasan atau keperluan pemindahan aset.',
                'is_active'    => true,
            ],
            [
                'module_key'   => 'mutasi',
                'field_name'   => 'dokumen',
                'label'        => 'Dokumen Pendukung / SK Mutasi',
                'field_type'   => 'file',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => false,
                'sort_order'   => 4,
                'help_text'    => 'Format file: PDF, JPG, PNG, DOC/DOCX (Maksimal 10MB).',
                'is_active'    => true,
            ],
        ];

        foreach ($mutasiFields as $f) {
            $exists = DB::table('as_custom_fields')
                ->where('module_key', $f['module_key'])
                ->where('field_name', $f['field_name'])
                ->exists();

            if (!$exists) {
                DB::table('as_custom_fields')->insert(array_merge($f, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus seed field konteks 'mutasi'
        DB::table('as_custom_fields')
            ->where('module_key', 'mutasi')
            ->delete();

        // Drop kolom custom_fields di as_mutasi_aset jika ada
        Schema::table('as_mutasi_aset', function (Blueprint $table) {
            if (Schema::hasColumn('as_mutasi_aset', 'custom_fields')) {
                $table->dropColumn('custom_fields');
            }
        });
    }
};
