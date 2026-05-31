@extends('customer.layouts.app')

@section('title', 'Detail Kendaraan - ' . $vehicle->plate_number)

@section('content')
<div class="container-fluid py-2">
    <!-- Back & Action Button Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <div class="d-flex align-items-center">
            <a href="{{ route('customer.vehicles') }}" class="btn btn-outline-secondary me-3" style="border-radius: 8px;">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <div>
                <h3 class="fw-bold text-dark mb-0">Rincian Kesehatan Unit</h3>
                <span class="text-muted small">Update Terakhir: {{ now()->format('d M Y H:i') }} WIB</span>
            </div>
        </div>

            @if($healthReport['health_score'] >= 75)
                <a href="{{ route('customer.vehicles.certificate', $vehicle->id) }}" target="_blank" class="btn btn-primary" style="background-color: #1e3a8a; border-radius: 8px; border: none;">
                    <i class="bi bi-patch-check-fill me-1"></i> Unduh Sertifikat
                </a>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        
        <!-- CENTERED COLUMN: Main Score & Vital Documents -->
        <div class="col-xl-6 col-lg-8 mx-auto">
            <!-- Unit Card Info -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden;">
                <div class="card-body p-4 text-center">
                    <span class="badge bg-dark font-monospace px-4 py-2 fs-5 mb-3 shadow" style="border-radius: 8px; letter-spacing: 0.5px;">
                        {{ $vehicle->plate_number }}
                    </span>
                    <h4 class="fw-bold text-dark mb-1">{{ $vehicle->type }}</h4>
                    <p class="text-muted mb-3"><i class="bi bi-folder-fill me-1"></i>{{ $vehicle->project->name ?? 'N/A' }}</p>
                    
                    <hr class="text-muted opacity-20 my-3">
                    
                    <div class="row g-2">
                        <div class="col-6 border-end">
                            <h6 class="text-muted small mb-1">Odometer</h6>
                            <h5 class="fw-bold text-dark mb-0">{{ number_format($vehicle->current_km, 0, ',', '.') }} KM</h5>
                        </div>
                        <div class="col-6">
                            <h6 class="text-muted small mb-1">Status Sewa</h6>
                            <h5 class="fw-bold text-success mb-0"><i class="bi bi-check-circle-fill me-1"></i>Aktif</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Health Score Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">Skor Kesehatan Unit</h5>
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold shadow me-4" 
                             style="width: 80px; height: 80px; background: linear-gradient(135deg, {{ $healthStatus['color'] === 'green' ? '#10b981, #059669' : ($healthStatus['color'] === 'yellow' ? '#f59e0b, #d97706' : ($healthStatus['color'] === 'orange' ? '#f97316, #ea580c' : '#ef4444, #dc2626')) }}); font-size: 1.8rem; font-weight: 700;">
                            {{ round($healthReport['health_score']) }}%
                        </div>
                        <div>
                            <span class="badge bg-{{ $healthStatus['color'] }} px-3 py-1.5 fs-6 mb-1">
                                {{ $healthStatus['label'] }}
                            </span>
                            <p class="text-muted small mb-0">{{ $healthStatus['action'] }}</p>
                        </div>
                    </div>

                    <!-- Health Components Breakdown -->
                    <div class="space-y-3">
                        <!-- Component Health -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Kesehatan Komponen</span>
                                <span class="fw-semibold small text-dark">{{ $healthReport['breakdown']['component_health'] }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $healthReport['breakdown']['component_health'] }}%"></div>
                            </div>
                        </div>

                        <!-- Maintenance Compliance -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Kepatuhan Jadwal Servis</span>
                                <span class="fw-semibold small text-dark">{{ $healthReport['breakdown']['maintenance_compliance'] }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $healthReport['breakdown']['maintenance_compliance'] }}%"></div>
                            </div>
                        </div>

                        <!-- Daily Check Score -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Hasil Cek Fisik Harian</span>
                                <span class="fw-semibold small text-dark">{{ $healthReport['breakdown']['daily_check_score'] }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $healthReport['breakdown']['daily_check_score'] }}%"></div>
                            </div>
                        </div>

                        <!-- Age Factor -->
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Faktor Umur Kendaraan</span>
                                <span class="fw-semibold small text-dark">{{ $healthReport['breakdown']['age_factor'] }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $healthReport['breakdown']['age_factor'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Status Card -->
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">Kepatuhan Dokumen</h5>
                    <div class="space-y-4">
                        @foreach($documents as $key => $doc)
                            <div class="p-3 border rounded-3 bg-light mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="bi {{ $doc['icon'] }} text-{{ $doc['color'] }} fs-4 me-3"></i>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">{{ $doc['label'] }}</h6>
                                            @if($doc['expiry'])
                                                <small class="text-muted">Sampai: {{ $doc['expiry'] }}</small>
                                            @else
                                                <small class="text-muted">Tanggal kadaluarsa belum diinput</small>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $doc['color'] }} bg-opacity-10 text-{{ $doc['color'] }} border border-{{ $doc['color'] }} border-opacity-25 px-2.5 py-1.5" style="font-size: 0.75rem;">
                                        {{ $doc['status'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
