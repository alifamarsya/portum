<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    protected $casts = [
        'created_at' => 'datetime',
    ];
    
    protected $fillable = [
        'user_id',
        'username',
        'aksi',
        'modul',
        'entitas',
        'entitas_id',
        'keterangan',
        'ip_address',
        'user_agent',
        'prev_hash',
        'hash'
    ];
    public $timestamps = false;

    protected static function booted(): void
    {
        // CPMK Blockchain: setiap baris baru dirantai ke hash baris sebelumnya.
        static::creating(function (AuditLog $log) {
            $prev = static::orderByDesc('id')->first();
            $log->prev_hash = $prev?->hash;
            $log->created_at = $log->created_at ?? now();
            $log->hash = hash('sha256', $log->prev_hash . '|' . $log->aksi . '|' . $log->modul . '|'
                . $log->entitas . '|' . $log->entitas_id . '|' . $log->keterangan . '|' . $log->created_at);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
