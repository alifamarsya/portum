<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

// PERINGATAN: Command ini HANYA untuk keperluan demo/testing.
// Di production, JANGAN gunakan command ini karena akan mengubah hash seluruh rantai.
// Fungsi: Menghitung ulang hash semua baris dari baris tertentu ke atas,
// sehingga rantai kembali valid setelah simulasi manipulasi sengaja.
class RepairAuditChainCommand extends Command
{
    protected $signature   = 'audit:repair-chain {--from=1 : Mulai perbaiki dari ID berapa}';
    protected $description = '[DEMO ONLY] Hitung ulang hash seluruh rantai audit_log mulai dari ID tertentu';

    public function handle(): int
    {
        $fromId = (int) $this->option('from');

        $this->warn("⚠️  [DEMO] Menghitung ulang rantai hash mulai dari log id >= {$fromId} ...");

        if (!$this->confirm('Lanjutkan? (Ini akan mengubah hash di database)')) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        // Ambil hash dari baris sebelum titik perbaikan (sebagai prev_hash awal)
        $prevLog      = AuditLog::where('id', '<', $fromId)->orderByDesc('id')->first();
        $expectedPrev = $prevLog?->hash;

        $diperbaiki = 0;

        AuditLog::where('id', '>=', $fromId)
            ->orderBy('id')
            ->chunk(500, function ($logs) use (&$expectedPrev, &$diperbaiki) {
                foreach ($logs as $log) {
                    // Hitung ulang hash dari data mentah yang ada di baris ini
                    $hashBaru = hash(
                        'sha256',
                        $expectedPrev . '|' . $log->aksi . '|' . $log->modul . '|'
                        . $log->entitas . '|' . $log->entitas_id . '|' . $log->keterangan . '|' . $log->created_at
                    );

                    // Update prev_hash dan hash di database langsung (bypass Eloquent booted)
                    \Illuminate\Support\Facades\DB::table('audit_log')
                        ->where('id', $log->id)
                        ->update([
                            'prev_hash' => $expectedPrev,
                            'hash'      => $hashBaru,
                        ]);

                    $this->line("  ✔ log #{$log->id} hash diperbarui");

                    $expectedPrev = $hashBaru;
                    $diperbaiki++;
                }
            });

        $this->info("Selesai. {$diperbaiki} baris hash diperbarui.");
        $this->info("Jalankan 'php artisan audit:verify-chain' untuk konfirmasi.");

        return self::SUCCESS;
    }
}
