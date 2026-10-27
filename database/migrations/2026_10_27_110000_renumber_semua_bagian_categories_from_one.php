<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Nomor ulang kategori "Semua Bagian" (department_id NULL) mulai dari 1,
     * dengan menjaga urutan yang sudah ada (sort_order, lalu id).
     * Aman dijalankan ulang: penomoran ulang yang kedua menghasilkan hasil sama.
     */
    public function up(): void
    {
        $ids = DB::table('ticket_categories')
            ->whereNull('department_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id');

        foreach ($ids as $index => $id) {
            DB::table('ticket_categories')->where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Kembalikan penomoran kategori "Semua Bagian" ke berbasis 0.
     */
    public function down(): void
    {
        $ids = DB::table('ticket_categories')
            ->whereNull('department_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id');

        foreach ($ids as $index => $id) {
            DB::table('ticket_categories')->where('id', $id)->update(['sort_order' => $index]);
        }
    }
};
