@extends('admin.layouts.app')

@section('title', 'Approve Customer')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="fw-bold mb-0 fs-4"><i class="bi bi-check-circle"></i> Approve Customer</h2>
        <a href="{{ route('admin.service.index') }}" class="btn btn-secondary btn-sm" style="min-height:38px;">
            <i class="bi bi-arrow-left"></i> Kembali ke Service
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($reports->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada laporan yang disetujui customer.
                </div>
            @else
                <div class="table-responsive table-responsive-cards">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal Approve</th>
                                <th>Driver</th>
                                <th>Plat Nomor</th>
                                <th>Customer</th>
                                <th>Approved By</th>
                                <th>Dokumen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr class="aset-row">
                                    <td data-label="Tanggal Approve">
                                        {{ $report->approved_at_customer ? $report->approved_at_customer->format('d-m-Y H:i') : '-' }}
                                    </td>
                                    <td data-label="Driver">{{ $report->driver->full_name ?? 'N/A' }}</td>
                                    <td data-label="Plat Nomor">
                                        <span class="badge bg-secondary">{{ $report->vehicle->plate_number ?? 'N/A' }}</span>
                                    </td>
                                    <td data-label="Customer">{{ $report->customer->name ?? '-' }}</td>
                                    <td data-label="Approved By">
                                        {{ $report->approvedByCustomer->name ?? '-' }}
                                    </td>
                                    <td data-label="Dokumen">
                                        @if($report->customer_signed_document_path)
                                            <a href="{{ asset('storage/' . $report->customer_signed_document_path) }}" 
                                               class="btn btn-sm btn-success" 
                                               download>
                                                <i class="bi bi-download"></i> Download TTD
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
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
