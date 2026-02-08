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
            /* Adjust based on padding + border */
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid #3b82f6;
            /* Primary Color */
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
                        <div class="metric-v-value">{{ number_format($statusSummary['km_saat_ini']) }}</div>
                        <span class="text-muted small">Kilometer</span>
                    </div>

                    <hr class="border-secondary opacity-10 my-4">

                    <div class="row text-start g-3">
                        <div class="col-6">
                            <label class="small text-muted fw-bold">SERVIS BERIKUTNYA</label>
                            <div class="fs-5 fw-bold text-dark">
                                {{ number_format($vehicle->last_service_km + $vehicle->service_interval_km) }}
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted fw-bold">SISA JARAK</label>
                            <div>
                                <span class="badge bg-{{ $statusSummary['color'] }} fs-6">
                                    {{ number_format($statusSummary['sisa_km']) }} Km
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4 gap-2">
                        <a href="{{ route('admin.aset.visual', $vehicle->id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-eye me-2"></i> Cek Fisik Terakhir
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#catatServisModal"
                            data-plat-nomor="{{ $vehicle->plate_number }}"
                            data-km-saat-ini="{{ $statusSummary['km_saat_ini'] }}"
                            data-action-url="{{ route('admin.aset.catatServis', $vehicle->id) }}">
                            <i class="bi bi-wrench me-2"></i> Catat Servis Baru
                        </button>
                    </div>
                </div>
            </div>

            {{-- PANEL KANAN: TIMELINE --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-info"></i>Jejak Perawatan
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-light bg-opacity-25">

                        <div class="timeline">
                            @forelse($vehicle->maintenanceLogs as $log)
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <span class="timeline-date">
                                        {{ \Carbon\Carbon::parse($log->service_date)->translatedFormat('l, d F Y') }}
                                    </span>

                                    <div class="timeline-card">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-dark bg-opacity-10 text-dark border">
                                                KM Servis: <strong>{{ number_format($log->km_at_service) }}</strong>
                                            </span>
                                            <small class="text-muted fst-italic" style="font-size: 0.75rem;">
                                                <i class="bi bi-pencil me-1"></i> {{ $log->recorder->name ?? 'Admin' }}
                                            </small>
                                        </div>
                                        <p class="mb-0 text-secondary" style="white-space: pre-line; line-height: 1.5;">
                                            {{ $log->description }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <div class="text-muted opacity-50 mb-2">
                                        <i class="bi bi-journal-x display-4"></i>
                                    </div>
                                    <h6 class="text-muted">Belum ada riwayat servis tercatat.</h6>
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include Modal Catat Servis --}}
    @include('admin.components.modal_catat_servis')

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var catatServisModal = document.getElementById('catatServisModal');
            if (catatServisModal) {
                catatServisModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) return;

                    var platNomor = button.getAttribute('data-plat-nomor');
                    var kmSaatIni = button.getAttribute('data-km-saat-ini');
                    var actionUrl = button.getAttribute('data-action-url');

                    catatServisModal.querySelector('#modalPlatNomor').textContent = platNomor;
                    catatServisModal.querySelector('#formCatatServis').setAttribute('action', actionUrl);
                    if (kmSaatIni) catatServisModal.querySelector('#km_servis_saat_ini').value = kmSaatIni;
                });
            }
        });
    </script>
@endpush