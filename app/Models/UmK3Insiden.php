<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmK3Insiden extends Model
{
    use HasFactory;

    protected $table = 'um_k3_insiden';

    protected $fillable = [
        'tanggal', 'jenis', 'lokasi', 'uraian',
        'korban', 'tindak_lanjut', 'status',
        'dokumen', 'dibuat_oleh',
    ];
}
