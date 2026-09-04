<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLogAnchor extends Model
{
    protected $fillable = ['root_hash', 'tx_hash', 'block_number', 'periode_awal', 'periode_akhir'];
}