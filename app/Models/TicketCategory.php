<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    use HasFactory;

    protected $table = 'ticket_categories';

    protected $fillable = [
        'name',
        'jenis_pengajuan',
        'sla_resolution_hours',
        'default_sla_hours',
        'is_active',
        'department_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sla_resolution_hours' => 'integer',
            'default_sla_hours' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the department assigned to handle this category.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(InternalDepartment::class, 'department_id');
    }

    /**
     * Get the tickets for this category.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }

    /**
     * Scope for active categories only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope filter by jenis_pengajuan (Permintaan / Permasalahan).
     */
    public function scopeByJenis($query, string $jenis)
    {
        return $query->where('jenis_pengajuan', $jenis);
    }

    public function getNamaKategoriAttribute(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function setNamaKategoriAttribute(?string $value): void
    {
        $this->attributes['name'] = $value;
    }
}
