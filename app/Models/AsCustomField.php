<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsCustomField extends Model
{
    use HasFactory;

    protected $table = 'as_custom_fields';

    protected $fillable = [
        'module_key',
        'field_name',
        'label',
        'field_type',
        'options',
        'is_required',
        'show_in_list',
        'sort_order',
        'help_text',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'options'      => 'array',
            'is_system'    => 'boolean',
            'is_required'  => 'boolean',
            'show_in_list' => 'boolean',
            'is_active'    => 'boolean',
            'sort_order'   => 'integer',
        ];
    }

    public function scopeForModule($query, string $moduleKey)
    {
        return $query->where('module_key', $moduleKey)->where('is_active', true)->orderBy('sort_order');
    }
}
