@extends('admin.layouts.app')

@section('title', 'Riwayat Servis - ' . $vehicle->plate_number)

@section('content')
<div class="container-fluid p-0">

    {{-- HEADER & NAVIGASI --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-0">Buku Riwayat Servis</h1>
            <div class="d-flex align-items-center gap-2 mt-1">
                <span class="badge bg-primary fs-6">{{ $vehicle->plate_number }}</span>
                <span class="text-muted border-start ps-2 ms-1">{{ $vehicle->type }}</span>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.aset.visual', $vehicle->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-eye-fill me-2"></i> Visual Check
            </a>
            <a href="{{ route('admin.maintenance.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-4">
        
        {{-- KARTU RINGKASAN (Kiri) --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted fw-bold small mb-3">Status Kendaraan Saat Ini</h6>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="display-6 fw-bold text-dark me-3">{{ number_format($statusSummary['km_saat_ini']) }}</div>
                        <span class="text-muted">Km (Odometer)</span>
                    </div>

                    <div class="p-3 rounded bg-light border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-bold text-muted">Servis Berikutnya</span>
                            <span class="fw-bold">{{ number_format($vehicle->last_service_km + $vehicle->service_interval_km) }} Km</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-bold text-muted">Sisa Jarak</span>
                            <span class="badge bg-{{ $statusSummary['color'] }}">{{ number_format($statusSummary['sisa_km']) }} Km</span>
                        </div>
                    </div>

                    <div class="d-grid">
                        {{-- Tombol Cepat Catat Servis (DIBUKA UNTUK SEMUA ADMIN) --}}
                        <button class="btn btn-success" data-bs-toggle="modal" 
                                data-bs-target="#catatServisModal"
                                data-plat-nomor="{{ $vehicle->plate_number }}"
                                data-km-saat-ini="{{ $statusSummary['km_saat_ini'] }}"
                                data-action-url="{{ route('admin.aset.catatServis', $vehicle->id) }}">
                            <i class="bi bi-plus-lg me-2"></i> Catat Servis Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL RIWAYAT (Kanan/Utama) --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-2"></i> Log Aktivitas Servis</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3">Tanggal</th>
                                    <th class="py-3">Keterangan Pengerjaan</th>
                                    <th class="py-3 text-end">KM Servis</th>
                                    <th class="py-3 text-center">Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicle->maintenanceLogs as $log)
                                    <tr>
                                        <td class="ps-4" style="width: 15%;">
                                            <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($log->service_date)->format('d M Y') }}</div>
                                          <small class="text-muted">{{ \Carbon\Carbon::parse($log->service_date)->translatedFormat('l, d F Y') }}</small>
                                        </td>
                                        <td style="width: 50%;">
                                            <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $log->description }}</p>
                                        </td>
                                        <td class="text-end font-monospace fw-bold text-primary" style="width: 20%;">
                                            {{ number_format($log->km_at_service) }}
                                        </td>
                                        <td class="text-center" style="width: 15%;">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                                {{ $log->recorder->name ?? 'System' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-clipboard-x display-6 d-block mb-3 opacity-25"></i>
                                            Belum ada riwayat servis yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include Modal Catat Servis (DIBUKA UNTUK SEMUA ADMIN) --}}
@include('admin.components.modal_catat_servis')

@endsection

@push('scripts')
<script>
    // Script Modal
    document.addEventListener('DOMContentLoaded', function() {
        var catatServisModal = document.getElementById('catatServisModal');
        if (catatServisModal) {
            catatServisModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                if (!button) return;
                var platNomor = button.getAttribute('data-plat-nomor');
                var kmSaatIni = button.getAttribute('data-km-saat-ini');
                var actionUrl = button.getAttribute('data-action-url');
                
                catatServisModal.querySelector('#modalPlatNomor').textContent = platNomor;
                catatServisModal.querySelector('#formCatatServis').setAttribute('action', actionUrl);
                if(kmSaatIni) catatServisModal.querySelector('#km_servis_saat_ini').value = kmSaatIni;
            });
        }
    });
</script>
@endpush