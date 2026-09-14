<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DkArsipDokumen extends Model
{
    use HasFactory;

    protected $table = 'dk_arsip_dokumen';

    protected $fillable = [
        'kode_arsip', 'judul', 'jenis', 'kategori',
        'tanggal_dokumen', 'lokasi_arsip', 'masa_retensi',
        'status_arsip', 'keterangan', 'lampiran', 'dibuat_oleh',
    ];
}
