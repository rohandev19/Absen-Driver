@extends('admin.layouts.app')

@section('title', 'Dashboard - Riwayat Unit')

@section('content')
    <style>
        /* === VARIABLES & RESET === */
        :root {
            --font-size-base: 14px;
            --border-radius-comfort: 8px;
            --transition-smooth: 0.2s ease-in-out;
        }

        body { font-size: var(--font-size-base); }

        /* === 1. METRIC CARDS === */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }
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
        .stat-card-primary .stat-icon { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; }
        .stat-card-success .stat-icon { background: linear-gradient(135deg, #198754 0%, #157347 100%); color: white; }
        .stat-card-warning .stat-icon { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; }
        
        .stat-content { flex: 1; }
        .stat-value { font-size: 2rem; font-weight: 700; line-height: 1; margin-bottom: 0.25rem; color: #2c3e50; }
        .stat-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6c757d; margin-bottom: 5px; }
        .stat-desc { font-size: 0.85rem; color: #888; margin-top: 4px; }

        /* === 2. FILTER SECTION === */
        .filter-container {
            background: #fbfbfb;
            border: 1px solid #e5e5e5;
            padding: 20px 24px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        /* === 3. CORPORATE TABLE === */
        .table-corporate {
            width: 100%; border-collapse: collapse; background: white;
        }
        .table-corporate thead th {
            background-color: #f8fafc; color: #475569;
            font-weight: 600; font-size: 0.75rem;
            text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0;
            padding: 16px;
        }
        .table-corporate tbody td {
            padding: 16px; vertical-align: middle;
            border-bottom: 1px solid #f1f5f9; color: #334155;
        }
        .table-corporate tbody tr:hover { background-color: #f8fafc; }

        /* === 4. BADGES & BUTTONS === */
        .badge-corp {
            padding: 5px 10px; border-radius: 6px;
            font-weight: 600; font-size: 0.7rem;
            display: inline-flex; align-items: center; gap: 4px;
            border: 1px solid transparent;
        }
        .badge-corp-success { background-color: #f0fdf4; color: #15803d; border-color: #dcfce7; }
        .badge-corp-danger { background-color: #fef2f2; color: #b91c1c; border-color: #fecaca; }
        
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

        /* === 5. MOBILE RESPONSIVE === */
        @media (max-width: 768px) {
            .table-corporate thead { display: none; }
            .table-corporate, .table-corporate tbody, .table-corporate tr, .table-corporate td {
                display: block; width: 100%;
            }
            .table-corporate tbody tr {
                margin-bottom: 16px; border: 1px solid #e2e8f0;
                border-radius: 8px; background: #fff;
                box-shadow: 0 2px 4px rgba(0,0,0,0.02);
                overflow: hidden;
            }
            .table-corporate td {
                padding: 12px 16px; text-align: left;
                border-bottom: 1px solid #f1f5f9;
                display: flex; justify-content: space-between; align-items: center;
                flex-wrap: wrap; gap: 10px;
            }
            
            .table-corporate td:nth-child(2) {
                background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;
                display: block;
            }

            .table-corporate td::before {
                content: attr(data-label);
                font-size: 0.7rem; font-weight: 700; color: #94a3b8;
                text-transform: uppercase; margin-right: auto; min-width: 100px;
            }
            .table-corporate td[data-label=""]::before { display: none; }

            .physical-check-wrapper {
                width: 100%; display: flex; gap: 8px; margin-top: 5px;
            }
        }
    </style>

    <div class="container-fluid p-0">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div></div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="javascript:history.back()" class="btn-corp btn-corp-outline" style="padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; text-decoration: none; border: 1px solid #cbd5e1; color: #475569; background-color: white;">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        {{-- === BAGIAN 1: STATISTIK CARDS === --}}
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon"><i class="bi bi-trophy-fill"></i></div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $topUnitPlate ?? '-' }}</div>
                        <div class="stat-label">Unit Terajin</div>
                        <div class="stat-desc text-primary fw-bold">
                            <i class="bi bi-speedometer2 me-1"></i> Total {{ number_format($topUnitKm) }} Km
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card stat-card-success">
                    <div class="stat-icon"><i class="bi bi-globe-asia-australia"></i></div>
                    <div class="stat-content">
                        <div class="stat-value">{{ number_format($totalJarakPeriode) }} <small class="fs-6 text-muted">Km</small></div>
                        <div class="stat-label">Total Jelajah</div>
                        <div class="stat-desc">Akumulasi sesuai filter</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon"><i class="bi bi-clipboard-check-fill"></i></div>
                    <div class="stat-content">
                        <div class="stat-value">{{ number_format($totalTrip) }} <small class="fs-6 text-muted">Trip</small></div>
                        <div class="stat-label">Total Penugasan</div>
                        <div class="stat-desc">Form checklist masuk</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- === BAGIAN 2: FILTER DATA (LENGKAP: PROJECT & TYPE) === --}}
        <div class="filter-container">
            <form action="{{ route('admin.riwayat_unit') }}" method="GET">
                <div class="row g-3 align-items-end">
                    
                    {{-- 1. Filter Project --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted text-uppercase">Project</label>
                        <select class="form-select form-select-sm" name="project_id" onchange="this.form.submit()">
                            <option value="">-- Semua --</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" {{ $project->id == $selectedProjectId ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Filter Type Mobil (BARU) --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted text-uppercase">Type Mobil</label>
                        <select class="form-select form-select-sm" name="type" onchange="this.form.submit()">
                            <option value="">-- Semua --</option>
                            @foreach ($types as $t)
                                <option value="{{ $t }}" {{ $t == $selectedType ? 'selected' : '' }}>
                                    {{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. Filter Unit (Plat) --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Cari Unit</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-truck"></i></span>
                            <input type="text" class="form-control" name="plate_number" 
                                value="{{ $filterPlat ?? '' }}" placeholder="Plat Nomor...">
                        </div>
                    </div>

                    {{-- 4. Filter Tanggal --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Periode</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                            <span class="input-group-text bg-light border-start-0 border-end-0">s/d</span>
                            <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                        </div>
                    </div>

                    {{-- 5. Tombol --}}
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="bi bi-search me-1"></i>
                        </button>
                        <a href="{{ route('admin.riwayat_unit') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- === BAGIAN 3: TABEL DATA === --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-check text-primary me-2"></i>Detail Riwayat</h6>
                <span class="badge bg-light text-dark border">
                    {{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive table-responsive-cards">
                    <table class="table-corporate">
                        <thead>
                            <tr>
                                <th class="ps-4">Waktu Cek</th>
                                <th>Driver & Unit</th>
                                <th class="text-end">Jarak Tempuh</th>
                                <th>Kondisi Fisik</th>
                                <th>Catatan</th>
                                <th class="text-center pe-4">Bukti</th>
                                @can('is-master-admin')
                                    <th class="text-center pe-4">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($checklistPaginator as $item)
                                <tr class="aset-row">
                                    {{-- 1. Waktu --}}
                                    <td class="ps-4" data-label="Waktu">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($item['timestamp_keluar'])->format('H:i') }}</span>
                                            <span class="text-muted small">{{ \Carbon\Carbon::parse($item['timestamp_keluar'])->format('d M Y') }}</span>
                                        </div>
                                    </td>

                                    {{-- 2. Driver & Unit --}}
                                    <td data-label="">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-3 text-success border border-success border-opacity-25" style="width: 40px; height: 40px;">
                                                <i class="bi bi-truck fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $item['driver_name'] }}</div>
                                                <div class="d-flex align-items-center gap-2 mt-1">
                                                    <span class="badge bg-dark text-white border font-monospace">{{ $item['plate_number'] }}</span>
                                                    {{-- Info Type --}}
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $item['vehicle_type'] }}</span>
                                                    {{-- Info Project --}}
                                                    <span class="badge bg-light text-muted border">{{ $item['project_name'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 3. Jarak --}}
                                    <td class="text-end" data-label="Jarak">
                                        <div class="fw-bold text-primary">{{ $item['total_jarak'] }} Km</div>
                                        <div class="small text-muted font-monospace bg-light px-2 rounded border d-inline-block mt-1">
                                            {{ $item['speedo_awal'] }} - {{ $item['speedo_akhir'] }}
                                        </div>
                                    </td>

                                    {{-- 4. Kondisi Fisik --}}
                                    <td data-label="Kondisi Fisik">
                                        <div class="physical-check-wrapper d-flex gap-2 flex-wrap">
                                            <span class="badge-corp {{ $item['cek_ban'] == 'Aman' ? 'badge-corp-success' : 'badge-corp-danger' }}">
                                                <i class="bi {{ $item['cek_ban'] == 'Aman' ? 'bi-check-circle' : 'bi-exclamation-circle' }}"></i> Ban
                                            </span>
                                            <span class="badge-corp {{ $item['cek_lampu'] == 'Aman' ? 'badge-corp-success' : 'badge-corp-danger' }}">
                                                <i class="bi {{ $item['cek_lampu'] == 'Aman' ? 'bi-check-circle' : 'bi-exclamation-circle' }}"></i> Lampu
                                            </span>
                                            <span class="badge-corp {{ $item['cek_rem'] == 'Aman' ? 'badge-corp-success' : 'badge-corp-danger' }}">
                                                <i class="bi {{ $item['cek_rem'] == 'Aman' ? 'bi-check-circle' : 'bi-exclamation-circle' }}"></i> Rem
                                            </span>
                                        </div>
                                    </td>

                                    {{-- 5. Catatan --}}
                                    <td data-label="Catatan">
                                        @if($item['catatan'] !== '-')
                                            <span class="text-muted small fst-italic">"{{ Str::limit($item['catatan'], 50) }}"</span>
                                        @else
                                            <span class="text-muted opacity-50 small">-</span>
                                        @endif
                                    </td>

                                    {{-- 6. Bukti --}}
                                    <td class="text-center pe-4" data-label="Bukti Foto">
                                        @if($item['link_speedo_akhir'] !== '#')
                                            <a href="{{ $item['link_speedo_akhir'] }}" target="_blank" 
                                               class="btn-icon-corp text-primary" title="Lihat Foto Odo">
                                                <i class="bi bi-image"></i>
                                            </a>
                                        @else
                                            <span class="text-muted opacity-25"><i class="bi bi-slash-circle"></i></span>
                                        @endif
                                    </td>

                                    {{-- 7. Aksi (Koreksi KM) --}}
                                    @can('is-master-admin')
                                    <td class="text-center pe-4" data-label="Aksi">
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
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.attendance.updateKm', $item['id']) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body p-4">
                                                                <div class="alert alert-light border mb-4 small d-flex align-items-center">
                                                                    <i class="bi bi-info-circle me-2 text-muted fs-5"></i>
                                                                    <div>
                                                                        Edit untuk: <strong>{{ $item['driver_name'] }}</strong><br>
                                                                        Unit: {{ $item['plate_number'] }}<br>
                                                                        Waktu: {{ $item['timestamp_keluar'] }}
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold text-muted">KM Awal</label>
                                                                    <div class="input-group">
                                                                        <input type="number" class="form-control" name="speedo_awal"
                                                                            value="{{ $item['raw_speedo_awal'] }}" required min="0" step="1">
                                                                        <span class="input-group-text bg-light text-muted">Km</span>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-4">
                                                                    <label class="form-label small fw-bold text-muted">KM Akhir</label>
                                                                    <div class="input-group">
                                                                        <input type="number" class="form-control" name="speedo_akhir"
                                                                            value="{{ $item['raw_speedo_akhir'] }}" required min="0" step="1">
                                                                        <span class="input-group-text bg-light text-muted">Km</span>
                                                                    </div>
                                                                    <div class="form-text mt-2 text-danger small">
                                                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                                                        Mengubah KM Akhir (jika ini log terbaru) akan otomatis mengubah KM Master Kendaraan.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light border-0 px-4 py-3">
                                                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-warning fw-bold px-4">
                                                                    <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="py-3">
                                            <i class="bi bi-clipboard-x fs-1 d-block mb-3 opacity-25"></i>
                                            <p class="mb-0 fw-medium">Tidak ada data checklist ditemukan.</p>
                                            <small>Coba ubah filter.</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                @if ($checklistPaginator->hasPages())
                    <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
                        {{ $checklistPaginator->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection