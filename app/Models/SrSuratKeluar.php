<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrSuratKeluar extends Model
{
    protected $table = 'sr_surat_keluar';

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
