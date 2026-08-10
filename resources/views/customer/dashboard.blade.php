@extends('customer.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid py-2">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 16px;">
                <div class="position-absolute top-0 end-0 p-3 opacity-10 d-none d-sm-block" style="font-size: 8rem; color: #fff;">
                    <i class="bi bi-building"></i>
                </div>
                <div class="card-body p-3 p-md-4 text-white position-relative">
                    <span class="badge bg-white bg-opacity-20 text-white px-3 py-1.5 mb-2" style="backdrop-filter: blur(5px); font-size: 0.75rem;">Portal Pelanggan Resmi</span>
                    <h1 class="fw-bold fs-3 fs-md-2 mb-1" style="line-height: 1.25;">Selamat Datang di Portal Monitoring Armada</h1>
                    <p class="mb-0 text-white-50 fs-6 fs-md-5 opacity-90">Transparansi penuh atas keandalan, kepatuhan dokumen, dan kesehatan unit sewa Anda.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Vehicles -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 transition-hover" style="border-radius: 12px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center">
                    <div class="rounded-3 p-2.5 p-md-3 bg-primary-subtle text-primary-emphasis border border-primary-subtle me-3">
                        <i class="bi bi-truck fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase tracking-wider" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Kendaraan</h6>
                        <h3 class="fw-bold mb-0 text-dark fs-4 fs-md-3">{{ $stats['total_vehicles'] }} Unit</h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Avg Health Score -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 transition-hover" style="border-radius: 12px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center">
                    @php
                        $avgHealth = round($stats['avg_health']);
                        $healthColor = $avgHealth >= 90 ? 'success' : ($avgHealth >= 75 ? 'primary' : ($avgHealth >= 60 ? 'warning' : 'danger'));
                    @endphp
                    <div class="rounded-3 p-2.5 p-md-3 bg-{{ $healthColor }} bg-opacity-10 text-{{ $healthColor }} me-3">
                        <i class="bi bi-heart-pulse-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase tracking-wider" style="font-size: 0.7rem; letter-spacing: 0.5px;">Rata-rata Kesehatan</h6>
                        <h3 class="fw-bold mb-0 text-{{ $healthColor }} fs-4 fs-md-3">{{ $avgHealth }}%</h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Active Alerts -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 transition-hover" style="border-radius: 12px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center">
                    <div class="rounded-3 p-2.5 p-md-3 bg-warning-subtle text-warning-emphasis border border-warning-subtle me-3">
                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase tracking-wider" style="font-size: 0.7rem; letter-spacing: 0.5px;">Perhatian / Alert</h6>
                        <h3 class="fw-bold mb-0 text-warning fs-4 fs-md-3">{{ $stats['total_alerts'] }} Issue</h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pending Approvals -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 transition-hover" style="border-radius: 12px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center">
                    <div class="rounded-3 p-2.5 p-md-3 bg-danger-subtle text-danger-emphasis border border-danger-subtle me-3 {{ $pendingApprovals > 0 ? 'animate-pulse' : '' }}" style="position: relative;">
                        <i class="bi bi-check-circle-fill fs-3 position-relative z-1"></i>
                        @if($pendingApprovals > 0)
                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-danger rounded-3 opacity-25" style="filter: blur(8px);"></div>
                        @endif
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase tracking-wider" style="font-size: 0.7rem; letter-spacing: 0.5px;">Persetujuan Service</h6>
                        <h3 class="fw-bold mb-0 text-danger fs-4 fs-md-3">{{ $pendingApprovals }} Butuh Approval</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details and Charts -->
    <div class="row g-4 mb-4">
        <!-- Health Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark mb-0">Status Kesehatan Fleet</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small"><i class="bi bi-circle-fill text-success me-2"></i>Sangat Baik (>= 90%)</span>
                            <span class="fw-semibold text-dark">{{ $stats['excellent'] }} Unit</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success rounded" role="progressbar" style="width: {{ $stats['total_vehicles'] > 0 ? ($stats['excellent'] / $stats['total_vehicles']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small"><i class="bi bi-circle-fill text-primary me-2"></i>Baik (75% - 89%)</span>
                            <span class="fw-semibold text-dark">{{ $stats['good'] }} Unit</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary rounded" role="progressbar" style="width: {{ $stats['total_vehicles'] > 0 ? ($stats['good'] / $stats['total_vehicles']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small"><i class="bi bi-circle-fill text-warning me-2"></i>Cukup (60% - 74%)</span>
                            <span class="fw-semibold text-dark">{{ $stats['warning'] }} Unit</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning rounded" role="progressbar" style="width: {{ $stats['total_vehicles'] > 0 ? ($stats['warning'] / $stats['total_vehicles']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small"><i class="bi bi-circle-fill text-danger me-2"></i>Kritis (< 60%)</span>
                            <span class="fw-semibold text-dark">{{ $stats['critical'] }} Unit</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger rounded" role="progressbar" style="width: {{ $stats['total_vehicles'] > 0 ? ($stats['critical'] / $stats['total_vehicles']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action / Info -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                    <h5 class="fw-bold text-dark mb-0">Overview Projek & Unit</h5>
                    <a href="{{ route('customer.vehicles') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                        Lihat Semua Unit <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        @forelse($projects as $proj)
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light d-flex align-items-center">
                                    <div class="p-2 rounded bg-white border text-primary me-3 shadow-sm">
                                        <i class="bi bi-folder-fill fs-4"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $proj->name }}</h6>
                                        <span class="text-muted small">
                                            {{ $healthReports->filter(fn($r) => $r['project_name'] === $proj->name)->count() }} Unit Kendaraan
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4">
                                <i class="bi bi-folder-x fs-1 text-muted mb-2"></i>
                                <p class="text-muted mb-0">Tidak ada proyek yang terhubung dengan akun Anda.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicles List Grid/Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-stars text-primary me-2"></i>Daftar Kesehatan Unit Kendaraan</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    @if($healthReports->isEmpty())
                        <div class="alert alert-info py-4 text-center border-0 mb-0" style="border-radius: 10px;">
                            <i class="bi bi-info-circle fs-3 mb-2 d-block"></i>
                            Tidak ada unit kendaraan yang terdaftar dalam proyek Anda saat ini.
                        </div>
                    @else
                        <div class="table-responsive table-responsive-cards">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Plat Nomor</th>
                                        <th>Tipe Unit</th>
                                        <th>Project</th>
                                        <th>Skor Kesehatan</th>
                                        <th>STNK</th>
                                        <th>KIR</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($healthReports as $report)
                                        <tr class="aset-row transition-all">
                                            <td data-label="Plat Nomor">
                                                <span class="badge bg-dark px-3 py-2 font-monospace fs-6" style="border-radius: 6px; letter-spacing: 0.5px;">
                                                    {{ $report['plate_number'] }}
                                                </span>
                                            </td>
                                            <td data-label="Tipe Unit" class="fw-semibold text-secondary">
                                                {{ $report['vehicle_type'] }}
                                            </td>
                                            <td data-label="Project">
                                                <span class="text-dark fw-bold">{{ $report['project_name'] }}</span>
                                            </td>
                                            <td data-label="Skor Kesehatan">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-{{ $report['status']['color'] }} me-2" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;">
                                                        {{ $report['health_score'] }}%
                                                    </span>
                                                    <span class="text-{{ $report['status']['color'] }} fw-semibold small d-none d-sm-inline">
                                                        {{ $report['status']['label'] }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td data-label="STNK">
                                                <span class="badge bg-{{ $report['stnk_status']['color'] }} bg-opacity-10 text-{{ $report['stnk_status']['color'] }} border border-{{ $report['stnk_status']['color'] }} border-opacity-25 px-2.5 py-1.5" style="font-size: 0.75rem;">
                                                    {{ $report['stnk_status']['label'] }}
                                                </span>
                                            </td>
                                            <td data-label="KIR">
                                                <span class="badge bg-{{ $report['kir_status']['color'] }} bg-opacity-10 text-{{ $report['kir_status']['color'] }} border border-{{ $report['kir_status']['color'] }} border-opacity-25 px-2.5 py-1.5" style="font-size: 0.75rem;">
                                                    {{ $report['kir_status']['label'] }}
                                                </span>
                                            </td>
                                            <td data-label="Aksi" class="text-center">
                                                <a href="{{ route('customer.vehicles.show', $report['vehicle_id']) }}" class="btn btn-sm btn-primary px-3 shadow-sm border-0" style="background-color: #1e3a8a; border-radius: 6px;">
                                                    <i class="bi bi-shield-shaded me-1"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .transition-hover {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .transition-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
    }
    .animate-pulse {
        animation: pulse-red 2s infinite;
    }
    @keyframes pulse-red {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4);
        }
        70% {
            transform: scale(1.05);
            box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
        }
    }
    .tracking-wider {
        letter-spacing: 0.05em;
    }
    .transition-all {
        transition: all 0.2s ease;
    }
    tr.aset-row:hover {
        background-color: var(--hover-bg) !important;
    }

    /* Mobile optimizations */
    @media (max-width: 768px) {
        .transition-hover:hover {
            transform: none;
        }
    }
    @media (hover: none) and (pointer: coarse) {
        .transition-hover:hover {
            transform: none;
            box-shadow: none !important;
        }
    }
</style>
@endsection
