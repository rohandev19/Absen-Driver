@extends('admin.layouts.app')

@section('title', 'Monitoring & Maintenance')

{{-- INJEKSI SERVICE KE DALAM BLADE AGAR SCORE SINKRON --}}
@inject('healthService', 'App\Services\VehicleHealthService')

@section('content')
    {{-- Include centralized design system for consistent UI/UX --}}
    @include('admin.maintenance.partials._design-system')

    <div class="container-fluid p-0">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Maintenance Monitor</h3>
                <p class="text-muted mb-0 small">Dashboard operasional kendaraan & jadwal servis.</p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.maintenance.export.dashboard', request()->all()) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel
                </a>
                <a href="{{ route('admin.maintenance.schedules') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-calendar-check me-1"></i> Jadwal Servis
                </a>
                <a href="{{ route('admin.maintenance.alerts') }}" class="btn btn-sm btn-danger position-relative">
                    <i class="bi bi-bell-fill me-1"></i> Cek Peringatan
                    @if(isset($unreadAlerts) && $unreadAlerts > 0)
                        <span
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                            {{ $unreadAlerts }}
                        </span>
                    @endif
                </a>
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="bi bi-calendar3 me-2"></i>{{ now()->format('d F Y') }}
                </span>
            </div>
        </div>

        {{-- BAGIAN 1: METRIC CARDS --}}
        <div class="row mb-4 g-3">
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.maintenance.dashboard', ['status_filter' => 'danger']) }}" class="stat-link">
                    <div class="card-metric border-left-danger {{ request('status_filter') == 'danger' ? 'active' : '' }}">
                        <div class="metric-label text-danger">Perlu Perhatian</div>
                        <div class="metric-value">{{ $stats['danger'] }}</div>
                        <div class="metric-desc">Unit Rusak / Telat Servis</div>
                        <div class="card-icon"><i class="bi bi-exclamation-octagon-fill text-danger"></i></div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.maintenance.dashboard', ['status_filter' => 'warning']) }}" class="stat-link">
                    <div
                        class="card-metric border-left-warning {{ request('status_filter') == 'warning' ? 'active' : '' }}">
                        <div class="metric-label text-warning">Segera Servis</div>
                        <div class="metric-value">{{ $stats['warning'] }}</div>
                        <div class="metric-desc">Sisa KM &lt; 1.000</div>
                        <div class="card-icon"><i class="bi bi-cone-striped text-warning"></i></div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.maintenance.dashboard', ['status_filter' => 'safe']) }}" class="stat-link">
                    <div class="card-metric border-left-success {{ request('status_filter') == 'safe' ? 'active' : '' }}">
                        <div class="metric-label text-success">Kondisi Prima</div>
                        <div class="metric-value">{{ $stats['sehat'] }}</div>
                        <div class="metric-desc">Siap Operasi</div>
                        <div class="card-icon"><i class="bi bi-check-circle-fill text-success"></i></div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.maintenance.dashboard', request()->except(['status_filter'])) }}"
                    class="stat-link">
                    <div class="card-metric border-left-primary">
                        <div class="metric-label text-primary">Total Armada</div>
                        <div class="metric-value">{{ $stats['total'] }}</div>
                        <div class="metric-desc">Unit Terdaftar</div>
                        <div class="card-icon"><i class="bi bi-truck text-primary"></i></div>
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
            
                <div class="table-responsive-lg" style="padding-bottom: 120px;">
                    <table class="table-corporate">
                        <thead>
                            <tr>
                                <th class="ps-4">Unit Kendaraan</th>
                                <th>Status Kesehatan</th>
                                <th>Health Score</th>
                                <th>Update Terakhir</th>
                                <th class="text-end pe-4">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maintenanceData as $vehicle)
                                @php
                                    // 1. HITUNG SKOR REAL-TIME (Sinkronisasi dengan Component Blade)
                                    $score = $healthService->calculateHealthScore($vehicle);

                                    // 2. TENTUKAN WARNA PROGRESS BAR & STATUS
                                    if ($score >= 75) {
                                        $barColor = '#52c41a'; // Hijau
                                        $statusClass = 'badge-corp-success';
                                        $statusIcon = 'bi-check-circle-fill';
                                        $statusText = 'Prima';
                                    } elseif ($score >= 40) {
                                        $barColor = '#faad14'; // Kuning
                                        $statusClass = 'badge-corp-warning';
                                        $statusIcon = 'bi-clock-history';
                                        $statusText = 'Segera Servis';
                                    } else {
                                        $barColor = '#ff4d4f'; // Merah
                                        $statusClass = 'badge-corp-danger';
                                        $statusIcon = 'bi-exclamation-diamond-fill';
                                        $statusText = 'Telat Servis';
                                    }
                                @endphp
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
                                        @if($vehicle->health_status_code === 'physical_issue')
                                            <span class="badge-corp badge-corp-danger"><i class="bi bi-wrench"></i> Isu Fisik</span>
                                        @else
                                            <span class="badge-corp {{ $statusClass }}">
                                                <i class="bi {{ $statusIcon }}"></i> {{ $statusText }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- PROGRESS KESEHATAN --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="progress-corp-bg">
                                                <div class="progress-corp-fill"
                                                    style="width: {{ $score }}%; background-color: {{ $barColor }};"></div>
                                            </div>
                                            <div style="min-width: 80px;">
                                                <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                                    {{ round($score, 1) }}/100
                                                </div>
                                                <div class="text-muted" style="font-size: 0.7rem;">Score</div>
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
                                            {{-- Primary Action: Komponen --}}
                                            <a href="{{ route('admin.maintenance.components', $vehicle->id) }}"
                                                class="btn-action-corp" title="Kelola Komponen">
                                                <i class="bi bi-gear"></i> Komponen
                                            </a>

                                            {{-- Conditional Action: Jadwal atau Selesai --}}
                                            @if ($vehicle->health_status_code === 'physical_issue')
                                                <form action="{{ route('admin.aset.resolveIssue', $vehicle->id) }}" method="POST"
                                                    class="form-confirm-repair">
                                                    @csrf
                                                    <button type="submit" class="btn-danger-corp">
                                                        <i class="bi bi-check-lg"></i> Selesai
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('admin.maintenance.schedules', ['vehicle_id' => $vehicle->id]) }}"
                                                    class="btn-primary-corp">
                                                    <i class="bi bi-calendar-plus"></i> Jadwal
                                                </a>
                                            @endif

                                            {{-- Dropdown Menu: More Actions --}}
                                            <div class="dropdown">
                                                <button class="btn-action-corp dropdown-toggle" type="button"
                                                    id="dropdownActions{{ $vehicle->id }}" data-bs-toggle="dropdown"
                                                    aria-expanded="false" title="Aksi Lainnya">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                                                    aria-labelledby="dropdownActions{{ $vehicle->id }}">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.aset.visual', $vehicle->id) }}">
                                                            <i class="bi bi-eye text-info me-2"></i> Cek Fisik
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.aset.riwayat', $vehicle->id) }}">
                                                            <i class="bi bi-journal-text text-primary me-2"></i> Riwayat Servis
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.aset.edit', $vehicle->id) }}">
                                                            <i class="bi bi-pencil text-warning me-2"></i> Edit Data
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
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