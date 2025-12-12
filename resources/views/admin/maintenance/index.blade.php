@extends('admin.layouts.app')

@section('title', 'Monitoring & Maintenance')

@section('content')
    <style>
        .maintenance-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .maintenance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        }
    </style>

    <div class="container-fluid p-0">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-0"><i
                        class="bi bi-wrench-adjustable-circle text-primary me-2"></i>Maintenance Monitor</h1>
                <span class="text-muted">Pantau kesehatan mesin dan jadwal servis berkala</span>
            </div>
            <form action="{{ route('admin.maintenance.dashboard') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari Plat Nomor..."
                    value="{{ $searchKeyword }}">
                <button class="btn btn-primary" type="submit">Cari</button>
            </form>
        </div>

        <div class="row">
            {{-- Loop data Vehicle Model --}}
            @forelse($maintenanceData as $vehicle)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div
                        class="card shadow border-0 h-100 maintenance-card {{ ($vehicle->health_status_code === 'service_due' || $vehicle->health_status_code === 'physical_issue') ? 'border-start border-5 border-danger' : '' }}">

                        <div class="card-body">
                            {{-- Judul Kartu --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0">{{ $vehicle->plate_number }}</h5>
                                    <small class="text-muted">{{ $vehicle->type }}</small>
                                </div>

                                @if($vehicle->health_status_code === 'service_due')
                                    <span class="badge bg-danger">SERVIS SEKARANG</span>
                                @elseif($vehicle->health_status_code === 'warning')
                                    <span class="badge bg-warning text-dark">Warning Servis</span>
                                @elseif($vehicle->health_status_code === 'physical_issue')
                                    <span class="badge bg-danger">Perbaikan Fisik</span>
                                @else
                                    <span class="badge bg-success">Prima</span>
                                @endif
                            </div>

                            {{-- Progress Bar --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Sisa Jarak Servis</span>
                                    <span
                                        class="fw-bold {{ ($vehicle->sisa_km !== null && $vehicle->sisa_km < 1000) ? 'text-danger' : 'text-success' }}">
                                        {{ $vehicle->sisa_km !== null ? number_format($vehicle->sisa_km) . ' Km' : '-' }}
                                    </span>
                                </div>

                                @php
                                    $percent = 100;
                                    if ($vehicle->service_interval_km > 0) {
                                        $jarakTempuh = $vehicle->current_km - $vehicle->last_service_km;
                                        $percent = 100 - (($jarakTempuh / $vehicle->service_interval_km) * 100);
                                    }
                                    if ($percent < 0)
                                        $percent = 0;
                                @endphp

                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $percent < 20 ? 'bg-danger' : 'bg-success' }}"
                                        role="progressbar" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>

                            {{-- Detail Info --}}
                            <div class="row g-2 small text-muted mb-4">
                                <div class="col-6">
                                    <i class="bi bi-speedometer2 me-1"></i> KM Saat Ini:<br>
                                    <strong class="text-dark">{{ number_format($vehicle->current_km) }}</strong>
                                </div>
                                <div class="col-6">
                                    <i class="bi bi-calendar-check me-1"></i> Update:<br>
                                    <strong class="text-dark">
                                        {{ $vehicle->latestAttendance ? $vehicle->latestAttendance->updated_at->diffForHumans() : '-' }}
                                    </strong>
                                </div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="d-grid gap-2">
                                <div class="btn-group w-100">
                                    <a href="{{ route('admin.aset.visual', $vehicle->id) }}"
                                        class="btn btn-outline-primary btn-sm w-50">
                                        <i class="bi bi-eye-fill me-1"></i> Visual
                                    </a>

                                    <a href="{{ route('admin.aset.riwayat', $vehicle->id) }}"
                                        class="btn btn-outline-info btn-sm w-50">
                                        <i class="bi bi-journal-text me-1"></i> Riwayat
                                    </a>
                                </div>

                                {{-- FITUR CATAT SERVIS & PERBAIKAN DIBUKA UNTUK SEMUA ADMIN --}}
                                @if ($vehicle->health_status_code === 'physical_issue')
                                    {{-- JIKA RUSAK FISIK: Tombol Selesai Perbaikan --}}
                                    <form action="{{ route('admin.aset.resolveIssue', $vehicle->id) }}" method="POST"
                                        class="d-grid form-confirm-repair">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-bandaid-fill me-2"></i> Tandai Selesai Perbaikan
                                        </button>
                                    </form>
                                @else
                                    {{-- JIKA AMAN / SERVIS RUTIN: Tombol Catat Servis --}}
                                    <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#catatServisModal" data-plat-nomor="{{ $vehicle->plate_number }}"
                                        data-km-saat-ini="{{ $vehicle->current_km }}"
                                        data-action-url="{{ route('admin.aset.catatServis', $vehicle->id) }}">
                                        <i class="bi bi-wrench-adjustable me-2"></i> Catat Servis Baru
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1"></i>
                    <p>Data aset tidak ditemukan.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- INCLUDE MODAL (DIBUKA UNTUK SEMUA ADMIN) --}}
    @include('admin.components.modal_catat_servis')

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // SweetAlert Konfirmasi Perbaikan
            const repairForms = document.querySelectorAll('.form-confirm-repair');
            repairForms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        title: 'Selesai Perbaikan?',
                        text: "Status mobil akan dikembalikan menjadi 'Aman' dan siap beroperasi.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Selesai!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush