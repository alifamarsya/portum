<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimKategori extends Model
{
    protected $table = 'dim_kategori';

    public $timestamps = false;

    protected $fillable = [
        'jenis_kategori',
        'nama_kategori'
    ];
}
