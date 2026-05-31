@extends('admin.layouts.app')

@section('title', 'Rekap Bulanan Uang Jalan')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-calendar-month"></i> Rekap Bulanan Uang Jalan</h2>
        <div class="d-flex gap-2">
            @if($recap)
                <a href="{{ route('admin.transport-costs.export_finance_recap', ['driver_id' => $driverId, 'month' => $month]) }}" class="btn btn-info text-white">
                    <i class="bi bi-file-earmark-word"></i> Ekspor Bulanan ke Finance (Word)
                </a>
            @endif
            <a href="{{ route('admin.transport-costs.dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.transport-costs.recap') }}" class="row g-3">
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
                    <label class="form-label">Driver <span class="text-danger">*</span></label>
                    <select name="driver_id" class="form-select" required>
                        <option value="">Pilih Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ $driverId == $driver->id ? 'selected' : '' }}>
                                {{ $driver->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bulan</label>
                    <input type="month" name="month" class="form-control" value="{{ $month }}" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Tampilkan Rekap
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($recap)
        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Total Trip</h6>
                        <h3>{{ $recap['total_trips'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Total KM</h6>
                        <h3>{{ number_format($recap['total_km_traveled'], 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6>Rata-rata Efisiensi</h6>
                        <h3>{{ $recap['average_fuel_efficiency'] ? number_format($recap['average_fuel_efficiency'], 2) . ' KM/L' : 'N/A' }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Grand Total</h6>
                        <h3>Rp {{ number_format($recap['grand_total'], 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cost Breakdown --}}
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Rincian Biaya</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th width="50%">Biaya Bensin</th>
                            <td class="text-end">Rp {{ number_format($recap['total_gasoline_cost'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Biaya Tol</th>
                            <td class="text-end">Rp {{ number_format($recap['total_toll_cost'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Biaya Parkir</th>
                            <td class="text-end">Rp {{ number_format($recap['total_parking_cost'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Total Lembur</th>
                            <td class="text-end">Rp {{ number_format($recap['total_overtime_payment'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Total Bonus</th>
                            <td class="text-end">Rp {{ number_format($recap['total_bonus_earned'], 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-success">
                            <th><strong>GRAND TOTAL</strong></th>
                            <th class="text-end"><strong>Rp {{ number_format($recap['grand_total'], 0, ',', '.') }}</strong></th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Trip List --}}
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Trip</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>DO Number</th>
                                <th>KM</th>
                                <th>Biaya</th>
                                <th>Lembur</th>
                                <th>Bonus</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recap['trips'] as $trip)
                                <tr>
                                    <td>{{ $trip->trip_date->format('d-m-Y') }}</td>
                                    <td>{{ Str::limit($trip->do_number, 20) }}</td>
                                    <td>{{ number_format($trip->odometer_difference, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($trip->total_cost, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($trip->overtime_payment, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($trip->bonus_driver, 0, ',', '.') }}</td>
                                    <td><strong>Rp {{ number_format($trip->total_cost + $trip->overtime_payment + $trip->bonus_driver, 0, ',', '.') }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
