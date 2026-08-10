@extends('admin.layouts.app')

@section('title', 'Dashboard - Riwayat Driver')

@section('content')
    <style>
        /* === VARIABLES & RESET === */
        :root {
            --font-size-base: 14px;
            --border-radius-comfort: 8px;
            --transition-smooth: 0.2s ease-in-out;
            --primary-color: #0d6efd;
        }

        body {
            font-size: var(--font-size-base);
        }

        /* === 1. COMPONENTS === */
        /* Filter Container */
        .filter-container {
            background: #fbfbfb;
            border: 1px solid #e5e5e5;
            padding: 20px 24px;
            border-radius: 8px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        /* Badge Formal */
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

        .badge-corp-neutral {
            background-color: #f3f4f6;
            color: #4b5563;
            border-color: #e5e7eb;
        }

        .badge-corp-primary {
            background-color: #eff6ff;
            color: #1d4ed8;
            border-color: #dbeafe;
        }

        .badge-corp-success {
            background-color: #f0fdf4;
            color: #15803d;
            border-color: #dcfce7;
        }

        /* Buttons Enterprise Style */
        .btn-corp {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .btn-corp-primary {
            background-color: #0f172a;
            color: white;
            border-color: #0f172a;
        }

        .btn-corp-primary:hover {
            background-color: #1e293b;
            color: white;
            transform: translateY(-1px);
        }

        .btn-corp-outline {
            background-color: white;
            color: #475569;
            border-color: #cbd5e1;
        }

        .btn-corp-outline:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .btn-corp-success {
            background-color: #10b981;
            color: white;
            border-color: #10b981;
        }

        .btn-corp-success:hover {
            background-color: #059669;
            color: white;
        }

        /* Action Icon Button (Tiny) */
        .btn-icon-corp {
            width: 34px; height: 34px; border-radius: 6px;
            display: inline-flex; align-items: center; justify-content: center;
            background: white; border: 1px solid #e2e8f0; color: #64748b;
            transition: 0.2s; text-decoration: none;
        }
        .btn-icon-corp:hover { border-color: #3b82f6; color: #3b82f6; background: #eff6ff; }
        
        [data-bs-theme="dark"] .btn-icon-corp {
            background: transparent; border-color: rgba(255,255,255,0.2); color: #e2e8f0;
        }
        [data-bs-theme="dark"] .btn-icon-corp:hover {
            background: rgba(255, 255, 255, 0.1); color: #fff; border-color: rgba(255, 255, 255, 0.3);
        }

        /* === 2. CORPORATE TABLE === */
        .table-corporate {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .table-corporate thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            border-top: 1px solid #e2e8f0;
            padding: 16px;
        }

        .table-corporate tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .table-corporate tbody tr:hover {
            background-color: #f8fafc;
        }

        /* === 3. MOBILE RESPONSIVE (CARD VIEW) === */
        @media (max-width: 768px) {
            .table-corporate thead {
                display: none;
            }

            .table-corporate,
            .table-corporate tbody,
            .table-corporate tr,
            .table-corporate td {
                display: block;
                width: 100%;
            }

            .table-corporate tbody tr {
                margin-bottom: 16px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #fff;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
                overflow: hidden;
            }

            .table-corporate td {
                padding: 12px 16px;
                text-align: left;
                border-bottom: 1px solid #f1f5f9;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            /* Header Kartu (Driver Info) */
            .table-corporate td:nth-child(3) {
                background-color: #f8fafc;
                border-bottom: 1px solid #e2e8f0;
                display: block;
                /* Agar layout flex di dalam cell bekerja normal */
            }

            /* Labeling for Mobile */
            .table-corporate td::before {
                content: attr(data-label);
                font-size: 0.75rem;
                font-weight: 700;
                color: #94a3b8;
                text-transform: uppercase;
                margin-right: auto;
                min-width: 100px;
            }

            /* Hide empty labels */
            .table-corporate td[data-label=""]::before {
                display: none;
            }

            /* Action Buttons Layout on Mobile */
            .table-corporate td:last-child {
                background-color: #fff;
                display: block;
                padding: 16px;
            }

            .table-corporate td:last-child .btn-group {
                width: 100%;
                display: flex;
                gap: 8px;
            }

            .table-corporate td:last-child .btn {
                flex: 1;
            }
        }
    </style>

    <div class="container-fluid p-0">

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Riwayat Perjalanan</h3>
                <p class="text-muted mb-0 small">Log aktivitas, jarak tempuh, dan absensi driver.</p>
            </div>

            <div class="d-flex gap-2 mt-3 mt-md-0">
                {{-- Export Buttons --}}
                <a href="{{ route('admin.riwayat_driver.export', request()->all()) }}" class="btn-corp btn-corp-success">
                    <i class="bi bi-file-earmark-excel"></i> Export Detail
                </a>
                <a href="{{ route('admin.absensi.export_rekap', request()->all()) }}" class="btn-corp btn-corp-outline">
                    <i class="bi bi-grid-3x3"></i> Export Rekap
                </a>
                <a href="javascript:history.back()" class="btn-corp btn-corp-outline">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- FILTER BAR --}}
        <div class="filter-container">
            <form action="{{ route('admin.riwayat_driver') }}" method="GET">
                <div class="row g-3 align-items-end">

                    {{-- Filter Project --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Project</label>
                        <select class="form-select form-select-sm" name="project_id" onchange="this.form.submit()">
                            <option value="">-- Semua Project --</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" {{ $project->id == $selectedProjectId ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Driver --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Driver</label>
                        <select class="form-select form-select-sm" name="driver_id" onchange="this.form.submit()">
                            <option value="">-- Semua Driver --</option>
                            @foreach ($allDrivers as $driver)
                                <option value="{{ $driver->id }}" {{ $driver->id == $selectedDriverId ? 'selected' : '' }}>
                                    {{ $driver->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Range --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Periode Tanggal</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                            <span class="input-group-text bg-light">s/d</span>
                            <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>

                    {{-- Reset --}}
                    <div class="col-md-2 text-end">
                        <a href="{{ route('admin.riwayat_driver') }}"
                            class="btn btn-sm text-danger text-decoration-none fw-bold">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- ALERT MESSAGES --}}
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        {{-- TABLE DATA --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-corporate">
                        <thead>
                            <tr>
                                <th class="ps-4">Waktu</th>
                                <th>Durasi</th>
                                <th>Driver & Unit</th>
                                <th class="text-center">Lokasi</th>
                                <th class="text-end">Jarak Tempuh</th>
                                <th class="text-center">Dokumentasi</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historyPaginator as $item)
                                <tr>
                                    {{-- 1. WAKTU --}}
                                    <td class="ps-4" data-label="Waktu Masuk">
                                        <div class="d-flex flex-column">
                                            <span
                                                class="fw-bold text-dark">{{ \Carbon\Carbon::parse($item['timestamp_masuk'])->format('H:i') }}</span>
                                            <span
                                                class="text-muted small">{{ \Carbon\Carbon::parse($item['timestamp_masuk'])->format('d M Y') }}</span>
                                        </div>
                                    </td>

                                    {{-- 2. DURASI --}}
                                    <td data-label="Durasi Kerja">
                                        <span class="badge-corp badge-corp-neutral">
                                            <i class="bi bi-hourglass-split text-muted"></i> {{ $item['total_jam_kerja'] }}
                                        </span>
                                    </td>

                                    {{-- 3. DRIVER --}}
                                    <td data-label="">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3 text-primary"
                                                style="width: 40px; height: 40px;">
                                                <i class="bi bi-person-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $item['driver_name'] }}</div>
                                                <div class="small text-muted d-flex align-items-center gap-2">
                                                    <span
                                                        class="badge bg-light text-dark border">{{ $item['plate_number'] }}</span>
                                                    @if(($item['vehicle_entry_method'] ?? 'qr') === 'manual')
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Manual</span>
                                                    @endif
                                                    @if(($item['vehicle_verification_status'] ?? 'verified') === 'pending')
                                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Pending Unit</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 4. LOKASI --}}
                                    <td class="text-center" data-label="Lokasi">
                                        <a href="{{ $item['gps_masuk'] }}" target="_blank"
                                            class="btn-icon-corp text-decoration-none" title="Lihat Lokasi GPS">
                                            <i class="bi bi-geo-alt-fill text-danger"></i>
                                        </a>
                                    </td>

                                    {{-- 5. JARAK --}}
                                    <td class="text-end" data-label="Jarak (KM)">
                                        <div class="fw-bold text-dark fs-6">{{ $item['jarak_tempuh'] }} Km</div>
                                        <div class="small text-muted font-monospace">
                                            {{ $item['speedo_awal'] }} - {{ $item['speedo_akhir'] }}
                                        </div>
                                    </td>



                                    {{-- 6. FOTO --}}
                                    <td class="text-center" data-label="Foto Bukti">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ $item['link_speedo_awal'] }}" target="_blank" class="btn-icon-corp text-primary"
                                                title="Foto Awal">
                                                <i class="bi bi-1-circle"></i>
                                            </a>
                                            <a href="{{ $item['link_speedo_akhir'] }}" target="_blank" class="btn-icon-corp text-primary"
                                                title="Foto Akhir">
                                                <i class="bi bi-2-circle"></i>
                                            </a>
                                            <a href="{{ $item['link_selfie'] }}" target="_blank" class="btn-icon-corp text-primary"
                                                title="Selfie">
                                                <i class="bi bi-person-bounding-box"></i>
                                            </a>
                                            @if(($item['vehicle_entry_method'] ?? 'qr') === 'manual')
                                                <a href="{{ $item['link_manual_vehicle_photo'] }}" target="_blank" class="btn-icon-corp text-primary"
                                                    title="Foto Plat/Unit Manual">
                                                    <i class="bi bi-truck-front"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 7. AKSI (KOREKSI) --}}
                                    <td class="text-end pe-4" data-label="">
                                        @can('is-master-admin')
                                            @if(!empty($item['id']))
                                                <button type="button" class="btn btn-sm btn-outline-warning border-0 fw-bold"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditKm{{ $item['id'] }}">
                                                    <i class="bi bi-pencil-square me-1"></i> Koreksi
                                                </button>

                                                {{-- MODAL KOREKSI --}}
                                                <div class="modal fade" id="modalEditKm{{ $item['id'] }}" tabindex="-1"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content text-start border-0 shadow">
                                                            <div class="modal-header bg-warning bg-opacity-10">
                                                                <h6 class="modal-title fw-bold text-warning-emphasis">
                                                                    <i class="bi bi-pencil-fill me-2"></i>Koreksi Data KM
                                                                </h6>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="{{ route('admin.attendance.updateKm', $item['id']) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-body p-4">
                                                                    <div
                                                                        class="alert alert-light border mb-4 small d-flex align-items-center">
                                                                        <i class="bi bi-info-circle me-2 text-muted fs-5"></i>
                                                                        <div>
                                                                            Edit untuk: <strong>{{ $item['driver_name'] }}</strong><br>
                                                                            Tanggal: {{ $item['timestamp_masuk'] }}
                                                                        </div>
                                                                    </div>

                                                                    <div class="row g-3 mb-4">
                                                                        <div class="col-6">
                                                                            <label
                                                                                class="form-label small fw-bold text-muted text-uppercase">Speedo
                                                                                Awal</label>
                                                                            <input type="number" name="speedo_awal" class="form-control"
                                                                                value="{{ $item['raw_speedo_awal'] ?? 0 }}" required>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <label
                                                                                class="form-label small fw-bold text-muted text-uppercase">Speedo
                                                                                Akhir</label>
                                                                            <input type="number" name="speedo_akhir"
                                                                                class="form-control"
                                                                                value="{{ $item['raw_speedo_akhir'] ?? 0 }}" required>
                                                                        </div>
                                                                    </div>

                                                                    {{-- [TAMBAHAN] TOMBOL LIHAT FOTO DI DALAM MODAL --}}
                                                                    <div class="bg-light p-3 rounded border text-center">
                                                                        <label
                                                                            class="small fw-bold text-muted text-uppercase mb-2 d-block">Verifikasi
                                                                            Foto Bukti</label>
                                                                        <div class="d-flex justify-content-center gap-2">
                                                                            <a href="{{ $item['link_speedo_awal'] }}" target="_blank"
                                                                                class="btn btn-sm btn-outline-secondary bg-white">
                                                                                <i class="bi bi-image me-1"></i> Foto Awal
                                                                            </a>
                                                                            <a href="{{ $item['link_speedo_akhir'] }}" target="_blank"
                                                                                class="btn btn-sm btn-outline-secondary bg-white">
                                                                                <i class="bi bi-image-fill me-1"></i> Foto Akhir
                                                                            </a>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                                        data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-sm btn-primary px-4">Simpan
                                                                        Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                            <p class="mb-0 fw-medium">Tidak ada data riwayat ditemukan.</p>
                                            <small>Coba sesuaikan filter tanggal atau project.</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                @if ($historyPaginator->hasPages())
                    <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
                        {{ $historyPaginator->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
