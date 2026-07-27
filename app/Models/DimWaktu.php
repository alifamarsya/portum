<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimWaktu extends Model
{
    protected $table = 'dim_waktu';

    public $timestamps = false;

    protected $fillable = [
        'tanggal',
        'tahun',
        'bulan',
        'nama_bulan',
        'kuartal'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
