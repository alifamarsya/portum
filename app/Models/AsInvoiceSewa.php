<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsInvoiceSewa extends Model
{
    protected $table = 'as_invoice_sewa';

    protected $fillable = [
        'no_invoice',
        'vendor',
        'jenis_sewa',
        'periode_mulai',
        'periode_selesai',
        'nilai',
        'jatuh_tempo',
        'dokumen',
        'status',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'jatuh_tempo' => 'date',
        'nilai' => 'decimal:2'
        ];
    }
}
