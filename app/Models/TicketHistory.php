<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistory extends Model
{
    use HasFactory;

    protected $table = 'ticket_histories';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'old_status',
        'new_status',
        'notes',
    ];

    /**
     * Get the ticket associated with this history record.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * Get the user who performed the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get role-friendly status label for this history step based on viewer's role.
     */
    public function getRoleStatusLabel(?User $user = null): string
    {
        return Ticket::mapStatusLabel($this->new_status, $user);
    }

    /**
     * Dynamic status label attribute for Blade views ($history->status_label).
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->getRoleStatusLabel();
    }

    /**
     * Get CSS badge classes for status history.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->new_status) {
            'Menunggu Verifikasi'                             => 'bg-amber-50 text-amber-700 border-amber-200',
            'Dialokasikan', 'Diverifikasi'                    => 'bg-blue-50 text-blue-700 border-blue-200',
            'Didistribusikan'                                 => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'Dalam Proses'                                    => 'bg-violet-50 text-violet-700 border-violet-200',
            'Selesai'                                         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Ditutup Pemohon'                                 => 'bg-teal-50 text-teal-700 border-teal-200',
            'Ditutup Otomatis (Sistem)'                        => 'bg-slate-100 text-slate-700 border-slate-300',
            'Ditolak'                                         => 'bg-rose-50 text-rose-700 border-rose-200',
            default                                           => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    /**
     * Clean and user-friendly history notes.
     */
    public function getDisplayNotesAttribute(): ?string
    {
        if (!$this->notes) {
            return null;
        }

        $notes = $this->notes;
        $notes = str_replace(
            ["menjadi 'Dialokasikan'", "menjadi 'Diverifikasi'"],
            ["menjadi '" . $this->status_label . "'", "menjadi '" . $this->status_label . "'"],
            $notes
        );
        $notes = str_ireplace(
            ["Departemen dialokasikan ke", "Tiket dialokasikan ke", "dialokasikan ke"],
            ["Departemen diteruskan ke", "Tiket diteruskan ke", "diteruskan ke"],
            $notes
        );

        return $notes;
    }
}
