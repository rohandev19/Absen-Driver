@extends('admin.layouts.app')

@section('title', 'Dashboard Uang Jalan')

@push('styles')
<style>
/* ── Tokens ── */
:root {
    --red:   #e11d48;
    --blue:  #2563eb;
    --red-soft:  #fff1f2;
    --blue-soft: #eff6ff;
    --text-primary:   #0f172a;
    --text-secondary: #64748b;
    --border: #e2e8f0;
    --bg: #f8fafc;
}

:root[data-bs-theme="dark"] {
    --text-primary:   #f8fafc;
    --text-secondary: #94a3b8;
    --border: rgba(255, 255, 255, 0.08);
    --bg: #0f172a;
    --blue-soft: rgba(37, 99, 235, 0.15);
    --red-soft: rgba(225, 29, 72, 0.15);
}

[data-bs-theme="dark"] .stats-table tbody td {
    border-bottom-color: rgba(255, 255, 255, 0.05);
}

[data-bs-theme="dark"] .stats-table tbody tr:hover td {
    background: rgba(255, 255, 255, 0.02) !important;
}

/* ── Page wrapper ── */
.uj-page { background: var(--bg); min-height: 100vh; padding-bottom: 40px; }

/* ── Top bar ── */
.uj-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}
.uj-topbar .page-eyebrow {
    font-size: .75rem;
    font-weight: 600;
    letter-spacing: .8px;
    text-transform: uppercase;
    color: var(--text-secondary);
    margin-bottom: 4px;
}
.uj-topbar h2 {
    font-size: 1.625rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.2;
}
/* red dot accent in title */
.uj-topbar h2 .dot-red  { color: var(--red);  }
.uj-topbar h2 .dot-blue { color: var(--blue); }

.btn-view-all {
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 9px 20px;
    font-size: .875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: background .15s, transform .1s, box-shadow .15s;
    box-shadow: 0 2px 8px rgba(37,99,235,.25);
}
.btn-view-all:hover {
    background: #1d4ed8;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(37,99,235,.35);
}

/* ── Filter card ── */
.filter-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.filter-card .f-label {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .7px;
    text-transform: uppercase;
    color: var(--text-secondary);
    margin-bottom: 6px;
    display: block;
}
.filter-card .form-select,
.filter-card .form-control {
    border-radius: 9px;
    border: 1px solid var(--border);
    font-size: .875rem;
    color: var(--text-primary);
    transition: border-color .15s, box-shadow .15s;
}
.filter-card .form-select:focus,
.filter-card .form-control:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* ── Metric cards ── */
.metrics-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
@media (max-width: 767px) { .metrics-row { grid-template-columns: 1fr; } }

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    border: 1px solid #f8f9fa;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    border-color: #e9ecef;
}

.stat-card-danger:hover .stat-icon { box-shadow: 0 8px 24px rgba(220, 53, 69, 0.3); transform: scale(1.05); }
.stat-card-warning:hover .stat-icon { box-shadow: 0 8px 24px rgba(255, 193, 7, 0.3); transform: scale(1.05); }
.stat-card-primary:hover .stat-icon { box-shadow: 0 8px 24px rgba(13, 110, 253, 0.3); transform: scale(1.05); }

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    flex-shrink: 0;
}

