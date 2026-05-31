@extends('admin.layouts.app')

@section('title', 'Dashboard Uang Jalan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-speedometer2"></i> Dashboard Uang Jalan</h2>
        <a href="{{ route('admin.transport-costs.index') }}" class="btn btn-primary">
            <i class="bi bi-list-ul"></i> Lihat Semua Laporan
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.transport-costs.dashboard') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bulan</label>
                    <input type="month" name="month" class="form-control" value="{{ $month }}" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-wallet2"></i> Total Biaya</h6>
                    <h3 class="mb-0">Rp {{ number_format($metrics['total_costs'], 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-clock-history"></i> Total Lembur</h6>
                    <h3 class="mb-0">Rp {{ number_format($metrics['total_overtime'], 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-gift"></i> Total Bonus</h6>
                    <h3 class="mb-0">Rp {{ number_format($metrics['total_bonus'], 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-hourglass-split"></i> Pending</h6>
                    <h3 class="mb-0">{{ $metrics['pending_count'] }} laporan</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Driver Statistics --}}
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-people"></i> Statistik Per Driver</h5>
        </div>
        <div class="card-body">
            @if($driverStats->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada data untuk periode ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Driver</th>
                                <th>Rata-rata Efisiensi BBM</th>
                                <th>Total Jam Lembur</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($driverStats as $stat)
                                <tr>
                                    <td>{{ $stat->driver->full_name }}</td>
                                    <td>
                                        @if($stat->avg_efficiency)
                                            <span class="badge {{ $stat->avg_efficiency >= 12 ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ number_format($stat->avg_efficiency, 2) }} KM/L
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($stat->total_overtime, 2) }} jam</td>
                                    <td>
                                        @if($stat->avg_efficiency && $stat->avg_efficiency < 10)
                                            <span class="badge bg-danger">Perlu Perhatian</span>
                                        @else
                                            <span class="badge bg-success">Baik</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
