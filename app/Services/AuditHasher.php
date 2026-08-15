<?php

namespace App\Services;

class AuditHasher
{
    /**
     * Logika hash chain yang sama persis dengan AuditLog::booted(),
     * diekstrak ke sini supaya bisa di-unit-test tanpa Eloquent/database.
     */
    public function hitungHash(
        ?string $prevHash,
        string $aksi,
        string $modul,
        string $entitas,
        int|string|null $entitasId,
        ?string $keterangan,
        string $createdAt
    ): string {
        $payload = $prevHash . '|' . $aksi . '|' . $modul . '|'
            . $entitas . '|' . $entitasId . '|' . $keterangan . '|' . $createdAt;

        return hash('sha256', $payload);
    }

    /**
     * Verifikasi satu baris log terhadap hash yang tersimpan.
     * Dipakai AuditComplianceCheckCommand & bisa dipakai VerifyAuditChainCommand
     * kalau nanti mau direfaktor supaya tidak duplikasi logika hash.
     */
    public function cocok(string $hashTersimpan, string $hashHitung): bool
    {
        return hash_equals($hashHitung, $hashTersimpan);
    }
}