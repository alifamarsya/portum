<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsMasterKodeAset extends Model
{
    use HasFactory;

    protected $table = 'as_master_kode_aset';

    protected $fillable = [
        'kode',
        'nama',
        'jenis',
        'merek',
        'tipe',
        'kapasitas',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function asets(): HasMany
    {
        return $this->hasMany(AsAset::class, 'kode_aset_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
