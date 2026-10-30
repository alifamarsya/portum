<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // No-op bila baris field belum ada (mis. DB lama sebelum seed builtin):
        // dropdown kategori tetap mengikuti config/modules.php sebagai fallback.
        DB::table('as_custom_fields')
            ->where('module_key', 'aset')
            ->where('field_name', 'kategori')
            ->update([
                'field_type' => 'select',
                'options'    => json_encode(['Aset TI', 'Aset Umum']),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('as_custom_fields')
            ->where('module_key', 'aset')
            ->where('field_name', 'kategori')
            ->update([
                'field_type' => 'text',
                'options'    => null,
                'updated_at' => now(),
            ]);
    }
};
