<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RisalahRapat extends Model
{
    protected $table = 'risalah_rapat';

    protected $fillable = [
        'nomor',
        'judul',
        'tanggal',
        'waktu',
        'tempat',
        'pemimpin',
        'peserta',
        'agenda',
        'pembahasan',
        'keputusan',
        'tindak_lanjut',
        'lampiran',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
