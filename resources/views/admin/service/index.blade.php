@extends('admin.layouts.app')

@section('title', 'Service Darurat')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="fw-bold mb-0 fs-4"><i class="bi bi-tools"></i> Service Darurat</h2>
    </div>

    {{-- Filter Status --}}
    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.service.index') }}" class="row g-2">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold mb-1">Filter Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved_admin" {{ request('status') == 'approved_admin' ? 'selected' : '' }}>Disetujui Admin</option>
                        <option value="pending_customer" {{ request('status') == 'pending_customer' ? 'selected' : '' }}>Menunggu Customer</option>
                        <option value="approved_customer" {{ request('status') == 'approved_customer' ? 'selected' : '' }}>Disetujui Customer</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-12 col-md-8 d-flex align-items-end">
                    @if(request('status'))
                        <a href="{{ route('admin.service.index') }}" class="btn btn-secondary btn-sm" style="min-height:38px;">Reset Filter</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table / Cards --}}
    <div class="card">
        <div class="card-body">
            @if($reports->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada laporan service.
                </div>
            @else
                <div class="table-responsive table-responsive-cards">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Driver</th>
                                <th>Plat Nomor</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr class="aset-row">
                                    <td data-label="Tanggal">{{ $report->timestamp->format('d-m-Y H:i') }}</td>
                                    <td data-label="Driver">{{ $report->driver->full_name ?? 'N/A' }}</td>
                                    <td data-label="Plat Nomor">
                                        <span class="badge bg-secondary">{{ $report->vehicle->plate_number ?? 'N/A' }}</span>
                                    </td>
                                    <td data-label="Customer">{{ $report->customer->name ?? '-' }}</td>
                                    <td data-label="Status">
                                        @php
                                            $statusBadge = match($report->status) {
                                                'pending' => 'bg-warning text-dark',
                                                'approved_admin' => 'bg-info',
                                                'pending_customer' => 'bg-primary',
                                                'approved_customer' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            $statusText = match($report->status) {
                                                'pending' => 'Pending',
                                                'approved_admin' => 'Disetujui Admin',
                                                'pending_customer' => 'Menunggu Customer',
                                                'approved_customer' => 'Disetujui Customer',
                                                'rejected' => 'Ditolak',
                                                default => $report->status
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.service.show', $report->id) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                            @if($report->customer_word_path)
                                                <a href="{{ asset('storage/' . $report->customer_word_path) }}" 
                                                   class="btn btn-sm btn-success" download>
                                                    <i class="bi bi-file-earmark-word"></i> Word
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
