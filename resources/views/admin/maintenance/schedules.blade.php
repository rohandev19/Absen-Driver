@extends('admin.layouts.app')

@section('title', 'Jadwal Maintenance')

@section('content')
{{-- Include centralized design system for consistent UI/UX --}}
@include('admin.maintenance.partials._design-system')

<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Jadwal Maintenance</h3>
            <p class="text-muted mb-0">Kelola jadwal servis dan maintenance kendaraan</p>
        </div>
        <div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.maintenance.export.schedules', request()->all()) }}" class="btn-primary-corp">
                    <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel
                </a>
                <button class="btn-primary-corp" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Jadwal
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card-metric border-left-danger">
                <div class="metric-label">OVERDUE</div>
                <div class="metric-value">{{ $stats['overdue'] }}</div>
                <div class="metric-desc">Jadwal terlambat</div>
                <i class="bi bi-calendar-x-fill card-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-metric border-left-warning">
                <div class="metric-label">TODAY</div>
                <div class="metric-value">{{ $stats['today'] }}</div>
                <div class="metric-desc">Jadwal hari ini</div>
                <i class="bi bi-calendar-day-fill card-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-metric border-left-info">
                <div class="metric-label">THIS WEEK</div>
                <div class="metric-value">{{ $stats['this_week'] }}</div>
                <div class="metric-desc">7 hari ke depan</div>
                <i class="bi bi-calendar-week-fill card-icon"></i>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.maintenance.schedules') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">STATUS</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">PRIORITY</label>
                    <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Priority</option>
                        <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">KENDARAAN</label>
                    <select name="vehicle_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Kendaraan</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->plate_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    @if(request()->hasAny(['status', 'priority', 'vehicle_id']))
                        <a href="{{ route('admin.maintenance.schedules') }}" class="btn-action-corp">
                            <i class="bi bi-x-lg"></i> Reset Filter
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Schedules Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-corporate mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Kendaraan</th>
                            <th>Komponen</th>
                            <th>Tipe</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Biaya</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                        <tr class="{{ $schedule->isOverdue() ? 'table-danger' : '' }}">
                            <td class="ps-4">
                                <div class="fw-bold">{{ $schedule->scheduled_date->format('d M Y') }}</div>
                                <small class="text-muted">{{ $schedule->scheduled_date->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $schedule->vehicle->plate_number }}</div>
                                <small class="text-muted">{{ $schedule->vehicle->type }}</small>
                            </td>
                            <td>
                                @if($schedule->component)
                                    <span class="badge-corp badge-corp-info"><i class="bi bi-gear"></i> {{ $schedule->component->component_name }}</span>
                                @else
                                    <span class="text-muted">General</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-corp badge-corp-primary">{{ ucfirst($schedule->type) }}</span>
                            </td>
                            <td>
                                <span class="badge-corp badge-corp-{{ $schedule->priority == 'critical' ? 'danger' : ($schedule->priority == 'high' ? 'warning' : ($schedule->priority == 'medium' ? 'info' : 'success')) }}">
                                    <i class="bi bi-{{ $schedule->priority == 'critical' ? 'exclamation-octagon' : ($schedule->priority == 'high' ? 'exclamation-triangle' : 'info-circle') }}"></i>
                                    {{ ucfirst($schedule->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-corp badge-corp-{{ $schedule->status == 'completed' ? 'success' : ($schedule->status == 'in_progress' ? 'primary' : 'warning') }}">
                                    <i class="bi bi-{{ $schedule->status == 'completed' ? 'check-circle' : ($schedule->status == 'in_progress' ? 'hourglass-split' : 'clock') }}"></i>
                                    {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold">Rp {{ number_format($schedule->estimated_cost, 0, ',', '.') }}</div>
                                @if($schedule->actual_cost)
                                    <small class="text-success">Actual: Rp {{ number_format($schedule->actual_cost, 0, ',', '.') }}</small>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($schedule->status != 'completed' && $schedule->status != 'cancelled')
                                    <button class="btn-primary-corp" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#completeModal{{ $schedule->id }}">
                                        <i class="bi bi-check-circle"></i> Selesai
                                    </button>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Complete Modal --}}
                        <div class="modal fade" id="completeModal{{ $schedule->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.maintenance.schedules.complete', $schedule) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Selesaikan Maintenance</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-info">
                                                <strong>{{ $schedule->vehicle->plate_number }}</strong> - {{ $schedule->component->component_name ?? 'General' }}
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Biaya Aktual (Rp)</label>
                                                <input type="number" name="actual_cost" class="form-control" 
                                                    value="{{ $schedule->estimated_cost }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Catatan</label>
                                                <textarea name="notes" class="form-control" rows="3">{{ $schedule->notes }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn-action-corp" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn-primary-corp">Selesai</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-check fs-2 d-block mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada jadwal maintenance.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($schedules->hasPages())
            <div class="card-footer bg-white">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Add Schedule Modal --}}
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.maintenance.schedules.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jadwal Maintenance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kendaraan <span class="text-danger">*</span></label>
                            <select name="vehicle_id" class="form-select" required id="vehicleSelect">
                                <option value="">Pilih Kendaraan</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}">{{ $v->plate_number }} - {{ $v->type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Komponen (Opsional)</label>
                            <select name="component_id" class="form-select" id="componentSelect">
                                <option value="">Pilih kendaraan dulu</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Jadwal <span class="text-danger">*</span></label>
                            <input type="date" name="scheduled_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Target KM</label>
                            <input type="number" name="scheduled_km" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="preventive">Preventive</option>
                                <option value="corrective">Corrective</option>
                                <option value="predictive">Predictive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estimasi Biaya (Rp)</label>
                            <input type="number" name="estimated_cost" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bengkel</label>
                            <input type="text" name="workshop_name" class="form-control" placeholder="Nama bengkel">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action-corp" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-corp">Tambah Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('vehicleSelect').addEventListener('change', function() {
    const vehicleId = this.value;
    const componentSelect = document.getElementById('componentSelect');
    
    componentSelect.innerHTML = '<option value="">Loading...</option>';
    
    if (vehicleId) {
        fetch(`/admin/api/vehicles/${vehicleId}/components`)
            .then(response => response.json())
            .then(data => {
                componentSelect.innerHTML = '<option value="">General Maintenance</option>';
                data.forEach(comp => {
                    const option = document.createElement('option');
                    option.value = comp.id;
                    option.textContent = comp.component_name;
                    componentSelect.appendChild(option);
                });
            })
            .catch(() => {
                componentSelect.innerHTML = '<option value="">General Maintenance</option>';
            });
    } else {
        componentSelect.innerHTML = '<option value="">Pilih kendaraan dulu</option>';
    }
});
</script>
@endpush
