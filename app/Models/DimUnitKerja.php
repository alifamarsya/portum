<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimUnitKerja extends Model
{
    protected $table = 'dim_unit_kerja';

    public $timestamps = false;

    protected $fillable = [
        'nama_unit'
    ];
}
