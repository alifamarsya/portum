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
        'custom_fields',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_at'    => 'datetime',
            'custom_fields' => 'array',
        ];
    }

    public function __get($key)
    {
        $val = parent::__get($key);
        if ($val === null && isset($this->attributes['custom_fields'])) {
            $cf = is_array($this->custom_fields) ? $this->custom_fields : json_decode($this->attributes['custom_fields'] ?? '{}', true);
            if (is_array($cf) && array_key_exists($key, $cf)) {
                return $cf[$key];
            }
        }
        return $val;
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
