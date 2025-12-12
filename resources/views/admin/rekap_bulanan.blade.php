@extends('admin.layouts.app')

@section('title', 'Dashboard - Rekap Bulanan')

@section('content')
    <div class="container-fluid p-0">

        {{-- Header & Filter Section --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-0">Rekapitulasi Bulanan</h4>
                <small class="text-muted">Statistik performa driver dan penggunaan armada</small>
            </div>
            
            <div class="d-flex gap-2">
                {{-- Form Filter --}}
                <form action="{{ route('admin.rekap_bulanan') }}" method="GET" class="d-flex align-items-center bg-white p-1 rounded shadow-sm border">
                    <input type="month" class="form-control border-0 bg-transparent" name="bulan" value="{{ $selectedMonth }}">
                    <button type="submit" class="btn btn-primary btn-sm rounded ms-2">Filter</button>
                </form>

                {{-- Tombol Export --}}
                <a href="{{ route('admin.rekap_bulanan.export_checklist', ['bulan' => $selectedMonth]) }}"
                    class="btn btn-success d-flex align-items-center shadow-sm link-confirm-global"
                    data-title="Download Excel?"
                    data-text="Unduh laporan checklist untuk periode {{ $selectedMonth }}?"
                    data-confirm-text="Ya, Unduh">
                    <i class="bi bi-file-earmark-excel me-2"></i> Export
                </a>
            </div>
        </div>

        <div class="row g-4">
            {{-- Kolom Kiri: Rekap Driver --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Top Driver</h6>
                        <span class="badge bg-light text-muted">Berdasarkan Total KM</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4 py-2">Nama Driver</th>
                                    <th class="text-end py-2">Trip</th>
                                    <th class="text-end pe-4 py-2">Total Jarak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rekapDriver as $nama => $data)
                                    <tr>
                                        <td class="ps-4 fw-medium">{{ $nama }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-secondary bg-opacity-10 text-dark border">{{ $data['jumlah_tugas'] }}</span>
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-primary">{{ number_format($data['total_km']) }} km</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">Belum ada data untuk bulan ini.</td>
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
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-truck text-info me-2"></i>Utilisasi Armada</h6>
                        <span class="badge bg-light text-muted">Berdasarkan Total KM</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4 py-2">Plat Nomor</th>
                                    <th class="text-end py-2">Trip</th>
                                    <th class="text-end pe-4 py-2">Total Jarak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rekapUnit as $plat => $data)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-dark font-monospace">{{ $plat }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-secondary bg-opacity-10 text-dark border">{{ $data['jumlah_tugas'] }}</span>
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-info">{{ number_format($data['total_km']) }} km</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">Belum ada data untuk bulan ini.</td>
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