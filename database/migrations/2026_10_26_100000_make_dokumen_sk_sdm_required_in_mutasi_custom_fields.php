<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('as_custom_fields')
            ->where('module_key', 'mutasi')
            ->where('field_name', 'dokumen')
            ->update([
                'label'       => 'Dokumen SK Mutasi dari SDM',
                'is_required' => true,
                'help_text'   => 'Wajib melampirkan Dokumen SK Mutasi dari SDM. Format file: PDF, JPG, PNG, DOC/DOCX (Maksimal 10MB).',
                'updated_at'  => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('as_custom_fields')
            ->where('module_key', 'mutasi')
            ->where('field_name', 'dokumen')
            ->update([
                'label'       => 'Dokumen Pendukung / SK Mutasi',
                'is_required' => false,
                'help_text'   => 'Format file: PDF, JPG, PNG, DOC/DOCX (Maksimal 10MB).',
                'updated_at'  => now(),
            ]);
    }
};
