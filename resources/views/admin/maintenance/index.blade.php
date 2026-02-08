@extends('admin.layouts.app')

@section('title', 'Monitoring & Maintenance')

@section('content')
    <style>
        /* === VARIABLES & RESET === */
        :root {
            --font-size-base: 14px;
            --border-radius-comfort: 8px;
            --transition-smooth: 0.2s ease-in-out;
        }

        body {
            font-size: var(--font-size-base);
        }

        /* === 1. CORPORATE STAT CARDS (Clean & Professional) === */
        .card-metric {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius-comfort);
            padding: 20px 24px;
            transition: all var(--transition-smooth);
            position: relative;
            overflow: hidden;
            height: 100%;
            border-left: 5px solid transparent;
        }

        .card-metric:hover {
            border-color: #b0b0b0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .card-metric.active {
            background-color: #f8fbff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-4px);
        }

        .border-left-danger {
            border-left-color: #dc3545;
        }

        .border-left-warning {
            border-left-color: #ffc107;
        }

        .border-left-success {
            border-left-color: #198754;
        }

        .border-left-primary {
            border-left-color: #0d6efd;
        }

        .metric-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #212529;
            line-height: 1.2;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .metric-label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6c757d;
        }

        .metric-desc {
            font-size: 0.85rem;
            color: #888;
            margin-top: 4px;
        }

        .stat-link {
            text-decoration: none;
            display: block;
            height: 100%;
            color: inherit;
        }

        .card-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2.5rem;
            opacity: 0.15;
        }

        /* === 2. PROFESSIONAL TABLE === */
        .table-corporate {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .table-corporate thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
            border-top: 1px solid #dee2e6;
            padding: 14px 20px;
        }

        .table-corporate tbody td {
            padding: 16px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
            color: #333;
        }

        .table-corporate tbody tr:hover {
            background-color: #fdfdfd;
        }

        .table-corporate tbody tr:last-child td {
            border-bottom: none;
        }

        /* === 3. COMPONENTS === */
        .badge-corp {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid transparent;
        }

        .badge-corp-danger {
            background-color: #fff5f5;
            color: #c62828;
            border-color: #ffcdd2;
        }

        .badge-corp-warning {
            background-color: #fffbf0;
            color: #f57f17;
            border-color: #ffe58f;
        }

        .badge-corp-success {
            background-color: #f6ffed;
            color: #389e0d;
            border-color: #b7eb8f;
        }

        .progress-corp-bg {
            background-color: #eee;
            height: 8px;
            width: 100px;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-corp-fill {
            height: 100%;
        }

        /* Buttons */
        .btn-action-corp {
            background: white;
            border: 1px solid #d9d9d9;
            color: #333;
            padding: 6px 16px;
            font-size: 0.85rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-action-corp:hover {
            border-color: #40a9ff;
            color: #096dd9;
            background-color: #e6f7ff;
        }

        .btn-primary-corp {
            background: #1890ff;
            border: 1px solid #1890ff;
            color: white;
            padding: 6px 16px;
            font-size: 0.85rem;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-primary-corp:hover {
            background: #40a9ff;
            border-color: #40a9ff;
            color: white;
        }

        .btn-danger-corp {
            background: #ff4d4f;
            border: 1px solid #ff4d4f;
            color: white;
            padding: 6px 16px;
            font-size: 0.85rem;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-danger-corp:hover {
            background: #ff7875;
            border-color: #ff7875;
            color: white;
        }

        .filter-container {
            background: #fbfbfb;
            border: 1px solid #e5e5e5;
            padding: 16px 24px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        /* === 4. MOBILE RESPONSIVE (TRANSFORM TABLE TO CARDS) === */
        @media (max-width: 768px) {

            /* Sembunyikan Header Tabel */
            .table-corporate thead {
                display: none;
            }

            /* Ubah Tabel jadi Blok */
            .table-corporate,
            .table-corporate tbody,
            .table-corporate tr,
            .table-corporate td {
                display: block;
                width: 100%;
            }

            /* Style Kartu (Baris) */
            .table-corporate tbody tr {
                margin-bottom: 20px;
                border: 1px solid #e0e0e0;
                border-radius: 12px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
                background: #fff;
                overflow: hidden;
            }

            /* Style Cell */
            .table-corporate td {
                padding: 12px 16px;
                text-align: left;
                border-bottom: 1px solid #f0f0f0;
                position: relative;
            }

            /* Header Kartu (Identitas) */
            .table-corporate td:first-child {
                background-color: #f8f9fa;
                border-bottom: 2px solid #e9ecef;
                padding: 15px;
            }

            /* Label Data */
            .table-corporate td:nth-of-type(2)::before {
                content: "STATUS KESEHATAN";
                display: block;
                font-size: 0.7rem;
                font-weight: bold;
                color: #adb5bd;
                margin-bottom: 5px;
            }

            .table-corporate td:nth-of-type(3)::before {
                content: "MONITORING KM";
                display: block;
                font-size: 0.7rem;
                font-weight: bold;
                color: #adb5bd;
                margin-bottom: 5px;
            }

            .table-corporate td:nth-of-type(4)::before {
                content: "UPDATE TERAKHIR";
                display: block;
                font-size: 0.7rem;
                font-weight: bold;
                color: #adb5bd;
                margin-bottom: 5px;
            }

            /* Tombol Aksi */
            .table-corporate td:last-child {
                border-bottom: none;
                background-color: #fff;
                padding: 15px;
            }

            /* Layout Tombol Mobile */
            .table-corporate td:last-child .d-flex {
                justify-content: space-between !important;
                width: 100%;
                gap: 10px;
            }

            .btn-action-corp,
            .btn-primary-corp,
            .btn-danger-corp {
                flex: 1;
                justify-content: center;
                padding: 10px;
            }

            .progress-corp-bg {
                width: 100%;
            }

            /* Filter Mobile */
            .filter-container {
                padding: 15px;
            }

            .filter-container select,
            .filter-container input,
            .filter-container button {
                width: 100%;
            }

            .filter-container .d-flex {
                flex-direction: column;
                width: 100%;
            }

            .filter-container .input-group {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>

    <div class="container-fluid p-0">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Maintenance Monitor</h3>
                <p class="text-muted mb-0 small">Dashboard operasional kendaraan & jadwal servis.</p>
            </div>
            <div class="text-end">
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="bi bi-calendar3 me-2"></i>{{ now()->format('d F Y') }}
                </span>
            </div>
        </div>

        {{-- BAGIAN 1: METRIC CARDS --}}
        <div class="row mb-4 g-3">
            {{-- DANGER --}}
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.maintenance.dashboard', ['status_filter' => 'danger']) }}" class="stat-link">
                    <div class="card-metric border-left-danger {{ request('status_filter') == 'danger' ? 'active' : '' }}">
                        <div class="metric-label text-danger">Perlu Perhatian</div>
                        <div class="metric-value">{{ $stats['danger'] }}</div>
                        <div class="metric-desc">Unit Rusak / Telat Servis</div>
                        <div class="card-icon">
                            <i class="bi bi-exclamation-octagon-fill text-danger"></i>
                        </div>
                    </div>
                </a>
            </div>

            {{-- WARNING --}}
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.maintenance.dashboard', ['status_filter' => 'warning']) }}" class="stat-link">
                    <div
                        class="card-metric border-left-warning {{ request('status_filter') == 'warning' ? 'active' : '' }}">
                        <div class="metric-label text-warning">Segera Servis</div>
                        <div class="metric-value">{{ $stats['warning'] }}</div>
                        <div class="metric-desc">Sisa KM &lt; 1.000</div>
                        <div class="card-icon">
                            <i class="bi bi-cone-striped text-warning"></i>
                        </div>
                    </div>
                </a>
            </div>

            {{-- SUCCESS --}}
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.maintenance.dashboard', ['status_filter' => 'safe']) }}" class="stat-link">
                    <div class="card-metric border-left-success {{ request('status_filter') == 'safe' ? 'active' : '' }}">
                        <div class="metric-label text-success">Kondisi Prima</div>
                        <div class="metric-value">{{ $stats['sehat'] }}</div>
                        <div class="metric-desc">Siap Operasi</div>
                        <div class="card-icon">
                            <i class="bi bi-check-circle-fill text-success"></i>
                        </div>
                    </div>
                </a>
            </div>

            {{-- TOTAL --}}
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.maintenance.dashboard', request()->except(['status_filter'])) }}"
                    class="stat-link">
                    <div class="card-metric border-left-primary">
                        <div class="metric-label text-primary">Total Armada</div>
                        <div class="metric-value">{{ $stats['total'] }}</div>
                        <div class="metric-desc">Unit Terdaftar</div>
                        <div class="card-icon">
                            <i class="bi bi-truck text-primary"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- BAGIAN 2: FILTER TOOLBAR --}}
        <div class="filter-container">
            <form action="{{ route('admin.maintenance.dashboard') }}" method="GET"
                class="d-flex flex-wrap gap-3 align-items-end">
                @if(request('status_filter'))
                    <input type="hidden" name="status_filter" value="{{ request('status_filter') }}">
                @endif

                <div class="flex-grow-1">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">PROJECT</label>
                            <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Semua Project</option>
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                        {{ $proj->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">TIPE MOBIL</label>
                            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Semua Tipe</option>
                                @foreach($types as $t)
                                    <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>
                                        {{ $t }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">PENCARIAN</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Cari Plat Nomor..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit"><i
                                        class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                @if(request()->hasAny(['search', 'project_id', 'type', 'status_filter']))
                    <div class="mb-1">
                        <a href="{{ route('admin.maintenance.dashboard') }}"
                            class="btn btn-sm btn-link text-danger text-decoration-none fw-bold">
                            <i class="bi bi-x-lg me-1"></i>Reset Filter
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- BAGIAN 3: TABLE DATA --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-corporate">
                        <thead>
                            <tr>
                                <th class="ps-4">Unit Kendaraan</th>
                                <th>Status Kesehatan</th>
                                <th>Monitoring KM</th>
                                <th>Update Terakhir</th>
                                <th class="text-end pe-4">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maintenanceData as $vehicle)
                                <tr>
                                    {{-- IDENTITAS --}}
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light border rounded d-flex align-items-center justify-content-center me-3"
                                                style="width: 40px; height: 40px; color: #555;">
                                                <i class="bi bi-car-front-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark font-monospace" style="font-size: 0.95rem;">
                                                    {{ $vehicle->plate_number }}
                                                </div>
                                                <div class="small text-muted" style="font-size: 0.75rem;">
                                                    {{ $vehicle->type }} <span class="mx-1">•</span>
                                                    {{ $vehicle->project->name ?? 'Pool' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- STATUS --}}
                                    <td>
                                        @if($vehicle->health_status_code === 'service_due')
                                            <span class="badge-corp badge-corp-danger"><i
                                                    class="bi bi-exclamation-diamond-fill"></i> Telat Servis</span>
                                        @elseif($vehicle->health_status_code === 'physical_issue')
                                            <span class="badge-corp badge-corp-danger"><i class="bi bi-wrench"></i> Isu Fisik</span>
                                        @elseif($vehicle->health_status_code === 'warning')
                                            <span class="badge-corp badge-corp-warning"><i class="bi bi-clock-history"></i> Segera
                                                Servis</span>
                                        @else
                                            <span class="badge-corp badge-corp-success"><i class="bi bi-check-circle-fill"></i>
                                                Prima</span>
                                        @endif
                                    </td>

                                    {{-- PROGRESS KM --}}
                                    <td>
                                        @php
                                            $percent = 100;
                                            if ($vehicle->service_interval_km > 0) {
                                                $jarakTempuh = $vehicle->current_km - $vehicle->last_service_km;
                                                $percent = 100 - (($jarakTempuh / $vehicle->service_interval_km) * 100);
                                            }
                                            if ($percent < 0)
                                                $percent = 0;
                                            $barColor = $percent < 20 ? '#ff4d4f' : '#52c41a';
                                        @endphp

                                        <div class="d-flex align-items-center gap-3">
                                            <div class="progress-corp-bg">
                                                <div class="progress-corp-fill"
                                                    style="width: {{ $percent }}%; background-color: {{ $barColor }};"></div>
                                            </div>
                                            <div style="min-width: 80px;">
                                                <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                                    {{ $vehicle->sisa_km !== null ? number_format($vehicle->sisa_km) : '0' }}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.7rem;">Km lagi</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- WAKTU --}}
                                    <td class="text-muted" style="font-size: 0.85rem;">
                                        {{ $vehicle->latestAttendance ? $vehicle->latestAttendance->updated_at->format('d/m/y H:i') : '-' }}
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.aset.visual', $vehicle->id) }}" class="btn-action-corp">
                                                <i class="bi bi-eye"></i> Fisik
                                            </a>
                                            <a href="{{ route('admin.aset.riwayat', $vehicle->id) }}" class="btn-action-corp">
                                                <i class="bi bi-journal-text"></i> Riwayat
                                            </a>

                                            @if ($vehicle->health_status_code === 'physical_issue')
                                                <form action="{{ route('admin.aset.resolveIssue', $vehicle->id) }}" method="POST"
                                                    class="form-confirm-repair">
                                                    @csrf
                                                    <button type="submit" class="btn-danger-corp">
                                                        <i class="bi bi-check-lg"></i> Selesai
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn-primary-corp" data-bs-toggle="modal"
                                                    data-bs-target="#catatServisModal"
                                                    data-plat-nomor="{{ $vehicle->plate_number }}"
                                                    data-km-saat-ini="{{ $vehicle->current_km }}"
                                                    data-action-url="{{ route('admin.aset.catatServis', $vehicle->id) }}">
                                                    <i class="bi bi-wrench"></i> Servis
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Tidak ada data yang sesuai filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.components.modal_catat_servis')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const repairForms = document.querySelectorAll('.form-confirm-repair');
            repairForms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Selesai?',
                        text: "Status mobil akan kembali normal.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1890ff',
                        confirmButtonText: 'Ya, Selesai',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) this.submit();
                    });
                });
            });
        });
    </script>
@endpush