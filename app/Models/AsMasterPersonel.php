<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsMasterPersonel extends Model
{
    use HasFactory;

    protected $table = 'as_master_personel';

    protected $fillable = [
        'lokasi_id',
        'nama_personel',
        'nip',
        'jabatan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(AsMasterLokasi::class, 'lokasi_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
