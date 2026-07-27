<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefAkun extends Model
{
    protected $table = 'ref_akun';

    public $timestamps = false;
    
    protected $fillable = [
        'nama_beban',
        'rekening_debet',
        'contoh_keterangan'
    ];
}
