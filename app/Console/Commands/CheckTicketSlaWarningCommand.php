<?php

namespace App\Console\Commands;

use App\Services\TicketSlaService;
use Illuminate\Console\Command;

class CheckTicketSlaWarningCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:check-sla-warning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memeriksa tiket yang ditugaskan kepada staf unit kerja dengan sisa waktu SLA Resolution <= 2 jam dan mengirim notifikasi peringatan';

    public function handle(TicketSlaService $slaService): int
    {
        $this->info('=== PEMANTAUAN SLA PERINGATAN STAF (SISA <= 2 JAM KERJA) ===');
        $this->line('Waktu Sekarang: ' . now()->translatedFormat('l, d F Y H:i:s') . ' WITA');
        $this->newLine();

        $result = $slaService->checkAndNotifyStaffSlaWarning();

        $this->line("Total tiket aktif yang diperiksa : {$result['total_checked']}");
        $this->line("Tiket bukan staf ditugaskan     : {$result['skipped_not_staff']}");
        $this->line("Tiket belum dalam masa warning  : {$result['skipped_not_in_warning']}");
        $this->line("Dilewati (sudah pernah dikirim) : {$result['skipped_already_notified']}");
        $this->newLine();

        if ($result['notified_count'] > 0) {
            $this->info("Berhasil mengirim {$result['notified_count']} notifikasi peringatan SLA ke staf terkait:");
            
            $tableData = [];
            foreach ($result['notified_tickets'] as $item) {
                $tableData[] = [
                    $item['ticket_number'],
                    $item['staff_name'],
                    $item['remaining_formatted'],
                    $item['due_at'] ? $item['due_at']->format('d/m/Y H:i') : '-',
                ];
            }

            $this->table(
                ['No. Tiket', 'Staf Ditugaskan', 'Sisa Waktu', 'Batas Waktu (Due)'],
                $tableData
            );
        } else {
            $this->line('Tidak ada tiket yang memerlukan pengiriman notifikasi peringatan SLA saat ini.');
        }

        return Command::SUCCESS;
    }
}
