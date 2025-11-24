@extends('admin.layouts.app')

@section('title', 'Dashboard - Rekap Harian')

@section('content')
    <div class="container-fluid p-0">

        @if (isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-calendar-day"></i> Rekap Harian</h2>
            </div>
            <div class="card-body">

                <form action="{{ route('admin.rekap_harian') }}" method="GET"
                    class="row g-3 align-items-end mb-3 p-3 bg-light border rounded">
                    <div class="col-md-3 col-12">
                        <label for="tanggal" class="form-label">Pilih Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ $selectedDate }}">
                    </div>
                    <div class="col-md-9 col-12 d-grid d-md-block">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i>
                            Tampilkan</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Driver</th>
                                <th>Plat</th>
                                <th>Total Jam Kerja</th>
                                <th class="text-end">Speedo Awal</th>
                                <th class="text-end">Speedo Akhir</th>
                                <th class="text-end">Jarak (Km)</th>
                                <th class="text-center">Link Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekapData as $item)
                                <tr>
                                    <td>{{ $item['timestamp_masuk'] }}</td>
                                    <td>{{ $item['timestamp_keluar'] }}</td>
                                    <td>{{ $item['driver_name'] }}</td>
                                    <td><span class="badge bg-secondary">{{ $item['plate_number'] }}</span></td>
                                    <td><b>{{ $item['total_jam_kerja'] }}</b></td>
                                    <td class="text-end">{{ $item['speedo_awal'] }} Km</td>
                                    <td class="text-end">{{ $item['speedo_akhir'] }} Km</td>
                                    <td class="text-end"><b>{{ $item['jarak_tempuh'] }} Km</b></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ $item['link_selfie'] }}" target="_blank"
                                                class="btn btn-outline-primary btn-sm"><i
                                                    class="bi bi-person-bounding-box"></i></a>
                                            <a href="{{ $item['link_speedo_awal'] }}" target="_blank"
                                                class="btn btn-outline-info btn-sm"><i class="bi bi-speedometer"></i></a>
                                            <a href="{{ $item['link_speedo_akhir'] }}" target="_blank"
                                                class="btn btn-outline-info btn-sm"><i class="bi bi-speedometer2"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada aktivitas selesai pada tanggal
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