<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsDistribusiBarang extends Model
{
    use HasFactory;

    protected $table = 'as_distribusi_barang';

    protected $fillable = [
        'no_distribusi',
        'tanggal',
        'nama_barang',
        'jumlah',
        'satuan',
        'tujuan_unit',
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
