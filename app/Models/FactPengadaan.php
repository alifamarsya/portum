<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactPengadaan extends Model
{
    protected $table = 'fact_pengadaan';

    protected $fillable = [
        'dim_waktu_id',
        'dim_vendor_id',
        'dim_kategori_id',
        'total_nilai',
        'jumlah_transaksi'
    ];

    protected function casts(): array
    {
        return [
        'total_nilai' => 'decimal:2'
        ];
    }

    public function waktu()
    {
        return $this->belongsTo(DimWaktu::class, 'dim_waktu_id');
    }

    public function vendor()
    {
        return $this->belongsTo(DimVendor::class, 'dim_vendor_id');
    }

    public function kategori()
    {
        return $this->belongsTo(DimKategori::class, 'dim_kategori_id');
    }
}
