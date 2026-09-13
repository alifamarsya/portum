<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsAsetHistory extends Model
{
    use HasFactory;

    protected $table = 'as_aset_histories';

    protected $fillable = [
        'aset_id',
        'user_id',
        'field_changed',
        'old_value',
        'new_value',
        'keterangan',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(AsAset::class, 'aset_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
