<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // No-op bila baris field belum ada: dropdown kondisi tetap
        // mengikuti config/modules.php sebagai fallback. Nilai data lama
        // di as_aset TIDAK diubah di sini — unit lama wajib memilih nilai
        // baru saat berikutnya diedit (perilaku yang sama dengan dropdown
        // master kode/lokasi).
        DB::table('as_custom_fields')
            ->where('module_key', 'aset')
            ->where('field_name', 'kondisi')
            ->update([
                'options'    => json_encode(['Baik', 'Rusak', 'Butuh Maintenance']),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('as_custom_fields')
            ->where('module_key', 'aset')
            ->where('field_name', 'kondisi')
            ->update([
                'options'    => json_encode(['Baik', 'Rusak Ringan', 'Rusak Berat']),
                'updated_at' => now(),
            ]);
    }
};
