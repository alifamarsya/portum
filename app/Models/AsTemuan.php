<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsTemuan extends Model
{
    protected $table = 'as_temuan';

    protected $fillable = [
        'no_temuan',
        'sumber',
        'uraian',
        'tanggal_temuan',
        'batas_tindak_lanjut',
        'status',
        'penanggung_jawab',
        'tindak_lanjut',
        'dokumen'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_temuan' => 'date',
        'batas_tindak_lanjut' => 'date'
        ];
    }
}
