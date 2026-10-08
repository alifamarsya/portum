<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketField extends Model
{
    use HasFactory;

    protected $table = 'ticket_fields';

    protected $fillable = [
        'field_name',
        'label',
        'field_type',
        'options',
        'is_required',
        'show_in_form',
        'show_in_list',
        'sort_order',
        'help_text',
        'is_active',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'options'      => 'array',
            'is_required'  => 'boolean',
            'show_in_form' => 'boolean',
            'show_in_list' => 'boolean',
            'is_active'    => 'boolean',
            'is_system'    => 'boolean',
            'sort_order'   => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeForForm($query)
    {
        return $query->where('is_active', true)
                     ->where('show_in_form', true)
                     ->orderBy('sort_order')
                     ->orderBy('id');
    }

    public function scopeForList($query)
    {
        return $query->where('is_active', true)
                     ->where('show_in_list', true)
                     ->orderBy('sort_order')
                     ->orderBy('id');
    }
}
