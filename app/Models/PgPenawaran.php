<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgPenawaran extends Model
{
    protected $table = 'pg_penawaran';

    protected $fillable = [
        'no_penawaran',
        'vendor',
        'barang_jasa',
        'nilai',
        'tanggal',
        'dokumen',
        'status',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date',
        'nilai' => 'decimal:2'
        ];
    }
}
