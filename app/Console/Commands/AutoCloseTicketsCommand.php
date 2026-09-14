<?php

namespace App\Console\Commands;

use App\Services\TicketService;
use Illuminate\Console\Command;

class AutoCloseTicketsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:auto-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menutup otomatis tiket berstatus Selesai yang telah melewati batas waktu konfirmasi 2 hari kerja';

    public function handle(TicketService $ticketService): int
    {
        $this->info('Memeriksa tiket berstatus Selesai yang melewati batas waktu konfirmasi...');

        $closedCount = $ticketService->autoCloseExpiredTickets();

        if ($closedCount > 0) {
            $this->info("Berhasil menutup otomatis {$closedCount} tiket.");
        } else {
            $this->line('Tidak ada tiket yang perlu ditutup otomatis saat ini.');
        }

        return Command::SUCCESS;
    }
}
