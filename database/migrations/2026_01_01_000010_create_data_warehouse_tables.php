<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Star schema untuk CPMK Data Warehouse (pengganti Interaksi Manusia Komputer).
    // Tabel dim_* dan fact_* ini diisi lewat proses ETL terjadwal (Artisan
    // command via Task Scheduler) yang menarik data dari tabel OLTP di atas
    // (um_biaya_harian, as_amortisasi, pg_penawaran/pg_negosiasi/pg_spk),
    // meng-agregasi per bulan, lalu memuatnya ke fact table di bawah.
    public function up(): void
    {
        Schema::create('dim_waktu', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->integer('tahun');
            $table->integer('bulan');
            $table->string('nama_bulan');
            $table->integer('kuartal');
        });

        Schema::create('dim_unit_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nama_unit')->unique();
        });

        Schema::create('dim_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_kategori'); // biaya | aset | pengadaan
            $table->string('nama_kategori');
        });

        Schema::create('dim_vendor', function (Blueprint $table) {
            $table->id();
            $table->string('nama_vendor')->unique();
        });

        Schema::create('fact_biaya_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dim_waktu_id')->constrained('dim_waktu');
            $table->foreignId('dim_unit_kerja_id')->nullable()->constrained('dim_unit_kerja');
            $table->foreignId('dim_kategori_id')->nullable()->constrained('dim_kategori');
            $table->decimal('total_biaya', 18, 2)->default(0);
            $table->integer('jumlah_transaksi')->default(0);
            $table->timestamps();
        });

        Schema::create('fact_amortisasi_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dim_waktu_id')->constrained('dim_waktu');
            $table->foreignId('as_amortisasi_id')->constrained('as_amortisasi');
            $table->decimal('nilai_penyusutan_bulan', 18, 2)->default(0);
            $table->decimal('akumulasi', 18, 2)->default(0);
            $table->decimal('nilai_buku', 18, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('fact_pengadaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dim_waktu_id')->constrained('dim_waktu');
            $table->foreignId('dim_vendor_id')->nullable()->constrained('dim_vendor');
            $table->foreignId('dim_kategori_id')->nullable()->constrained('dim_kategori');
            $table->decimal('total_nilai', 18, 2)->default(0);
            $table->integer('jumlah_transaksi')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_pengadaan');
        Schema::dropIfExists('fact_amortisasi_aset');
        Schema::dropIfExists('fact_biaya_bulanan');
        Schema::dropIfExists('dim_vendor');
        Schema::dropIfExists('dim_kategori');
        Schema::dropIfExists('dim_unit_kerja');
        Schema::dropIfExists('dim_waktu');
    }
};
