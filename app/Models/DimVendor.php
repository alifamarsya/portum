<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimVendor extends Model
{
    protected $table = 'dim_vendor';

    public $timestamps = false;

    protected $fillable = [
        'nama_vendor'
    ];
}
