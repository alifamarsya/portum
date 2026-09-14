<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan kolom is_system pada as_custom_fields jika belum ada
        Schema::table('as_custom_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('as_custom_fields', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('options');
            }
        });

        // 2. Buat kolom-kolom string/text pada as_aset menjadi nullable agar aman saat field dihapus dari Dynamic Field
        Schema::table('as_aset', function (Blueprint $table) {
            $table->string('nama_aset')->nullable()->change();
            $table->string('kategori')->nullable()->change();
            $table->string('lokasi')->nullable()->change();
            $table->decimal('nilai_perolehan', 18, 2)->nullable()->default(0)->change();
            $table->integer('umur_ekonomis')->nullable()->default(0)->change();
            $table->string('kondisi')->nullable()->default('Baik')->change();
        });

        // 3. Seeding field bawaan modul 'aset' (Inventarisasi Aset)
        $asetFields = [
            [
                'field_name'   => 'kode_aset',
                'label'        => 'Kode Aset',
                'field_type'   => 'text',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => true,
                'sort_order'   => 1,
                'help_text'    => 'Kode unik pengenal aset',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'nama_aset',
                'label'        => 'Nama Aset',
                'field_type'   => 'text',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => true,
                'show_in_list' => true,
                'sort_order'   => 2,
                'help_text'    => 'Nama barang atau aset',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'kategori',
                'label'        => 'Kategori',
                'field_type'   => 'text',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => true,
                'show_in_list' => true,
                'sort_order'   => 3,
                'help_text'    => 'Kategori kelompok aset',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'lokasi',
                'label'        => 'Lokasi',
                'field_type'   => 'text',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => true,
                'sort_order'   => 4,
                'help_text'    => 'Lokasi atau ruangan aset berada',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'tanggal_perolehan',
                'label'        => 'Tgl Perolehan',
                'field_type'   => 'date',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => false,
                'sort_order'   => 5,
                'help_text'    => 'Tanggal pembelian/pengadaan aset',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'nilai_perolehan',
                'label'        => 'Nilai Perolehan',
                'field_type'   => 'money',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => true,
                'sort_order'   => 6,
                'help_text'    => 'Nominal harga perolehan aset',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'umur_ekonomis',
                'label'        => 'Umur Ekonomis (bln)',
                'field_type'   => 'number',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => false,
                'sort_order'   => 7,
                'help_text'    => 'Masa manfaat dalam hitungan bulan',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'kondisi',
                'label'        => 'Kondisi',
                'field_type'   => 'select',
                'options'      => json_encode(['Baik', 'Rusak Ringan', 'Rusak Berat']),
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => true,
                'sort_order'   => 8,
                'help_text'    => 'Status kondisi fisik aset saat ini',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'penanggung_jawab',
                'label'        => 'Penanggung Jawab',
                'field_type'   => 'text',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => false,
                'sort_order'   => 9,
                'help_text'    => 'Nama pegawai atau unit yang bertanggung jawab',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'keterangan',
                'label'        => 'Keterangan',
                'field_type'   => 'textarea',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => false,
                'sort_order'   => 10,
                'help_text'    => 'Catatan atau spesifikasi tambahan',
                'is_active'    => true,
            ],
        ];

        foreach ($asetFields as $f) {
            $exists = DB::table('as_custom_fields')
                ->where('module_key', 'aset')
                ->where('field_name', $f['field_name'])
                ->first();

            if (!$exists) {
                DB::table('as_custom_fields')->insert(array_merge($f, [
                    'module_key' => 'aset',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Update custom field nomor_seri agar urutannya setelah field bawaan
        DB::table('as_custom_fields')
            ->where('module_key', 'aset')
            ->where('field_name', 'nomor_seri')
            ->update(['sort_order' => 11, 'is_system' => false]);

        // 4. Seeding field bawaan modul 'aset_history' (Riwayat Pergerakan Aset)
        $historyFields = [
            [
                'field_name'   => 'aset_id',
                'label'        => 'ID Aset',
                'field_type'   => 'number',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => true,
                'show_in_list' => true,
                'sort_order'   => 1,
                'help_text'    => 'Nomor ID referensi aset',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'field_changed',
                'label'        => 'Perubahan',
                'field_type'   => 'text',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => true,
                'show_in_list' => true,
                'sort_order'   => 2,
                'help_text'    => 'Nama kolom atau aktivitas perubahan',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'old_value',
                'label'        => 'Nilai Lama',
                'field_type'   => 'text',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => true,
                'sort_order'   => 3,
                'help_text'    => 'Nilai sebelum perubahan terjadi',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'new_value',
                'label'        => 'Nilai Baru',
                'field_type'   => 'text',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => true,
                'sort_order'   => 4,
                'help_text'    => 'Nilai setelah perubahan terjadi',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'keterangan',
                'label'        => 'Keterangan',
                'field_type'   => 'textarea',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => true,
                'sort_order'   => 5,
                'help_text'    => 'Penjelasan alasan pergerakan/perubahan aset',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'changed_at',
                'label'        => 'Waktu Perubahan',
                'field_type'   => 'date',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => true,
                'sort_order'   => 6,
                'help_text'    => 'Waktu pencatatan mutasi/pergerakan',
                'is_active'    => true,
            ],
        ];

        foreach ($historyFields as $f) {
            $exists = DB::table('as_custom_fields')
                ->where('module_key', 'aset_history')
                ->where('field_name', $f['field_name'])
                ->first();

            if (!$exists) {
                DB::table('as_custom_fields')->insert(array_merge($f, [
                    'module_key' => 'aset_history',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::table('as_custom_fields', function (Blueprint $table) {
            if (Schema::hasColumn('as_custom_fields', 'is_system')) {
                $table->dropColumn('is_system');
            }
        });
    }
};
