@extends('customer.layouts.app')

@section('title', 'Detail Konfirmasi Service Unit')

@section('content')
<div class="container-fluid mb-5">
    {{-- Top Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 fs-5 text-dark">Detail Konfirmasi Service Unit</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('customer.approve.index') }}" class="text-decoration-none text-muted">Konfirmasi Service Unit</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Laporan</li>
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

    {{-- Action Buttons --}}
    <div class="d-flex justify-content-between mb-4">
        <a href="{{ route('customer.approve.index') }}" class="btn btn-white border shadow-sm">
            <i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('customer.approve.download', $report->id) }}" class="btn btn-white border text-primary shadow-sm fw-semibold">
                <i class="bi bi-download me-2"></i> Download Draft PDF
            </a>
            <button type="button" class="btn btn-primary shadow-sm fw-semibold" onclick="window.print()">
                <i class="bi bi-printer me-2"></i> Cetak Ringkasan
            </button>
        </div>
    </div>

    {{-- Stepper UI --}}
    <div class="card border-0 shadow-sm mb-4 bg-white d-print-none">
        <div class="card-body p-3 p-md-4 pt-4 pt-md-5 pb-3 pb-md-4">
            <div class="stepper-wrapper {{ $report->status === 'approved_customer' ? 'step-3' : 'step-1' }} w-100 w-md-75 mx-auto">
                <div class="stepper-progress">
                    <div class="stepper-progress-bar"></div>
                </div>
                
                @if($report->status === 'approved_customer')
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
                @else
                    <div class="stepper-item active">
                        <div class="stepper-circle">1</div>
                        <div class="stepper-title">Periksa Laporan</div>
                        <div class="stepper-subtitle">Sedang Berlangsung</div>
                    </div>
                    <div class="stepper-item">
                        <div class="stepper-circle">2</div>
                        <div class="stepper-title">Konfirmasi & Tanda Tangan</div>
                        <div class="stepper-subtitle">Menunggu</div>
                    </div>
                    <div class="stepper-item">
                        <div class="stepper-circle">3</div>
                        <div class="stepper-title">Selesai</div>
                        <div class="stepper-subtitle">Menunggu</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Info Card Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <small class="text-muted fw-bold d-block mb-1">No Tiket</small>
                <div class="fw-bold fs-5 text-dark">{{ $report->ticket_number ?? 'N/A' }}</div>
            </div>
            <div>
                <small class="text-muted fw-bold d-block mb-1">Status Konfirmasi</small>
                @if($report->status === 'pending_customer')
                    <span class="badge bg-warning text-dark border border-warning px-3 py-2 bg-opacity-25 rounded-1">Menunggu Konfirmasi</span>
                @elseif($report->status === 'approved_customer')
                    <span class="badge bg-success text-success border border-success px-3 py-2 bg-opacity-25 rounded-1">Terkonfirmasi</span>
                @elseif($report->status === 'revision_requested')
                    <span class="badge bg-info text-info border border-info px-3 py-2 bg-opacity-25 rounded-1">Minta Klarifikasi</span>
                @elseif($report->status === 'rejected_customer')
                    <span class="badge bg-danger text-danger border border-danger px-3 py-2 bg-opacity-25 rounded-1">Ditolak</span>
                @endif
            </div>
            <div>
                <small class="text-muted fw-bold d-block mb-1">Dikirim oleh PT Hamada</small>
                <div class="fw-semibold text-dark">{{ $report->timestamp->format('d-m-Y H:i') }}</div>
            </div>
            @if($report->status === 'pending_customer')
            <div class="alert alert-warning mb-0 px-4 py-2 border-warning bg-warning bg-opacity-10 d-flex align-items-center">
                <i class="bi bi-hourglass-split fs-4 text-warning me-3"></i>
                <div>
                    <div class="small fw-semibold text-warning">Mohon konfirmasi sebelum</div>
                    <div class="fw-bold text-dark fs-6">{{ $report->timestamp->addDays(5)->format('d-m-Y 23:59') }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column --}}
        <div class="col-12 col-lg-7">
            {{-- Informasi Unit --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-truck me-2"></i>Informasi Unit</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="mb-3">
                                <small class="text-muted fw-bold d-block mb-1">Plat Nomor</small>
                                <span class="badge bg-secondary px-3 py-2 text-dark bg-opacity-10 border border-secondary">{{ $report->vehicle->plate_number ?? '-' }}</span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold d-block mb-1">Unit</small>
                                <div class="text-dark fw-semibold">{{ $report->vehicle->type ?? '-' }}</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold d-block mb-1">Driver</small>
                                <div class="text-dark fw-semibold">{{ $report->driver->name ?? '-' }}</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold d-block mb-1">Customer / Project</small>
                                <div class="text-dark fw-semibold">{{ $report->customer->name ?? '-' }}</div>
                            </div>
                            <div>
                                <small class="text-muted fw-bold d-block mb-1">KM Saat Kendala</small>
                                <div class="text-dark fw-semibold">{{ number_format($report->odometer, 0, ',', '.') }} KM</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <small class="text-muted fw-bold d-block mb-1">Tanggal Kendala</small>
                                <div class="text-dark fw-semibold">{{ $report->timestamp->format('d-m-Y H:i') }}</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold d-block mb-1">Tanggal Laporan</small>
                                <div class="text-dark fw-semibold">{{ $report->timestamp->format('d-m-Y H:i') }}</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold d-block mb-1">Selesai Ditangani</small>
                                <div class="text-dark fw-semibold">{{ $report->approved_at_admin ? $report->approved_at_admin->format('d-m-Y H:i') : '-' }}</div>
                            </div>
                            <div>
                                <small class="text-muted fw-bold d-block mb-1">Lokasi Kendala</small>
                                <div class="text-dark fw-semibold mb-1">{{ $report->gps_location ?? '-' }}</div>
                                @if($report->gps_location)
                                    <a href="https://maps.google.com/?q={{ $report->gps_location }}" target="_blank" class="small text-decoration-none text-primary fw-semibold">Lihat di Google Maps <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dokumentasi Kendaraan --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-images me-2"></i>Dokumentasi Kendaraan</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        {{-- Photo 1 --}}
                        <div class="col-12 col-md-4">
                            <div class="border rounded bg-light p-2 h-100 d-flex flex-column">
                                <small class="fw-bold text-dark mb-2 d-block">Sebelum Service / Kendala</small>
                                <div class="position-relative bg-dark rounded overflow-hidden flex-grow-1" style="min-height: 120px;">
                                    @if($report->vehicle_condition_photo_path)
                                        <img src="{{ $report->vehicle_condition_photo_url }}" class="img-fluid w-100 h-100 object-fit-cover" alt="Kondisi Kendaraan" style="cursor: zoom-in;" onclick="viewImage(this.src)">
                                        <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white small px-2 py-1 m-1 rounded pointer-events-none" style="font-size: 0.7rem;">
                                            {{ $report->before_service_photo_uploaded_at ? \Carbon\Carbon::parse($report->before_service_photo_uploaded_at)->format('d-m-Y H:i') : '-' }}
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 w-100 text-muted">
                                            <i class="bi bi-image fs-1"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-2 text-muted small" style="font-size: 0.75rem; line-height: 1.3;">
                                    Foto kondisi sebelum perbaikan.
                                </div>
                            </div>
                        </div>

                        {{-- Photo 2 --}}
                        <div class="col-12 col-md-4">
                            <div class="border rounded bg-light p-2 h-100 d-flex flex-column">
                                <small class="fw-bold text-dark mb-2 d-block">Setelah Service</small>
                                <div class="position-relative bg-dark rounded overflow-hidden flex-grow-1" style="min-height: 120px;">
                                    @if($report->after_service_photo_path)
                                        <img src="{{ asset('storage/' . $report->after_service_photo_path) }}" class="img-fluid w-100 h-100 object-fit-cover" alt="Setelah Service" style="cursor: zoom-in;" onclick="viewImage(this.src)">
                                        <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white small px-2 py-1 m-1 rounded pointer-events-none" style="font-size: 0.7rem;">
                                            {{ $report->after_service_photo_taken_at ? \Carbon\Carbon::parse($report->after_service_photo_taken_at)->format('d-m-Y H:i') : '-' }}
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 w-100 text-muted">
                                            <i class="bi bi-image fs-1"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-2 text-muted small" style="font-size: 0.75rem; line-height: 1.3;">
                                    Foto setelah perbaikan selesai.
                                </div>
                            </div>
                        </div>

                        {{-- Photo 3 --}}
                        <div class="col-12 col-md-4">
                            <div class="border rounded bg-light p-2 h-100 d-flex flex-column">
                                <small class="fw-bold text-dark mb-2 d-block">Odometer / KM</small>
                                <div class="position-relative bg-dark rounded overflow-hidden flex-grow-1" style="min-height: 120px;">
                                    @if($report->odometer_photo_path)
                                        <img src="{{ asset('storage/' . $report->odometer_photo_path) }}" class="img-fluid w-100 h-100 object-fit-cover" alt="Odometer" style="cursor: zoom-in;" onclick="viewImage(this.src)">
                                        <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white small px-2 py-1 m-1 rounded pointer-events-none" style="font-size: 0.7rem;">
                                            {{ $report->odometer_photo_taken_at ? \Carbon\Carbon::parse($report->odometer_photo_taken_at)->format('d-m-Y H:i') : '-' }}
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 w-100 text-muted">
                                            <i class="bi bi-image fs-1"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-2 text-muted small" style="font-size: 0.75rem; line-height: 1.3;">
                                    Foto odometer kendaraan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-12 col-lg-5">
            {{-- Kronologi Kendala --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-clock-history me-2"></i>Kronologi Kendala</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2 fs-6">Deskripsi Kendala</h6>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">{{ $report->description ?? '-' }}</p>
                    </div>
                    <hr class="text-muted border-secondary opacity-25">
                    
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2 fs-6">Tindakan Penanganan</h6>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">{{ $report->service_action ?? '-' }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2 fs-6">Status Akhir Unit</h6>
                        @if($report->unit_status_after_service == 'Aman' || str_contains(strtolower($report->unit_status_after_service), 'jalan'))
                            <span class="badge bg-success bg-opacity-10 text-success border border-success py-2 px-3"><i class="bi bi-circle-fill small me-2" style="font-size:8px;"></i>{{ $report->unit_status_after_service }}</span>
                        @elseif($report->unit_status_after_service)
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger py-2 px-3"><i class="bi bi-circle-fill small me-2" style="font-size:8px;"></i>{{ $report->unit_status_after_service }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                    
                    <hr class="text-muted border-secondary opacity-25">
                    <div>
                        <h6 class="fw-bold text-dark mb-2 fs-6">Catatan dari Admin</h6>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">{{ $report->admin_notes ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Konfirmasi Laporan Box --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 bg-light bg-opacity-50 border border-info border-opacity-25 rounded">
                    <h5 class="fw-bold text-dark mb-3">Konfirmasi Laporan</h5>
                    <p class="small text-muted mb-4" style="line-height: 1.6;">
                        Dengan menekan tombol konfirmasi, Anda menyatakan bahwa laporan kendala dan penanganan unit di atas telah diterima dan diketahui.
                    </p>
                    <div class="alert alert-primary bg-primary bg-opacity-10 border-0 d-flex mb-0 p-3">
                        <i class="bi bi-info-circle-fill text-primary mt-1 me-3 fs-5"></i>
                        <p class="small text-dark mb-0">
                            <b>Konfirmasi ini bukan</b> merupakan kuitansi tagihan atau rincian invoice pembayaran service.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Actions Bar --}}
    @if($report->status === 'pending_customer')
    <div class="card border-0 shadow-sm mt-4 bg-white sticky-bottom" style="bottom: 20px; z-index: 1000;">
        <div class="card-body p-4 d-flex flex-column flex-lg-row align-items-center justify-content-between gap-4">
            {{-- Verifikasi Admin --}}
            <div class="d-flex align-items-center bg-warning bg-opacity-10 border border-warning border-opacity-50 p-3 rounded flex-grow-1 w-100" style="max-width: 500px;">
                <i class="bi bi-check-circle-fill text-warning fs-3 me-3"></i>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Verifikasi PT Hamada Global Jaya</h6>
                    <p class="small text-muted mb-0" style="font-size: 0.75rem;">
                        Laporan ini telah diverifikasi oleh Admin Operasional PT Hamada Global Jaya sebelum dikirim ke Anda.
                    </p>
                </div>
                <div class="ms-auto ps-3 border-start d-none d-sm-block">
                    <div class="small fw-bold text-dark mb-1">Diverifikasi oleh</div>
                    <div class="small text-muted fw-semibold">{{ $report->admin_signer_name ?? 'Admin Operasional' }}</div>
                    <div class="small fw-bold text-dark mt-2 mb-1">Waktu Verifikasi</div>
                    <div class="small text-muted">{{ $report->approved_at_admin ? $report->approved_at_admin->format('d-m-Y H:i') : '-' }}</div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="d-flex flex-wrap gap-2 justify-content-lg-end w-100">
                <button type="button" class="btn btn-outline-primary px-4 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#clarifyModal">
                    <i class="bi bi-pencil-square me-2"></i> Minta Klarifikasi
                </button>
                <button type="button" class="btn btn-outline-danger px-4 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle me-2"></i> Tolak Laporan
                </button>
                <a href="{{ route('customer.approve.sign', $report->id) }}" class="btn btn-success px-4 fw-semibold shadow-sm text-white">
                    <i class="bi bi-check-circle me-2"></i> Konfirmasi Laporan
                </a>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-bottom-0 py-3">
                <h5 class="modal-title fs-5 fw-bold"><i class="bi bi-x-circle-fill me-2"></i>Tolak Laporan Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('customer.approve.reject', $report->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Silakan tuliskan alasan Anda menolak hasil service ini. Alasan ini akan dikirimkan kembali ke pihak Admin Hamada Global Jaya untuk ditindaklanjuti.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="customer_rejection_reason" class="form-control bg-light" rows="4" placeholder="Misal: Perbaikan tidak sesuai kesepakatan, plat nomor salah, dll" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4 fw-semibold"><i class="bi bi-check me-1"></i> Konfirmasi Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Clarify Modal --}}
<div class="modal fade" id="clarifyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-bottom-0 py-3">
                <h5 class="modal-title fs-5 fw-bold"><i class="bi bi-pencil-square me-2"></i>Minta Klarifikasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('customer.approve.clarify', $report->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Tuliskan hal-hal yang perlu diklarifikasi atau direvisi oleh Admin mengenai laporan service ini.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pesan Klarifikasi <span class="text-danger">*</span></label>
                        <textarea name="customer_revision_notes" class="form-control bg-light" rows="4" placeholder="Misal: Terdapat kesalahan penulisan Plat Nomor, mohon diperbaiki." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold"><i class="bi bi-send-fill me-1"></i> Kirim Pesan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<div class="modal fade" id="imageViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 justify-content-end p-2 position-absolute w-100" style="z-index: 10;">
                <button type="button" class="btn-close btn-close-white bg-dark rounded-circle p-2" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.8;"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="viewerImage" src="" class="img-fluid rounded shadow-lg" style="max-height: 85vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
<script>
    function viewImage(src) {
        document.getElementById('viewerImage').src = src;
        var imageModal = new bootstrap.Modal(document.getElementById('imageViewerModal'));
        imageModal.show();
    }
</script>
@endpush
