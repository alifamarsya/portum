<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;

class TicketSlaService
{
    public const WORK_START_HOUR = 8;  // 08:00
    public const WORK_START_MINUTE = 0;
    public const WORK_END_HOUR = 17;   // 17:00
    public const WORK_END_MINUTE = 0;
    public const MAX_RESPONSE_HOURS = 2; // 2 jam maksimal response time
    public const MAX_RESPONSE_MINUTES = 120;

    /**
     * Hitung waktu awal timer SLA berjalan berdasarkan jam dan hari kerja.
     * - Hanya dihitung pada hari kerja (Senin - Jumat), jam 08:00 - 17:00.
     * - Masuk sebelum 08:00 -> mulai pukul 08:00 hari yang sama.
     * - Masuk antara 08:00 - 17:00 -> mulai saat itu juga.
     * - Masuk setelah 17:00 -> mulai pukul 08:00 hari kerja berikutnya.
     * - Masuk akhir pekan (Sabtu/Minggu) -> mulai pukul 08:00 hari Senin berikutnya.
     */
    public function calculateSlaStart(Carbon $createdAt): Carbon
    {
        $time = $createdAt->copy();

        // 1. Jika jatuh di akhir pekan, geser ke hari Senin jam 08:00
        if ($time->isWeekend()) {
            return $this->getNextWorkDayStart($time);
        }

        $workStart = $time->copy()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE, 0);
        $workEnd   = $time->copy()->setTime(self::WORK_END_HOUR, self::WORK_END_MINUTE, 0);

        // 2. Jika sebelum jam 08:00 hari kerja, timer mulai jam 08:00 hari ini
        if ($time->lt($workStart)) {
            return $workStart;
        }

        // 3. Jika setelah atau tepat jam 17:00, timer mulai jam 08:00 hari kerja berikutnya
        if ($time->gte($workEnd)) {
            return $this->getNextWorkDayStart($time);
        }

