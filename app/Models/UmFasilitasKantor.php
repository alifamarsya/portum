<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmFasilitasKantor extends Model
{
    use HasFactory;

    protected $table = 'um_fasilitas_kantor';

    protected $fillable = [
        'nama_fasilitas', 'kode', 'kategori', 'lokasi',
        'kondisi', 'tanggal_perolehan', 'penanggung_jawab', 'keterangan',
    ];
}
