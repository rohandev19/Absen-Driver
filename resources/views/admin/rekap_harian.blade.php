@extends('admin.layouts.app')

@section('title', 'Dashboard - Rekap Harian')

@section('content')
    <div class="container-fluid p-0">

        @if (isset($error))
            <div class="alert alert-danger shadow-sm border-0">{{ $error }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-calendar-day text-info me-2"></i> Rekap Harian
                        </h5>
                        <small class="text-muted">Laporan aktivitas per tanggal</small>
                    </div>

                    <form action="{{ route('admin.rekap_harian') }}" method="GET" class="d-flex gap-2 align-items-center">
                        <label for="tanggal" class="form-label mb-0 text-muted small me-1">Tanggal:</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ $selectedDate }}">
                        <button type="submit" class="btn btn-info text-white px-4">Filter</button>
                    </form>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4">Waktu</th>
                                <th class="py-3">Driver & Unit</th>
                                <th class="py-3">Durasi Kerja</th>
                                <th class="py-3 text-end">Jarak Tempuh</th>
                                <th class="py-3 text-end">Speedo (Awal - Akhir)</th>
                                <th class="py-3 text-center pe-4">Dokumentasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekapData as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-success"><i class="bi bi-box-arrow-in-right me-1"></i>
                                                {{ \Carbon\Carbon::parse($item['timestamp_masuk'])->format('H:i') }}</span>
                                            <span class="fw-bold text-danger"><i class="bi bi-box-arrow-left me-1"></i>
                                                {{ \Carbon\Carbon::parse($item['timestamp_keluar'])->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item['driver_name'] }}</div>
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-dark border">{{ $item['plate_number'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                            <i class="bi bi-stopwatch me-1"></i> {{ $item['total_jam_kerja'] }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold fs-6">{{ $item['jarak_tempuh'] }}</td>
                                    <td class="text-end small text-muted font-monospace">
                                        {{ $item['speedo_awal'] }} - {{ $item['speedo_akhir'] }} Km
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group" role="group">
                                            <a href="{{ $item['link_selfie'] }}" target="_blank"
                                                class="btn btn-sm btn-outline-secondary" title="Selfie"><i
                                                    class="bi bi-person"></i></a>
                                            <a href="{{ $item['link_speedo_awal'] }}" target="_blank"
                                                class="btn btn-sm btn-outline-secondary" title="Speedo Awal"><i
                                                    class="bi bi-speedometer"></i></a>
                                            <a href="{{ $item['link_speedo_akhir'] }}" target="_blank"
                                                class="btn btn-sm btn-outline-secondary" title="Speedo Akhir"><i
                                                    class="bi bi-speedometer2"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x display-6 d-block mb-2 opacity-25"></i>
                                        Tidak ada aktivitas selesai pada tanggal
                                        {{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}.
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