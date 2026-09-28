<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomFieldSeeder extends Seeder
{
    public function run(): void
    {
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
}
