<?php

namespace Database\Seeders;

use App\Models\AsMasterLokasi;
use App\Models\AsMasterPersonel;
use Illuminate\Database\Seeder;

class MasterLokasiPersonelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Divisi Cyber' => [
                'tipe'      => 'Divisi',
                'personels' => ['Dirli', 'Marsya', 'Zahra'],
            ],
            'Divisi IT' => [
                'tipe'      => 'Divisi',
                'personels' => ['Fauzhira', 'Atalya', 'Nadia'],
            ],
            'Divisi SKAI' => [
                'tipe'      => 'Divisi',
                'personels' => ['Aulia', 'Fatan'],
            ],
        ];

        $sort = 1;
        foreach ($data as $namaLokasi => $item) {
            $lokasi = AsMasterLokasi::firstOrCreate(
                ['nama_lokasi' => $namaLokasi],
                [
                    'tipe'       => $item['tipe'] ?? 'Divisi',
                    'is_active'  => true,
                    'sort_order' => $sort++,
                ]
            );

            foreach ($item['personels'] as $namaPersonel) {
                AsMasterPersonel::firstOrCreate(
                    [
                        'lokasi_id'     => $lokasi->id,
                        'nama_personel' => $namaPersonel,
                    ],
                    [
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
