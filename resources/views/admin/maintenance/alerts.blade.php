@extends('admin.layouts.app')

@section('title', 'Maintenance Alerts')

@section('content')
{{-- Include centralized design system for consistent UI/UX --}}
@include('admin.maintenance.partials._design-system')

<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Maintenance Alerts</h3>
            <p class="text-muted mb-0">Notifikasi otomatis untuk komponen yang perlu perhatian</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <form action="{{ route('admin.maintenance.alerts.generate') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-repeat me-1"></i> Perbarui Alert
                </button>
            </form>
            <a href="{{ route('admin.maintenance.export.alerts', request()->all()) }}" class="btn btn-sm btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel
            </a>
            <span class="badge bg-light text-dark border px-3 py-2">
                <i class="bi bi-calendar3 me-2"></i>{{ now()->format('d F Y') }}
            </span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card-metric border-left-danger">
                <div class="metric-label">🔴 OVERDUE</div>
                <div class="metric-value">{{ $summary['by_type']['overdue'] }}</div>
                <div class="metric-desc">Sudah lewat batas</div>
                <i class="bi bi-exclamation-octagon-fill card-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-metric border-left-warning">
                <div class="metric-label">🟠 CRITICAL</div>
                <div class="metric-value">{{ $summary['by_type']['critical'] }}</div>
                <div class="metric-desc">Perlu segera</div>
                <i class="bi bi-exclamation-triangle-fill card-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-metric border-left-info">
                <div class="metric-label">🟡 WARNING</div>
                <div class="metric-value">{{ $summary['by_type']['warning'] }}</div>
                <div class="metric-desc">Perlu perhatian</div>
                <i class="bi bi-info-circle-fill card-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-metric border-left-primary">
                <div class="metric-label">TOTAL ALERTS</div>
                <div class="metric-value">{{ $summary['total'] }}</div>
                <div class="metric-desc">Alert aktif</div>
                <i class="bi bi-bell-fill card-icon"></i>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.maintenance.alerts') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">STATUS</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="acknowledged" {{ request('status') == 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="dismissed" {{ request('status') == 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">TIPE ALERT</label>
                    <select name="alert_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Tipe</option>
                        <option value="overdue" {{ request('alert_type') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="critical" {{ request('alert_type') == 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="warning" {{ request('alert_type') == 'warning' ? 'selected' : '' }}>Warning</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    @if(request()->hasAny(['status', 'alert_type']))
                        <a href="{{ route('admin.maintenance.alerts') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset Filter
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Alerts List --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($alerts->count() > 0)
                <div class="table-responsive">
                    <table class="table-corporate">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Component</th>
                                <th>Alert Type</th>
                                <th>Message</th>
                                <th>Triggered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alerts as $alert)
                                <tr>
                                    <td data-label="Vehicle">
                                        <span class="badge-corp badge-corp-primary">
                                            <i class="bi bi-truck"></i> {{ $alert->vehicle->plate_number }}
                                        </span>
                                    </td>
                                    <td data-label="Component">
                                        @if($alert->component)
                                            <span class="badge-corp badge-corp-info">
                                                <i class="bi bi-gear"></i> {{ $alert->component->component_name }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td data-label="Alert Type">
                                        <span class="badge-corp badge-corp-{{ $alert->alert_type == 'overdue' ? 'danger' : ($alert->alert_type == 'critical' ? 'warning' : 'info') }}">
                                            <i class="bi bi-{{ $alert->alert_type == 'overdue' ? 'exclamation-octagon' : ($alert->alert_type == 'critical' ? 'exclamation-triangle' : 'info-circle') }}"></i>
                                            {{ strtoupper($alert->alert_type) }}
                                        </span>
                                    </td>
                                    <td data-label="Message">
                                        {{ $alert->message }}
                                        @if($alert->acknowledged_at)
                                            <br><small class="text-muted">
                                                <i class="bi bi-check-circle"></i> Acknowledged {{ $alert->acknowledged_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>
                                    <td data-label="Triggered">
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> {{ $alert->triggered_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td data-label="Actions">
                                        @if($alert->status == 'active')
                                            <div class="d-flex gap-2">
                                                <form action="{{ route('admin.maintenance.alerts.acknowledge', $alert) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn-action-corp" title="Tandai sudah dibaca">
                                                        <i class="bi bi-check"></i> Tandai Dibaca
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.maintenance.alerts.resolve', $alert) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn-primary-corp" title="Tandai sudah selesai">
                                                        <i class="bi bi-check-circle"></i> Selesaikan
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="badge-corp badge-corp-success">
                                                <i class="bi bi-check-circle"></i> {{ ucfirst($alert->status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-check-circle"></i>
                    <h5>Tidak ada alert</h5>
                    <p>Semua kendaraan dalam kondisi baik! 🎉</p>
                </div>
            @endif
        </div>
        
        @if($alerts->hasPages())
            <div class="card-footer bg-white">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
