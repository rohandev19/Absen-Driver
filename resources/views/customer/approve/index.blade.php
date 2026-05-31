@extends('customer.layouts.app')

@section('title', 'Approve Service')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="fw-bold mb-0 fs-5"><i class="bi bi-check-circle"></i> Approve Service</h2>
    </div>

    <div class="card">
        <div class="card-body">
            @if($reports->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Tidak ada laporan service yang perlu disetujui saat ini.
                </div>
            @else
                <div class="table-responsive table-responsive-cards">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal Service</th>
                                <th>Plat Nomor</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr class="aset-row">
                                    <td data-label="Tanggal">{{ $report->timestamp->format('d-m-Y H:i') }}</td>
                                    <td data-label="Plat Nomor">
                                        <span class="badge bg-secondary">{{ $report->vehicle->plate_number ?? 'N/A' }}</span>
                                    </td>
                                    <td data-label="Deskripsi">
                                        {{ Str::limit($report->description, 60) }}
                                    </td>
                                    <td data-label="Status">
                                        @if($report->status === 'pending_customer')
                                            <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                                        @elseif($report->status === 'approved_customer')
                                            <span class="badge bg-success">Disetujui</span>
                                        @endif
                                    </td>
                                    <td data-label="Aksi">
                                        <a href="{{ route('customer.approve.show', $report->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
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
