@extends('customer.layouts.app')

@section('title', 'Riwayat Maintenance - ' . $vehicle->plate_number)

@section('content')
<div class="container-fluid py-2">
    <!-- Back & Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <div class="d-flex align-items-center">
            <a href="{{ route('customer.vehicles.show', $vehicle->id) }}" class="btn btn-outline-secondary me-3" style="border-radius: 8px;">
                <i class="bi bi-arrow-left"></i> Rincian Unit
            </a>
            <div>
                <h3 class="fw-bold text-dark mb-0">Riwayat Perawatan & Servis</h3>
                <span class="text-muted small">Kronologi lengkap pemeliharaan unit {{ $vehicle->plate_number }}</span>
            </div>
        </div>
        
        <span class="badge bg-dark font-monospace px-3 py-2 fs-6 shadow-sm" style="border-radius: 6px; letter-spacing: 0.5px;">
            {{ $vehicle->plate_number }}
        </span>
    </div>

    <!-- Navigation Tabs for Logs vs Completed Schedules -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <ul class="nav nav-pills nav-fill bg-light p-1 border rounded-3 mb-2" id="historyTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold py-2.5" id="logs-tab" data-bs-toggle="pill" data-bs-target="#logs-panel" type="button" role="tab" style="border-radius: 8px;">
                                <i class="bi bi-file-text me-2"></i>Catatan Bengkel Harian ({{ $logs->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold py-2.5" id="schedules-tab" data-bs-toggle="pill" data-bs-target="#schedules-panel" type="button" role="tab" style="border-radius: 8px;">
                                <i class="bi bi-check-circle me-2"></i>Pemeliharaan Preventif Selesai ({{ $completedSchedules->count() }})
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content" id="historyTabsContent">
                        
                        <!-- PANEL 1: Catatan Bengkel Harian -->
                        <div class="tab-pane fade show active" id="logs-panel" role="tabpanel">
                            @if($logs->isEmpty())
                                <div class="alert alert-info py-4 text-center border-0 mb-0" style="border-radius: 10px;">
                                    <i class="bi bi-info-circle fs-3 mb-2 d-block"></i>
                                    Belum ada rekaman catatan servis harian bengkel untuk unit ini.
                                </div>
                            @else
                                <!-- Chronological Vertical Timeline -->
                                <div class="timeline-container py-3 position-relative">
                                    <div class="timeline-line position-absolute start-50 translate-middle-x bg-secondary bg-opacity-20" style="width: 2px; top: 0; bottom: 0;"></div>
                                    
                                    @foreach($logs as $index => $log)
                                        @php
                                            $isEven = $index % 2 === 0;
                                        @endphp
                                        <div class="timeline-item mb-5 d-flex flex-column flex-md-row position-relative">
                                            <!-- Icon badge in middle -->
                                            <div class="timeline-badge rounded-circle bg-primary text-white position-absolute start-50 translate-middle-x d-flex align-items-center justify-content-center border border-white border-3 shadow" 
                                                 style="width: 42px; height: 42px; z-index: 1; top: 0;">
                                                <i class="bi bi-wrench-adjustable" style="font-size: 0.95rem;"></i>
                                            </div>

                                            <div class="row w-100 g-0">
                                                <!-- Left slot (date on odd, card on even) -->
                                                <div class="col-md-5 {{ $isEven ? 'order-1 text-md-end pe-md-5 pt-1' : 'order-3 order-md-1 ps-md-5 pt-1 text-md-start' }}">
                                                    @if($isEven)
                                                        <div class="p-4 border rounded-3 bg-white shadow-sm hover-grow text-start">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle border-opacity-25">{{ \Carbon\Carbon::parse($log['date'])->format('d M Y') }}</span>
                                                                <small class="text-muted"><i class="bi bi-speedometer2"></i> {{ $log['km_at_service'] }} KM</small>
                                                            </div>
                                                            <h6 class="fw-bold text-dark mb-2">Perbaikan / Pemeliharaan Bengkel</h6>
                                                            <p class="text-muted small mb-0">{{ $log['description'] }}</p>
                                                            <div class="border-top pt-2 mt-2">
                                                                <small class="text-muted"><i class="bi bi-shop me-1"></i>Bengkel: <strong>{{ $log['workshop'] }}</strong></small>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="pe-md-5 text-md-end pt-1">
                                                            <h5 class="fw-bold text-primary mb-0">{{ \Carbon\Carbon::parse($log['date'])->format('d M Y') }}</h5>
                                                            <span class="text-muted small"><i class="bi bi-speedometer2"></i> {{ $log['km_at_service'] }} KM</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Spacing middle -->
                                                <div class="col-md-2 order-2"></div>

                                                <!-- Right slot (card on odd, date on even) -->
                                                <div class="col-md-5 {{ $isEven ? 'order-3 ps-md-5 pt-1 text-md-start' : 'order-1 text-md-start pe-md-5 pt-1' }}">
                                                    @if(!$isEven)
                                                        <div class="p-4 border rounded-3 bg-white shadow-sm hover-grow">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle border-opacity-25">{{ \Carbon\Carbon::parse($log['date'])->format('d M Y') }}</span>
                                                                <small class="text-muted"><i class="bi bi-speedometer2"></i> {{ $log['km_at_service'] }} KM</small>
                                                            </div>
                                                            <h6 class="fw-bold text-dark mb-2">Perbaikan / Pemeliharaan Bengkel</h6>
                                                            <p class="text-muted small mb-0">{{ $log['description'] }}</p>
                                                            <div class="border-top pt-2 mt-2">
                                                                <small class="text-muted"><i class="bi bi-shop me-1"></i>Bengkel: <strong>{{ $log['workshop'] }}</strong></small>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="ps-md-5 pt-1">
                                                            <h5 class="fw-bold text-primary mb-0">{{ \Carbon\Carbon::parse($log['date'])->format('d M Y') }}</h5>
                                                            <span class="text-muted small"><i class="bi bi-speedometer2"></i> {{ $log['km_at_service'] }} KM</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- PANEL 2: Pemeliharaan Preventif Selesai -->
                        <div class="tab-pane fade" id="schedules-panel" role="tabpanel">
                            @if($completedSchedules->isEmpty())
                                <div class="alert alert-info py-4 text-center border-0 mb-0" style="border-radius: 10px;">
                                    <i class="bi bi-info-circle fs-3 mb-2 d-block"></i>
                                    Belum ada rekam jejak agenda preventif terjadwal yang diselesaikan untuk unit ini.
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach($completedSchedules as $schedule)
                                        <div class="p-4 border rounded-3 bg-white shadow-sm hover-grow mb-3 position-relative overflow-hidden">
                                            <!-- Top indicator -->
                                            <div class="position-absolute top-0 start-0 bottom-0" style="width: 5px; background-color: #10b981;"></div>
                                            
                                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start mb-3 gap-2">
                                                <div>
                                                    <div class="d-flex align-items-center gap-2 mb-1.5">
                                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle border-opacity-25">Diselesaikan</span>
                                                        <span class="badge bg-primary text-uppercase" style="font-size: 0.7rem;">{{ $schedule['type'] }}</span>
                                                    </div>
                                                    <h5 class="fw-bold text-dark mb-0">Part / Komponen: {{ $schedule['component'] }}</h5>
                                                </div>
                                                <div class="text-sm-end text-muted small">
                                                    <i class="bi bi-calendar-check me-1"></i>Selesai: <strong>{{ $schedule['date'] }}</strong>
                                                </div>
                                            </div>
                                            
                                            <div class="p-3 bg-light rounded-2 border">
                                                <h6 class="fw-bold text-secondary mb-1" style="font-size: 0.8rem;">Catatan Penyelesaian:</h6>
                                                <p class="text-muted small mb-0">{{ $schedule['notes'] ?? 'Tanpa catatan tambahan.' }}</p>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-2">
                                                <small class="text-muted"><i class="bi bi-shop me-1"></i>Bengkel Pelaksana: <strong>{{ $schedule['workshop'] ?? '-' }}</strong></small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Timeline styles for wide layout */
    @media (max-width: 768px) {
        .timeline-line {
            left: 20px !important;
        }
        .timeline-badge {
            left: 20px !important;
            transform: none !important;
        }
        .timeline-item {
            flex-direction: column !important;
            padding-left: 50px !important;
        }
        .timeline-item > .row > div {
            text-align: left !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            width: 100% !important;
        }
        .timeline-item > .row > .order-2 {
            display: none !important;
        }
        .timeline-item > .row > .order-1,
        .timeline-item > .row > .order-3 {
            order: unset !important;
        }
    }
    
    .hover-grow {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .hover-grow:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05) !important;
    }
</style>
@endsection
