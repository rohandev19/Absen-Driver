@extends('customer.layouts.app')

@section('title', 'Konfirmasi Service Unit')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-1 fs-4 text-dark">Konfirmasi Service Unit</h2>
        <p class="text-muted small">Daftar laporan service unit yang memerlukan konfirmasi Anda.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-clock-history text-warning fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold fs-5">{{ $countPending }}</h3>
                        <p class="text-muted small mb-0">Menunggu Konfirmasi</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold fs-5">{{ $countApproved }}</h3>
                        <p class="text-muted small mb-0">Terkonfirmasi</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-question-circle text-info fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold fs-5">{{ $countClarification }}</h3>
                        <p class="text-muted small mb-0">Minta Klarifikasi</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-x-circle text-danger fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold fs-5">{{ $countRejected }}</h3>
                        <p class="text-muted small mb-0">Ditolak</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Table Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            
            {{-- Filter Form --}}
            <form action="{{ route('customer.approve.index') }}" method="GET" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Periode (Mulai)</label>
                        <div class="input-group">
                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Periode (Selesai)</label>
                        <div class="input-group">
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Status Konfirmasi</label>
                        <select class="form-select" name="status">
                            <option value="Semua Status" {{ request('status') == 'Semua Status' ? 'selected' : '' }}>Semua Status</option>
                            <option value="pending_customer" {{ request('status') == 'pending_customer' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                            <option value="approved_customer" {{ request('status') == 'approved_customer' ? 'selected' : '' }}>Terkonfirmasi</option>
                            <option value="revision_requested" {{ request('status') == 'revision_requested' ? 'selected' : '' }}>Minta Klarifikasi</option>
                            <option value="rejected_customer" {{ request('status') == 'rejected_customer' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Plat Nomor</label>
                        <input type="text" class="form-control" name="plate_number" placeholder="Cari plat nomor" value="{{ request('plate_number') }}">
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <a href="{{ route('customer.approve.index') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            {{-- Mobile Card View --}}
            <div class="d-block d-md-none">
                @forelse($reports as $report)
                    <div class="card border mb-3 shadow-sm rounded-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                <div>
                                    <span class="fw-bold text-dark d-block" style="font-size: 0.95rem;">{{ $report->ticket_number ?? 'N/A' }}</span>
                                    <span class="badge bg-secondary mt-1">{{ $report->vehicle->plate_number ?? 'N/A' }}</span>
                                </div>
                                <div class="text-end">
                                    @if($report->status === 'pending_customer')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning" style="font-size: 0.7rem;">Menunggu</span>
                                    @elseif($report->status === 'approved_customer')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success" style="font-size: 0.7rem;">Terkonfirmasi</span>
                                    @elseif($report->status === 'revision_requested')
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info" style="font-size: 0.7rem;">Klarifikasi</span>
                                    @elseif($report->status === 'rejected_customer')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger" style="font-size: 0.7rem;">Ditolak</span>
                                    @else
                                        <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ $report->status }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div class="small text-muted" style="font-size: 0.8rem;">
                                    <i class="bi bi-calendar-event me-1"></i> {{ $report->timestamp->format('d-m-Y') }}
                                </div>
                                <a href="{{ route('customer.approve.show', $report->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                    Lihat <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted border rounded bg-light">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Tidak ada laporan.
                    </div>
                @endforelse
            </div>

            {{-- Desktop Table View --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="small fw-bold text-muted py-3 text-nowrap">No Tiket</th>
                            <th class="small fw-bold text-muted py-3 d-none d-md-table-cell text-nowrap">Tanggal Kendala</th>
                            <th class="small fw-bold text-muted py-3 d-none d-md-table-cell text-nowrap">Plat Nomor</th>
                            <th class="small fw-bold text-muted py-3 d-none d-lg-table-cell text-nowrap">Unit</th>
                            <th class="small fw-bold text-muted py-3 d-none d-lg-table-cell text-nowrap">Jenis Kendala</th>
                            <th class="small fw-bold text-muted py-3 d-none d-md-table-cell text-nowrap">Status Unit</th>
                            <th class="small fw-bold text-muted py-3 text-nowrap">Status Konfirmasi</th>
                            <th class="small fw-bold text-muted py-3 text-center text-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $report->ticket_number ?? 'N/A' }}</span>
                                </td>
                                <td class="d-none d-md-table-cell text-nowrap">{{ $report->timestamp->format('d-m-Y H:i') }}</td>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge bg-secondary">{{ $report->vehicle->plate_number ?? 'N/A' }}</span>
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $report->vehicle->type ?? '-' }}</td>
                                <td class="d-none d-lg-table-cell">{{ $report->problem_category ?? '-' }}</td>
                                <td class="d-none d-md-table-cell">
                                    @if($report->unit_status_after_service == 'Aman' || str_contains(strtolower($report->unit_status_after_service), 'jalan'))
                                        <span class="text-success small fw-bold"><i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>{{ $report->unit_status_after_service }}</span>
                                    @elseif($report->unit_status_after_service)
                                        <span class="text-danger small fw-bold"><i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>{{ $report->unit_status_after_service }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($report->status === 'pending_customer')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Menunggu Konfirmasi</span>
                                    @elseif($report->status === 'approved_customer')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">Terkonfirmasi</span>
                                    @elseif($report->status === 'revision_requested')
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info">Minta Klarifikasi</span>
                                    @elseif($report->status === 'rejected_customer')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $report->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('customer.approve.show', $report->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i> Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data laporan service yang sesuai kriteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Info & Links --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-3">
                <div class="small text-muted">
                    Menampilkan {{ $reports->firstItem() ?? 0 }} - {{ $reports->lastItem() ?? 0 }} dari {{ $reports->total() }} data
                </div>
                <div>
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Information Alert --}}
    <div class="alert alert-primary bg-primary bg-opacity-10 border-0 shadow-sm d-flex align-items-start p-4">
        <i class="bi bi-info-circle-fill text-primary fs-3 me-3 mt-1"></i>
        <div>
            <h6 class="fw-bold text-primary mb-1">Informasi</h6>
            <p class="mb-0 small text-dark">
                Konfirmasi yang Anda berikan hanya untuk menyatakan bahwa laporan kendala dan penanganan unit telah diterima dan diketahui. 
                <b>Konfirmasi ini bukan kuitansi tagihan atau rincian invoice pembayaran service.</b>
            </p>
        </div>
    </div>

</div>
@endsection
