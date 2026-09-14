<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsMutasiAset extends Model
{
    use HasFactory;

    protected $table = 'as_mutasi_aset';

    protected $fillable = [
        'no_mutasi',
        'aset_id',
        'pengaju_id',
        'nama_pemohon',
        'jabatan_pemohon',
        'username_pemohon',
        'dari_lokasi',
        'ke_lokasi',
        'dari_penanggung_jawab',
        'ke_penanggung_jawab',
        'alasan',
        'status',             // Diajukan, Diproses, Menunggu Approval, Disetujui, Ditutup
        'status_hasil',       // Disetujui, Ditolak, Tidak Valid, null
        'operator_id',
        'operator_checked_at',
        'catatan_operator',
        'verifikator_id',
        'verified_at',
        'catatan_verifikasi',
        'approver_id',
        'approved_at',
        'catatan_approval',
        'alasan_penolakan',
        'catatan_konfirmasi',
        'confirmed_at',
        'maker_id',
        'checker_id',
        'approval_status',
        'keterangan',
        'dokumen',
    ];

    protected function casts(): array
    {
        return [
            'operator_checked_at' => 'datetime',
            'verified_at'         => 'datetime',
            'approved_at'         => 'datetime',
            'confirmed_at'        => 'datetime',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(AsAset::class, 'aset_id');
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengaju_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    /**
     * Helper status badge styling
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->status === 'Ditutup') {
            return match ($this->status_hasil) {
                'Disetujui' => [
                    'label' => 'Ditutup (Disetujui & Selesai)',
                    'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'dot'   => 'bg-emerald-500',
                ],
                'Ditolak' => [
                    'label' => 'Ditutup (Ditolak)',
                    'class' => 'bg-rose-100 text-rose-800 border-rose-300',
                    'dot'   => 'bg-rose-500',
                ],
                'Tidak Valid' => [
                    'label' => 'Ditutup (Data Tidak Valid)',
                    'class' => 'bg-amber-100 text-amber-800 border-amber-300',
                    'dot'   => 'bg-amber-500',
                ],
                default => [
                    'label' => 'Ditutup',
                    'class' => 'bg-slate-100 text-slate-800 border-slate-300',
                    'dot'   => 'bg-slate-500',
                ],
            };
        }

        return match ($this->status) {
            'Diajukan' => [
                'label' => 'Diajukan',
                'class' => 'bg-amber-100 text-amber-800 border-amber-300',
                'dot'   => 'bg-amber-500',
            ],
            'Diproses' => [
                'label' => 'Diproses (Verifikasi Staf)',
                'class' => 'bg-blue-100 text-blue-800 border-blue-300',
                'dot'   => 'bg-blue-500',
            ],
            'Menunggu Approval' => [
                'label' => 'Menunggu Approval Kabag',
                'class' => 'bg-purple-100 text-purple-800 border-purple-300',
                'dot'   => 'bg-purple-500',
            ],
            'Disetujui' => [
                'label' => 'Disetujui (Menunggu Konfirmasi Pemohon)',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot'   => 'bg-emerald-400',
            ],
            default => [
                'label' => $this->status,
                'class' => 'bg-slate-100 text-slate-800 border-slate-300',
                'dot'   => 'bg-slate-500',
            ],
        };
    }
}
