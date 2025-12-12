@extends('admin.layouts.app')

@section('title', 'Dashboard - Riwayat Driver')

@section('content')
    <div class="container-fluid p-0">

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- === KARTU FILTER === --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-funnel text-primary me-2"></i> Filter Riwayat Aktivitas
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.riwayat_driver') }}" method="GET" class="row g-3 align-items-end">

                    {{-- Filter Driver --}}
                    <div class="col-md-4 col-12">
                        <label for="driver_id" class="form-label text-muted small text-uppercase fw-bold">Pilih
                            Driver</label>
                        <select class="form-select" id="driver_id" name="driver_id">
                            <option value="">Semua Driver</option>
                            @foreach ($allDrivers as $driver)
                                <option value="{{ $driver->id }}" {{ $driver->id == $selectedDriverId ? 'selected' : '' }}>
                                    {{ $driver->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Tanggal --}}
                    <div class="col-md-3 col-12">
                        <label for="start_date" class="form-label text-muted small text-uppercase fw-bold">Dari
                            Tanggal</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3 col-12">
                        <label for="end_date" class="form-label text-muted small text-uppercase fw-bold">Sampai
                            Tanggal</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="col-md-2 col-12 d-grid d-md-block">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>
                            Filter</button>
                        {{-- Reset Button --}}
                        <a href="{{ route('admin.riwayat_driver') }}" class="btn btn-light border w-100 mt-2 mt-md-0"
                            title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Kartu Riwayat --}}
        <div class="card shadow-sm border-0">
            <div
                class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-info me-2"></i> Hasil Pencarian
                    </h5>
                    <small class="text-muted">Menampilkan data perjalanan driver</small>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="bi bi-calendar-range me-1"></i>
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                    {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="py-3 ps-3">Waktu Masuk</th>
                                <th class="py-3">Waktu Keluar</th>
                                <th class="py-3">Driver & Unit</th>
                                <th class="text-center py-3">Lokasi</th>
                                <th class="text-end py-3">Jarak</th>
                                <th class="text-center py-3">Durasi</th>
                                <th class="text-center py-3 pe-3">Dokumentasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historyPaginator as $item)
                                <tr>
                                    <td class="ps-3 fw-medium">{{ $item['timestamp_masuk'] }}</td>
                                    <td class="fw-medium">{{ $item['timestamp_keluar'] }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item['driver_name'] }}</div>
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-dark border">{{ $item['plate_number'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ $item['gps_masuk'] }}" target="_blank"
                                            class="btn btn-sm btn-outline-success" title="Lihat di Peta">
                                            <i class="bi bi-geo-alt-fill"></i>
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold">{{ $item['jarak_tempuh'] }} Km</div>
                                        <small class="text-muted font-monospace d-block">{{ $item['speedo_awal'] }} -
                                            {{ $item['speedo_akhir'] }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">
                                            {{ $item['total_jam_kerja'] }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ $item['link_selfie'] }}" target="_blank"
                                                class="btn btn-outline-secondary" title="Selfie">
                                                <i class="bi bi-person"></i>
                                            </a>
                                            <a href="{{ $item['link_speedo_awal'] }}" target="_blank"
                                                class="btn btn-outline-secondary" title="Speedo Awal">
                                                <i class="bi bi-speedometer"></i>
                                            </a>
                                            <a href="{{ $item['link_speedo_akhir'] }}" target="_blank"
                                                class="btn btn-outline-secondary" title="Speedo Akhir">
                                                <i class="bi bi-speedometer2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                        <p class="mb-0">Tidak ada riwayat perjalanan ditemukan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($historyPaginator->hasPages())
                    <div class="card-footer bg-white py-3">
                        {{ $historyPaginator->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection