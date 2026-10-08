<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketFieldSeeder extends Seeder
{
    public function run(): void
    {
        $defaultFields = [
            [
                'field_name'   => 'ticket_number',
                'label'        => 'No. Tiket',
                'field_type'   => 'text',
                'options'      => null,
                'is_required'  => true,
                'show_in_form' => false,
                'show_in_list' => true,
                'sort_order'   => 1,
                'help_text'    => 'Nomor tiket unik yang digenerate otomatis oleh sistem.',
                'is_active'    => true,
                'is_system'    => true,
            ],
            [
                'field_name'   => 'user_id',
                'label'        => 'Pemohon & Unit Kerja',
                'field_type'   => 'text',
                'options'      => null,
                'is_required'  => true,
                'show_in_form' => true,
                'show_in_list' => true,
                'sort_order'   => 2,
                'help_text'    => 'Identitas nama staf dan unit kerja pengaju tiket.',
                'is_active'    => true,
                'is_system'    => true,
            ],
            [
                'field_name'   => 'created_at',
                'label'        => 'Tanggal Pengajuan',
                'field_type'   => 'date',
                'options'      => null,
                'is_required'  => true,
                'show_in_form' => false,
                'show_in_list' => true,
                'sort_order'   => 3,
                'help_text'    => 'Waktu pencatatan tiket masuk ke helpdesk.',
                'is_active'    => true,
                'is_system'    => true,
            ],
            [
                'field_name'   => 'status',
                'label'        => 'Status Tiket',
                'field_type'   => 'select',
                'options'      => json_encode(['Menunggu Verifikasi', 'Diverifikasi', 'Dalam Proses', 'Selesai', 'Ditutup Pemohon', 'Ditolak']),
                'is_required'  => true,
                'show_in_form' => false,
                'show_in_list' => true,
                'sort_order'   => 4,
                'help_text'    => 'Tahapan alur penanganan tiket helpdesk.',
                'is_active'    => true,
                'is_system'    => true,
            ],
            [
                'field_name'   => 'jenis_pengajuan',
                'label'        => 'Jenis Pengajuan',
                'field_type'   => 'select',
                'options'      => json_encode(['Permintaan', 'Permasalahan']),
                'is_required'  => true,
                'show_in_form' => true,
                'show_in_list' => true,
                'sort_order'   => 5,
                'help_text'    => 'Klasifikasi kebutuhan (Permintaan barang/jasa atau Permasalahan teknis).',
                'is_active'    => true,
                'is_system'    => true,
            ],
            [
                'field_name'   => 'category_id',
                'label'        => 'Kategori Tiket',
                'field_type'   => 'select',
                'options'      => null,
                'is_required'  => true,
                'show_in_form' => true,
                'show_in_list' => true,
                'sort_order'   => 6,
                'help_text'    => 'Kategori spesifik layanan/gangguan yang menentukan target SLA.',
                'is_active'    => true,
                'is_system'    => true,
            ],
            [
                'field_name'   => 'department_id',
                'label'        => 'Tujuan Bagian',
                'field_type'   => 'select',
                'options'      => null,
                'is_required'  => false,
                'show_in_form' => false,
                'show_in_list' => true,
                'sort_order'   => 7,
                'help_text'    => 'Unit kerja internal penerima disposisi tiket.',
                'is_active'    => true,
                'is_system'    => true,
            ],
            [
                'field_name'   => 'sla',
                'label'        => 'SLA Respon & Resolusi',
                'field_type'   => 'text',
                'options'      => null,
                'is_required'  => false,
                'show_in_form' => false,
                'show_in_list' => true,
                'sort_order'   => 8,
                'help_text'    => 'Indikator batas waktu respon verifikasi dan resolusi pengerjaan.',
                'is_active'    => true,
                'is_system'    => true,
            ],
            [
                'field_name'   => 'description',
                'label'        => 'Deskripsi / Uraian Masalah',
                'field_type'   => 'textarea',
                'options'      => null,
                'is_required'  => true,
                'show_in_form' => true,
                'show_in_list' => false,
                'sort_order'   => 9,
                'help_text'    => 'Jelaskan kebutuhan, lokasi, kendala spesifik, atau perincian secara rinci (minimal 100 karakter).',
                'is_active'    => true,
                'is_system'    => true,
            ],
            [
                'field_name'   => 'attachment',
                'label'        => 'Dokumen Lampiran Pendukung',
                'field_type'   => 'file',
                'options'      => null,
                'is_required'  => false,
                'show_in_form' => true,
                'show_in_list' => false,
                'sort_order'   => 10,
                'help_text'    => 'Berkas PDF pendukung (maksimal 10MB per berkas).',
                'is_active'    => true,
                'is_system'    => true,
            ],
        ];

        foreach ($defaultFields as $f) {
            $exists = DB::table('ticket_fields')
                ->where('field_name', $f['field_name'])
                ->exists();

            if (!$exists) {
                DB::table('ticket_fields')->insert(array_merge($f, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
