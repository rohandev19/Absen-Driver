@extends('customer.layouts.app')

@section('title', 'Unit Kendaraan')

@section('content')
<div class="container-fluid py-2">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-truck text-primary me-2"></i>Unit Kendaraan</h2>
            <p class="text-muted mb-0">Pantau kelayakan fisik, dokumen penting, dan status maintenance real-time seluruh armada Anda.</p>
        </div>
        
        <!-- Search bar -->
        <form action="{{ route('customer.vehicles') }}" method="GET" class="d-flex align-items-center">
            <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden; width: 300px;">
                <span class="input-group-text bg-white border-0 pe-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-0 ps-2" placeholder="Cari plat atau tipe..." value="{{ $search }}">
                @if($search)
                    <a href="{{ route('customer.vehicles') }}" class="btn btn-white border-0 text-muted d-flex align-items-center justify-content-center px-2">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
                <button type="submit" class="btn btn-primary px-3 border-0" style="background-color: #1e3a8a;">Cari</button>
            </div>
        </form>
    </div>

    <!-- Vehicles Grid -->
    @if($vehicles->isEmpty())
        <div class="card border-0 shadow-sm py-5 text-center" style="border-radius: 12px;">
            <div class="card-body">
                <i class="bi bi-truck-flatbed fs-1 text-muted mb-3 d-block"></i>
                <h4 class="fw-bold text-dark">Tidak Ada Unit Ditemukan</h4>
                <p class="text-muted mb-0">Coba kata kunci pencarian lain atau hubungi admin jika terdapat kesalahan data.</p>
                @if($search)
                    <a href="{{ route('customer.vehicles') }}" class="btn btn-sm btn-outline-primary mt-3" style="border-radius: 8px;">Reset Pencarian</a>
                @endif
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($vehicles as $vehicle)
                @php
                    $hColor = $vehicle->health_status['color'];
                    $hLabel = $vehicle->health_status['label'];
                @endphp
                <div class="col-xl-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 transition-hover position-relative" style="border-radius: 14px; overflow: hidden;">

                        <div class="card-body p-4 pt-4">
                            <!-- Card Header Info -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-dark font-monospace px-3 py-2 fs-6 mb-2 shadow-sm" style="border-radius: 6px; letter-spacing: 0.5px;">
                                        {{ $vehicle->plate_number }}
                                    </span>
                                    <h5 class="fw-bold mb-0 text-dark text-truncate" style="max-width: 180px;">{{ $vehicle->type }}</h5>
                                    <span class="text-muted small"><i class="bi bi-folder-fill me-1"></i>{{ $vehicle->project->name ?? '-' }}</span>
                                </div>
                                <div class="text-end">
                                    <div class="d-flex flex-column align-items-end">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold mb-1 shadow-sm" 
                                             style="width: 54px; height: 54px; background: linear-gradient(135deg, {{ $hColor === 'green' ? '#10b981, #059669' : ($hColor === 'yellow' ? '#f59e0b, #d97706' : ($hColor === 'orange' ? '#f97316, #ea580c' : '#ef4444, #dc2626')) }}); font-size: 1.15rem; font-weight: 700;">
                                            {{ round($vehicle->health_score) }}%
                                        </div>
                                        @php
                                            $bsColor = $hColor === 'green' ? 'success' : ($hColor === 'yellow' || $hColor === 'orange' ? 'warning' : 'danger');
                                        @endphp
                                        <span class="badge bg-{{ $bsColor }} bg-opacity-10 text-{{ $bsColor }} border border-{{ $bsColor }} border-opacity-25" style="font-size: 0.75rem;">
                                            {{ $hLabel }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <hr class="text-muted opacity-20 my-3">

                            <!-- Details list -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Odometer Saat Ini:</span>
                                    <span class="fw-semibold text-dark">{{ number_format($vehicle->current_km, 0, ',', '.') }} KM</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Masa Berlaku STNK:</span>
                                    @php
                                        $stnkDiff = $vehicle->pajak_stnk_berlaku_sampai ? now()->diffInDays($vehicle->pajak_stnk_berlaku_sampai, false) : null;
                                        $stnkClass = $stnkDiff === null ? 'secondary' : ($stnkDiff < 0 ? 'danger' : ($stnkDiff <= 30 ? 'warning' : 'success'));
                                        $stnkText = $stnkDiff === null ? 'Belum diisi' : ($stnkDiff < 0 ? 'MATI' : ($stnkDiff <= 30 ? $stnkDiff.' hari lagi' : 'Aktif'));
                                    @endphp
                                    <span class="badge bg-{{ $stnkClass }} bg-opacity-10 text-{{ $stnkClass }} border border-{{ $stnkClass }} border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                                        {{ $stnkText }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Masa Berlaku KIR:</span>
                                    @php
                                        $kirDiff = $vehicle->kir_berlaku_sampai ? now()->diffInDays($vehicle->kir_berlaku_sampai, false) : null;
                                        $kirClass = $kirDiff === null ? 'secondary' : ($kirDiff < 0 ? 'danger' : ($kirDiff <= 30 ? $kirDiff.' hari lagi' : 'Aktif'));
                                        $kirText = $kirDiff === null ? 'Belum diisi' : ($kirDiff < 0 ? 'MATI' : ($kirDiff <= 30 ? $kirDiff.' hari lagi' : 'Aktif'));
                                    @endphp
                                    <span class="badge bg-{{ $kirClass }} bg-opacity-10 text-{{ $kirClass }} border border-{{ $kirClass }} border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                                        {{ $kirText }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Isu Aktif:</span>
                                    <span class="fw-semibold {{ $vehicle->active_alerts_count > 0 ? 'text-danger' : 'text-success' }}">
                                        <i class="bi {{ $vehicle->active_alerts_count > 0 ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' }} me-1"></i>
                                        {{ $vehicle->active_alerts_count }} Laporan
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="{{ route('customer.vehicles.show', $vehicle->id) }}" class="btn btn-outline-primary w-100 shadow-sm border-2 font-weight-semibold py-2" style="border-radius: 8px; font-size: 0.85rem;">
                                        <i class="bi bi-eye-fill"></i> Detail Unit
                                    </a>
                                </div>
                                <div class="col-6">
                                    @if($vehicle->health_score >= 75)
                                        <a href="{{ route('customer.vehicles.certificate', $vehicle->id) }}" target="_blank" class="btn btn-primary w-100 shadow-sm border-0 py-2" style="background-color: #1e3a8a; border-radius: 8px; font-size: 0.85rem;">
                                            <i class="bi bi-patch-check-fill"></i> Sertifikat
                                        </a>
                                    @else
                                        <button class="btn btn-secondary w-100 py-2 border-0 opacity-50" style="border-radius: 8px; font-size: 0.85rem;" disabled data-bs-toggle="tooltip" title="Sertifikat hanya tersedia jika kesehatan >= 75%">
                                            <i class="bi bi-patch-exclamation-fill"></i> Sertifikat
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-5">
            {{ $vehicles->appends(['search' => $search])->links() }}
        </div>
    @endif
</div>

<style>
    .transition-hover {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .transition-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08) !important;
    }
</style>
@endsection

@push('scripts')
<script>
    // Enable Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endpush
