@extends('admin.layouts.app')

@section('title', 'Dashboard - Laporan Darurat')

@section('content')
    <div class="container-fluid p-0">

        @if (session('error'))
            <div class="alert alert-danger shadow-sm border-0">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Laporan Insiden Darurat</h5>
                <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill">{{ count($laporanMasalah) }}
                    Kasus</span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4 py-3">Waktu Kejadian</th>
                                <th class="py-3">Pelapor</th>
                                <th class="py-3">Kendaraan</th>
                                <th class="py-3 w-50">Deskripsi Masalah</th>
                                <th class="text-center py-3 pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($laporanMasalah as $laporan)
                                <tr>
                                    <td class="ps-4 fw-medium text-nowrap">{{ $laporan['timestamp'] }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $laporan['driver_name'] }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $laporan['plate_number'] }}</span>
                                    </td>
                                    <td class="text-muted">
                                        {{ $laporan['deskripsi'] }}
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ $laporan['lokasi_gps'] }}" target="_blank"
                                                class="btn btn-sm btn-outline-danger" title="Lihat Lokasi">
                                                <i class="bi bi-geo-alt-fill"></i> Map
                                            </a>
                                            <a href="{{ $laporan['link_foto'] }}" target="_blank"
                                                class="btn btn-sm btn-outline-dark" title="Lihat Bukti Foto">
                                                <i class="bi bi-image"></i> Foto
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-shield-check display-4 d-block mb-3 opacity-25 text-success"></i>
                                        <h5 class="text-success">Aman</h5>
                                        <p class="mb-0">Tidak ada laporan darurat yang masuk.</p>
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