<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmKendaraan extends Model
{
    protected $table = 'um_kendaraan';

    protected $fillable = [
        'no_polisi',
        'jenis',
        'merk',
        'tahun',
        'peruntukan',
        'driver',
        'status',
        'keterangan'
    ];
}
