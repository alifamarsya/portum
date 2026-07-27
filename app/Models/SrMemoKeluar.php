<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrMemoKeluar extends Model
{
    protected $table = 'sr_memo_keluar';

    protected $fillable = [
        'nomor_agenda',
        'no_surat',
        'pengirim',
        'perihal',
        'tanggal',
        'penerima',
        'lokasi_arsip',
        'lampiran',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
