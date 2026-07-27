<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgNegosiasi extends Model
{
    protected $table = 'pg_negosiasi';

    protected $fillable = [
        'no_berita_acara',
        'vendor',
        'barang_jasa',
        'nilai_awal',
        'nilai_nego',
        'tanggal',
        'hasil',
        'dokumen'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date',
        'nilai_awal' => 'decimal:2',
        'nilai_nego' => 'decimal:2'
        ];
    }
}
