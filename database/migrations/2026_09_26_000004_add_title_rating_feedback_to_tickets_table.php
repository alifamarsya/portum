<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom title (judul tiket singkat), rating (1-5 bintang pemohon),
     * dan feedback (komentar penutupan) ke tabel tickets.
     * Juga menghapus kolom unit_kerja_id yang sudah tidak digunakan setelah simplifikasi role.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Judul ringkas tiket yang diisi pemohon
            if (!Schema::hasColumn('tickets', 'title')) {
                $table->string('title')->nullable()->after('ticket_number');
            }

            // Rating kepuasan pemohon (1–5) saat menutup tiket
            if (!Schema::hasColumn('tickets', 'rating')) {
                $table->unsignedTinyInteger('rating')->nullable()->after('closed_at');
            }

            // Feedback / komentar penutupan dari pemohon
            if (!Schema::hasColumn('tickets', 'feedback')) {
                $table->text('feedback')->nullable()->after('rating');
            }

            // Hapus kolom unit_kerja_id yang tidak lagi relevan setelah simplifikasi role
            if (Schema::hasColumn('tickets', 'unit_kerja_id')) {
                // Drop foreign key constraint dulu jika ada
                try {
                    $table->dropForeign(['unit_kerja_id']);
                } catch (\Throwable $e) {
                    // Foreign key mungkin tidak ada, lanjutkan
                }
                $table->dropColumn('unit_kerja_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['title', 'rating', 'feedback']);

            // Restore unit_kerja_id jika rollback diperlukan
            $table->unsignedBigInteger('unit_kerja_id')->nullable()->after('department_id');
        });
    }
};
