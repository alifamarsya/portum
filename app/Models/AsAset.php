<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsAset extends Model
{
    protected $table = 'as_aset';

    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kategori',
        'lokasi',
        'tanggal_perolehan',
        'nilai_perolehan',
        'umur_ekonomis',
        'kondisi',
        'penanggung_jawab',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_perolehan' => 'date',
        'nilai_perolehan' => 'decimal:2'
        ];
    }

    public function amortisasi()
    {
        return $this->hasMany(AsAmortisasi::class, 'nama_biaya', 'nama_aset');
    }
}
