<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsMemoSewaCabang extends Model
{
    protected $table = 'as_memo_sewa_cabang';

    protected $fillable = [
        'no_memo',
        'cabang',
        'jenis',
        'tanggal',
        'nilai',
        'dokumen',
        'status_persetujuan',
        'maker_id',
        'checker_id',
        'approved_at',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date',
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
