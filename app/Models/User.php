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

    public function kabagDepartmentId(): ?int
    {
        $roleName = strtolower($this->role?->nama ?? '');
        return match ($roleName) {
            'kabag_umum' => 1,
            'kabag_aset' => 2,
            'kabag_pengadaan' => 3,
            default => $this->department_id,
        };
    }

    public function effectiveDepartmentId(): ?int
    {
        if ($this->department_id) {
            return (int) $this->department_id;
        }

        $roleName = strtolower($this->role?->nama ?? '');
        return match ($roleName) {
            'umum_rt', 'kabag_umum' => 1,
            'aset', 'kabag_aset' => 2,
            'pengadaan', 'kabag_pengadaan' => 3,
            default => null,
        };
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
