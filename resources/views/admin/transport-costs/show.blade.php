@extends('admin.layouts.app')

@section('title', 'Detail Uang Jalan')

@push('styles')
<style>
.receipt-thumbnail-container {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}
.receipt-thumbnail-container:hover {
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}
.receipt-thumbnail-container:hover .thumbnail-hover {
    opacity: 1 !important;
}
.receipt-thumbnail-container:hover .receipt-thumbnail {
    transform: scale(1.03);
}
.receipt-thumbnail {
    max-height: 180px; 
    width: 100%;
    object-fit: cover; 
    transition: all 0.3s ease;
}
.thumbnail-hover {
    transition: all 0.3s ease;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- PRINT LAYOUT (Hanya Tampil Saat Dicetak) --}}
    <div class="d-none d-print-block w-100" style="color: #000 !important; background: #fff !important; font-family: Arial, sans-serif;">
        {{-- Kop Surat --}}
        <div style="text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px;">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0; text-transform: uppercase;">PT HAMADA LOGISTIK</h1>
            <p style="font-size: 14px; margin: 0;">Slip Rincian Uang Jalan & Pengeluaran</p>
        </div>

        {{-- Info Dasar --}}
        <table style="width: 100%; margin-bottom: 20px; font-size: 12px; border-collapse: collapse;">
            <tr>
                <td style="width: 15%; font-weight: bold; padding: 3px 0;">Tanggal</td>
                <td style="width: 35%; padding: 3px 0;">: {{ $trip->trip_date->format('d-m-Y') }}</td>
                <td style="width: 15%; font-weight: bold; padding: 3px 0;">Status</td>
                <td style="width: 35%; padding: 3px 0;">: {{ ucfirst($trip->approval_status) }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 3px 0;">Nama Driver</td>
                <td style="padding: 3px 0;">: {{ $trip->driver->full_name }}</td>
                <td style="font-weight: bold; padding: 3px 0;">Project</td>
                <td style="padding: 3px 0;">: {{ $trip->project->name }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 3px 0;">Plat Nomor</td>
                <td style="padding: 3px 0;">: {{ $trip->vehicle->plate_number }}</td>
                <td style="font-weight: bold; padding: 3px 0;">DO Number</td>
                <td style="padding: 3px 0;">: {{ $trip->do_number }}</td>
            </tr>
        </table>

        {{-- Odometer & Efisiensi --}}
        <table style="width: 100%; margin-bottom: 20px; font-size: 12px; border-collapse: collapse; border: 1px solid #000;">
            <thead>
                <tr>
                    <th colspan="4" style="background: #f0f0f0; border-bottom: 1px solid #000; padding: 5px; text-align: left;">Data Perjalanan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd; width: 25%;">Odometer Awal</td>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd; width: 25%;">{{ number_format($trip->odometer_start, 0, ',', '.') }} KM</td>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd; width: 25%;">Bensin Terpakai</td>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd; width: 25%;">{{ $trip->fuel_consumed ? number_format($trip->fuel_consumed, 2) . ' L' : '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px; width: 25%;">Odometer Akhir</td>
                    <td style="padding: 5px; width: 25%;">{{ number_format($trip->odometer_end, 0, ',', '.') }} KM</td>
                    <td style="padding: 5px; width: 25%;">Efisiensi BBM</td>
                    <td style="padding: 5px; width: 25%;">{{ $trip->fuel_efficiency_ratio ? number_format($trip->fuel_efficiency_ratio, 2) . ' KM/L' : '-' }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Rincian Biaya --}}
        <table style="width: 100%; margin-bottom: 20px; font-size: 12px; border-collapse: collapse; border: 1px solid #000;">
            <thead>
                <tr>
                    <th colspan="2" style="background: #f0f0f0; border-bottom: 1px solid #000; padding: 5px; text-align: left;">Rincian Pengeluaran (Reimburse)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd;">Biaya Bensin</td>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd; text-align: right;">Rp {{ number_format($trip->gasoline_cost, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd;">Biaya Tol</td>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd; text-align: right;">Rp {{ number_format($trip->toll_cost, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd;">Biaya Parkir</td>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd; text-align: right;">Rp {{ number_format($trip->parking_cost, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th style="padding: 5px; text-align: left;">Subtotal Pengeluaran</th>
                    <th style="padding: 5px; text-align: right;">Rp {{ number_format($trip->total_cost, 0, ',', '.') }}</th>
                </tr>
            </tbody>
        </table>

        {{-- Lembur & Bonus --}}
        <table style="width: 100%; margin-bottom: 20px; font-size: 12px; border-collapse: collapse; border: 1px solid #000;">
            <thead>
                <tr>
                    <th colspan="2" style="background: #f0f0f0; border-bottom: 1px solid #000; padding: 5px; text-align: left;">Rincian Lembur & Bonus</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd;">Waktu Kerja</td>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd; text-align: right;">{{ $trip->delivery_start_time->format('H:i') }} - {{ $trip->delivery_end_time->format('H:i') }} ({{ number_format($trip->actual_delivery_hours, 2) }} jam)</td>
                </tr>
                <tr>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd;">Uang Lembur ({{ number_format($trip->overtime_hours, 2) }} jam)</td>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd; text-align: right;">Rp {{ number_format($trip->overtime_payment, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd;">Bonus Driver</td>
                    <td style="padding: 5px; border-bottom: 1px solid #ddd; text-align: right;">Rp {{ number_format($trip->bonus_driver, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th style="padding: 5px; text-align: left;">Subtotal Lembur & Bonus</th>
                    <th style="padding: 5px; text-align: right;">Rp {{ number_format($trip->overtime_payment + $trip->bonus_driver, 0, ',', '.') }}</th>
                </tr>
            </tbody>
        </table>

        {{-- Grand Total --}}
        <div style="text-align: right; margin-bottom: 40px; border: 2px solid #000; padding: 10px;">
            <h3 style="margin: 0; font-size: 16px;">GRAND TOTAL: Rp {{ number_format($trip->total_cost + $trip->overtime_payment + $trip->bonus_driver, 0, ',', '.') }}</h3>
        </div>

        {{-- Tanda Tangan --}}
        <table style="width: 100%; text-align: center; font-size: 12px; page-break-inside: avoid;">
            <tr>
                <td style="width: 33%;">
                    <p style="margin-bottom: 60px;">Dibuat Oleh (Driver),</p>
                    <p style="font-weight: bold; text-decoration: underline; margin: 0;">{{ $trip->driver->full_name }}</p>
                </td>
                <td style="width: 33%;">
                    <p style="margin-bottom: 60px;">Diperiksa Oleh (Admin),</p>
                    <p style="font-weight: bold; text-decoration: underline; margin: 0;">{{ $trip->approver->name ?? '(.........................)' }}</p>
                </td>
                <td style="width: 33%;">
                    <p style="margin-bottom: 60px;">Disetujui Oleh (Finance),</p>
                    <p style="font-weight: bold; text-decoration: underline; margin: 0;">(.........................)</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- WEB UI --}}
    <div class="d-print-none">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-cash-coin"></i> Detail Uang Jalan</h2>
        <a href="{{ route('admin.transport-costs.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Status & Actions --}}
    {{-- Status & Actions --}}
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="mb-1">Status Persetujuan: 
                        @php
                            $statusBadge = match($trip->approval_status) {
                                'pending' => 'bg-warning text-dark',
                                'approved' => 'bg-success',
                                'rejected' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            $statusText = match($trip->approval_status) {
                                'pending' => 'Pending',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => $trip->approval_status
                            };
                        @endphp
                        <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                        
                        @if($trip->approval_status == 'approved')
                            @if($trip->submitted_to_finance)
                                <span class="badge bg-info text-white ms-0 ms-md-2 mt-2 mt-md-0">
                                    <i class="bi bi-send-check"></i> Diajukan ke Finance
                                </span>
                            @else
                                <span class="badge bg-secondary text-white ms-0 ms-md-2 mt-2 mt-md-0">
                                    <i class="bi bi-clock"></i> Belum Diajukan ke Finance
                                </span>
                            @endif
                        @endif
                    </h5>
                    
                    @if($trip->approval_status == 'approved')
                        <div class="mt-1">
                            <small class="text-muted d-block">
                                <i class="bi bi-person-check"></i> Disetujui oleh <strong>{{ $trip->approver->name ?? 'N/A' }}</strong> pada {{ $trip->approved_at->format('d-m-Y H:i') }}
                            </small>
                            @if($trip->submitted_to_finance)
                                <small class="text-info d-block">
                                    <i class="bi bi-send"></i> Diajukan ke Finance oleh <strong>{{ $trip->financeSubmitter->name ?? 'N/A' }}</strong> pada {{ $trip->submitted_to_finance_at->format('d-m-Y H:i') }}
                                </small>
                            @endif
                        </div>
                    @elseif($trip->approval_status == 'rejected')
                        <small class="text-danger d-block mt-1">
                            <i class="bi bi-person-x"></i> Ditolak oleh <strong>{{ $trip->approver->name ?? 'N/A' }}</strong> pada {{ $trip->approved_at->format('d-m-Y H:i') }}
                        </small>
                    @endif
                </div>
                
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" onclick="window.print()" class="btn btn-secondary text-white d-print-none shadow-sm">
                        <i class="bi bi-printer"></i> Cetak Slip
                    </button>
                    @if($trip->approval_status == 'pending')
                        <button type="button" class="btn btn-success" 
                                data-action="approve"
                                data-url="{{ route('admin.transport-costs.approve', $trip->id) }}"
                                data-confirm="Apakah Anda yakin ingin menyetujui laporan ini?">
                            <i class="bi bi-check-circle"></i> Setujui
                        </button>
                        <button type="button" class="btn btn-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle"></i> Tolak
                        </button>
                    @elseif($trip->approval_status == 'approved')
                        @if(!$trip->submitted_to_finance)
                            <button type="button" class="btn btn-primary" 
                                    data-action="submit-finance"
                                    data-url="{{ route('admin.transport-costs.submit_to_finance', $trip->id) }}"
                                    data-confirm="Apakah Anda yakin ingin mengajukan laporan uang jalan ini ke finance?">
                                <i class="bi bi-send"></i> Ajukan ke Finance
                            </button>
                        @endif
                        <a href="{{ route('admin.transport-costs.export_finance', $trip->id) }}" class="btn btn-info text-white">
                            <i class="bi bi-file-earmark-word"></i> Download Form Finance (Word)
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Trip Information --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Trip</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Tanggal</th>
                                <td>{{ $trip->trip_date->format('d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <th>Driver</th>
                                <td>{{ $trip->driver->full_name }}</td>
                            </tr>
                            <tr>
                                <th>Plat Nomor</th>
                                <td><span class="badge bg-secondary">{{ $trip->vehicle->plate_number }}</span></td>
                            </tr>
                            <tr>
                                <th>Project</th>
                                <td>{{ $trip->project->name }}</td>
                            </tr>
                            <tr>
                                <th>DO Number</th>
                                <td>{{ $trip->do_number }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Drop Point</th>
                                <td>{{ $trip->drop_point_count }} titik</td>
                            </tr>
                            <tr>
                                <th>Lokasi Pengiriman</th>
                                <td>{{ $trip->delivery_location }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Odometer & Efficiency --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-speedometer"></i> Odometer & Efisiensi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Odometer Awal</th>
                                <td>{{ number_format($trip->odometer_start, 0, ',', '.') }} KM</td>
                            </tr>
                            <tr>
                                <th>Odometer Akhir</th>
                                <td>{{ number_format($trip->odometer_end, 0, ',', '.') }} KM</td>
                            </tr>
                            <tr>
                                <th>Jarak Tempuh</th>
                                <td><strong>{{ number_format($trip->odometer_difference, 0, ',', '.') }} KM</strong></td>
                            </tr>
                            <tr>
                                <th>Bensin Terpakai</th>
                                <td>{{ $trip->fuel_consumed ? number_format($trip->fuel_consumed, 2) . ' Liter' : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Efisiensi BBM</th>
                                <td>
                                    @if($trip->fuel_efficiency_ratio)
                                        <span class="badge bg-success">{{ number_format($trip->fuel_efficiency_ratio, 2) }} KM/L</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Cost Breakdown --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-wallet2"></i> Rincian Biaya</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Biaya Bensin</th>
                                <td class="text-end">Rp {{ number_format($trip->gasoline_cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Biaya Tol</th>
                                <td class="text-end">Rp {{ number_format($trip->toll_cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Biaya Parkir</th>
                                <td class="text-end">Rp {{ number_format($trip->parking_cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-active">
                                <th>Total Biaya</th>
                                <th class="text-end">Rp {{ number_format($trip->total_cost, 0, ',', '.') }}</th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Overtime & Bonus --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-gift"></i> Lembur & Bonus</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Waktu Mulai</th>
                                <td>{{ $trip->delivery_start_time->format('d-m-Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Waktu Selesai</th>
                                <td>{{ $trip->delivery_end_time->format('d-m-Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Durasi Kerja</th>
                                <td>{{ number_format($trip->actual_delivery_hours, 2) }} jam</td>
                            </tr>
                            <tr>
                                <th>Jam Lembur</th>
                                <td>{{ number_format($trip->overtime_hours, 2) }} jam</td>
                            </tr>
                            <tr>
                                <th>Bayaran Lembur</th>
                                <td class="text-end">Rp {{ number_format($trip->overtime_payment, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-success">
                                <th>Bonus Driver</th>
                                <th class="text-end">Rp {{ number_format($trip->bonus_driver, 0, ',', '.') }}</th>
                            </tr>
                        </table>
                    </div>
                    @if($trip->bonus_notes)
                        <div class="alert alert-info mt-2 mb-0">
                            <small><strong>Rincian Bonus:</strong><br>{{ nl2br($trip->bonus_notes) }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Grand Total --}}
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body bg-light">
            <div class="row align-items-center">
                <div class="col-md-8 text-center text-md-start">
                    <h4 class="mb-0 fw-bold">Grand Total (Biaya + Lembur)</h4>
                </div>
                <div class="col-md-4 text-center text-md-end mt-2 mt-md-0">
                    <h3 class="mb-0 text-primary fw-bold">
                        Rp {{ number_format($trip->total_cost + $trip->overtime_payment + $trip->bonus_driver, 0, ',', '.') }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Expense Receipts Gallery --}}
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex align-items-center">
            <h5 class="mb-0"><i class="bi bi-images"></i> Lampiran Kuitansi / Bukti Pengeluaran</h5>
        </div>
        <div class="card-body bg-light bg-opacity-50">
            <div class="row g-3">
                {{-- Gasoline Receipt --}}
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                            <h6 class="fw-bold mb-2 text-primary"><i class="bi bi-fuel-pump"></i> Kuitansi SPBU / BBM</h6>
                            @if($trip->gasoline_receipt_path)
                                <div class="receipt-thumbnail-container">
                                    <img src="{{ asset('storage/' . $trip->gasoline_receipt_path) }}" 
                                         alt="Kuitansi SPBU" 
                                         class="receipt-thumbnail">
                                    <div class="position-absolute top-50 start-50 translate-middle w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 opacity-0 thumbnail-hover">
                                        <i class="bi bi-zoom-in text-white fs-3"></i>
                                    </div>
                                </div>
                            @else
                                <div class="py-4 text-muted">
                                    <i class="bi bi-file-earmark-x fs-1 opacity-50 d-block mb-2"></i>
                                    <small class="text-muted italic">Tidak ada kuitansi diunggah</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Toll Receipt --}}
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                            <h6 class="fw-bold mb-2 text-info"><i class="bi bi-credit-card-2-front"></i> Bukti Pembayaran Tol</h6>
                            @if($trip->toll_receipt_path)
                                <div class="receipt-thumbnail-container">
                                    <img src="{{ asset('storage/' . $trip->toll_receipt_path) }}" 
                                         alt="Kuitansi Tol" 
                                         class="receipt-thumbnail">
                                    <div class="position-absolute top-50 start-50 translate-middle w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 opacity-0 thumbnail-hover">
                                        <i class="bi bi-zoom-in text-white fs-3"></i>
                                    </div>
                                </div>
                            @else
                                <div class="py-4 text-muted">
                                    <i class="bi bi-file-earmark-x fs-1 opacity-50 d-block mb-2"></i>
                                    <small class="text-muted italic">Tidak ada kuitansi diunggah</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Parking Receipt --}}
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                            <h6 class="fw-bold mb-2 text-success"><i class="bi bi-p-circle"></i> Karcis Parkir / Retribusi</h6>
                            @if($trip->parking_receipt_path)
                                <div class="receipt-thumbnail-container">
                                    <img src="{{ asset('storage/' . $trip->parking_receipt_path) }}" 
                                         alt="Kuitansi Parkir" 
                                         class="receipt-thumbnail">
                                    <div class="position-absolute top-50 start-50 translate-middle w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 opacity-0 thumbnail-hover">
                                        <i class="bi bi-zoom-in text-white fs-3"></i>
                                    </div>
                                </div>
                            @else
                                <div class="py-4 text-muted">
                                    <i class="bi bi-file-earmark-x fs-1 opacity-50 d-block mb-2"></i>
                                    <small class="text-muted italic">Tidak ada kuitansi diunggah</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modern Glassmorphism Zoom Modal --}}
    <div class="modal fade" id="receiptZoomModal" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(10px);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 bg-transparent">
                <div class="modal-header border-0 p-0 position-absolute top-0 end-0 z-3">
                    <button type="button" class="btn-close btn-close-white m-3 bg-dark bg-opacity-50 p-2 rounded-circle" data-bs-dismiss="modal" aria-label="Close" style="box-shadow: 0 4px 10px rgba(0,0,0,0.3);"></button>
                </div>
                <div class="modal-body p-0 text-center">
                    <img id="zoomedReceiptImage" src="" alt="Zoomed Receipt" class="img-fluid rounded shadow-lg" style="max-height: 80vh; object-fit: contain; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    <div class="text-white mt-3 p-2 bg-dark bg-opacity-50 rounded-pill d-inline-block px-4 fw-bold shadow" id="zoomedReceiptTitle"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rejection Reason --}}
    @if($trip->approval_status == 'rejected' && $trip->rejection_reason)
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Alasan Penolakan</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $trip->rejection_reason }}</p>
            </div>
        </div>
    @endif
    </div> <!-- End .d-print-none -->
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.transport-costs.reject', $trip->id) }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-circle"></i> Tolak Laporan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required 
                                  placeholder="Jelaskan alasan penolakan (minimal 10 karakter)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Tolak Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/global-actions.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle approve button
    const approveBtn = document.querySelector('[data-action="approve"]');
    if (approveBtn) {
        approveBtn.addEventListener('click', function() {
            const url = this.dataset.url;
            const confirmMsg = this.dataset.confirm;
            
            if (confirm(confirmMsg)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Handle submit to finance button
    const submitFinanceBtn = document.querySelector('[data-action="submit-finance"]');
    if (submitFinanceBtn) {
        submitFinanceBtn.addEventListener('click', function() {
            const url = this.dataset.url;
            const confirmMsg = this.dataset.confirm;
            
            if (confirm(confirmMsg)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Handle receipt zoom modal
    const zoomModalEl = document.getElementById('receiptZoomModal');
    if (zoomModalEl) {
        const receiptModal = new bootstrap.Modal(zoomModalEl);
        const zoomedImage = document.getElementById('zoomedReceiptImage');
        const zoomedTitle = document.getElementById('zoomedReceiptTitle');

        document.querySelectorAll('.receipt-thumbnail-container').forEach(container => {
            container.addEventListener('click', function() {
                const img = this.querySelector('.receipt-thumbnail');
                zoomedImage.src = img.src;
                zoomedTitle.innerHTML = `<i class="bi bi-image"></i> ${img.alt}`;
                receiptModal.show();
            });
        });
    }
});
</script>
@endpush
