@extends('admin.layouts.app')

@section('title', 'Dashboard - Rekap Bulanan')

@section('content')
    <div class="container-fluid p-0">

        {{-- Header & Filter Section --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-line text-success me-2"></i> Rekap Bulanan
                    </h5>
                    <small class="text-muted">Statistik performa driver dan penggunaan unit</small>
                </div>
                <form action="{{ route('admin.rekap_bulanan') }}" method="GET" class="d-flex gap-2 align-items-center">
                    <label for="bulan" class="form-label mb-0 text-muted small me-1">Periode:</label>
                    <input type="month" class="form-control" id="bulan" name="bulan" value="{{ $selectedMonth }}">
                    <button type="submit" class="btn btn-success px-4">Tampilkan</button>
                </form>
            </div>
        </div>

        {{-- TOMBOL EXPORT VERSI PRO (Pakai Class Global) --}}
        <a href="{{ route('admin.rekap_bulanan.export_checklist', ['bulan' => $selectedMonth]) }}"
            class="btn btn-outline-success link-confirm-global" data-title="Download Excel?"
            data-text="Apakah Anda ingin mendownload file Checklist periode ini?" data-confirm-text="Ya, Download!">
            <i class="bi bi-calendar-check"></i> Export Checklist
        </a>
    </div>

    <div class="row g-4 mt-1">
        {{-- Kolom Kiri: Rekap Driver --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-person-badge me-2"></i>Performa Driver</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Nama Driver</th>
                                <th class="text-end py-3">Trip</th>
                                <th class="text-end pe-4 py-3">Total Jarak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekapDriver as $nama => $data)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $nama }}</td>
                                    <td class="text-end"><span
                                            class="badge bg-secondary rounded-pill">{{ $data['jumlah_tugas'] }}</span></td>
                                    <td class="text-end pe-4 fw-bold">{{ number_format($data['total_km']) }} <small
                                            class="text-muted fw-normal">Km</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Rekap Unit --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-info"><i class="bi bi-truck me-2"></i>Penggunaan Unit</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Plat Nomor</th>
                                <th class="text-end py-3">Trip</th>
                                <th class="text-end pe-4 py-3">Total Jarak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekapUnit as $plat => $data)
                                <tr>
                                    <td class="ps-4"><span class="badge bg-dark font-monospace">{{ $plat }}</span></td>
                                    <td class="text-end"><span
                                            class="badge bg-secondary rounded-pill">{{ $data['jumlah_tugas'] }}</span></td>
                                    <td class="text-end pe-4 fw-bold">{{ number_format($data['total_km']) }} <small
                                            class="text-muted fw-normal">Km</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection