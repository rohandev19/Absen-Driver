@extends('admin.layouts.app')

@section('title', 'Dashboard - Rekap Bulanan')

@section('content')
    <div class="container-fluid p-0">

        @if (isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-calendar-month"></i> Rekap Bulanan</h2>
            </div>
            <div class="card-body">

                <form action="{{ route('admin.rekap_bulanan') }}" method="GET"
                    class="row g-3 align-items-end mb-3 p-3 bg-light border rounded">
                    <div class="col-md-3 col-12">
                        <label for="bulan" class="form-label">Pilih Bulan & Tahun</label>
                        <input type="month" class="form-control" id="bulan" name="bulan" value="{{ $selectedMonth }}">
                    </div>
                    <div class="col-md-9 col-12 d-grid d-md-block">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i>
                            Tampilkan</button>
                    </div>
                </form>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <h4 class="mb-3">Rekap per Driver</h4>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Driver</th>
                                        <th class="text-end">Jumlah Tugas</th>
                                        <th class="text-end">Total Jarak (Km)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rekapDriver as $nama => $data)
                                        <tr>
                                            <td>{{ $nama }}</td>
                                            <td class="text-end">{{ $data['jumlah_tugas'] }}</td>
                                            <td class="text-end"><b>{{ $data['total_km'] }} Km</b></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada data.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <h4 class="mb-3">Rekap per Unit (Mobil)</h4>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Plat Nomor</th>
                                        <th class="text-end">Jumlah Tugas</th>
                                        <th class="text-end">Total Jarak (Km)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rekapUnit as $plat => $data)
                                        <tr>
                                            <td><span class="badge bg-secondary">{{ $plat }}</span></td>
                                            <td class="text-end">{{ $data['jumlah_tugas'] }}</td>
                                            <td class="text-end"><b>{{ $data['total_km'] }} Km</b></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada data.</td>
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