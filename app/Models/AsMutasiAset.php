<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsMutasiAset extends Model
{
    use HasFactory;

    protected $table = 'as_mutasi_aset';

    protected $fillable = [
        'no_mutasi',
        'aset_id',
        'dari_lokasi',
        'ke_lokasi',
        'dari_penanggung_jawab',
        'ke_penanggung_jawab',
        'alasan',
        'status',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'keterangan',
        'dokumen',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
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

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}
