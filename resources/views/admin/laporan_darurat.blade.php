@extends('admin.layouts.app')

@section('title', 'Dashboard - Laporan Darurat')

@section('content')
    <div class="container-fluid p-0">

        @if (isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning">
                <h2 class="h5 mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Daftar Laporan Masalah Darurat</h2>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>Driver</th>
                                <th>Plat Mobil</th>
                                <th>Deskripsi Masalah</th>
                                <th class="text-center">Lokasi</th>
                                <th class="text-center">Foto Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($laporanMasalah as $laporan)
                                <tr>
                                    <td>{{ $laporan['timestamp'] }}</td>
                                    <td>{{ $laporan['driver_name'] }}</td>
                                    <td><span class="badge bg-secondary">{{ $laporan['plate_number'] }}</span></td>
                                    <td style="white-space: normal;">{{ $laporan['deskripsi'] }}</td>
                                    <td class="text-center">
                                        <a href="{{ $laporan['lokasi_gps'] }}" target="_blank"
                                            class="btn btn-outline-success btn-sm"><i class="bi bi-geo-alt-fill"></i>
                                            Peta</a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ $laporan['link_foto'] }}" target="_blank"
                                            class="btn btn-outline-primary btn-sm"><i class="bi bi-camera-fill"></i>
                                            Foto</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada laporan masalah darurat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection