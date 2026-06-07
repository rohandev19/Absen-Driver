@extends('admin.layouts.app')

@section('title', 'Service Darurat')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-1 fs-4 text-dark"><i class="bi bi-tools text-primary me-2"></i>Service Darurat</h2>
        <p class="text-muted small">Daftar laporan kendala dan service unit dari driver.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bs-warning) !important;">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-clock-history text-warning fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold fs-5">{{ $countPendingAdmin }}</h3>
                        <p class="text-muted small mb-0">Perlu Tindakan Admin</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-person-lines-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold fs-5">{{ $countPendingCustomer }}</h3>
                        <p class="text-muted small mb-0">Menunggu Customer</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bs-success) !important;">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold fs-5">{{ $countApproved }}</h3>
                        <p class="text-muted small mb-0">Selesai (Disetujui)</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bs-danger) !important;">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold fs-5">{{ $countIssues }}</h3>
                        <p class="text-muted small mb-0">Revisi / Ditolak</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Table Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            
            {{-- Filter Form --}}
            <form action="{{ route('admin.service.index') }}" method="GET" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Periode (Mulai)</label>
                        <div class="input-group">
                            <input type="date" class="form-control form-control-sm" name="start_date" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Periode (Selesai)</label>
                        <div class="input-group">
                            <input type="date" class="form-control form-control-sm" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Status Laporan</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="Semua Status" {{ request('status') == 'Semua Status' ? 'selected' : '' }}>Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="pending_admin" {{ request('status') == 'pending_admin' ? 'selected' : '' }}>Pending Admin</option>
                            <option value="pending_customer" {{ request('status') == 'pending_customer' ? 'selected' : '' }}>Menunggu Customer</option>
                            <option value="approved_customer" {{ request('status') == 'approved_customer' ? 'selected' : '' }}>Disetujui Customer</option>
                            <option value="revision_requested" {{ request('status') == 'revision_requested' ? 'selected' : '' }}>Revisi Diminta</option>
                            <option value="rejected_customer" {{ request('status') == 'rejected_customer' ? 'selected' : '' }}>Ditolak Customer</option>
                            <option value="rejected_admin" {{ request('status') == 'rejected_admin' ? 'selected' : '' }}>Ditolak Admin</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Plat Nomor</label>
                        <input type="text" class="form-control form-control-sm" name="plate_number" placeholder="Cari plat" value="{{ request('plate_number') }}">
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <a href="{{ route('admin.service.index') }}" class="btn btn-outline-secondary btn-sm w-100 d-flex align-items-center justify-content-center" style="min-height:31px;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center" style="min-height:31px;">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="small fw-bold text-muted py-3">No Tiket</th>
                            <th class="small fw-bold text-muted py-3 d-none d-md-table-cell">Tanggal</th>
                            <th class="small fw-bold text-muted py-3">Driver</th>
                            <th class="small fw-bold text-muted py-3 d-none d-lg-table-cell">Unit</th>
                            <th class="small fw-bold text-muted py-3 d-none d-md-table-cell">Customer</th>
                            <th class="small fw-bold text-muted py-3">Status</th>
                            <th class="small fw-bold text-muted py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-primary">{{ $report->ticket_number ?? 'N/A' }}</span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="d-block small fw-bold">{{ $report->timestamp->format('d M Y') }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $report->timestamp->format('H:i') }} WIB</span>
                                </td>
                                <td>
                                    <span class="d-block fw-semibold small">{{ $report->driver->name ?? 'N/A' }}</span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <span class="badge bg-secondary mb-1">{{ $report->vehicle->plate_number ?? 'N/A' }}</span>
                                    <span class="d-block text-muted" style="font-size: 0.75rem;">{{ $report->vehicle->type ?? '-' }}</span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="fw-semibold small">{{ $report->customer->name ?? '-' }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusBadge = match($report->status) {
                                            'pending', 'pending_admin' => 'bg-warning text-dark',
                                            'waiting_completion' => 'bg-info text-dark',
                                            'approved_admin', 'pending_customer' => 'bg-primary',
                                            'approved_customer' => 'bg-success',
                                            'revision_requested' => 'bg-danger',
                                            'rejected', 'rejected_admin', 'rejected_customer' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        $statusText = match($report->status) {
                                            'pending', 'pending_admin' => 'Menunggu Admin',
                                            'waiting_completion' => 'Menunggu Kelengkapan',
                                            'approved_admin', 'pending_customer' => 'Menunggu Customer',
                                            'approved_customer' => 'Selesai',
                                            'revision_requested' => 'Klarifikasi Customer',
                                            'rejected', 'rejected_admin' => 'Ditolak Admin',
                                            'rejected_customer' => 'Ditolak Customer',
                                            default => $report->status
                                        };
                                    @endphp
                                    <span class="badge rounded-pill px-3 py-2 {{ $statusBadge }} fw-semibold shadow-sm" style="font-size: 0.75rem;">
                                        @if(in_array($report->status, ['pending', 'pending_admin']))
                                            <i class="bi bi-hourglass-split me-1"></i>
                                        @elseif(in_array($report->status, ['approved_admin', 'pending_customer']))
                                            <i class="bi bi-clock-history me-1"></i>
                                        @elseif($report->status === 'approved_customer')
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                        @elseif($report->status === 'revision_requested')
                                            <i class="bi bi-exclamation-circle-fill me-1"></i>
                                        @else
                                            <i class="bi bi-x-circle-fill me-1"></i>
                                        @endif
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.service.show', $report->id) }}" 
                                           class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="tooltip" title="Lihat Detail">
                                            Detail
                                        </a>
                                        @if($report->customer_word_path)
                                            <a href="{{ asset('storage/' . $report->customer_word_path) }}" 
                                               class="btn btn-sm btn-outline-success rounded-pill px-3" download data-bs-toggle="tooltip" title="Download BAST Word">
                                                <i class="bi bi-file-earmark-word"></i> Word
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                        <h6 class="mt-3 fw-bold text-dark">Belum ada laporan service</h6>
                                        <p class="text-muted small">Data laporan service darurat yang masuk akan tampil di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($reports->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                    <p class="text-muted small mb-0">Menampilkan {{ $reports->firstItem() ?? 0 }} s/d {{ $reports->lastItem() ?? 0 }} dari {{ $reports->total() }} data</p>
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
@endpush
@endsection
