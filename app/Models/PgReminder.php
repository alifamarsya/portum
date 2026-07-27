<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgReminder extends Model
{
    protected $table = 'pg_reminder';

    protected $fillable = [
        'judul',
        'kategori',
        'tanggal_jatuh_tempo',
        'status',
        'catatan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_jatuh_tempo' => 'date'
        ];
    }
}
