@extends('admin.layouts.app')

@section('title', 'Dashboard - Laporan Darurat')

@section('content')
    <style>
        /* === VARIABLES & RESET === */
        :root {
            --font-size-base: 14px;
            --border-radius-comfort: 8px;
            --transition-smooth: 0.2s ease-in-out;
        }

        body {
            font-size: var(--font-size-base);
        }

        /* === 1. METRIC CARD === */
        .card-metric {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius-comfort);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 5px solid #dc3545;
            /* Merah Darurat */
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        /* === 2. CORPORATE TABLE === */
        .table-corporate {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .table-corporate thead th {
            background-color: #fef2f2;
            color: #991b1b;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #fee2e2;
            border-top: 1px solid #fee2e2;
            padding: 16px;
        }

        .table-corporate tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
            color: #334155;
        }

        .table-corporate tbody tr:hover {
            background-color: #fff1f2;
        }

        /* === 3. BUTTONS === */
        .btn-action {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
            width: 100%;
            justify-content: center;
        }

        .btn-action-map {
            background-color: #fff;
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        .btn-action-map:hover {
            background-color: #dc3545;
            color: white;
        }

        .btn-action-photo {
            background-color: #fff;
            border: 1px solid #64748b;
            color: #64748b;
        }

        .btn-action-photo:hover {
            background-color: #64748b;
            color: white;
        }

        /* === 4. MOBILE RESPONSIVE (FIXED LAYOUT) === */
        @media (max-width: 768px) {
            .table-corporate thead {
                display: none;
            }

            .table-corporate,
            .table-corporate tbody,
            .table-corporate tr,
            .table-corporate td {
                display: block;
                width: 100%;
            }

            /* Style Kartu */
            .table-corporate tbody tr {
                margin-bottom: 20px;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                overflow: hidden;
            }

            .table-corporate td {
                padding: 12px 20px;
                text-align: left;
                border-bottom: 1px solid #f8fafc;
            }

            /* FIX: Label sekarang di ATAS data (Block), bukan di samping */
            .table-corporate td::before {
                content: attr(data-label);
                display: block;
                /* Memaksa ganti baris */
                font-size: 0.7rem;
                font-weight: 800;
                color: #9ca3af;
                text-transform: uppercase;
                margin-bottom: 4px;
                letter-spacing: 0.5px;
            }

            /* Sembunyikan label kosong */
            .table-corporate td[data-label=""]::before {
                display: none;
            }

            /* Header Kartu (Waktu) - Dibuat Merah Muda */
            .table-corporate td:first-child {
                background-color: #fef2f2;
                border-bottom: 1px solid #fee2e2;
                padding: 15px 20px;
            }

            .table-corporate td:first-child::before {
                display: none;
            }

            /* Label tidak perlu untuk header */

            /* Footer Kartu (Tombol Aksi) */
            .table-corporate td:last-child {
                background-color: #f9fafb;
                padding: 15px 20px;
                border-top: 1px solid #e5e7eb;
            }

            /* Layout Tombol Sejajar */
            .action-wrapper {
                display: flex;
                gap: 10px;
            }
        }
    </style>

    <div class="container-fluid p-0">

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Laporan Insiden</h3>
                <p class="text-muted mb-0 small">Daftar laporan darurat yang dikirimkan oleh driver.</p>
            </div>

            {{-- SUMMARY CARD --}}
            <div class="d-none d-md-block">
                <div class="card-metric py-2 px-4">
                    <div>
                        <div class="text-uppercase text-muted small fw-bold" style="font-size: 0.7rem;">Total Kasus</div>
                        <div class="fs-4 fw-bold text-danger">{{ count($laporanMasalah) }}</div>
                    </div>
                    <i class="bi bi-shield-exclamation text-danger opacity-25 fs-2 ms-3"></i>
                </div>
            </div>
        </div>

        {{-- TABLE DATA --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center">
                    <span class="bg-danger text-white rounded p-1 me-2 d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;">
                        <i class="bi bi-megaphone-fill"></i>
                    </span>
                    <h6 class="mb-0 fw-bold text-dark">Log Laporan Masuk</h6>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-corporate">
                        <thead>
                            <tr>
                                <th class="ps-4">Waktu Kejadian</th>
                                <th>Pelapor</th>
                                <th>Kendaraan</th>
                                <th style="width: 40%;">Deskripsi Masalah</th>
                                <th class="text-center pe-4">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($laporanMasalah as $laporan)
                                <tr>
                                    {{-- 1. WAKTU --}}
                                    <td class="ps-4" data-label="Waktu">
                                        <div class="d-flex align-items-center text-danger">
                                            <i class="bi bi-calendar-event me-2"></i>
                                            <span
                                                class="fw-bold">{{ \Carbon\Carbon::parse($laporan['timestamp'])->format('d M Y, H:i') }}</span>
                                        </div>
                                    </td>

                                    {{-- 2. PELAPOR --}}
                                    <td data-label="Pelapor">
                                        <div class="fw-bold text-dark fs-6">{{ $laporan['driver_name'] }}</div>
                                    </td>

                                    {{-- 3. KENDARAAN --}}
                                    <td data-label="Kendaraan">
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1 fs-6">
                                            {{ $laporan['plate_number'] }}
                                        </span>
                                    </td>

                                    {{-- 4. DESKRIPSI --}}
                                    <td data-label="Deskripsi">
                                        <div class="bg-light p-3 rounded border border-light text-muted small fst-italic">
                                            "{{ $laporan['deskripsi'] }}"
                                        </div>
                                    </td>

                                    {{-- 5. AKSI --}}
                                    <td class="text-center pe-4" data-label="">
                                        <div class="action-wrapper">
                                            <a href="{{ $laporan['lokasi_gps'] }}" target="_blank"
                                                class="btn-action btn-action-map" title="Lihat Lokasi">
                                                <i class="bi bi-geo-alt-fill"></i> Lokasi
                                            </a>
                                            <a href="{{ $laporan['link_foto'] }}" target="_blank"
                                                class="btn-action btn-action-photo" title="Lihat Bukti Foto">
                                                <i class="bi bi-image"></i> Foto
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="py-4 opacity-50">
                                            <i class="bi bi-shield-check display-1 text-success mb-3"></i>
                                            <h5 class="text-dark fw-bold">Aman</h5>
                                            <p class="text-muted">Tidak ada laporan darurat yang masuk.</p>
                                        </div>
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