        // 4. Jika berada di dalam jam kerja (08:00 - 17:00), mulai saat itu juga
        return $time;
    }

    /**
     * Hitung batas akhir (due time) Response Time SLA (maksimal 2 jam kerja).
     * Memperhitungkan carry-over ke hari kerja berikutnya jika sisa jam kerja hari ini < 2 jam.
     */
    public function calculateResponseDueAt(Carbon $createdAt, int $maxMinutes = self::MAX_RESPONSE_MINUTES): Carbon
    {
        $start = $this->calculateSlaStart($createdAt);
        $workEnd = $start->copy()->setTime(self::WORK_END_HOUR, self::WORK_END_MINUTE, 0);

        $minutesLeftToday = $start->diffInMinutes($workEnd, false);

        if ($minutesLeftToday >= $maxMinutes) {
            // Sisa waktu hari ini mencukupi 2 jam kerja
            return $start->copy()->addMinutes($maxMinutes);
        }

        // Sisa waktu hari ini tidak mencukupi, sisa menit dioper ke hari kerja berikutnya mulai 08:00
        $leftoverMinutes = $maxMinutes - max(0, $minutesLeftToday);
        $nextWorkDayStart = $this->getNextWorkDayStart($start);

        return $nextWorkDayStart->addMinutes($leftoverMinutes);
    }

    /**
     * Cari awal jam kerja (08:00) pada hari kerja berikutnya.
     */
    public function getNextWorkDayStart(Carbon $date): Carbon
    {
        $next = $date->copy()->addDay()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE, 0);

        while ($next->isWeekend()) {
            $next->addDay();
        }

        return $next;
    }

    /**
     * Menghitung total menit jam kerja (08:00 - 17:00, Senin - Jumat)
     * yang telah berlalu antara dua tanggal.
     */
    public function calculateWorkingMinutesBetween(Carbon $from, Carbon $to): int
    {
        if ($to->lte($from)) {
            return 0;
        }

        $totalMinutes = 0;
        $current = $from->copy();

        while ($current->lt($to)) {
            if (!$current->isWeekend()) {
                $dayWorkStart = $current->copy()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE, 0);
                $dayWorkEnd   = $current->copy()->setTime(self::WORK_END_HOUR, self::WORK_END_MINUTE, 0);

                // Menentukan window kerja hari ini yang overlapping dengan [$current, $to]
                $windowStart = $current->gt($dayWorkStart) ? $current->copy() : $dayWorkStart;
                $windowEnd   = $to->lt($dayWorkEnd) ? $to->copy() : $dayWorkEnd;

                if ($windowStart->lt($windowEnd) && $windowEnd->gt($dayWorkStart) && $windowStart->lt($dayWorkEnd)) {
                    // Pastikan windowStart tidak sebelum 08:00 dan windowEnd tidak setelah 17:00
                    $effectiveStart = $windowStart->lt($dayWorkStart) ? $dayWorkStart : $windowStart;
                    $effectiveEnd   = $windowEnd->gt($dayWorkEnd) ? $dayWorkEnd : $windowEnd;

                    if ($effectiveEnd->gt($effectiveStart)) {
                        $totalMinutes += $effectiveStart->diffInMinutes($effectiveEnd);
                    }
                }
            }

            // Pindah ke awal hari berikutnya jam 08:00
            $current->addDay()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE, 0);
        }

        return $totalMinutes;
    }

    /**
     * Dapatkan informasi detail SLA Response Time untuk sebuah tiket.
     */
    public function getSlaResponseInfo(Ticket $ticket): array
    {
        $createdAt = $ticket->created_at ?? now();
        $startAt = $ticket->sla_response_start_at 
            ? Carbon::parse($ticket->sla_response_start_at) 
            : $this->calculateSlaStart($createdAt);

        $dueAt = $ticket->sla_response_due_at 
            ? Carbon::parse($ticket->sla_response_due_at) 
            : $this->calculateResponseDueAt($createdAt);

        $now = now();
        $isVerified = !is_null($ticket->verified_at) || !in_array($ticket->status, ['Menunggu Verifikasi']);
        $verifiedAt = $ticket->verified_at ? Carbon::parse($ticket->verified_at) : null;

        if ($isVerified) {
            $endComparison = $verifiedAt ?? Carbon::parse($ticket->updated_at ?? now());
            $elapsedMinutes = $ticket->sla_response_time_minutes 
                ?? $this->calculateWorkingMinutesBetween($startAt, $endComparison);
            $isOverdue = $elapsedMinutes > self::MAX_RESPONSE_MINUTES;

            return [
                'is_verified'        => true,
                'is_overdue'         => $isOverdue,
                'is_warning'         => false,
                'status'             => $isOverdue ? 'Terlambat' : 'Tepat Waktu',
                'start_at'           => $startAt,
                'due_at'             => $dueAt,
                'verified_at'        => $endComparison,
                'elapsed_minutes'    => $elapsedMinutes,
                'elapsed_formatted'  => $this->formatMinutes($elapsedMinutes),
                'remaining_minutes'  => max(0, self::MAX_RESPONSE_MINUTES - $elapsedMinutes),
                'remaining_formatted'=> $isOverdue 
                    ? 'Terlambat ' . $this->formatMinutes($elapsedMinutes - self::MAX_RESPONSE_MINUTES)
                    : 'Diselesaikan dalam ' . $this->formatMinutes($elapsedMinutes),
                'percentage_used'    => min(100, (int) round(($elapsedMinutes / self::MAX_RESPONSE_MINUTES) * 100)),
                'badge_class'        => $isOverdue 
                    ? 'bg-rose-100 text-rose-800 border-rose-200' 
                    : 'bg-emerald-100 text-emerald-800 border-emerald-200',
            ];
        }

        // Tiket masih dalam status 'Menunggu Verifikasi'
        // Cek apakah timer sudah mulai berjalan atau masih menunggu jam kerja
        $isWaitingStart = $now->lt($startAt);
        $elapsedMinutes = $isWaitingStart ? 0 : $this->calculateWorkingMinutesBetween($startAt, $now);
        $isOverdue = $now->gt($dueAt) || $elapsedMinutes > self::MAX_RESPONSE_MINUTES;
        $remainingMinutes = max(0, self::MAX_RESPONSE_MINUTES - $elapsedMinutes);
        $isWarning = !$isOverdue && !$isWaitingStart && $remainingMinutes <= 30;

        if ($isWaitingStart) {
            $status = 'Menunggu Jam Kerja';
            $remainingFormatted = 'Mulai ' . $startAt->translatedFormat('D, H:i');
            $badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
            $percentageUsed = 0;
        } elseif ($isOverdue) {
            $overdueMinutes = $elapsedMinutes > self::MAX_RESPONSE_MINUTES 
                ? ($elapsedMinutes - self::MAX_RESPONSE_MINUTES) 
                : $dueAt->diffInMinutes($now);
            $status = 'Terlambat (Melewati SLA)';
            $remainingFormatted = 'Lewat ' . $this->formatMinutes($overdueMinutes);
            $badgeClass = 'bg-rose-100 text-rose-800 border-rose-300 font-bold animate-pulse';
            $percentageUsed = 100;
        } elseif ($isWarning) {
            $status = 'Kritis (< 30 Menit)';
            $remainingFormatted = 'Sisa ' . $this->formatMinutes($remainingMinutes);
            $badgeClass = 'bg-amber-100 text-amber-900 border-amber-300 font-semibold';
            $percentageUsed = (int) round(($elapsedMinutes / self::MAX_RESPONSE_MINUTES) * 100);
        } else {
            $status = 'Berjalan (Dalam SLA)';
            $remainingFormatted = 'Sisa ' . $this->formatMinutes($remainingMinutes);
            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $percentageUsed = (int) round(($elapsedMinutes / self::MAX_RESPONSE_MINUTES) * 100);
        }

        return [
            'is_verified'        => false,
            'is_waiting_start'   => $isWaitingStart,
            'is_overdue'         => $isOverdue,
            'is_warning'         => $isWarning,
            'status'             => $status,
            'start_at'           => $startAt,
            'due_at'             => $dueAt,
            'verified_at'        => null,
            'elapsed_minutes'    => $elapsedMinutes,
            'elapsed_formatted'  => $this->formatMinutes($elapsedMinutes),
            'remaining_minutes'  => $remainingMinutes,
            'remaining_formatted'=> $remainingFormatted,
            'percentage_used'    => $percentageUsed,
            'badge_class'        => $badgeClass,
        ];
    }

    /**
     * Catat verifikasi tiket oleh Operator dan simpan hasil evaluasi Response Time SLA.
     */
    public function recordVerification(Ticket $ticket, ?User $operator = null): void
    {
        $now = now();
        $startAt = $ticket->sla_response_start_at 
            ? Carbon::parse($ticket->sla_response_start_at) 
            : $this->calculateSlaStart($ticket->created_at ?? $now);

        $elapsedMinutes = $this->calculateWorkingMinutesBetween($startAt, $now);
        $status = $elapsedMinutes <= self::MAX_RESPONSE_MINUTES ? 'Tepat Waktu' : 'Terlambat';

        $ticket->update([
            'verified_at'                => $now,
            'verified_by'                => $operator?->id ?? auth()->id(),
            'sla_response_time_minutes'  => $elapsedMinutes,
            'sla_response_status'        => $status,
        ]);
    }

    /**
     * Format menit ke format manusiawi yang rapi (contoh: "1 jam 25 mnt", "45 mnt").
     */
    public function formatMinutes(int $minutes): string
    {
        $minutes = max(0, $minutes);
        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        if ($hours > 0 && $remainder > 0) {
            return "{$hours} jam {$remainder} mnt";
        }

        if ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$remainder} mnt";
    }
}
