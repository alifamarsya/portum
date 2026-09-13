<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsRekonsiliasiAset extends Model
{
    use HasFactory;

    protected $table = 'as_rekonsiliasi_aset';

    protected $fillable = [
        'no_rekonsiliasi',
        'periode',
        'tanggal',
        'aset_id',
        'jenis',
        'kategori_awal',
        'kategori_baru',
        'kondisi_awal',
        'kondisi_baru',
        'hasil_rekonsiliasi',
        'status',
        'petugas',
        'dokumen',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(AsAset::class, 'aset_id');
    }
}
