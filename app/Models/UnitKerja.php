<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitKerja extends Model
{
    use HasFactory;

    protected $table = 'unit_kerja';

    protected $fillable = [
        'department_id',
        'nama',
        'kode',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Unit kerja ini milik departemen mana.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(InternalDepartment::class, 'department_id');
    }

    /**
     * Staf (users) yang berada di unit kerja ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'unit_kerja_id');
    }

    /**
     * Tiket yang ditugaskan ke unit kerja ini.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'unit_kerja_id');
    }
}
