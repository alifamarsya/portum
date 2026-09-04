<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalDepartment extends Model
{
    use HasFactory;

    protected $table = 'internal_departments';

    protected $fillable = [
        'name',
    ];

    /**
     * Get the tickets assigned to this department.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'department_id');
    }
}
