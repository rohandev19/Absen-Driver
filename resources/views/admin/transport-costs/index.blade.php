@extends('admin.layouts.app')

@section('title', 'Uang Jalan - Daftar Laporan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-cash-coin"></i> Uang Jalan - Daftar Laporan</h2>
        <a href="{{ route('admin.transport-costs.dashboard') }}" class="btn btn-primary">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.transport-costs.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status Persetujuan</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status Finance</label>
                    <select name="finance_status" class="form-select">
                        <option value="">Semua Status Finance</option>
                        <option value="not_submitted" {{ request('finance_status') == 'not_submitted' ? 'selected' : '' }}>Belum Diajukan</option>
                        <option value="submitted" {{ request('finance_status') == 'submitted' ? 'selected' : '' }}>Telah Diajukan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">Semua Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Driver</label>
                    <select name="driver_id" class="form-select">
                        <option value="">Semua Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    @if(request()->hasAny(['status', 'finance_status', 'project_id', 'driver_id', 'date_from', 'date_to']))
                        <a href="{{ route('admin.transport-costs.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($trips->isEmpty())
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> Belum ada laporan uang jalan.
                </div>
            @else
                {{-- Bulk Action Banner --}}
                <div id="bulkBanner" class="alert alert-primary d-none align-items-center justify-content-between py-2 px-3 mb-3 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-all fs-5"></i>
                        <span>Terpilih <strong id="selectedCount">0</strong> laporan untuk diajukan ke finance.</span>
                    </div>
                    <button type="button" id="btnBulkSubmit" class="btn btn-light btn-sm fw-bold text-primary">
                        <i class="bi bi-send"></i> Ajukan Terpilih ke Finance
                    </button>
                </div>

                {{-- Hidden Form for Bulk Action --}}
                <form id="bulkForm" method="POST" action="{{ route('admin.transport-costs.bulk_submit_to_finance') }}" class="d-none">
                    @csrf
                </form>

                <div class="table-responsive table-responsive-cards">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Tanggal</th>
                                <th>Driver</th>
                                <th>Plat Nomor</th>
                                <th>DO Number</th>
                                <th>Total Biaya</th>
                                <th>Lembur</th>
                                <th>Status</th>
                                <th>Finance</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $trip)
                                <tr class="aset-row">
                                    <td class="text-center">
                                        @if($trip->approval_status == 'approved' && !$trip->submitted_to_finance)
                                            <input type="checkbox" value="{{ $trip->id }}" class="trip-checkbox form-check-input">
                                        @else
                                            <input type="checkbox" disabled class="form-check-input opacity-25">
                                        @endif
                                    </td>
                                    <td data-label="Tanggal">{{ $trip->trip_date->format('d-m-Y') }}</td>
                                    <td data-label="Driver">{{ $trip->driver->full_name }}</td>
                                    <td data-label="Plat Nomor">
                                        <span class="badge bg-secondary">{{ $trip->vehicle->plate_number }}</span>
                                    </td>
                                    <td data-label="DO Number">{{ Str::limit($trip->do_number, 20) }}</td>
                                    <td data-label="Total Biaya">Rp {{ number_format($trip->total_cost, 0, ',', '.') }}</td>
                                    <td data-label="Lembur">Rp {{ number_format($trip->overtime_payment, 0, ',', '.') }}</td>
                                    <td data-label="Status">
                                        @php
                                            $statusBadge = match($trip->approval_status) {
                                                'pending' => 'bg-warning text-dark',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            $statusText = match($trip->approval_status) {
                                                'pending' => 'Pending',
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak',
                                                default => $trip->approval_status
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                                    </td>
                                    <td data-label="Finance">
                                        @if($trip->approval_status == 'approved')
                                            @if($trip->submitted_to_finance)
                                                <span class="badge bg-info text-white"><i class="bi bi-send-check"></i> Diajukan</span>
                                            @else
                                                <span class="badge bg-secondary text-white"><i class="bi bi-clock"></i> Belum</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.transport-costs.show', $trip->id) }}" 
                                               class="btn btn-xs btn-primary py-1 px-2">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                            @if($trip->approval_status == 'approved')
                                                <a href="{{ route('admin.transport-costs.export_finance', $trip->id) }}" 
                                                   class="btn btn-xs btn-info py-1 px-2 text-white" title="Download Form Finance">
                                                    <i class="bi bi-download"></i>
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
                    {{ $trips->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.trip-checkbox');
    const bulkBanner = document.getElementById('bulkBanner');
    const selectedCount = document.getElementById('selectedCount');
    const btnBulkSubmit = document.getElementById('btnBulkSubmit');
    const bulkForm = document.getElementById('bulkForm');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.trip-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count > 0) {
            bulkBanner.classList.remove('d-none');
            bulkBanner.classList.add('d-flex');
            selectedCount.textContent = count;
        } else {
            bulkBanner.classList.add('d-none');
            bulkBanner.classList.remove('d-flex');
            selectedCount.textContent = '0';
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = this.checked;
                }
            });
            updateBulkActions();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked && selectAll) {
                selectAll.checked = false;
            }
            updateBulkActions();
        });
    });

    if (btnBulkSubmit) {
        btnBulkSubmit.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.trip-checkbox:checked');
            if (checkedBoxes.length === 0) return;

            if (confirm(`Apakah Anda yakin ingin mengajukan ${checkedBoxes.length} laporan uang jalan terpilih ke finance?`)) {
                // Clear existing inputs
                bulkForm.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

                // Add checked IDs to form
                checkedBoxes.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = cb.value;
                    bulkForm.appendChild(input);
                });

                bulkForm.submit();
            }
        });
    }
});
</script>
@endpush
