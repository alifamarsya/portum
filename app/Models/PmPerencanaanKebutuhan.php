<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmPerencanaanKebutuhan extends Model
{
    protected $table = 'pm_perencanaan_kebutuhan';

    protected $fillable = [
        'no_rencana',
        'periode',
        'jenis_kebutuhan',
        'nama_item',
        'jumlah',
        'satuan',
        'spesifikasi',
        'estimasi_harga',
        'prioritas',
        'status',
        'keterangan',
        'maker_id',
    ];

    protected function casts(): array
    {
        return [
            'estimasi_harga' => 'decimal:2',
        ];
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }
}
