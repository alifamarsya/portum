<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('as_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_aset', 'tahun_pembelian')) {
                $table->integer('tahun_pembelian')->nullable()->after('tanggal_perolehan');
            }
            if (!Schema::hasColumn('as_aset', 'tahun_penggunaan')) {
                $table->integer('tahun_penggunaan')->nullable()->after('tahun_pembelian');
            }
        });

        // Baris field di as_custom_fields (idempotent per field). Bila
        // kedua field belum ada sama sekali (DB lama), geser dulu baris
        // aset dengan sort_order > 5 agar kedua field baru tepat berada
        // setelah tanggal_perolehan (urutan 6 dan 7). Bila salah satu
        // sudah ada (mis. fresh install dari seed yang diperbarui),
        // jangan geser apa pun — cukup sisipkan yang belum ada.
        $fields = [
            [
                'field_name'   => 'tahun_pembelian',
                'label'        => 'Tahun Pembelian',
                'field_type'   => 'number',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => false,
                'sort_order'   => 6,
                'help_text'    => 'Tahun aset dibeli/diadakan',
                'is_active'    => true,
            ],
            [
                'field_name'   => 'tahun_penggunaan',
                'label'        => 'Tahun Penggunaan',
                'field_type'   => 'number',
                'options'      => null,
                'is_system'    => true,
                'is_required'  => false,
                'show_in_list' => false,
                'sort_order'   => 7,
                'help_text'    => 'Tahun aset mulai digunakan',
                'is_active'    => true,
            ],
        ];

        $existing = DB::table('as_custom_fields')
            ->where('module_key', 'aset')
            ->whereIn('field_name', ['tahun_pembelian', 'tahun_penggunaan'])
            ->pluck('field_name')
            ->all();

        if (count($existing) === 0) {
            DB::table('as_custom_fields')
                ->where('module_key', 'aset')
                ->where('sort_order', '>', 5)
                ->increment('sort_order', 2);
        }

        foreach ($fields as $f) {
            if (in_array($f['field_name'], $existing, true)) {
                continue;
            }
            DB::table('as_custom_fields')->insert(array_merge($f, [
                'module_key' => 'aset',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('as_custom_fields')
            ->where('module_key', 'aset')
            ->whereIn('field_name', ['tahun_pembelian', 'tahun_penggunaan'])
            ->delete();

        Schema::table('as_aset', function (Blueprint $table) {
            if (Schema::hasColumn('as_aset', 'tahun_penggunaan')) {
                $table->dropColumn('tahun_penggunaan');
            }
            if (Schema::hasColumn('as_aset', 'tahun_pembelian')) {
                $table->dropColumn('tahun_pembelian');
            }
        });
    }
};
