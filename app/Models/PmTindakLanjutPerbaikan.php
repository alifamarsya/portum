<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmTindakLanjutPerbaikan extends Model
{
    protected $table = 'pm_tindak_lanjut_perbaikan';

    protected $fillable = [
        'no_tindak_lanjut',
        'aset_id',
        'nama_aset',
        'ticket_id',
        'sumber',
        'uraian_kerusakan',
        'tanggal_laporan',
        'tanggal_perbaikan',
        'teknisi',
        'hasil_perbaikan',
        'status',
        'dokumen',
        'maker_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_laporan'   => 'date',
            'tanggal_perbaikan' => 'date',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(AsAset::class, 'aset_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }
}
