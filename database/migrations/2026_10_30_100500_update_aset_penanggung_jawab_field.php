<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // No-op bila baris field belum ada (mis. DB lama sebelum seed builtin):
        // dropdown penanggung jawab tetap mengikuti config/modules.php sebagai fallback.
        DB::table('as_custom_fields')
            ->where('module_key', 'aset')
            ->where('field_name', 'penanggung_jawab')
            ->update([
                'field_type' => 'select',
                'help_text'  => 'Pilih personel dari lokasi yang dipilih',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('as_custom_fields')
            ->where('module_key', 'aset')
            ->where('field_name', 'penanggung_jawab')
            ->update([
                'field_type' => 'text',
                'help_text'  => 'Nama pegawai atau unit yang bertanggung jawab',
                'updated_at' => now(),
            ]);
    }
};
