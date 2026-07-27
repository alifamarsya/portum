<?php

use App\Jobs\CheckJatuhTempoReminderJob;
use App\Jobs\GenerateLaporanBiayaBulananJob;
use Illuminate\Support\Facades\Schedule;

// Laravel 13 Task Scheduler (menggantikan app/Console/Kernel.php lama).
// Jalankan `php artisan schedule:work` saat development, atau daftarkan
// satu baris cron `* * * * * php artisan schedule:run` di server produksi.

// CPMK Sistem Komputasi Terdistribusi: scheduler ini yang men-dispatch job
// ke queue -- worker (`php artisan queue:work`) yang benar-benar
// mengeksekusinya, proses terpisah dari scheduler maupun dari web server.
Schedule::job(new CheckJatuhTempoReminderJob)
    ->dailyAt('07:00')
    ->name('cek-reminder-jatuh-tempo')
    ->withoutOverlapping();

// CPMK Data Warehouse: ETL bulanan, jalan tanggal 1 tiap bulan untuk
// data bulan sebelumnya (default command tanpa opsi = bulan lalu).
Schedule::command('dw:etl')
    ->monthlyOn(1, '02:00')
    ->name('etl-data-warehouse')
    ->withoutOverlapping();

// CPMK Blockchain: verifikasi rantai hash audit_log tiap malam, supaya
// manipulasi data langsung di database (bypass Eloquent) cepat ketahuan.
Schedule::command('audit:verify-chain')
    ->dailyAt('23:30')
    ->name('verifikasi-rantai-audit')
    ->emailOutputOnFailure(config('mail.admin_address', 'admin@banksulteng.co.id'));
