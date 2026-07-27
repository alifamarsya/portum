<?php

namespace App\Http\Controllers;

use App\Models\FactBiayaBulanan;
use App\Models\FactPengadaan;
use App\Models\FactAmortisasiAset;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        // 1. KPI Summary Cards
        $totalBiaya = (float) FactBiayaBulanan::sum('total_biaya');
        $totalPengadaan = (float) FactPengadaan::sum('total_nilai');
        
        // Latest total nilai buku
        $latestWaktuId = FactAmortisasiAset::max('dim_waktu_id');
        $totalNilaiBuku = $latestWaktuId 
            ? (float) FactAmortisasiAset::where('dim_waktu_id', $latestWaktuId)->sum('nilai_buku') 
            : 0.0;

        // 2. Biaya Bulanan per Kategori
        $biaya = FactBiayaBulanan::join('dim_waktu', 'fact_biaya_bulanan.dim_waktu_id', '=', 'dim_waktu.id')
            ->join('dim_kategori', 'fact_biaya_bulanan.dim_kategori_id', '=', 'dim_kategori.id')
            ->selectRaw('dim_waktu.nama_bulan, dim_waktu.tahun, dim_waktu.bulan, dim_kategori.nama_kategori, sum(fact_biaya_bulanan.total_biaya) as total')
            ->groupBy('dim_waktu.tahun', 'dim_waktu.bulan', 'dim_waktu.nama_bulan', 'dim_kategori.nama_kategori')
            ->orderBy('dim_waktu.tahun')
            ->orderBy('dim_waktu.bulan')
            ->get();

        $biayaLabels = [];
        $kategoriList = [];
        $biayaMatrix = [];

        foreach ($biaya as $row) {
            $label = $row->nama_bulan . ' ' . $row->tahun;
            if (!in_array($label, $biayaLabels)) {
                $biayaLabels[] = $label;
            }
            $kategori = $row->nama_kategori;
            if (!in_array($kategori, $kategoriList)) {
                $kategoriList[] = $kategori;
            }
            $biayaMatrix[$kategori][$label] = (float) $row->total;
        }

        $biayaDatasets = [];
        $colors = [
            'BBM' => '#3b82f6',          // blue
            'Perawatan' => '#f59e0b',    // amber
            'Rumah Tangga' => '#10b981', // emerald
            'Lainnya' => '#6b7280'       // gray
        ];
        $defaultColors = ['#3b82f6', '#f59e0b', '#10b981', '#ec4899', '#8b5cf6', '#6b7280'];
        $colorIndex = 0;

        foreach ($kategoriList as $kategori) {
            $data = [];
            foreach ($biayaLabels as $lbl) {
                $data[] = $biayaMatrix[$kategori][$lbl] ?? 0;
            }
            $color = $colors[$kategori] ?? ($defaultColors[$colorIndex++ % count($defaultColors)]);
            $biayaDatasets[] = [
                'label' => $kategori,
                'data' => $data,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'borderWidth' => 1
            ];
        }

        // 3. Pengadaan per Vendor (Doughnut Chart)
        $pengadaan = FactPengadaan::join('dim_vendor', 'fact_pengadaan.dim_vendor_id', '=', 'dim_vendor.id')
            ->selectRaw('dim_vendor.nama_vendor, sum(fact_pengadaan.total_nilai) as total')
            ->groupBy('dim_vendor.nama_vendor')
            ->get();

        $vendorLabels = [];
        $vendorTotals = [];
        foreach ($pengadaan as $row) {
            $vendorLabels[] = $row->nama_vendor ?: 'Lainnya';
            $vendorTotals[] = (float) $row->total;
        }

        // 4. Amortisasi Aset (Line Chart)
        $amortisasi = FactAmortisasiAset::join('dim_waktu', 'fact_amortisasi_aset.dim_waktu_id', '=', 'dim_waktu.id')
            ->selectRaw('dim_waktu.nama_bulan, dim_waktu.tahun, dim_waktu.bulan, sum(fact_amortisasi_aset.nilai_penyusutan_bulan) as total_penyusutan, sum(fact_amortisasi_aset.nilai_buku) as total_nilai_buku')
            ->groupBy('dim_waktu.tahun', 'dim_waktu.bulan', 'dim_waktu.nama_bulan')
            ->orderBy('dim_waktu.tahun')
            ->orderBy('dim_waktu.bulan')
            ->get();

        $amortisasiLabels = [];
        $penyusutanData = [];
        $nilaiBukuData = [];

        foreach ($amortisasi as $row) {
            $label = $row->nama_bulan . ' ' . $row->tahun;
            $amortisasiLabels[] = $label;
            $penyusutanData[] = (float) $row->total_penyusutan;
            $nilaiBukuData[] = (float) $row->total_nilai_buku;
        }

        return view('analitik', compact(
            'totalBiaya',
            'totalPengadaan',
            'totalNilaiBuku',
            'biayaLabels',
            'biayaDatasets',
            'vendorLabels',
            'vendorTotals',
            'amortisasiLabels',
            'penyusutanData',
            'nilaiBukuData'
        ));
    }
}
