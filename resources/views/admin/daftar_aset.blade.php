@extends('admin.layouts.app')

@section('title', 'Dashboard - Daftar Aset Mobil')

@section('content')
    <div class="container-fluid p-0">

        {{-- Alert Section --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">

                {{-- Kartu Utama --}}
                <div class="card shadow-sm border-0">

                    {{-- Header Kartu--}}
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-truck text-primary me-2"></i> Daftar Aset
                                Mobil</h5>
                            <small class="text-muted">Data administrasi dan legalitas armada</small>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                            <i class="bi bi-database me-1"></i> {{ $vehicles->total() }} Unit Total
                        </span>
                    </div>

                    <div class="card-body">

                        {{-- Baris Pencarian & Filter (DIPERBARUI) --}}
                            <div class="row mb-4 justify-content-between align-items-center">

                                {{-- Tombol Tambah Aset (Kiri) --}}
                                <div class="col-md-3 mb-2 mb-md-0">
                                    @can('is-master-admin')
                                        <a href="{{ route('admin.aset.create') }}" class="btn btn-primary w-100 w-md-auto">
                                            <i class="bi bi-plus-lg me-2"></i>Tambah Aset Baru
                                        </a>
                                    @endcan
                                </div>

                                {{-- Form Filter & Search (Kanan) --}}
                                <div class="col-md-9">
                                    <form action="{{ route('admin.daftar_aset') }}" method="GET" class="row g-2 justify-content-end">

                                        {{-- 1. DROPDOWN PROJECT (BARU) --}}
                                        <div class="col-md-3 col-6">
                                            <select name="project_id" class="form-select bg-light border-0" onchange="this.form.submit()">
                                                <option value="">Semua Project</option>
                                                @foreach($projects as $proj)
                                                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                                        {{ strtoupper($proj->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- 2. DROPDOWN KATEGORI --}}
                                        <div class="col-md-3 col-6">
                                            <select name="kategori" class="form-select bg-light border-0" onchange="this.form.submit()">
                                                <option value="">Semua Kategori</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat }}" {{ request('kategori') == $cat ? 'selected' : '' }}>
                                                        {{ $cat }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- 3. SEARCH BAR --}}
                                        <div class="col-md-4 col-12">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0 border-0"><i class="bi bi-search text-muted"></i></span>
                                                <input type="search" class="form-control bg-light border-start-0 border-0 ps-0" 
                                                    name="search" 
                                                    placeholder="Cari plat..." 
                                                    value="{{ request('search') }}">
                                                <button class="btn btn-primary" type="submit">Cari</button>
                                            </div>
                                        </div>

                                        {{-- TOMBOL RESET (Muncul jika ada filter aktif) --}}
                                        @if(request('kategori') || request('search') || request('project_id'))
                                            <div class="col-auto">
                                                <a href="{{ route('admin.daftar_aset') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Reset Filter">
                                                    <i class="bi bi-x-lg"></i>
                                                </a>
                                            </div>
                                        @endif

                                    </form>
                                </div>
                            </div>

                            {{-- Tabel Data --}}
                            <div class="table-responsive table-responsive-cards">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-3" width="5%">No.</th>
                                            <th class="py-3">Plat Nomor</th>
                                            <th class="py-3">Jenis Mobil</th>
                                            <th class="py-3">Project</th> {{-- KOLOM BARU --}}
                                            <th class="py-3">Status Ops</th>
                                            <th class="py-3">Driver</th>
                                            <th class="py-3 text-end pe-3">KM Terakhir</th>
                                            <th class="py-3">Update Terakhir</th>
                                            <th class="py-3 text-center" width="18%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($vehicles as $vehicle)
                                            <tr class="aset-row">
                                                <td data-label="No." class="fw-bold text-muted">
                                                    {{ $loop->iteration + ($vehicles->currentPage() - 1) * $vehicles->perPage() }}
                                                </td>

                                                <td data-label="Plat Nomor">
                                                    <span class="badge bg-dark fs-6 font-monospace">{{ $vehicle->plate_number }}</span>
                                                </td>

                                                <td data-label="Jenis Mobil" class="fw-medium">{{ $vehicle->type ?? '-' }}</td>

                                                {{-- ISI DATA PROJECT --}}
                                                <td data-label="Project">
                                                    @if($vehicle->project)
                                                        <span class="badge bg-info text-dark bg-opacity-10 border border-info">
                                                            {{ strtoupper($vehicle->project->name) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>

                                                <td data-label="Status">
                                                    @if ($vehicle->status == 'maintenance')
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                                            <i class="bi bi-tools me-1"></i> Perbaikan
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                            <i class="bi bi-check-circle me-1"></i> Ready
                                                        </span>
                                                    @endif
                                                </td>

                                                <td data-label="Driver">
                                                    @if($vehicle->latestAttendance && $vehicle->latestAttendance->driver)
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-secondary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                                style="width: 24px; height: 24px;">
                                                                <i class="bi bi-person-fill text-secondary" style="font-size: 12px;"></i>
                                                            </div>
                                                            <span>{{ $vehicle->latestAttendance->driver->full_name }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <td data-label="KM Terakhir" class="text-end pe-3 font-monospace fw-bold text-dark">
                                                    {{ number_format($vehicle->current_km) }} <span class="text-muted fw-normal" style="font-size: 0.8em">Km</span>
                                                </td>

                                                <td data-label="Update Terakhir" class="text-muted small">
                                                    {{ $vehicle->updated_at->format('d M Y') }}
                                                </td>

                                                <td data-label="Aksi" class="text-center">
                                                    <div class="d-inline-flex flex-nowrap gap-1">
                                                        {{-- Detail --}}
                                                        <button class="btn btn-outline-info btn-sm" data-bs-toggle="collapse"
                                                            data-bs-target="#detail-{{ $vehicle->id }}" aria-expanded="false"
                                                            data-bs-toggle="tooltip" title="Lihat Status Pajak">
                                                            <i class="bi bi-info-lg"></i>
                                                        </button>

                                                        {{-- Riwayat --}}
                                                        <a href="{{ route('admin.aset.riwayat', $vehicle->id) }}"
                                                            class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip"
                                                            title="Riwayat Servis">
                                                            <i class="bi bi-clock-history"></i>
                                                        </a>

                                                        @can('is-master-admin')
                                                            {{-- Edit --}}
                                                            <a href="{{ route('admin.aset.edit', $vehicle->id) }}"
                                                                class="btn btn-outline-warning btn-sm" data-bs-toggle="tooltip"
                                                                title="Edit Data Aset">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </a>

                                                            {{-- Hapus --}}
                                                            <form action="{{ route('admin.aset.destroy', $vehicle->id) }}" method="POST"
                                                                class="d-inline form-delete-global"
                                                                data-message="Apakah Anda yakin ingin menghapus aset <b class='text-danger'>{{ $vehicle->plate_number }}</b>?">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                    data-bs-toggle="tooltip" title="Hapus Aset">
                                                                    <i class="bi bi-trash-fill"></i>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- Baris Detail (Legalitas) --}}
                                            <tr class="aset-detail-row collapse" id="detail-{{ $vehicle->id }}">
                                                <td colspan="9" class="border-0 bg-light p-4">
                                                    <div class="card border-0 shadow-sm">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h6 class="card-title fw-bold text-primary mb-0">
                                                                    <i class="bi bi-file-earmark-text me-2"></i>Status Legalitas:
                                                                    {{ $vehicle->plate_number }}
                                                                </h6>
                                                                <a href="{{ route('admin.maintenance.dashboard', ['search' => $vehicle->plate_number]) }}"
                                                                    class="btn btn-sm btn-link text-decoration-none">
                                                                    Cek Kondisi Mesin <i class="bi bi-arrow-right"></i>
                                                                </a>
                                                            </div>

                                                            <div class="row g-4">
                                                                {{-- Status STNK --}}
                                                                @php
                                                                    $stnkText = 'Aman';
                                                                    $stnkBadge = 'success';
                                                                    $stnkDisplay = '-';
                                                                    if ($vehicle->pajak_stnk_berlaku_sampai) {
                                                                        $date = \Carbon\Carbon::parse($vehicle->pajak_stnk_berlaku_sampai);
                                                                        if ($date->isPast()) {
                                                                            $stnkText = 'Expired';
                                                                            $stnkBadge = 'danger';
                                                                        } elseif ($date->diffInDays(now()) < 30) {
                                                                            $stnkText = 'Segera Habis';
                                                                            $stnkBadge = 'warning';
                                                                        }
                                                                        $stnkDisplay = $date->format('d M Y');
                                                                    } else {
                                                                        $stnkText = 'Belum Set';
                                                                        $stnkBadge = 'secondary';
                                                                    }
                                                                @endphp

                                                                <div class="col-md-6">
                                                                    <div class="p-3 rounded border bg-white h-100">
                                                                        <label class="small text-muted text-uppercase fw-bold d-block mb-2">Pajak STNK</label>
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <span class="fw-bold">{{ $stnkDisplay }}</span>
                                                                            <span class="badge bg-{{ $stnkBadge }}">{{ $stnkText }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- Status KIR --}}
                                                                @php
                                                                    $kirText = 'Aman';
                                                                    $kirBadge = 'success';
                                                                    $kirDisplay = '-';
                                                                    if ($vehicle->kir_berlaku_sampai) {
                                                                        $date = \Carbon\Carbon::parse($vehicle->kir_berlaku_sampai);
                                                                        if ($date->isPast()) {
                                                                            $kirText = 'Expired';
                                                                            $kirBadge = 'danger';
                                                                        } elseif ($date->diffInDays(now()) < 30) {
                                                                            $kirText = 'Segera Habis';
                                                                            $kirBadge = 'warning';
                                                                        }
                                                                        $kirDisplay = $date->format('d M Y');
                                                                    } else {
                                                                        $kirText = 'Belum Set';
                                                                        $kirBadge = 'secondary';
                                                                    }
                                                                @endphp

                                                                <div class="col-md-6">
                                                                    <div class="p-3 rounded border bg-white h-100">
                                                                        <label class="small text-muted text-uppercase fw-bold d-block mb-2">Uji KIR</label>
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <span class="fw-bold">{{ $kirDisplay }}</span>
                                                                            <span class="badge bg-{{ $kirBadge }}">{{ $kirText }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="bi bi-inbox-fill display-4 d-block mb-3 opacity-50"></i>
                                                        <h5>Data tidak ditemukan</h5>
                                                        <p>Belum ada data aset mobil yang terdaftar.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination Links --}}
                            <div class="mt-3">
                                {{ $vehicles->withQueryString()->links() }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi Tooltip
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
@endpush