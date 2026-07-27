<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsPks extends Model
{
    protected $table = 'as_pks';

    protected $fillable = [
        'no_pks',
        'judul',
        'vendor',
        'div_owner',
        'tanggal_mulai',
        'jatuh_tempo',
        'nilai',
        'dokumen',
        'status',
        'memo_dibuat',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_mulai' => 'date',
        'jatuh_tempo' => 'date',
        'nilai' => 'decimal:2',
        'memo_dibuat' => 'boolean',
        'approved_at' => 'datetime'
        ];
    }

    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}
