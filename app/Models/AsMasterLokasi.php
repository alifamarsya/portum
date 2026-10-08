<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsMasterLokasi extends Model
{
    use HasFactory;

    protected $table = 'as_master_lokasi';

    protected $fillable = [
        'nama_lokasi',
        'tipe',
        'kode_lokasi',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function personels(): HasMany
    {
        return $this->hasMany(AsMasterPersonel::class, 'lokasi_id');
    }

    public function activePersonels(): HasMany
    {
        return $this->personels()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
