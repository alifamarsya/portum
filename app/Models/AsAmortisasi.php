<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsAmortisasi extends Model
{
    protected $table = 'as_amortisasi';

    protected $fillable = [
        'nama_biaya',
        'nilai_perolehan',
        'tanggal_mulai',
        'umur_bulan',
        'nilai_per_bulan',
        'akumulasi',
        'nilai_buku',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_mulai' => 'date',
        'nilai_perolehan' => 'decimal:2',
        'nilai_per_bulan' => 'decimal:2',
        'akumulasi' => 'decimal:2',
        'nilai_buku' => 'decimal:2'
        ];
    }

    public function factAmortisasi()
    {
        return $this->hasMany(FactAmortisasiAset::class, 'as_amortisasi_id');
    }
}
