<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgMemoInternal extends Model
{
    protected $table = 'pg_memo_internal';

    protected $fillable = [
        'no_memo',
        'dari_unit',
        'ke_unit',
        'perihal',
        'tanggal',
        'jenis',
        'dokumen',
        'status',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
