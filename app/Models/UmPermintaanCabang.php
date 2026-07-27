<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmPermintaanCabang extends Model
{
    protected $table = 'um_permintaan_cabang';

    protected $fillable = [
        'no_permintaan',
        'tanggal',
        'unit_kerja',
        'jenis',
        'uraian',
        'jumlah',
        'satuan',
        'dokumen',
        'status',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'petugas',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date',
        'approved_at' => 'datetime'
        ];
    }

    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}
