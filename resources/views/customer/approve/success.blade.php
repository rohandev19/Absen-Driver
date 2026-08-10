@extends('customer.layouts.app')

@section('title', 'Konfirmasi Selesai')



@section('content')
<div class="container-fluid mb-5">
    
    {{-- Header --}}
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1 fs-5 text-dark">Konfirmasi Service Unit</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item text-muted">Detail Laporan</li>
                    <li class="breadcrumb-item text-muted">Konfirmasi Laporan</li>
                    <li class="breadcrumb-item active" aria-current="page">Selesai</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="#" class="text-decoration-none text-primary small fw-semibold"><i class="bi bi-question-circle me-1"></i> Bantuan</a>
            <div class="d-flex align-items-center bg-white border rounded-pill px-3 py-1 shadow-sm">
                <div class="text-end me-2">
                    <div class="fw-bold fs-6" style="line-height: 1.2;">{{ Auth::user()->name }}</div>
                    <div class="text-muted" style="font-size: 0.7rem;">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 35px; height: 35px;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Stepper UI --}}
    <div class="card border-0 shadow-sm mb-4 bg-white d-print-none">
        <div class="card-body p-3 p-md-4 pt-4 pt-md-5 pb-3 pb-md-4">
            <div class="stepper-wrapper step-3 w-100 w-md-75 mx-auto">
                <div class="stepper-progress">
                    <div class="stepper-progress-bar"></div>
                </div>
                <div class="stepper-item completed">
                    <div class="stepper-circle"><i class="bi bi-check-lg"></i></div>
                    <div class="stepper-title">Periksa Laporan</div>
                    <div class="stepper-subtitle">Selesai</div>
                </div>
                <div class="stepper-item completed">
                    <div class="stepper-circle"><i class="bi bi-check-lg"></i></div>
                    <div class="stepper-title">Konfirmasi & Tanda Tangan</div>
                    <div class="stepper-subtitle">Selesai</div>
                </div>
                <div class="stepper-item completed">
                    <div class="stepper-circle"><i class="bi bi-check-lg"></i></div>
                    <div class="stepper-title">Selesai</div>
                    <div class="stepper-subtitle">Selesai</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="row g-4">
        {{-- Left Column: Success Message --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100 text-center p-5">
                <div class="d-flex justify-content-center mb-4 mt-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                        <i class="bi bi-check-lg text-success" style="font-size: 4rem;"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-2">Terima Kasih!</h3>
                <h4 class="fw-bold text-success mb-4">Konfirmasi Berhasil Dikirim</h4>
                <p class="text-muted small px-3 mb-5" style="line-height: 1.6;">
                    Laporan service unit telah berhasil Anda konfirmasi. Berita acara konfirmasi akan segera tersedia untuk diunduh.
                </p>

                <div class="card border border-success border-opacity-25 bg-success bg-opacity-10 shadow-none text-start mb-5">
                    <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0">
                        <h6 class="fw-bold text-success mb-0"><i class="bi bi-info-circle-fill me-2"></i>Informasi Konfirmasi</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-dark fw-bold">No Tiket</small>
                            <small class="text-dark">{{ $report->ticket_number }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-dark fw-bold">Tanggal Konfirmasi</small>
                            <small class="text-dark">{{ $report->approved_at_customer ? $report->approved_at_customer->format('d-m-Y H:i') : '-' }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-dark fw-bold">Dikonfirmasi oleh</small>
                            <small class="text-dark">{{ $report->customer_signer_name ?? $report->approvedByCustomer->name }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-dark fw-bold">Jabatan</small>
                            <small class="text-dark">{{ $report->customer_signer_role ?? 'Customer' }}</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-dark fw-bold">Metode Konfirmasi</small>
                            <small class="text-dark">Tanda Tangan Digital</small>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top border-success border-opacity-25 p-3 rounded-bottom text-center">
                        <small class="text-primary fw-semibold"><i class="bi bi-shield-check me-1"></i> Dokumen berita acara konfirmasi ini bersifat sah dan mengikat secara digital serta telah tersimpan dengan aman dalam sistem.</small>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-center">
                    <a href="{{ route('customer.approve.index') }}" class="btn btn-outline-primary px-4 fw-semibold w-50">
                        Lihat Laporan Lain
                    </a>
                    <a href="{{ route('customer.approve.download', $report->id) }}" class="btn btn-primary px-4 fw-semibold w-50">
                        Download Berita Acara (PDF)
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Column: Summary --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom pt-4 pb-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Ringkasan Konfirmasi</h5>
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2">TERKONFIRMASI</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center p-4">
                            <div class="text-muted fw-bold small"><i class="bi bi-hash me-2 fs-5 text-secondary"></i>No Tiket</div>
                            <div class="text-dark fw-semibold">{{ $report->ticket_number }}</div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-4">
                            <div class="text-muted fw-bold small"><i class="bi bi-car-front me-2 fs-5 text-secondary"></i>Plat Nomor</div>
                            <div><span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary px-3 py-2">{{ $report->vehicle->plate_number }}</span></div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-4">
                            <div class="text-muted fw-bold small"><i class="bi bi-truck me-2 fs-5 text-secondary"></i>Unit</div>
                            <div class="text-dark fw-semibold">{{ $report->vehicle->type }}</div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-4">
                            <div class="text-muted fw-bold small"><i class="bi bi-wrench-adjustable me-2 fs-5 text-secondary"></i>Jenis Kendala</div>
                            <div class="text-dark fw-semibold">{{ $report->problem_category }}</div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-4">
                            <div class="text-muted fw-bold small"><i class="bi bi-clock me-2 fs-5 text-secondary"></i>Waktu Kendala</div>
                            <div class="text-dark fw-semibold">{{ $report->timestamp->format('d-m-Y H:i') }}</div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-4">
                            <div class="text-muted fw-bold small"><i class="bi bi-geo-alt me-2 fs-5 text-secondary"></i>Lokasi Kendala</div>
                            <div class="text-dark fw-semibold">{{ $report->gps_location }}</div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-4">
                            <div class="text-muted fw-bold small"><i class="bi bi-check-circle me-2 fs-5 text-secondary"></i>Status Akhir Unit</div>
                            @if(str_contains(strtolower($report->unit_status_after_service), 'jalan') || $report->unit_status_after_service == 'Aman')
                                <div class="text-success fw-bold small"><i class="bi bi-circle-fill me-2" style="font-size: 8px;"></i>{{ $report->unit_status_after_service }}</div>
                            @else
                                <div class="text-danger fw-bold small"><i class="bi bi-circle-fill me-2" style="font-size: 8px;"></i>{{ $report->unit_status_after_service }}</div>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-4">
                            <div class="text-muted fw-bold small"><i class="bi bi-person me-2 fs-5 text-secondary"></i>Dikonfirmasi oleh</div>
                            <div class="text-dark fw-semibold">{{ $report->customer_signer_name ?? $report->approvedByCustomer->name }} ({{ $report->customer_signer_role ?? 'Customer' }})</div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-4">
                            <div class="text-muted fw-bold small"><i class="bi bi-calendar-check me-2 fs-5 text-secondary"></i>Tanggal Konfirmasi</div>
                            <div class="text-dark fw-semibold">{{ $report->approved_at_customer ? $report->approved_at_customer->format('d-m-Y H:i') : '-' }}</div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Download Box --}}
            <div class="card border border-warning border-opacity-25 bg-warning bg-opacity-10 shadow-none mb-4">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-pdf me-2 text-warning"></i>Dokumen Siap Diunduh</h6>
                        <p class="small text-muted mb-0">Dokumen Berita Acara Konfirmasi Service Unit telah dibuat dan siap untuk diunduh.</p>
                    </div>
                    <a href="{{ route('customer.approve.download', $report->id) }}" class="btn btn-outline-primary fw-semibold bg-white">
                        <i class="bi bi-download me-1"></i> Download PDF
                    </a>
                </div>
            </div>
            
            {{-- Info Box --}}
            <div class="alert alert-primary bg-primary bg-opacity-10 border-0 p-4 mb-0">
                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-info-circle-fill me-2"></i>Informasi Penting</h6>
                <p class="small text-dark mb-0" style="line-height: 1.6;">
                    Konfirmasi ini hanya menyatakan bahwa laporan kendala dan penanganan unit telah diterima dan diketahui. <br>
                    <b>Konfirmasi ini bukan persetujuan biaya, kuitansi, invoice, atau rincian pembayaran service.</b>
                </p>
                <div class="text-end mt-2">
                    <a href="#" class="text-primary text-decoration-none small fw-semibold">Selengkapnya <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
