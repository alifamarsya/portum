<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactBiayaBulanan extends Model
{
    protected $table = 'fact_biaya_bulanan';

    protected $fillable = [
        'dim_waktu_id',
        'dim_unit_kerja_id',
        'dim_kategori_id',
        'total_biaya',
        'jumlah_transaksi'
    ];

    protected function casts(): array
    {
        return [
        'total_biaya' => 'decimal:2'
        ];
    }

    public function waktu()
    {
        return $this->belongsTo(DimWaktu::class, 'dim_waktu_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(DimUnitKerja::class, 'dim_unit_kerja_id');
    }

    public function kategori()
    {
        return $this->belongsTo(DimKategori::class, 'dim_kategori_id');
    }
}
