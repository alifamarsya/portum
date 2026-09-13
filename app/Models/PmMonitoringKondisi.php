<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmMonitoringKondisi extends Model
{
    protected $table = 'pm_monitoring_kondisi';

    protected $fillable = [
        'aset_id',
        'nama_aset',
        'tanggal_inspeksi',
        'kondisi',
        'temuan',
        'rekomendasi',
        'petugas',
        'status',
        'dokumen',
        'maker_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_inspeksi' => 'date',
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
