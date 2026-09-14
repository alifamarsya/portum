<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmChecklistKebersihan extends Model
{
    use HasFactory;

    protected $table = 'um_checklist_kebersihan';

    protected $fillable = [
        'tanggal', 'jenis', 'area', 'petugas',
        'status', 'catatan', 'dibuat_oleh',
    ];
}
