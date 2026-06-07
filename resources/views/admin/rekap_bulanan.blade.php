@extends('admin.layouts.app')

@section('title', 'Dashboard - Rekap Bulanan')

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

        /* === 1. FILTER CONTAINER === */
        .filter-container {
            background: #fbfbfb;
            border: 1px solid #e5e5e5;
            padding: 20px 24px;
            border-radius: 8px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
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
            padding: 12px 16px;
        }

        .table-corporate tbody td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .table-corporate tbody tr:hover {
            background-color: #f8fafc;
        }

        /* === 3. BADGES === */
        .badge-corp {
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid transparent;
        }

        .badge-corp-dark {
            background-color: #1e293b;
            color: #f8fafc;
            border-color: #0f172a;
        }

        .badge-corp-light {
            background-color: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }

        /* === 4. MOBILE RESPONSIVE (CARD VIEW) === */
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
                margin-bottom: 12px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #fff;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
                overflow: hidden;
            }

            .table-corporate td {
                padding: 10px 16px;
                text-align: left;
                border-bottom: 1px solid #f1f5f9;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            /* Header Kartu (Nama/Plat) */
            .table-corporate td:first-child {
                background-color: #f8fafc;
                border-bottom: 1px solid #e2e8f0;
                font-weight: bold;
            }

            .table-corporate td::before {
                content: attr(data-label);
                font-size: 0.7rem;
                font-weight: 700;
                color: #94a3b8;
                text-transform: uppercase;
            }
        }
    </style>

    <div class="container-fluid p-0">
        {{-- PRINT HEADER --}}
        <div class="d-none d-print-block text-center mb-4">
            <h2 class="fw-bold mb-1" style="color: #000; font-size: 24px; text-transform: uppercase;">PT HAMADA LOGISTIK</h2>
            <p class="mb-0" style="font-size: 14px; color: #000; border-bottom: 2px solid #000; padding-bottom: 10px;">Laporan Rekap Bulanan - Periode: {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</p>
        </div>

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Rekapitulasi Bulanan</h3>
                <p class="text-muted mb-0 small">Statistik performa driver dan penggunaan armada.</p>
            </div>
        </div>

        {{-- FILTER BAR --}}
        <div class="filter-container">
            <form action="{{ route('admin.rekap_bulanan') }}" method="GET">
                <div class="row g-3 align-items-end">
                    {{-- Filter Project --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Project</label>
                        <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Project --</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Bulan --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Periode Bulan</label>
                        <input type="month" class="form-control form-select-sm" name="bulan" value="{{ $selectedMonth }}"
                            onchange="this.form.submit()">
                    </div>

                    {{-- Tombol Export & Cetak --}}
                    <div class="col-md-4 d-flex justify-content-end gap-2">
                        <button type="button" onclick="window.print()" class="btn btn-primary btn-sm w-100 w-md-auto d-flex align-items-center justify-content-center d-print-none shadow-sm">
                            <i class="bi bi-printer me-2"></i> Cetak
                        </button>
                        <a href="{{ route('admin.rekap_bulanan.export_checklist', ['bulan' => $selectedMonth, 'project_id' => request('project_id')]) }}"
                            class="btn btn-success btn-sm w-100 w-md-auto d-flex align-items-center justify-content-center shadow-sm">
                            <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm w-100 w-md-auto d-flex align-items-center justify-content-center d-print-none shadow-sm">
                            <i class="bi bi-arrow-left me-2"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="row g-4">
            {{-- KOLOM KIRI: REKAP DRIVER --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100 rounded-3 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Top Driver
                        </h6>
                        <span class="badge bg-light text-muted border">Total Jarak</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th class="ps-4">Nama Driver</th>
                                    <th class="text-end">Jumlah Trip</th>
                                    <th class="text-end pe-4">Total Jarak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($rekapDriver))
                                    @forelse ($rekapDriver as $nama => $data)
                                        <tr>
                                            <td class="ps-4 fw-medium text-dark" data-label="Nama">{{ $nama }}</td>
                                            <td class="text-end" data-label="Trip">
                                                <span class="badge-corp badge-corp-light">{{ $data['jumlah_tugas'] }} Trip</span>
                                            </td>
                                            <td class="text-end pe-4 fw-bold text-primary" data-label="Total Jarak">
                                                {{ number_format($data['total_km']) }} Km
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">Belum ada data driver.</td>
                                        </tr>
                                    @endforelse
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-danger">Data belum dimuat.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: REKAP UNIT --}}
            <div class="col-lg-6 mt-4 mt-lg-0 mt-print-0">
                <div class="card shadow-sm border-0 h-100 rounded-3 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-truck text-info me-2"></i>Utilisasi Armada</h6>
                        <span class="badge bg-light text-muted border">Total Jarak</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th class="ps-4">Plat Nomor</th>
                                    <th class="text-end">Jumlah Trip</th>
                                    <th class="text-end pe-4">Total Jarak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rekapUnit as $plat => $data)
                                    <tr>
                                        <td class="ps-4" data-label="Unit">
                                            <span class="badge-corp badge-corp-dark font-monospace">{{ $plat }}</span>
                                        </td>
                                        <td class="text-end" data-label="Trip">
                                            <span class="badge-corp badge-corp-light">{{ $data['jumlah_tugas'] }} Trip</span>
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-info" data-label="Total Jarak">
                                            {{ number_format($data['total_km']) }} Km
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">Belum ada data unit.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection