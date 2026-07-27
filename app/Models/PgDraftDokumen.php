<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgDraftDokumen extends Model
{
    protected $table = 'pg_draft_dokumen';

    protected $fillable = [
        'jenis',
        'no_dokumen',
        'judul',
        'vendor',
        'tanggal',
        'status',
        'file',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
