<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';

    protected $fillable = [
        'ticket_number',
        'user_id',
        'category_id',
        'department_id',
        'assigned_to',
        'disposed_by',
        'priority',
        'status',
        'description',
        'disposition_notes',
        'disposed_at',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'disposed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the staff user assigned to process the ticket.
     */
    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the Kabag user who disposed the ticket.
     */
    public function disposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    /**
     * Get the category of the ticket.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    /**
     * Get the internal department assigned to the ticket.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(InternalDepartment::class, 'department_id');
    }

    /**
     * Get the history records for the ticket.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class, 'ticket_id');
    }

    /**
     * Scope a query to only include tickets visible to the given user role.
     */
    public function scopeVisibleTo($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // 1. Superadmin, Operator, Pimpinan/Kepala Divisi: melihat semua tiket
        if ($user->isSuperAdmin() || $user->isOperator() || $user->isKepalaDivisi()) {
            return $query;
        }

        // 2. Kepala Bagian (kabag_umum, kabag_aset, kabag_pengadaan): melihat tiket di bagiannya
        if ($user->isKabag()) {
            return $query->where('department_id', $user->effectiveDepartmentId());
        }

        // 3. Staf Bagian Internal (umum_rt, aset, pengadaan):
        // Tiket yang dialokasikan ke bagiannya dan sudah diverifikasi/didisposisikan
        if ($user->isInternalStaff()) {
            return $query->where('department_id', $user->effectiveDepartmentId())
                         ->where('status', '!=', 'Menunggu Verifikasi');
        }

        // 4. User Pemohon: hanya tiket miliknya sendiri
        if ($user->isUser()) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function isAwaitingKabagDisposition(): bool
    {
        return $this->status === 'Diverifikasi' && is_null($this->assigned_to);
    }

    /**
     * Get CSS badge classes for status.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'Menunggu Verifikasi' => 'bg-amber-50 text-amber-700 border-amber-200',
            'Diverifikasi'        => 'bg-blue-50 text-blue-700 border-blue-200',
            'Didistribusikan'     => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'Dalam Proses'        => 'bg-violet-50 text-violet-700 border-violet-200',
            'Selesai'             => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Ditutup Pemohon'     => 'bg-teal-50 text-teal-700 border-teal-200',
            'Ditolak'             => 'bg-rose-50 text-rose-700 border-rose-200',
            default               => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    /**
     * Get a user-friendly status label for the ticket requester (pemohon).
     * Translates internal status names to terms that are meaningful to the end user.
     */
    public function getPemohonStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'Menunggu Verifikasi' => 'Diajukan',
            'Diverifikasi'        => 'Diverifikasi',
            'Didistribusikan'     => 'Sedang Ditangani',
            'Dalam Proses'        => 'Sedang Dikerjakan',
            'Selesai'             => 'Selesai',
            'Ditutup Pemohon'     => 'Ditutup (Dikonfirmasi)',
            'Ditolak'             => 'Tidak Dapat Diproses',
            default               => $this->status,
        };
    }

    /**
     * Get CSS badge classes for priority.
     */
    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            'Kritis'  => 'bg-rose-100 text-rose-900 font-bold ring-1 ring-rose-400',
            'Darurat' => 'bg-rose-100 text-rose-800 font-semibold',
            'Tinggi'  => 'bg-orange-100 text-orange-800',
            'Normal'  => 'bg-blue-100 text-blue-800',
            'Sedang'  => 'bg-amber-100 text-amber-800',
            'Rendah'  => 'bg-emerald-100 text-emerald-800',
            default   => 'bg-slate-100 text-slate-800',
        };
    }
}
