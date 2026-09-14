<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('kategori_pekerjaan')->nullable()->after('disposition_notes')
                ->comment('Kategori pekerjaan penanganan tiket (Rutin, Perbaikan Ringan, Perbaikan Berat, Penggantian Komponen, dsb)');
            $table->string('skala_eselonisasi')->nullable()->after('kategori_pekerjaan')
                ->comment('Skala eselonisasi/dampak pekerjaan (Unit Kerja/Cabang, Divisi, Kantor Pusat/Direksi)');
            $table->decimal('estimasi_biaya', 15, 2)->nullable()->after('skala_eselonisasi')
                ->comment('Estimasi biaya penanganan tiket sesuai pengecekan pagu anggaran RBB');
            $table->integer('sla_resolution_hours')->nullable()->after('estimasi_biaya')
                ->comment('Target jam resolusi SLA (Rendah=72, Sedang=48, Tinggi=12, Kritis=4 atau disesuaikan Kabag)');
            $table->timestamp('sla_resolution_start_at')->nullable()->after('sla_resolution_hours')
                ->comment('Waktu timer resolusi mulai berjalan otomatis saat tiket disetujui & didisposisi Kabag');
            $table->timestamp('sla_resolution_due_at')->nullable()->after('sla_resolution_start_at')
                ->comment('Batas waktu akhir penyelesaian tiket oleh staf pelaksana');
            $table->timestamp('resolved_at')->nullable()->after('sla_resolution_due_at')
                ->comment('Waktu tiket selesai dikerjakan (status Selesai)');
            $table->integer('sla_resolution_time_minutes')->nullable()->after('resolved_at')
                ->comment('Total menit aktual waktu pengerjaan dari disetujui Kabag hingga Selesai');
            $table->string('sla_resolution_status')->default('Menunggu Disposisi')->after('sla_resolution_time_minutes')
                ->comment('Menunggu Disposisi | Berjalan | Tepat Waktu | Terlambat');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'kategori_pekerjaan',
                'skala_eselonisasi',
                'estimasi_biaya',
                'sla_resolution_hours',
                'sla_resolution_start_at',
                'sla_resolution_due_at',
                'resolved_at',
                'sla_resolution_time_minutes',
                'sla_resolution_status',
            ]);
        });
    }
};
