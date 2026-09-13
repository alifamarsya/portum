<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmPengawasanPenggunaan extends Model
{
    protected $table = 'pm_pengawasan_penggunaan';

    protected $fillable = [
        'aset_id',
        'nama_aset',
        'tanggal',
        'pengguna',
        'uraian_penggunaan',
        'kesesuaian',
        'catatan',
        'petugas',
        'dokumen',
        'maker_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
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
}
