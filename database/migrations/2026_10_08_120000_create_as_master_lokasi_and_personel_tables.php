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
        // 1. Tabel Master Lokasi (Divisi / Cabang / Unit)
        if (!Schema::hasTable('as_master_lokasi')) {
            Schema::create('as_master_lokasi', function (Blueprint $table) {
                $table->id();
                $table->string('nama_lokasi', 150);
                $table->string('tipe', 50)->default('Divisi'); // Divisi, Cabang, Unit Kerja
                $table->string('kode_lokasi', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 2. Tabel Master Personel per Lokasi
        if (!Schema::hasTable('as_master_personel')) {
            Schema::create('as_master_personel', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lokasi_id')->constrained('as_master_lokasi')->cascadeOnDelete();
                $table->string('nama_personel', 150);
                $table->string('nip', 50)->nullable();
                $table->string('jabatan', 100)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Kolom Additive pada as_mutasi_aset
        Schema::table('as_mutasi_aset', function (Blueprint $table) {
            if (!Schema::hasColumn('as_mutasi_aset', 'lokasi_asal_id')) {
                $table->foreignId('lokasi_asal_id')->nullable()->after('aset_id')->constrained('as_master_lokasi')->nullOnDelete();
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'pemohon_personel_id')) {
                $table->foreignId('pemohon_personel_id')->nullable()->after('lokasi_asal_id')->constrained('as_master_personel')->nullOnDelete();
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'lokasi_tujuan_id')) {
                $table->foreignId('lokasi_tujuan_id')->nullable()->after('ke_lokasi')->constrained('as_master_lokasi')->nullOnDelete();
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'penanggung_jawab_id')) {
                $table->foreignId('penanggung_jawab_id')->nullable()->after('ke_penanggung_jawab')->constrained('as_master_personel')->nullOnDelete();
            }
            if (!Schema::hasColumn('as_mutasi_aset', 'is_pemohon_pindah')) {
                $table->boolean('is_pemohon_pindah')->default(false)->after('penanggung_jawab_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('as_mutasi_aset', function (Blueprint $table) {
            if (Schema::hasColumn('as_mutasi_aset', 'penanggung_jawab_id')) {
                $table->dropForeign(['penanggung_jawab_id']);
                $table->dropColumn('penanggung_jawab_id');
            }
            if (Schema::hasColumn('as_mutasi_aset', 'is_pemohon_pindah')) {
                $table->dropColumn('is_pemohon_pindah');
            }
            if (Schema::hasColumn('as_mutasi_aset', 'lokasi_tujuan_id')) {
                $table->dropForeign(['lokasi_tujuan_id']);
                $table->dropColumn('lokasi_tujuan_id');
            }
            if (Schema::hasColumn('as_mutasi_aset', 'pemohon_personel_id')) {
                $table->dropForeign(['pemohon_personel_id']);
                $table->dropColumn('pemohon_personel_id');
            }
            if (Schema::hasColumn('as_mutasi_aset', 'lokasi_asal_id')) {
                $table->dropForeign(['lokasi_asal_id']);
                $table->dropColumn('lokasi_asal_id');
            }
        });

        Schema::dropIfExists('as_master_personel');
        Schema::dropIfExists('as_master_lokasi');
    }
};
