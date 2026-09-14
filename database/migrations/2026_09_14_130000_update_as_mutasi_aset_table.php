<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('as_mutasi_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_mutasi_aset', 'pengaju_id')) {
                $table->foreignId('pengaju_id')->nullable()->after('aset_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'status_hasil')) {
                $table->string('status_hasil')->nullable()->after('status'); // Disetujui, Ditolak, Tidak Valid
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'operator_id')) {
                $table->foreignId('operator_id')->nullable()->after('status_hasil')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'operator_checked_at')) {
                $table->timestamp('operator_checked_at')->nullable()->after('operator_id');
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'catatan_operator')) {
                $table->text('catatan_operator')->nullable()->after('operator_checked_at');
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'verifikator_id')) {
                $table->foreignId('verifikator_id')->nullable()->after('catatan_operator')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verifikator_id');
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'catatan_verifikasi')) {
                $table->text('catatan_verifikasi')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'approver_id')) {
                $table->foreignId('approver_id')->nullable()->after('catatan_verifikasi')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'catatan_approval')) {
                $table->text('catatan_approval')->nullable()->after('approver_id');
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable()->after('catatan_approval');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('as_mutasi_aset', function (Blueprint $table) {
            $table->dropForeign(['pengaju_id']);
            $table->dropForeign(['operator_id']);
            $table->dropForeign(['verifikator_id']);
            $table->dropForeign(['approver_id']);
            $table->dropColumn([
                'pengaju_id',
                'status_hasil',
                'operator_id',
                'operator_checked_at',
                'catatan_operator',
                'verifikator_id',
                'verified_at',
                'catatan_verifikasi',
                'approver_id',
                'catatan_approval',
                'alasan_penolakan',
            ]);
        });
    }
};
