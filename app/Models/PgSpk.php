<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgSpk extends Model
{
    protected $table = 'pg_spk';

    protected $fillable = [
        'no_spk',
        'vendor',
        'pekerjaan',
        'nilai',
        'tanggal_terbit',
        'tanggal_selesai',
        'dokumen',
        'status',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_terbit' => 'date',
        'tanggal_selesai' => 'date',
        'nilai' => 'decimal:2',
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
