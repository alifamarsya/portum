<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DkDokumenLegalitas extends Model
{
    use HasFactory;

    protected $table = 'dk_dokumen_legalitas';

    protected $fillable = [
        'nama_dokumen', 'jenis', 'no_dokumen', 'penerbit',
        'tanggal_terbit', 'tanggal_berlaku', 'status',
        'keterangan', 'dokumen',
    ];
}
