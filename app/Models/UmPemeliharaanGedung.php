<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmPemeliharaanGedung extends Model
{
    use HasFactory;

    protected $table = 'um_pemeliharaan_gedung';

    protected $fillable = [
        'jenis_pekerjaan', 'uraian', 'lokasi',
        'tanggal_rencana', 'tanggal_realisasi',
        'vendor', 'biaya', 'status', 'dokumen',
        'dibuat_oleh', 'disetujui_oleh', 'status_approval',
    ];
}
