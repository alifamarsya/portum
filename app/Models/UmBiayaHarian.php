<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmBiayaHarian extends Model
{
    protected $table = 'um_biaya_harian';

    protected $fillable = [
        'tanggal',
        'kategori',
        'kendaraan',
        'nama_beban',
        'rekening_debet',
        'rekening_kredit',
        'uraian',
        'jumlah',
        'no_nota',
        'dokumen',
        'status',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'catatan_approval',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
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
