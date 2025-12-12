@extends('admin.layouts.app')

@section('title', 'Dashboard - Riwayat Unit')

@section('content')
    <div class="container-fluid p-0">

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter Section --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('admin.riwayat_unit') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4 col-12">
                        <label for="plate_number" class="form-label text-muted small text-uppercase fw-bold">Cari Plat
                            Nomor</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0" id="plate_number" name="plate_number"
                                value="{{ $filterPlat ?? '' }}" placeholder="Contoh: B 1234 ABC">
                        </div>
                    </div>
                    <div class="col-md-8 col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">Tampilkan</button>
                        @if(!empty($filterPlat))
                            <a href="{{ route('admin.riwayat_unit') }}" class="btn btn-light border">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-card-checklist text-success me-2"></i> Riwayat
                        Checklist Fisik</h5>
                    <small class="text-muted">Kondisi kendaraan pasca-operasional</small>
                </div>

                @if (!empty($filterPlat))
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill border border-primary">
                        <i class="bi bi-funnel-fill me-1"></i> Filter: {{ $filterPlat }}
                    </span>
                @endif
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4 py-3">Waktu Selesai</th>
                                <th class="py-3">Driver & Unit</th>
                                <th class="text-end py-3">Odometer Akhir</th>
                                <th class="text-center py-3">Kondisi Fisik</th>
                                <th class="py-3 w-25">Catatan Driver</th>
                                <th class="text-center py-3 pe-4">Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($checklistPaginator as $item)
                                <tr>
                                    <td class="ps-4 text-nowrap">{{ $item['timestamp_keluar'] }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item['driver_name'] }}</div>
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-dark border">{{ $item['plate_number'] }}</span>
                                    </td>
                                    <td class="text-end fw-bold font-monospace">{{ number_format($item['speedo_akhir']) }} Km
                                    </td>

                                    {{-- Status Fisik Gabungan --}}
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <span
                                                class="badge rounded-pill {{ $item['cek_ban'] == 'Aman' ? 'bg-success' : 'bg-danger' }}"
                                                title="Kondisi Ban">
                                                Ban
                                            </span>
                                            <span
                                                class="badge rounded-pill {{ $item['cek_lampu'] == 'Aman' ? 'bg-success' : 'bg-danger' }}"
                                                title="Kondisi Lampu">
                                                Lampu
                                            </span>
                                            <span
                                                class="badge rounded-pill {{ $item['cek_rem'] == 'Aman' ? 'bg-success' : 'bg-danger' }}"
                                                title="Kondisi Rem">
                                                Rem
                                            </span>
                                        </div>
                                    </td>

                                    <td class="text-muted small fst-italic">
                                        {{ $item['catatan'] ?: '-' }}
                                    </td>

                                    <td class="text-center pe-4">
                                        <a href="{{ $item['link_speedo_akhir'] }}" target="_blank"
                                            class="btn btn-sm btn-outline-info rounded-pill px-3">
                                            <i class="bi bi-image me-1"></i> Foto
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-clipboard-check display-6 d-block mb-2 opacity-25"></i>
                                        Tidak ada data checklist ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($checklistPaginator->hasPages())
                    <div class="card-footer bg-white py-3">
                        {{ $checklistPaginator->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection