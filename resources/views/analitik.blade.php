@extends('layouts.app')
@section('title', 'Analitik Data Warehouse')
@section('content')
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-ink">Analitik Data Warehouse</h1>
            <p class="text-sm text-slate-500">Visualisasi data ringkasan bulanan dari Tabel Fakta (Fact Tables).</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 font-mono bg-white border border-slate-200 px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 shadow-card">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                ETL Terakhir: Terjadwal Bulanan
            </span>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card hover:shadow-hover transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-gold-light text-gold flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'bank', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[12.5px] font-semibold text-slate-500 mb-1">Total Biaya Operasional (ETL)</p>
                <p class="text-2xl font-bold text-ink">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Akumulasi seluruh biaya harian disetujui</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card hover:shadow-hover transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'wrench', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[12.5px] font-semibold text-slate-500 mb-1">Total Nilai Pengadaan (ETL)</p>
                <p class="text-2xl font-bold text-ink">Rp {{ number_format($totalPengadaan, 0, ',', '.') }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Akumulasi nilai negosiasi pengadaan</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card hover:shadow-hover transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'trend-up', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[12.5px] font-semibold text-slate-500 mb-1">Total Nilai Buku Aset (Bulan Ini)</p>
                <p class="text-2xl font-bold text-ink">Rp {{ number_format($totalNilaiBuku, 0, ',', '.') }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Sisa nilai buku seluruh amortisasi aktif</p>
            </div>
        </div>
    </div>

    <!-- Charts Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- 1. Biaya Bulanan per Kategori (2/3 width on large screens) -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card lg:col-span-2">
            <div class="mb-4">
                <h2 class="font-bold text-ink text-[15px]">Biaya Bulanan per Kategori</h2>
                <p class="text-[12px] text-slate-400">Total pengeluaran dikelompokkan per kategori biaya harian</p>
            </div>
            <div class="relative min-h-[300px] flex items-center justify-center">
                @if (empty($biayaLabels))
                    <div class="text-center text-slate-400 py-12">
                        <p class="text-sm font-medium">Belum ada data untuk ditampilkan</p>
                        <p class="text-[11.5px] mt-1">Silakan jalankan perintah <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">php artisan dw:etl</code> di terminal Anda</p>
                    </div>
                @else
                    <canvas id="biayaChart" class="w-full max-h-[300px]"></canvas>
                @endif
            </div>
        </div>

        <!-- 2. Pengadaan per Vendor (1/3 width on large screens) -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card">
            <div class="mb-4">
                <h2 class="font-bold text-ink text-[15px]">Distribusi Pengadaan per Vendor</h2>
                <p class="text-[12px] text-slate-400">Porsi pembagian nilai pengadaan kepada pihak rekanan</p>
            </div>
            <div class="relative min-h-[300px] flex items-center justify-center">
                @if (empty($vendorLabels))
                    <div class="text-center text-slate-400 py-12">
                        <p class="text-sm font-medium">Belum ada data vendor pengadaan</p>
                        <p class="text-[11.5px] mt-1">Input data negosiasi lalu jalankan ETL</p>
                    </div>
                @else
                    <canvas id="pengadaanChart" class="w-full max-h-[300px]"></canvas>
                @endif
            </div>
        </div>
    </div>

    <!-- 3. Amortisasi Aset bulanan (Full Width) -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card mb-6">
        <div class="mb-4">
            <h2 class="font-bold text-ink text-[15px]">Tren Amortisasi &amp; Nilai Buku Aset</h2>
            <p class="text-[12px] text-slate-400">Tren kumulatif penyusutan bulanan dibandingkan dengan nilai buku aset</p>
        </div>
        <div class="relative min-h-[250px] flex items-center justify-center">
            @if (empty($amortisasiLabels))
                <div class="text-center text-slate-400 py-12">
                    <p class="text-sm font-medium">Belum ada data tren amortisasi aset</p>
                    <p class="text-[11.5px] mt-1">Input data amortisasi lalu jalankan ETL</p>
                </div>
            @else
                <canvas id="amortisasiChart" class="w-full max-h-[260px]"></canvas>
            @endif
        </div>
    </div>

    @if (!empty($biayaLabels) || !empty($vendorLabels) || !empty($amortisasiLabels))
        <!-- ChartJS Library -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Konfigurasi format rupiah untuk tooltip
                const formatRupiah = (value) => {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(value);
                };

                // 1. Chart Biaya Bulanan
                @if(!empty($biayaLabels))
                const ctxBiaya = document.getElementById('biayaChart').getContext('2d');
                new Chart(ctxBiaya, {
                    type: 'bar',
                    data: {
                        labels: @json($biayaLabels),
                        datasets: @json($biayaDatasets)
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { family: 'Manrope', size: 11 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + formatRupiah(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Manrope', size: 11 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { family: 'Manrope', size: 10 },
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
                @endif

                // 2. Chart Pengadaan (Doughnut)
                @if(!empty($vendorLabels))
                const ctxPengadaan = document.getElementById('pengadaanChart').getContext('2d');
                new Chart(ctxPengadaan, {
                    type: 'doughnut',
                    data: {
                        labels: @json($vendorLabels),
                        datasets: [{
                            data: @json($vendorTotals),
                            backgroundColor: [
                                '#BF8F3D', '#16233D', '#10b981', '#3b82f6', '#ec4899', '#8b5cf6', '#6b7280'
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 10, font: { family: 'Manrope', size: 10 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + formatRupiah(context.raw);
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
                @endif

                // 3. Chart Amortisasi (Line Chart)
                @if(!empty($amortisasiLabels))
                const ctxAmortisasi = document.getElementById('amortisasiChart').getContext('2d');
                new Chart(ctxAmortisasi, {
                    type: 'line',
                    data: {
                        labels: @json($amortisasiLabels),
                        datasets: [
                            {
                                label: 'Nilai Buku Aset',
                                data: @json($nilaiBukuData),
                                borderColor: '#16233D',
                                backgroundColor: 'rgba(22, 35, 61, 0.05)',
                                fill: true,
                                tension: 0.3,
                                borderWidth: 2.5,
                                pointBackgroundColor: '#16233D'
                            },
                            {
                                label: 'Nilai Penyusutan Bulanan',
                                data: @json($penyusutanData),
                                borderColor: '#BF8F3D',
                                backgroundColor: 'transparent',
                                tension: 0.3,
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointBackgroundColor: '#BF8F3D'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { family: 'Manrope', size: 11 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + formatRupiah(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Manrope', size: 11 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { family: 'Manrope', size: 10 },
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
                @endif
            });
        </script>
    @endif
@endsection
