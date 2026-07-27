<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Panduan extends Model
{
    protected $table = 'panduan';

    protected $fillable = [
        'judul',
        'kategori',
        'konten',
        'urutan',
        'updated_by'
    ];
}
