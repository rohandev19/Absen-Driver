@extends('admin.layouts.app')

@section('title', 'Dashboard - Riwayat Unit')

@section('content')
    <div class="container-fluid p-0">

        @if (isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.riwayat_unit') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4 col-12">
                        <label for="plate_number" class="form-label">Filter Berdasarkan Plat Nomor</label>
                        <input type="text" class="form-control" id="plate_number" name="plate_number"
                            value="{{ $filterPlat ?? '' }}" placeholder="Contoh: B 1234 ABC">
                    </div>
                    <div class="col-md-8 col-12 d-grid d-md-block">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Tampilkan
                            Riwayat</button>
                        <a href="{{ route('admin.riwayat_unit') }}" class="btn btn-secondary ms-md-2 mt-2 mt-md-0"><i
                                class="bi bi-arrow-counterclockwise"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-card-checklist"></i> Riwayat Checklist Selesai Tugas</h2>

                {{-- === PERUBAHAN BARU: TAMPILKAN PLAT YANG DIFILTER === --}}
                @if (!empty($filterPlat))
                    <span class="badge bg-primary fs-6">
                        Menampilkan Riwayat: {{ $filterPlat }}
                    </span>
                @endif
                {{-- === AKHIR PERUBAHAN === --}}

            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tgl Selesai</th>
                                <th>Driver</th>
                                <th>Plat</th>
                                <th class="text-end">Speedo Akhir</th>
                                <th class="text-center">Foto Speedo</th>
                                <th>Ban</th>
                                <th>Lampu</th>
                                <th>Rem</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($checklistPaginator as $item)
                                <tr>
                                    <td>{{ $item['timestamp_keluar'] }}</td>
                                    <td>{{ $item['driver_name'] }}</td>
                                    <td><span class="badge bg-secondary">{{ $item['plate_number'] }}</span></td>
                                    <td class="text-end">{{ $item['speedo_akhir'] }} Km</td>
                                    <td class="text-center">
                                        <a href="{{ $item['link_speedo_akhir'] }}" target="_blank"
                                            class="btn btn-outline-info btn-sm"><i class="bi bi-speedometer2"></i> Foto</a>
                                    </td>
                                    <td><span
                                            class="badge {{ $item['cek_ban'] == 'Aman' ? 'bg-success' : 'bg-danger' }}">{{ $item['cek_ban'] }}</span>
                                    </td>
                                    <td><span
                                            class="badge {{ $item['cek_lampu'] == 'Aman' ? 'bg-success' : 'bg-danger' }}">{{ $item['cek_lampu'] }}</span>
                                    </td>
                                    <td><span
                                            class="badge {{ $item['cek_rem'] == 'Aman' ? 'bg-success' : 'bg-danger' }}">{{ $item['cek_rem'] }}</span>
                                    </td>
                                    <td style="white-space: normal;">{{ $item['catatan'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada riwayat checklist
                                        {!! !empty($filterPlat) ? 'untuk plat <b>' . e($filterPlat) . '</b>' : '' !!}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div> @if ($checklistPaginator->hasPages())
                    <div class="mt-3">
                        {{-- Pastikan paginasi tetap membawa filter plat --}}
                        {{ $checklistPaginator->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection