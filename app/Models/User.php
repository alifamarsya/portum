<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
        'email',
        'jabatan',
        'bagian',
        'department_id',
        'unit_kerja_id',
        'role_id',
        'is_active',
        'must_change_pwd',
        'last_login'
    ];

    protected function casts(): array
    {
        return [
        'is_active' => 'boolean',
        'must_change_pwd' => 'boolean',
        'last_login' => 'datetime'
        ];
    }
    use Notifiable;

    protected $hidden = ['password', 'rememberToken'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(InternalDepartment::class, 'department_id');
    }

    /**
     * Unit Kerja tempat user bernaung (untuk role uk_umum_rt & uk_dokumen).
     */
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function ticketHistories()
    {
        return $this->hasMany(TicketHistory::class, 'user_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Check if user has specific role by name.
     */
    public function hasRole(string|array $roles): bool
    {
        $roleName = strtolower($this->role?->nama ?? '');
        if (is_array($roles)) {
            $roles = array_map('strtolower', $roles);
            return in_array($roleName, $roles, true);
        }
        return $roleName === strtolower($roles);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(['superadmin', 'admin']);
    }

    public function isOperator(): bool
    {
        return $this->hasRole('operator');
    }

    public function isKabag(): bool
    {
        return $this->hasRole(['kabag_umum', 'kabag_aset', 'kabag_pengadaan']);
    }

    /**
     * Apakah user adalah staf Unit Kerja baru (uk_umum_rt atau uk_dokumen).
     */
    public function isUkUmumRt(): bool
    {
        return $this->hasRole('uk_umum_rt');
    }

    public function isUkDokumen(): bool
    {
        return $this->hasRole('uk_dokumen');
    }

    public function isUkAdministrasiAset(): bool
    {
        return $this->hasRole('uk_administrasi_aset');
    }

    public function isUkLogistik(): bool
    {
        return $this->hasRole('uk_logistik');
    }

    /**
     * Fase 3 — Unit Kerja Pengadaan Aset & Inventaris.
     */
    public function isUkPengadaan(): bool
    {
        return $this->hasRole('uk_pengadaan');
    }

    /**
     * Fase 3 — Unit Kerja Pemeliharaan & Pengawasan Aset/Inventaris.
     */
    public function isUkPemeliharaan(): bool
    {
        return $this->hasRole('uk_pemeliharaan');
    }

    /**
     * True jika user adalah staf Unit Kerja (role baru Fase 1, 2 & 3).
     */
    public function isUnitKerjaStaf(): bool
    {
        return $this->hasRole(['uk_umum_rt', 'uk_dokumen', 'uk_administrasi_aset', 'uk_logistik', 'uk_pengadaan', 'uk_pemeliharaan']);
    }

    public function kabagDepartmentId(): ?int
    {
        $roleName = strtolower($this->role?->nama ?? '');
        return match ($roleName) {
            'kabag_umum'      => 1,
            'kabag_aset'      => 2,
            'kabag_pengadaan' => 3,
            default           => $this->department_id,
        };
    }

    public function effectiveDepartmentId(): ?int
    {
        if ($this->department_id) {
            return (int) $this->department_id;
        }

        $roleName = strtolower($this->role?->nama ?? '');
        return match ($roleName) {
            'umum_rt', 'kabag_umum', 'uk_umum_rt', 'uk_dokumen'                            => 1,
            'aset', 'kabag_aset', 'uk_administrasi_aset', 'uk_logistik'                    => 2,
            'pengadaan', 'kabag_pengadaan', 'uk_pengadaan', 'uk_pemeliharaan'              => 3,
            default                                                                         => null,
        };
    }

    /**
     * Unit Kerja ID efektif user.
     * Untuk role uk_umum_rt / uk_dokumen mengambil dari kolom unit_kerja_id.
     */
    public function effectiveUnitKerjaId(): ?int
    {
        if ($this->unit_kerja_id) {
            return (int) $this->unit_kerja_id;
        }
        return null;
    }

    public function isInternalStaff(): bool
    {
        return !is_null($this->effectiveDepartmentId()) && !$this->isKabag();
    }

    public function isBagianInternal(): bool
    {
        return !is_null($this->effectiveDepartmentId());
    }

    public function isKepalaDivisi(): bool
    {
        return $this->hasRole(['pimpinan', 'kepala_divisi']) && is_null($this->department_id);
    }

    public function isUser(): bool
    {
        return $this->hasRole('user');
    }
}