.stat-card-danger .stat-icon { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
.stat-card-warning .stat-icon { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; }
.stat-card-primary .stat-icon { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; }

.stat-content { flex: 1; }
.stat-value { font-size: 2rem; font-weight: 700; line-height: 1; margin-bottom: 0.25rem; color: #2c3e50; }
.stat-label { font-size: 0.875rem; color: #6c757d; font-weight: 500; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }

/* ── Stats card ── */
.stats-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.stats-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border);
}
.stats-card-header .sh-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    background: var(--blue-soft);
    color: var(--blue);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
}
.stats-card-header h5 {
    font-size: .9375rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.stats-table thead th {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .7px;
    text-transform: uppercase;
    color: var(--text-secondary);
    background: var(--bg);
    border-top: none;
    border-bottom: 1px solid var(--border);
    padding: 11px 16px;
}
.stats-table tbody td {
    padding: 13px 16px;
    vertical-align: middle;
    font-size: .875rem;
    color: var(--text-primary);
    border-bottom: 1px solid #f1f5f9;
}
.stats-table tbody tr:last-child td { border-bottom: none; }
.stats-table tbody tr:hover td { background: #fafbff; }

.driver-name { font-weight: 600; color: var(--text-primary); }

/* pill badges */
.pill {
    display: inline-flex; align-items: center; gap: 4px;
    border-radius: 50px; padding: 3px 10px;
    font-size: .75rem; font-weight: 600;
}
.pill-green { background: #dcfce7; color: #15803d; }
.pill-yellow{ background: #fef9c3; color: #854d0e; }
.pill-red   { background: #fee2e2; color: #b91c1c; }

/* ── Empty state ── */
.empty-state {
    text-align: center; padding: 56px 24px;
}
.empty-state .es-icon {
    width: 64px; height: 64px;
    border-radius: 16px;
    background: var(--blue-soft);
    color: var(--blue);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem;
    margin: 0 auto 16px;
}
.empty-state p { color: var(--text-secondary); font-size: .9rem; margin: 0; }
</style>
@endpush

@section('content')
<div class="uj-page">
<div class="container-fluid">

    {{-- ── Top bar ── --}}
    <div class="uj-topbar">
        <div>
            <div class="page-eyebrow">Keuangan &rsaquo; Uang Jalan</div>
            <h2>
                <span class="dot-red">Dashboard</span>
                <span style="color:var(--text-secondary);font-weight:400"> / </span>
                <span class="dot-blue">Uang Jalan</span>
            </h2>
        </div>
        <a href="{{ route('admin.transport-costs.index') }}" class="btn-view-all">
            <i class="bi bi-list-ul"></i> Lihat Semua Laporan
        </a>
    </div>

    {{-- ── Filters ── --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.transport-costs.dashboard') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="f-label">Project</label>
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
                <label class="f-label">Bulan</label>
                <input type="month" name="month" class="form-control" value="{{ $month }}" onchange="this.form.submit()">
            </div>
        </form>
    </div>

    <!-- METRICS ROW -->
    <div class="metrics-row">
        <!-- 1. Total Biaya -->
        <div class="stat-card stat-card-danger animate-fade-in" style="animation-delay: 0.1s">
            <div class="stat-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size: 1.5rem;">Rp&nbsp;{{ number_format($metrics['total_costs'], 0, ',', '.') }}</div>
                <div class="stat-label">Total Biaya</div>
            </div>
        </div>

        <!-- 2. Lembur -->
        <div class="stat-card stat-card-primary animate-fade-in" style="animation-delay: 0.2s">
            <div class="stat-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size: 1.5rem;">Rp&nbsp;{{ number_format($metrics['total_overtime'], 0, ',', '.') }}</div>
                <div class="stat-label">Total Lembur</div>
            </div>
        </div>

        <!-- 3. Pending -->
        <div class="stat-card stat-card-warning animate-fade-in" style="animation-delay: 0.3s">
            <div class="stat-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $metrics['pending_count'] }} <span style="font-size: 1rem; color: #6c757d;">laporan</span></div>
                <div class="stat-label">Pending Approval</div>
            </div>
        </div>
    </div>

    {{-- ── Driver Statistics ── --}}
    <div class="stats-card">
        <div class="stats-card-header">
            <div class="sh-icon"><i class="bi bi-people-fill"></i></div>
            <h5>Statistik Per Driver</h5>
        </div>

        @if($driverStats->isEmpty())
            <div class="empty-state">
                <div class="es-icon"><i class="bi bi-inbox"></i></div>
                <p>Belum ada data untuk periode ini.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table stats-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Driver</th>
                            <th>Rata-rata Efisiensi BBM</th>
                            <th>Total Jam Lembur</th>
                            <th class="pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($driverStats as $stat)
                            <tr>
                                <td class="ps-4">
                                    <span class="driver-name">{{ $stat->driver->full_name }}</span>
                                </td>
                                <td>
                                    @if($stat->avg_efficiency)
                                        <span class="pill {{ $stat->avg_efficiency >= 12 ? 'pill-green' : 'pill-yellow' }}">
                                            <i class="bi bi-fuel-pump"></i>
                                            {{ number_format($stat->avg_efficiency, 2) }} KM/L
                                        </span>
                                    @else
                                        <span style="color:#cbd5e1;font-size:.8rem">—</span>
                                    @endif
                                </td>
                                <td>{{ number_format($stat->total_overtime, 2) }} jam</td>
                                <td class="pe-4">
                                    @if($stat->avg_efficiency && $stat->avg_efficiency < 10)
                                        <span class="pill pill-red">
                                            <i class="bi bi-exclamation-circle"></i> Perlu Perhatian
                                        </span>
                                    @else
                                        <span class="pill pill-green">
                                            <i class="bi bi-check-circle"></i> Baik
                                        </span>
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
