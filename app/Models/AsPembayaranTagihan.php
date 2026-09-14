<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsPembayaranTagihan extends Model
{
    use HasFactory;

    protected $table = 'as_pembayaran_tagihan';

    protected $fillable = [
        'no_tagihan',
        'tanggal_tagihan',
        'tanggal_bayar',
        'vendor',
        'uraian',
        'nilai',
        'status',
        'no_rekening',
        'dokumen',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_tagihan' => 'date',
            'tanggal_bayar'   => 'date',
            'nilai'           => 'decimal:2',
            'approved_at'     => 'datetime',
        ];
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}
