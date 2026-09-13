<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsPenerimaanBarang extends Model
{
    use HasFactory;

    protected $table = 'as_penerimaan_barang';

    protected $fillable = [
        'no_penerimaan',
        'tanggal',
        'vendor',
        'nama_barang',
        'jumlah',
        'satuan',
        'kondisi',
        'penerima',
        'status',
        'dokumen',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah'  => 'integer',
        ];
    }
}
