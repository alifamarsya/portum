<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\TicketSlaService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckTicketResponseSlaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:check-sla {--backfill : Mengisi data SLA untuk tiket lama yang belum memiliki SLA}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memantau SLA Response Time (maksimal 2 jam kerja) tiket dalam antrean Menunggu Verifikasi';

    public function handle(TicketSlaService $slaService): int
    {
        $this->info('=== PEMANTAUAN SLA RESPONSE & RESOLUTION TIME ===');
        $this->line('Waktu Sekarang: ' . now()->translatedFormat('l, d F Y H:i:s') . ' WITA');
        $this->line('Jam Kerja: 08:00 - 17:00 (Senin - Jumat)');
        $this->newLine();

        // 1. Backfill jika diminta atau jika ada tiket tanpa start/due response
        $ticketsNeedingBackfill = Ticket::whereNull('sla_response_due_at')->get();
        if ($ticketsNeedingBackfill->isNotEmpty()) {
            $this->comment("Ditemukan {$ticketsNeedingBackfill->count()} tiket tanpa kalkulasi SLA Response. Melakukan kalkulasi...");
            foreach ($ticketsNeedingBackfill as $t) {
                $start = $slaService->calculateSlaStart($t->created_at);
                $due   = $slaService->calculateResponseDueAt($t->created_at);

                $update = [
                    'sla_response_start_at' => $start,
                    'sla_response_due_at'   => $due,
                ];

                if ($t->status === 'Menunggu Verifikasi') {
                    $update['sla_response_status'] = now()->gt($due) ? 'Terlambat' : 'Menunggu';
                } elseif (!is_null($t->verified_at)) {
                    $mins = $slaService->calculateWorkingMinutesBetween($start, Carbon::parse($t->verified_at));
                    $update['sla_response_time_minutes'] = $mins;
                    $update['sla_response_status'] = $mins <= TicketSlaService::MAX_RESPONSE_MINUTES ? 'Tepat Waktu' : 'Terlambat';
                }

                $t->update($update);
            }
            $this->info("Berhasil mengkalkulasi SLA Response untuk {$ticketsNeedingBackfill->count()} tiket lama.");
            $this->newLine();
        }

        // 2. Periksa tiket yang sedang 'Menunggu Verifikasi' (SLA Response Time)
        $pendingTickets = Ticket::where('status', 'Menunggu Verifikasi')
            ->with(['user'])
            ->orderBy('created_at')
            ->get();

        $this->info("--- 1. SLA RESPONSE TIME (MAKS. 2 JAM KERJA) ---");
        if ($pendingTickets->isEmpty()) {
            $this->line('Tidak ada tiket yang sedang menunggu verifikasi saat ini.');
        } else {
            $this->info("Ditemukan {$pendingTickets->count()} tiket dalam status Menunggu Verifikasi:");
            $tableData = [];

            $overdueCount = 0;
            $warningCount = 0;
            $onTimeCount  = 0;

            foreach ($pendingTickets as $ticket) {
                $slaInfo = $slaService->getSlaResponseInfo($ticket);

                if ($slaInfo['is_overdue'] && $ticket->sla_response_status !== 'Terlambat') {
                    $ticket->update(['sla_response_status' => 'Terlambat']);
                }

                if ($slaInfo['is_overdue']) {
                    $overdueCount++;
                } elseif ($slaInfo['is_warning']) {
                    $warningCount++;
                } else {
                    $onTimeCount++;
                }

                $tableData[] = [
                    $ticket->ticket_number,
                    $ticket->user?->nama_lengkap ?? '-',
                    $ticket->created_at->format('d/m/Y H:i'),
                    $slaInfo['start_at']->format('d/m/Y H:i'),
                    $slaInfo['due_at']->format('d/m/Y H:i'),
                    $slaInfo['elapsed_formatted'],
                    $slaInfo['remaining_formatted'],
                    $slaInfo['status'],
                ];
            }

            $this->table(
                ['No. Tiket', 'Pemohon', 'Dibuat', 'SLA Mulai', 'Batas Akhir (Due)', 'Waktu Terpakai', 'Sisa Waktu', 'Status SLA'],
                $tableData
            );
            $this->line("Ringkasan Response: <fg=green>{$onTimeCount} Dalam SLA</> | <fg=yellow>{$warningCount} Kritis (< 30m)</> | <fg=red>{$overdueCount} Terlambat</>");
        }

        $this->newLine();

        // 3. Periksa tiket yang sedang berjalan (SLA Resolution Time)
        $this->info("--- 2. SLA RESOLUTION TIME (Kritis=4j, Tinggi=12j, Sedang=48j, Rendah=72j) ---");
        $inProgressTickets = Ticket::whereIn('status', ['Didistribusikan', 'Dalam Proses'])
            ->whereNotNull('sla_resolution_start_at')
            ->with(['user', 'assignedToUser'])
            ->orderBy('sla_resolution_due_at')
            ->get();

        if ($inProgressTickets->isEmpty()) {
            $this->line('Tidak ada tiket yang sedang berjalan dengan SLA Resolusi aktif.');
        } else {
            $resTableData = [];
            $resOverdue = 0;
            $resWarning = 0;
            $resOnTime = 0;

            foreach ($inProgressTickets as $ticket) {
                $slaRes = $slaService->getSlaResolutionInfo($ticket);

                if ($slaRes['is_overdue'] && $ticket->sla_resolution_status !== 'Terlambat') {
                    $ticket->update(['sla_resolution_status' => 'Terlambat']);
                }

                if ($slaRes['is_overdue']) {
                    $resOverdue++;
                } elseif ($slaRes['is_warning']) {
                    $resWarning++;
                } else {
                    $resOnTime++;
                }

                $resTableData[] = [
                    $ticket->ticket_number,
                    $ticket->priority ?? 'Sedang',
                    $ticket->assignedToUser?->nama_lengkap ?? '-',
                    $slaRes['target_hours'] . ' Jam',
                    $slaRes['start_at'] ? $slaRes['start_at']->format('d/m/Y H:i') : '-',
                    $slaRes['due_at'] ? $slaRes['due_at']->format('d/m/Y H:i') : '-',
                    $slaRes['remaining_formatted'],
                    $slaRes['status'],
                ];
            }

            $this->table(
                ['No. Tiket', 'Prioritas', 'Teknisi/Staf', 'Target', 'Mulai (Kabag Disetujui)', 'Batas Akhir (Due)', 'Sisa / Status', 'Status Resolusi'],
                $resTableData
            );
            $this->line("Ringkasan Resolusi: <fg=green>{$resOnTime} Dalam SLA</> | <fg=yellow>{$resWarning} Kritis (< 4j)</> | <fg=red>{$resOverdue} Terlambat</>");
        }

        return Command::SUCCESS;
    }
}
