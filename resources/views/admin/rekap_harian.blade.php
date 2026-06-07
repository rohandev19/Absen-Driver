@extends('admin.layouts.app')

@section('title', 'Dashboard - Rekap Harian')

@section('content')
    <div class="container-fluid p-0">
        {{-- PRINT HEADER --}}
        <div class="d-none d-print-block text-center mb-4">
            <h2 class="fw-bold mb-1" style="color: #000; font-size: 24px; text-transform: uppercase;">PT HAMADA LOGISTIK</h2>
            <p class="mb-0" style="font-size: 14px; color: #000; border-bottom: 2px solid #000; padding-bottom: 10px;">Laporan Rekap Harian Driver - Tanggal: {{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger shadow-sm border-0">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-calendar-check text-success me-2"></i> Rekap
                            Harian</h5>
                        <small class="text-muted">Laporan aktivitas driver per tanggal</small>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <form action="{{ route('admin.rekap_harian') }}" method="GET"
                            class="d-flex gap-2 align-items-center bg-light p-2 rounded border mb-0">
                            <label for="tanggal"
                                class="form-label mb-0 text-muted small me-1 fw-bold text-uppercase">Tanggal:</label>
                            <input type="date" class="form-control border-0 bg-transparent py-0" id="tanggal" name="tanggal"
                                value="{{ $selectedDate }}">
                            <button type="submit" class="btn btn-sm btn-success px-3 rounded-pill">Tampilkan</button>
                        </form>
                        <button onclick="window.print()" class="btn btn-primary btn-sm px-3 rounded-pill d-print-none shadow-sm">
                            <i class="bi bi-printer me-1"></i> Cetak
                        </button>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm px-3 rounded-pill d-print-none shadow-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive table-responsive-cards">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="py-3 ps-4">Waktu Kerja</th>
                                <th class="py-3">Driver & Unit</th>
                                <th class="py-3 text-center">Durasi</th>
                                <th class="py-3 text-end">Jarak Tempuh</th>
                                <th class="py-3 text-end">Odometer</th>
                                <th class="py-3 text-center pe-4 d-print-none">Dokumentasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekapData as $item)
                                <tr class="aset-row">
                                    <td class="ps-4" data-label="Waktu Kerja">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success"
                                                title="Masuk">
                                                {{ \Carbon\Carbon::parse($item['timestamp_masuk'])->format('H:i') }}
                                            </span>
                                            <i class="bi bi-arrow-right text-muted small"></i>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"
                                                title="Keluar">
                                                {{ \Carbon\Carbon::parse($item['timestamp_keluar'])->format('H:i') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td data-label="Driver & Unit">
                                        <div class="fw-bold text-dark">{{ $item['driver_name'] }}</div>
                                        <span class="small text-muted font-monospace"><i
                                                class="bi bi-truck me-1"></i>{{ $item['plate_number'] }}</span>
                                    </td>
                                    <td class="text-center" data-label="Durasi">
                                        <span class="badge bg-light text-dark border rounded-pill px-3">
                                            <i class="bi bi-stopwatch me-1"></i> {{ $item['total_jam_kerja'] }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-primary" data-label="Jarak Tempuh">{{ $item['jarak_tempuh'] }}</td>
                                    <td class="text-end small text-muted font-monospace" data-label="Odometer">
                                        {{ $item['speedo_awal'] }} <i class="bi bi-arrow-right mx-1"></i>
                                        {{ $item['speedo_akhir'] }}
                                    </td>
                                    <td class="text-center pe-4 d-print-none" data-label="Dokumentasi">
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
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x display-6 d-block mb-3 opacity-25"></i>
                                        <p class="mb-0">Tidak ada aktivitas pada tanggal
                                            <b>{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</b>.</p>
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