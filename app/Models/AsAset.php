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
            'nilai_perolehan' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (AsAset $aset) {
            AsAsetHistory::create([
                'aset_id'       => $aset->id,
                'user_id'       => auth()->id(),
                'field_changed' => 'Registrasi Aset',
                'old_value'     => null,
                'new_value'     => $aset->nama_aset . ($aset->kode_aset ? " [{$aset->kode_aset}]" : ''),
                'keterangan'    => 'Pencatatan aset baru ke dalam inventaris.',
                'changed_at'    => now(),
            ]);
        });

        static::updated(function (AsAset $aset) {
            $trackedFields = [
                'lokasi'           => 'Lokasi',
                'penanggung_jawab' => 'Penanggung Jawab',
                'kondisi'          => 'Kondisi',
                'kategori'         => 'Kategori',
                'nilai_perolehan'  => 'Nilai Perolehan',
            ];

            foreach ($trackedFields as $field => $label) {
                if ($aset->wasChanged($field)) {
                    AsAsetHistory::create([
                        'aset_id'       => $aset->id,
                        'user_id'       => auth()->id(),
                        'field_changed' => $label,
                        'old_value'     => (string) $aset->getOriginal($field),
                        'new_value'     => (string) $aset->getAttribute($field),
                        'keterangan'    => "Perubahan {$label} aset.",
                        'changed_at'    => now(),
                    ]);
                }
            }
        });
    }

    public function histories()
    {
        return $this->hasMany(AsAsetHistory::class, 'aset_id');
    }

    public function mutasi()
    {
        return $this->hasMany(AsMutasiAset::class, 'aset_id');
    }

    public function disposal()
    {
        return $this->hasMany(AsDisposalAset::class, 'aset_id');
    }

    public function rekonsiliasi()
    {
        return $this->hasMany(AsRekonsiliasiAset::class, 'aset_id');
    }

    public function amortisasi()
    {
        return $this->hasMany(AsAmortisasi::class, 'nama_biaya', 'nama_aset');
    }
}
