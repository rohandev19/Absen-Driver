@extends('admin.layouts.app')

@section('title', 'Riwayat Servis - ' . $vehicle->plate_number)

@section('content')
    <style>
        /* === CORPORATE TIMELINE STYLE === */
        .timeline {
            border-left: 2px solid #e2e8f0;
            margin-left: 16px;
            padding-left: 24px;
            position: relative;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 32px;
        }

        .timeline-dot {
            position: absolute;
            left: -33px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid #3b82f6;
            box-shadow: 0 0 0 4px #eff6ff;
        }

        .timeline-date {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
            display: block;
        }

        .timeline-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
            transition: all 0.2s;
        }

        .timeline-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        /* === METRIC CARD (VERTICAL) === */
        .card-metric-v {
            background: linear-gradient(to bottom right, #ffffff, #f8fafc);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
        }

        .metric-v-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1;
            margin: 10px 0;
        }

        .metric-v-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 600;
        }
    </style>

    <div class="container-fluid p-0">

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Buku Riwayat Servis</h3>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary fs-6 font-monospace">{{ $vehicle->plate_number }}</span>
                    <span class="text-muted border-start ps-2 small">{{ $vehicle->type }}</span>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.aset.export_excel', $vehicle->id) }}"
                    class="btn btn-success btn-sm d-flex align-items-center">
                    <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
                </a>
                <a href="{{ route('admin.maintenance.dashboard') }}"
                    class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                    <i class="bi bi-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row g-4">

            {{-- PANEL KIRI: STATUS SAAT INI --}}
            <div class="col-lg-4">
                <div class="card-metric-v h-100">
                    <div class="mb-4">
                        <div class="metric-v-label">ODOMETER SAAT INI</div>
                        {{-- Mengambil langsung dari $vehicle->current_km --}}
                        <div class="metric-v-value">{{ number_format($vehicle->current_km) }}</div>
                        <span class="text-muted small">Kilometer</span>
                    </div>

                    <hr class="border-secondary opacity-10 my-4">

                    <div class="row text-start g-3">
                        <div class="col-6">
                            <label class="small text-muted fw-bold">HEALTH SCORE</label>
                            @php
                                $scoreColor = $score >= 75 ? 'success' : ($score >= 40 ? 'warning' : 'danger');
                            @endphp
                            <div class="fs-5 fw-bold text-{{ $scoreColor }}">
                                {{ round($score, 1) }}/100
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted fw-bold">JADWAL TERDEKAT</label>
                            <div>
                                @if($nextSchedule)
                                    <span class="badge bg-primary mt-1" style="font-size: 0.85rem;">
                                        {{ \Carbon\Carbon::parse($nextSchedule->scheduled_date)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary mt-1" style="font-size: 0.85rem;">Belum Ada</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4 gap-2">
                        <a href="{{ route('admin.aset.visual', $vehicle->id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-eye me-2"></i> Cek Fisik Terakhir
                        </a>

                        <a href="{{ route('admin.maintenance.schedules', ['vehicle_id' => $vehicle->id]) }}"
                            class="btn btn-primary">
                            <i class="bi bi-calendar-plus me-2"></i> Buat Jadwal Servis
                        </a>
                    </div>
                </div>
            </div>

            {{-- PANEL KANAN: TIMELINE (Dari Maintenance Schedules) --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-info"></i>Jejak Perawatan
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-light bg-opacity-25">

                        <div class="timeline">
                            @forelse($vehicle->maintenanceSchedules as $jadwal)
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <span class="timeline-date">
                                        {{ \Carbon\Carbon::parse($jadwal->completed_at ?? $jadwal->scheduled_date)->translatedFormat('l, d F Y') }}
                                    </span>

                                    <div class="timeline-card">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                                {{ $jadwal->component ? $jadwal->component->component_name : 'General Checkup / Lainnya' }}
                                            </span>
                                            <span class="badge bg-dark bg-opacity-10 text-dark border">
                                                KM: <strong>{{ number_format($jadwal->scheduled_km ?? 0) }}</strong>
                                            </span>
                                        </div>

                                        <div class="row text-secondary mb-2 mt-3" style="font-size: 0.85rem;">
                                            <div class="col-6">
                                                <i class="bi bi-shop me-1"></i>
                                                {{ $jadwal->workshop_name ?: 'Internal/Tdk. Diketahui' }}
                                            </div>
                                            <div class="col-6 text-end">
                                                <i class="bi bi-cash me-1"></i> Rp
                                                {{ number_format($jadwal->actual_cost ?: $jadwal->estimated_cost, 0, ',', '.') }}
                                            </div>
                                        </div>

                                        <p class="mb-0 text-secondary bg-light p-2 rounded mt-2"
                                            style="white-space: pre-line; font-size: 0.85rem;">
                                            <strong>Catatan:</strong><br>
                                            {{ $jadwal->notes ?: 'Tidak ada catatan khusus.' }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <div class="text-muted opacity-50 mb-2">
                                        <i class="bi bi-journal-check display-4"></i>
                                    </div>
                                    <h6 class="text-muted">Belum ada riwayat servis yang diselesaikan.</h6>
                                    <p class="small text-secondary">Silakan catat perawatan melalui menu Jadwal Servis.</p>
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection