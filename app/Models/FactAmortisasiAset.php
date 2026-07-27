<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactAmortisasiAset extends Model
{
    protected $table = 'fact_amortisasi_aset';

    protected $fillable = [
        'dim_waktu_id',
        'as_amortisasi_id',
        'nilai_penyusutan_bulan',
        'akumulasi',
        'nilai_buku'
    ];

    protected function casts(): array
    {
        return [
        'nilai_penyusutan_bulan' => 'decimal:2',
        'akumulasi' => 'decimal:2',
        'nilai_buku' => 'decimal:2'
        ];
    }

    public function waktu()
    {
        return $this->belongsTo(DimWaktu::class, 'dim_waktu_id');
    }

    public function amortisasi()
    {
        return $this->belongsTo(AsAmortisasi::class, 'as_amortisasi_id');
    }
}
