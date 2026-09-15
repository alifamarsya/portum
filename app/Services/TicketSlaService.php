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

    public const RESOLUTION_HOURS_RENDAH = 72; // 72 jam kerja
    public const RESOLUTION_HOURS_SEDANG = 48; // 48 jam kerja
    public const RESOLUTION_HOURS_TINGGI = 12; // 12 jam kerja
    public const RESOLUTION_HOURS_KRITIS = 4;  // 4 jam kerja

    /**
     * Dapatkan target durasi resolusi default (dalam jam) berdasarkan tingkat prioritas.
     */
    public function getDefaultResolutionHours(?string $priority): int
    {
        return match ($priority) {
            'Kritis' => self::RESOLUTION_HOURS_KRITIS,
            'Tinggi' => self::RESOLUTION_HOURS_TINGGI,
            'Sedang' => self::RESOLUTION_HOURS_SEDANG,
            'Rendah' => self::RESOLUTION_HOURS_RENDAH,
            default  => self::RESOLUTION_HOURS_SEDANG,
        };
    }

    /**
     * Menambahkan sejumlah jam kerja (08:00 - 17:00, Senin - Jumat) ke waktu awal.
     * Otomatis melompati jam non-kerja, malam hari, dan akhir pekan.
     */
    public function addWorkingHours(Carbon $start, float|int $hours): Carbon
    {
        $current = $this->calculateSlaStart($start);
        $minutesToAdd = (int) round($hours * 60);

        while ($minutesToAdd > 0) {
            $dayWorkEnd = $current->copy()->setTime(self::WORK_END_HOUR, self::WORK_END_MINUTE, 0);
            $availableToday = $current->diffInMinutes($dayWorkEnd, false);

            if ($availableToday <= 0) {
                $current = $this->getNextWorkDayStart($current);
                continue;
            }

            if ($minutesToAdd <= $availableToday) {
                $current->addMinutes($minutesToAdd);
                $minutesToAdd = 0;
            } else {
                $minutesToAdd -= $availableToday;
                $current = $this->getNextWorkDayStart($current);
            }
        }

        return $current;
    }

    /**
     * Hitung batas akhir (due date) SLA Resolusi dari waktu disetujui Kabag.
     */
    public function calculateResolutionDueAt(Carbon $startAt, int $resolutionHours): Carbon
    {
        return $this->addWorkingHours($startAt, $resolutionHours);
    }

    /**
     * Hitung rekomendasi jam resolusi berdasarkan kategori pekerjaan, skala/eselonisasi, dan estimasi biaya.
     */
    public function calculateAdjustedResolutionHours(
        string $priority,
        ?string $kategoriPekerjaan = null,
        ?string $skalaEselonisasi = null,
        ?float $estimasiBiaya = null
    ): int {
        $baseHours = $this->getDefaultResolutionHours($priority);

        // Penyesuaian Kategori Pekerjaan
        $kategoriAdjust = match ($kategoriPekerjaan) {
            'Perbaikan Berat / Bongkar Pasang' => 24,
            'Penggantian Komponen / Suku Cadang' => 24,
            'Instalasi Baru / Pengadaan Khusus' => 48,
            default => 0,
        };

        // Penyesuaian Estimasi Biaya (proses pengadaan / administrasi anggaran)
        $biayaAdjust = 0;
        if ($estimasiBiaya !== null) {
            if ($estimasiBiaya > 25000000) {
                $biayaAdjust = 24; // > 25 Juta butuh verifikasi komite pengadaan
            } elseif ($estimasiBiaya >= 5000000) {
                $biayaAdjust = 12; // 5 - 25 Juta butuh SPB / nota dinas
            }
        }

        // Tiket Kritis tetap diprioritaskan cepat jika tidak ada kendala berat
        if ($priority === 'Kritis' && $kategoriAdjust > 0) {
            $kategoriAdjust = (int) round($kategoriAdjust / 2);
        }

        return $baseHours + $kategoriAdjust + $biayaAdjust;
    }

    /**
     * Mulai timer SLA Resolusi secara otomatis saat tiket disetujui & didisposisikan oleh Kabag.
     */
    public function startResolutionSla(Ticket $ticket, array $dispositionData, User $kabag): void
    {
        $now = now();
        $startAt = $this->calculateSlaStart($now);

        $priority = $ticket->priority ?? 'Sedang';
        $targetHours = isset($dispositionData['sla_resolution_hours']) && (int) $dispositionData['sla_resolution_hours'] > 0
            ? (int) $dispositionData['sla_resolution_hours']
            : $this->calculateAdjustedResolutionHours(
                $priority,
                $dispositionData['kategori_pekerjaan'] ?? null,
                $dispositionData['skala_eselonisasi'] ?? null,
                isset($dispositionData['estimasi_biaya']) ? (float) $dispositionData['estimasi_biaya'] : null
            );

        $dueAt = $this->calculateResolutionDueAt($startAt, $targetHours);

        $ticket->update([
            'kategori_pekerjaan'         => $dispositionData['kategori_pekerjaan'] ?? null,
            'skala_eselonisasi'          => $dispositionData['skala_eselonisasi'] ?? null,
            'estimasi_biaya'             => isset($dispositionData['estimasi_biaya']) ? (float) $dispositionData['estimasi_biaya'] : null,
            'sla_resolution_hours'       => $targetHours,
            'sla_resolution_start_at'    => $startAt,
            'sla_resolution_due_at'      => $dueAt,
            'sla_resolution_status'      => 'Berjalan',
        ]);
    }

    /**
     * Catat penyelesaian tiket saat status diubah menjadi 'Selesai'.
     */
    public function recordResolution(Ticket $ticket): void
    {
        if (is_null($ticket->sla_resolution_start_at) && $ticket->disposed_at) {
            $ticket->sla_resolution_start_at = $this->calculateSlaStart(Carbon::parse($ticket->disposed_at));
        }

        $now = now();
        $startAt = $ticket->sla_resolution_start_at ? Carbon::parse($ticket->sla_resolution_start_at) : $now;
        $targetHours = $ticket->sla_resolution_hours ?? $this->getDefaultResolutionHours($ticket->priority);
        $dueAt = $ticket->sla_resolution_due_at 
            ? Carbon::parse($ticket->sla_resolution_due_at) 
            : $this->calculateResolutionDueAt($startAt, $targetHours);

        $elapsedMinutes = $this->calculateWorkingMinutesBetween($startAt, $now);
        $maxAllowedMinutes = $targetHours * 60;
        $status = ($elapsedMinutes <= $maxAllowedMinutes && $now->lte($dueAt)) ? 'Tepat Waktu' : 'Terlambat';

        $ticket->update([
            'resolved_at'                 => $now,
            'sla_resolution_time_minutes' => $elapsedMinutes,
            'sla_resolution_status'       => $status,
        ]);
    }

    /**
     * Dapatkan detail informasi SLA Resolution Time untuk sebuah tiket.
     */
    public function getSlaResolutionInfo(Ticket $ticket): array
    {
        $priority = $ticket->priority ?? 'Sedang';
        $targetHours = $ticket->sla_resolution_hours ?? $this->getDefaultResolutionHours($priority);
        $maxMinutes = $targetHours * 60;

        // Jika belum disetujui / didisposisikan oleh Kabag
        if (is_null($ticket->sla_resolution_start_at) && is_null($ticket->disposed_at)) {
            return [
                'is_started'          => false,
                'is_resolved'         => false,
                'is_overdue'          => false,
                'is_warning'          => false,
                'status'              => 'Menunggu Persetujuan Kabag',
                'target_hours'        => $targetHours,
                'start_at'            => null,
                'due_at'              => null,
                'resolved_at'         => null,
                'elapsed_minutes'     => 0,
                'elapsed_formatted'   => '0 mnt',
                'remaining_minutes'   => $maxMinutes,
                'remaining_formatted' => "Standar {$targetHours} Jam Kerja",
                'percentage_used'     => 0,
                'badge_class'         => 'bg-slate-100 text-slate-600 border-slate-200',
            ];
        }

        $startAt = $ticket->sla_resolution_start_at 
            ? Carbon::parse($ticket->sla_resolution_start_at) 
            : $this->calculateSlaStart(Carbon::parse($ticket->disposed_at));

        $dueAt = $ticket->sla_resolution_due_at 
            ? Carbon::parse($ticket->sla_resolution_due_at) 
            : $this->calculateResolutionDueAt($startAt, $targetHours);

        $isResolved = in_array($ticket->status, ['Selesai', 'Ditutup Pemohon']) || !is_null($ticket->resolved_at);
        $resolvedAt = $ticket->resolved_at ? Carbon::parse($ticket->resolved_at) : ($isResolved ? Carbon::parse($ticket->updated_at ?? now()) : null);

        // Jika tiket sudah selesai
        if ($isResolved && $resolvedAt) {
            $elapsedMinutes = $ticket->sla_resolution_time_minutes 
                ?? $this->calculateWorkingMinutesBetween($startAt, $resolvedAt);
            $isOverdue = $elapsedMinutes > $maxMinutes || $resolvedAt->gt($dueAt);

            return [
                'is_started'          => true,
                'is_resolved'         => true,
                'is_overdue'          => $isOverdue,
                'is_warning'          => false,
                'status'              => $isOverdue ? 'Selesai Terlambat' : 'Selesai Tepat Waktu',
                'target_hours'        => $targetHours,
                'start_at'            => $startAt,
                'due_at'              => $dueAt,
                'resolved_at'         => $resolvedAt,
                'elapsed_minutes'     => $elapsedMinutes,
                'elapsed_formatted'   => $this->formatMinutes($elapsedMinutes),
                'remaining_minutes'   => max(0, $maxMinutes - $elapsedMinutes),
                'remaining_formatted' => $isOverdue 
                    ? 'Terlambat ' . $this->formatMinutes($elapsedMinutes - $maxMinutes)
                    : 'Selesai dalam ' . $this->formatMinutes($elapsedMinutes),
                'percentage_used'     => min(100, (int) round(($elapsedMinutes / $maxMinutes) * 100)),
                'badge_class'         => $isOverdue 
                    ? 'bg-rose-100 text-rose-800 border-rose-200' 
                    : 'bg-emerald-100 text-emerald-800 border-emerald-200',
            ];
        }

        // Tiket sedang berjalan (disetujui Kabag & dalam penanganan staf)
        $now = now();
        $isWaitingStart = $now->lt($startAt);
        $elapsedMinutes = $isWaitingStart ? 0 : $this->calculateWorkingMinutesBetween($startAt, $now);
        $isOverdue = $now->gt($dueAt) || $elapsedMinutes > $maxMinutes;
        $remainingMinutes = max(0, $maxMinutes - $elapsedMinutes);
        $percentageUsed = min(100, (int) round(($elapsedMinutes / $maxMinutes) * 100));
        $isWarning = !$isOverdue && !$isWaitingStart && ($remainingMinutes <= 240 || $percentageUsed >= 80);

        if ($isWaitingStart) {
            $status = 'Menunggu Jam Kerja';
            $remainingFormatted = 'Mulai ' . $startAt->translatedFormat('D, H:i');
            $badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
        } elseif ($isOverdue) {
            $overdueMinutes = $elapsedMinutes > $maxMinutes 
                ? ($elapsedMinutes - $maxMinutes) 
                : $dueAt->diffInMinutes($now);
            $status = 'Terlambat (Melewati SLA)';
            $remainingFormatted = 'Lewat ' . $this->formatMinutes($overdueMinutes);
            $badgeClass = 'bg-rose-100 text-rose-800 border-rose-300 font-bold animate-pulse';
        } elseif ($isWarning) {
            $status = 'Kritis (< 4 Jam)';
            $remainingFormatted = 'Sisa ' . $this->formatMinutes($remainingMinutes);
            $badgeClass = 'bg-amber-100 text-amber-900 border-amber-300 font-semibold';
        } else {
            $status = 'Sedang Dikerjakan (Dalam SLA)';
            $remainingFormatted = 'Sisa ' . $this->formatMinutes($remainingMinutes);
            $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
        }

        return [
            'is_started'          => true,
            'is_resolved'         => false,
            'is_waiting_start'    => $isWaitingStart,
            'is_overdue'          => $isOverdue,
            'is_warning'          => $isWarning,
            'status'              => $status,
            'target_hours'        => $targetHours,
            'start_at'            => $startAt,
            'due_at'              => $dueAt,
            'resolved_at'         => null,
            'elapsed_minutes'     => $elapsedMinutes,
            'elapsed_formatted'   => $this->formatMinutes($elapsedMinutes),
            'remaining_minutes'   => $remainingMinutes,
            'remaining_formatted' => $remainingFormatted,
            'percentage_used'     => $percentageUsed,
            'badge_class'         => $badgeClass,
        ];
    }

    public const CONFIRMATION_WORKING_HOURS = 48; // 2 x 24 jam kerja (2 hari kerja konfirmasi pemohon)

    /**
     * Hitung batas waktu konfirmasi pemohon (2 x 24 jam kerja = 48 jam kerja).
     */
    public function calculateConfirmationDeadline(Carbon $completedAt): Carbon
    {
        return $this->addWorkingHours($completedAt, self::CONFIRMATION_WORKING_HOURS);
    }

    /**
     * Dapatkan status dan sisa waktu konfirmasi pemohon untuk tiket berstatus 'Selesai'.
     */
    public function getConfirmationInfo(Ticket $ticket): array
    {
        if ($ticket->status !== 'Selesai' || is_null($ticket->completed_at)) {
            return [
                'is_active'           => false,
                'is_expired'          => false,
                'completed_at'        => $ticket->completed_at ? Carbon::parse($ticket->completed_at) : null,
                'deadline'            => $ticket->confirmation_deadline ? Carbon::parse($ticket->confirmation_deadline) : null,
                'remaining_formatted' => '-',
            ];
        }

        $now = now();
        $completedAt = Carbon::parse($ticket->completed_at);
        $deadline = $ticket->confirmation_deadline 
            ? Carbon::parse($ticket->confirmation_deadline) 
            : $this->calculateConfirmationDeadline($completedAt);

        $isExpired = $now->gte($deadline);
        $remainingMinutes = $isExpired ? 0 : $now->diffInMinutes($deadline);

        return [
            'is_active'           => true,
            'is_expired'          => $isExpired,
            'completed_at'        => $completedAt,
            'deadline'            => $deadline,
            'remaining_minutes'   => $remainingMinutes,
            'remaining_formatted' => $isExpired 
                ? 'Batas konfirmasi telah berakhir (siap ditutup otomatis)' 
                : $this->formatMinutes($remainingMinutes),
        ];
    }

    public const SLA_WARNING_THRESHOLD_MINUTES = 120; // 2 jam kerja sebelum resolution time habis

    /**
     * Memeriksa tiket aktif yang ditugaskan kepada Staf Unit Kerja / Internal Staff
     * yang mendekati batas waktu SLA Resolution (sisa <= 2 jam kerja) dan belum melewati SLA.
     * Mengirimkan notifikasi peringatan tepat 1 kali per tiket ke staf terkait.
     *
     * @return array Ringkasan hasil pemrosesan notifikasi
     */
    public function checkAndNotifyStaffSlaWarning(): array
    {
        $activeTickets = Ticket::whereIn('status', ['Didistribusikan', 'Dalam Proses'])
            ->whereNotNull('assigned_to')
            ->where(function ($q) {
                $q->whereNotNull('sla_resolution_start_at')
                  ->orWhereNotNull('disposed_at');
            })
            ->with(['assignedStaff.role'])
            ->get();

        $notifiedTickets = [];
        $skippedAlreadyNotified = 0;
        $skippedNotStaff = 0;
        $skippedNotInWarning = 0;

        foreach ($activeTickets as $ticket) {
            $staff = $ticket->assignedStaff;
            if (!$staff) {
                continue;
            }

            // Batasan: Hanya untuk role Staf unit kerja (bukan Operator, Kabag, User, atau Pimpinan)
            $isStaff = ($staff->isUnitKerjaStaf() || $staff->isInternalStaff())
                && !$staff->isKabag()
                && !$staff->isOperator()
                && !$staff->isSuperAdmin()
                && !$staff->isKepalaDivisi()
                && !$staff->isUser();

            if (!$isStaff) {
                $skippedNotStaff++;
                continue;
            }

            $slaRes = $this->getSlaResolutionInfo($ticket);

            // Kondisi peringatan SLA:
            // 1. Timer SLA sudah berjalan ($slaRes['is_started'])
            // 2. Belum diselesaikan ($slaRes['is_resolved'] === false)
            // 3. Belum melewati batas waktu ($slaRes['is_overdue'] === false)
            // 4. Sisa waktu <= 120 menit (2 jam kerja) dan > 0 menit
            $isInWarning = $slaRes['is_started'] 
                && !$slaRes['is_resolved'] 
                && !$slaRes['is_overdue'] 
                && $slaRes['remaining_minutes'] > 0 
                && $slaRes['remaining_minutes'] <= self::SLA_WARNING_THRESHOLD_MINUTES;

            if (!$isInWarning) {
                $skippedNotInWarning++;
                continue;
            }

            // Cek deduplikasi: pastikan notifikasi sla_warning belum pernah dikirim untuk tiket ini ke staf ini
            $alreadyNotified = \Illuminate\Support\Facades\DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $staff->id)
                ->where(function ($q) {
                    $q->where('type', 'sla_warning')
                      ->orWhere('type', \App\Notifications\TicketSlaWarningNotification::class);
                })
                ->where(function ($q) use ($ticket) {
                    $q->whereJsonContains('data->ticket_id', $ticket->id)
                      ->orWhere('data', 'like', '%"ticket_id":' . $ticket->id . '%')
                      ->orWhere('data', 'like', '%"ticket_id": ' . $ticket->id . '%')
                      ->orWhere('data', 'like', '%"ticket_id":"' . $ticket->id . '"%');
                })
                ->exists();

            if ($alreadyNotified) {
                $skippedAlreadyNotified++;
                continue;
            }

            // Kirim notifikasi ke staf
            $staff->notify(new \App\Notifications\TicketSlaWarningNotification($ticket, $slaRes['remaining_formatted']));

            $notifiedTickets[] = [
                'ticket_id'           => $ticket->id,
                'ticket_number'       => $ticket->ticket_number,
                'staff_id'            => $staff->id,
                'staff_name'          => $staff->nama_lengkap ?? $staff->username,
                'remaining_formatted' => $slaRes['remaining_formatted'],
                'remaining_minutes'   => $slaRes['remaining_minutes'],
                'due_at'              => $slaRes['due_at'],
            ];
        }

        return [
            'notified_count'          => count($notifiedTickets),
            'notified_tickets'        => $notifiedTickets,
            'skipped_already_notified'=> $skippedAlreadyNotified,
            'skipped_not_staff'       => $skippedNotStaff,
            'skipped_not_in_warning'  => $skippedNotInWarning,
            'total_checked'           => $activeTickets->count(),
        ];
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

