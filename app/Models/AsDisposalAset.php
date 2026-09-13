<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsDisposalAset extends Model
{
    use HasFactory;

    protected $table = 'as_disposal_aset';

    protected $fillable = [
        'no_disposal',
        'aset_id',
        'tanggal_pengajuan',
        'alasan_penghapusan',
        'metode',
        'nilai_buku_terakhir',
        'status',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'keterangan',
        'dokumen',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan'   => 'date',
            'nilai_buku_terakhir' => 'decimal:2',
            'approved_at'         => 'datetime',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(AsAset::class, 'aset_id');
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
