@extends('admin.layouts.app')

@section('title', 'Dashboard - Aktivitas Driver')

@section('content')
    <style>
        /* Efek Hover untuk Kartu KPI agar terasa 'bisa diklik' */
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
            border-color: #0d6efd;
            /* Sedikit highlight border biru */
        }
    </style>

    <div class="container-fluid p-0">

        @if (isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- === KARTU STATISTIK (KPI) === --}}
        <div class="row g-3 mb-4">

            {{-- Kartu 1: Driver Bertugas -> Link ke Riwayat Driver --}}
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.riwayat_driver') }}"
                    class="card shadow-sm card-hover text-decoration-none text-reset h-100">
                    <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-center">
                        <div class="text-center text-sm-start">
                            <h2 class="h3 fw-bold mb-0" id="kpi-driver-bertugas">{{ count($onDutyDrivers) }}</h2>
                            <span class="text-muted">Driver Bertugas</span>
                        </div>
                        <i class="bi bi-broadcast fs-1 text-danger opacity-50 mt-2 mt-sm-0"></i>
                    </div>
                </a>
            </div>

            {{-- Kartu 2: Aset Tersedia -> Link ke Daftar Aset --}}
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.daftar_aset') }}"
                    class="card shadow-sm card-hover text-decoration-none text-reset h-100">
                    <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-center">
                        <div class="text-center text-sm-start">
                            <h2 class="h3 fw-bold mb-0" id="kpi-aset-tersedia">
                                {{ $totalAsetTersedia }}
                                <span class="h5 text-muted">/ {{ $totalAset }}</span>
                            </h2>
                            <span class="text-muted">Aset Tersedia / Total</span>
                        </div>
                        <i class="bi bi-p-circle-fill fs-1 text-success opacity-50 mt-2 mt-sm-0"></i>
                    </div>
                </a>
            </div>

            {{-- Kartu 3: Jarak Bulan Ini -> Link ke Riwayat Unit --}}
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.riwayat_unit') }}"
                    class="card shadow-sm card-hover text-decoration-none text-reset h-100">
                    <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-center">
                        <div class="text-center text-sm-start">
                            <h2 class="h3 fw-bold mb-0" id="kpi-jarak-bulanan">{{ number_format($totalJarakBulanIni) }}
                                <span class="h5 text-muted">Km</span></h2>
                            <span class="text-muted">Jarak Bulan Ini</span>
                        </div>
                        <i class="bi bi-sign-turn-right-fill fs-1 text-info opacity-50 mt-2 mt-sm-0"></i>
                    </div>
                </a>
            </div>

            {{-- Kartu 4: Laporan Hari Ini -> Link ke Laporan Darurat --}}
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.laporan_darurat') }}"
                    class="card shadow-sm card-hover text-decoration-none text-reset h-100">
                    <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-center">
                        <div class="text-center text-sm-start">
                            <h2 class="h3 fw-bold mb-0" id="kpi-laporan-hari-ini">{{ $totalLaporan }}</h2>
                            <span class="text-muted">Laporan Hari Ini</span>
                        </div>
                        <i class="bi bi-exclamation-triangle-fill fs-1 text-warning opacity-50 mt-2 mt-sm-0"></i>
                    </div>
                </a>
            </div>

        </div>
        {{-- === AKHIR KARTU KPI === --}}


        {{-- KARTU GRAFIK --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-bar-chart-line-fill"></i> Aktivitas 7 Hari Terakhir (Total KM)</h2>
            </div>
            <div class="card-body">
                <canvas id="activityChart"></canvas>
            </div>
        </div>


        {{-- Kartu Driver Sedang Bertugas (Dengan Scroll) --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-broadcast text-danger"></i> Driver Sedang Bertugas</h2>

                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm text-danger me-2" role="status" id="loading-spinner"
                        style="display: none;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="badge bg-danger rounded-pill" id="badge-driver-aktif">{{ count($onDutyDrivers) }} Driver
                        Aktif</span>
                </div>

            </div>

            <div class="card-body p-0">
                @if (count($onDutyDrivers) > 0)
                    <div style="max-height: 450px; overflow-y: auto;" class="p-3" id="driver-bertugas-container">
                        <div class="row g-3">
                            @foreach ($onDutyDrivers as $driver)
                                <div class="col-lg-4 col-md-6">
                                    <div class="card shadow-sm border-danger h-100">
                                        <div class="card-body d-flex flex-column">

                                            <div>
                                                <h5 class="card-title fw-bold mb-1">{{ $driver['driver_name'] }}</h5>
                                                <span class="badge bg-secondary fs-6 mb-2">{{ $driver['plate_number'] }}</span>
                                                <p class="card-text text-muted mb-0">
                                                    <i class="bi bi-clock-fill"></i> Masuk:
                                                </p>
                                                <p class="card-text">{{ $driver['timestamp_masuk'] }}</p>
                                            </div>

                                            <div class="mt-auto pt-3 border-top">
                                                <a href="{{ $driver['gps_masuk'] }}" target="_blank"
                                                    class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-geo-alt-fill"></i> Peta
                                                </a>
                                                <a href="{{ $driver['link_selfie'] }}" target="_blank"
                                                    class="btn btn-outline-primary btn-sm ms-1">
                                                    <i class="bi bi-person-bounding-box"></i> Selfie
                                                </a>
                                                <a href="{{ $driver['link_speedo_awal'] }}" target="_blank"
                                                    class="btn btn-outline-info btn-sm ms-1">
                                                    <i class="bi bi-speedometer"></i> Speedo
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-4 text-center text-muted" id="driver-bertugas-container">
                        <i class="bi bi-moon-stars-fill fs-3 d-block mb-2"></i>
                        <h5 class="mb-0">Tidak Ada Driver Aktif</h5>
                        <p>Semua driver sedang parkir/istirahat.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection

{{-- === PERUBAHAN BARU: SCRIPT GRAFIK (TANPA LIVE UPDATE) === --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // --- LOGIKA UNTUK GRAFIK BARU ---
            const ctx = document.getElementById('activityChart');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Total Jarak (Km)',
                            data: @json($chartData),
                            backgroundColor: 'rgba(0, 123, 255, 0.7)',
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return value + ' Km';
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += context.parsed.y + ' Km';
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        });
    </script>
@endpush