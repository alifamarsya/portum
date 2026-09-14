<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmJadwalPemeliharaan extends Model
{
    protected $table = 'pm_jadwal_pemeliharaan';

    protected $fillable = [
        'no_jadwal',
        'aset_id',
        'nama_aset',
        'jenis_pemeliharaan',
        'tanggal_rencana',
        'tanggal_realisasi',
        'pelaksana',
        'status',
        'keterangan',
        'dokumen',
        'maker_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_rencana'    => 'date',
            'tanggal_realisasi'  => 'date',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(AsAset::class, 'aset_id');
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }
}
