@extends('admin.layouts.app')

@section('title', 'Dashboard - Riwayat Driver')

@section('content')
    <div class="container-fluid p-0">

        @if (isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- === KARTU FILTER (DIPERBARUI) === --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-funnel-fill"></i> Filter Riwayat Aktivitas</h2>
            </div>
            <div class="card-body">
                {{-- Arahkan form ke route yang benar --}}
                <form action="{{ route('admin.riwayat_driver') }}" method="GET"
                    class="row g-3 align-items-end">

                    {{-- PERUBAHAN 1: Dropdown Driver (Baru) --}}
                    <div class="col-md-4 col-12">
                        <label for="driver_id" class="form-label">Pilih Driver</label>
                        <select class="form-select" id="driver_id" name="driver_id">
                            <option value="">Semua Driver</option>
                            @foreach ($allDrivers as $driver)
                                <option value="{{ $driver->id }}"
                                    {{-- Cek apakah driver ini sedang dipilih --}}
                                    {{ $driver->id == $selectedDriverId ? 'selected' : '' }}>
                                    {{ $driver->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- PERUBAHAN 2: Ubah layout kolom (md-5 -> md-3) --}}
                    <div class="col-md-3 col-12">
                        <label for="start_date" class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3 col-12">
                        <label for="end_date" class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                    </div>

                    {{-- PERUBAHAN 3: Ubah layout kolom (md-2) --}}
                    <div class="col-md-2 col-12 d-grid d-md-block">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
                        <a href="{{ route('admin.riwayat_driver') }}" class="btn btn-secondary ms-md-2 mt-2 mt-md-0" title="Reset"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>
        </div>
        {{-- === AKHIR PERUBAHAN KARTU FILTER === --}}

        {{-- Kartu Riwayat --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h2 class="h5 mb-0 me-3">
                    <i class="bi bi-clock-history"></i> Riwayat Aktivitas
                </h2>
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                    <i class="bi bi-calendar-range"></i> 
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Driver</th>
                                <th>Plat</th>
                                <th class="text-center">Lokasi Masuk</th>
                                <th class="text-end">Speedo Awal</th>
                                <th class="text-end">Speedo Akhir</th>
                                <th class="text-end">Jarak (Km)</th>
                                <th>Total Jam Kerja</th>
                                <th class="text-center">Link Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historyPaginator as $item)
                                <tr>
                                    <td>{{ $item['timestamp_masuk'] }}</td>
                                    <td>{{ $item['timestamp_keluar'] }}</td>
                                    <td>{{ $item['driver_name'] }}</td>
                                    <td><span class="badge bg-secondary">{{ $item['plate_number'] }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ $item['gps_masuk'] }}" target="_blank"
                                            class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-geo-alt-fill"></i> Peta
                                        </a>
                                    </td>
                                    <td class="text-end">{{ $item['speedo_awal'] }} Km</td>
                                    <td class="text-end">{{ $item['speedo_akhir'] }} Km</td>
                                    <td class="text-end"><b>{{ $item['jarak_tempuh'] }} Km</b></td>
                                    <td><b>{{ $item['total_jam_kerja'] }}</b></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ $item['link_selfie'] }}" target="_blank"
                                                class="btn btn-outline-primary btn-sm"><i
                                                    class="bi bi-person-bounding-box"></i> Selfie</a>
                                            <a href="{{ $item['link_speedo_awal'] }}" target="_blank"
                                                class="btn btn-outline-info btn-sm"><i class="bi bi-speedometer"></i>
                                                Awal</a>
                                            <a href="{{ $item['link_speedo_akhir'] }}" target="_blank"
                                                class="btn btn-outline-info btn-sm"><i class="bi bi-speedometer2"></i>
                                                Akhir</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">
                                        Tidak ada riwayat aktivitas
                                        {{-- Pesan jika ada filter driver --}}
                                        @if($selectedDriverId && $allDrivers->find($selectedDriverId))
                                            untuk driver <b>{{ $allDrivers->find($selectedDriverId)->full_name }}</b>
                                        @endif
                                        pada rentang tanggal yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div> @if ($historyPaginator->hasPages())
                    <div class="mt-3">
                        {{-- Pastikan paginasi membawa semua filter --}}
                        {{ $historyPaginator->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection