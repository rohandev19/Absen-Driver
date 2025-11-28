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
                                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-truck text-primary me-2"></i> Daftar Aset Mobil</h5>
                                <small class="text-muted">Data administrasi dan legalitas armada</small>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                <i class="bi bi-database me-1"></i> {{ count($daftarMobil) }} Unit Total
                            </span>
                        </div>

                        <div class="card-body">

                            {{-- Baris Pencarian --}}
                            <div class="row mb-4">
                                <div class="col-md-6 col-lg-4 ms-auto">
                                    <form action="{{ route('admin.daftar_aset') }}" method="GET">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                            <input type="search" class="form-control bg-light border-start-0 ps-0" name="search" 
                                                   placeholder="Cari plat, jenis, atau driver..." 
                                                   value="{{ $searchKeyword ?? '' }}">
                                            <button class="btn btn-primary" type="submit">Cari</button>
                                        </div>
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
                                            <th class="py-3">Status Ops</th>
                                            <th class="py-3">Driver</th>
                                            <th class="py-3 text-end pe-3">KM Terakhir</th>
                                            <th class="py-3">Update Terakhir</th>
                                            <th class="py-3 text-center" width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($daftarMobil as $mobil)
                                            <tr class="aset-row">
                                                <td data-label="No." class="fw-bold text-muted">{{ $loop->iteration }}</td>

                                                <td data-label="Plat Nomor">
                                                    <span class="badge bg-dark fs-6 font-monospace">{{ $mobil['plat_nomor'] }}</span>
                                                </td>

                                                <td data-label="Jenis Mobil" class="fw-medium">{{ $mobil['jenis_mobil'] }}</td>

                                                <td data-label="Status">
                                                    @if ($mobil['status'] == 'Sedang Dipakai')
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                                            <i class="bi bi-broadcast me-1"></i> {{ $mobil['status'] }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                            <i class="bi bi-p-circle me-1"></i> {{ $mobil['status'] }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <td data-label="Driver">
                                                    @if($mobil['driver_terakhir'] != '-' && $mobil['driver_terakhir'] != 'N/A')
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-secondary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                                                <i class="bi bi-person-fill text-secondary" style="font-size: 12px;"></i>
                                                            </div>
                                                            <span>{{ $mobil['driver_terakhir'] }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <td data-label="KM Terakhir" class="text-end pe-3 font-monospace fw-bold text-dark">
                                                    {{ number_format($mobil['km_terakhir']) }} <span class="text-muted fw-normal" style="font-size: 0.8em">Km</span>
                                                </td>

                                                <td data-label="Update Terakhir" class="text-muted small">
                                                    {{ $mobil['tgl_terakhir'] }}
                                                </td>

                                                <td data-label="Aksi" class="text-center">
                                                    <div class="d-inline-flex flex-nowrap gap-1">

                                                        {{-- Tombol Detail (Collapse) --}}
                                                        <button class="btn btn-outline-info btn-sm" data-bs-toggle="collapse"
                                                            data-bs-target="#detail-{{ $mobil['id'] }}" 
                                                            aria-expanded="false" 
                                                            data-bs-toggle="tooltip" title="Lihat Status Pajak">
                                                            <i class="bi bi-info-lg"></i>
                                                        </button>

                                                        {{-- Tombol Riwayat --}}
                                                        {{-- Opsional: Bisa dihapus jika sudah ada di menu Maintenance --}}
                                                        <a href="{{ route('admin.riwayat_unit', ['plate_number' => $mobil['plat_nomor']]) }}"
                                                           class="btn btn-outline-secondary btn-sm" 
                                                           data-bs-toggle="tooltip" title="Riwayat Perjalanan">
                                                            <i class="bi bi-clock-history"></i>
                                                        </a>

                                                        @can('is-master-admin')
                                                            {{-- Tombol Edit Data --}}
                                                            <a href="{{ route('admin.aset.edit', $mobil['id']) }}"
                                                                class="btn btn-outline-warning btn-sm" 
                                                                data-bs-toggle="tooltip" title="Edit Data Aset">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </a>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- Baris Detail (Perbaikan: Menghapus Data Servis yang Error) --}}
                                            <tr class="aset-detail-row collapse" id="detail-{{ $mobil['id'] }}">
                                                <td colspan="8" class="border-0 bg-light p-4">
                                                    <div class="card border-0 shadow-sm">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h6 class="card-title fw-bold text-primary mb-0">
                                                                    <i class="bi bi-file-earmark-text me-2"></i>Status Legalitas: {{ $mobil['plat_nomor'] }}
                                                                </h6>
                                                                {{-- Link pintas ke Maintenance untuk info lebih lanjut --}}
                                                                <a href="{{ route('admin.maintenance.dashboard', ['search' => $mobil['plat_nomor']]) }}" class="btn btn-sm btn-link text-decoration-none">
                                                                    Cek Kondisi Mesin <i class="bi bi-arrow-right"></i>
                                                                </a>
                                                            </div>

                                                            <div class="row g-4">
                                                                {{-- HANYA TAMPILKAN STATUS STNK & KIR (Data Administrasi) --}}
                                                                <div class="col-md-6">
                                                                    <div class="p-3 rounded border bg-white h-100">
                                                                        <label class="small text-muted text-uppercase fw-bold d-block mb-2">Pajak STNK</label>
                                                                        <span class="badge bg-{{ $mobil['status_stnk']['badge'] }} fs-6">
                                                                            {{ $mobil['status_stnk']['text'] }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="p-3 rounded border bg-white h-100">
                                                                        <label class="small text-muted text-uppercase fw-bold d-block mb-2">Uji KIR</label>
                                                                        <span class="badge bg-{{ $mobil['status_kir']['badge'] }} fs-6">
                                                                            {{ $mobil['status_kir']['text'] }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Tooltip Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
@endpush