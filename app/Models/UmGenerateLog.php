<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmGenerateLog extends Model
{
    protected $table = 'um_generate_log';

    protected $fillable = [
        'periode_awal',
        'periode_akhir',
        'kategori',
        'jumlah_item',
        'total',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'total' => 'decimal:2'
        ];
    }
}
