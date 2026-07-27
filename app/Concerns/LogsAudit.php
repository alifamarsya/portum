<?php

namespace App\Concerns;

use App\Models\AuditLog;

trait LogsAudit
{
    protected function audit(string $aksi, string $modul, string $entitas, int|string $entitasId, string $keterangan): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'username' => auth()->user()?->username,
            'aksi' => $aksi,
            'modul' => $modul,
            'entitas' => $entitas,
            'entitas_id' => (string) $entitasId,
            'keterangan' => $keterangan,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
