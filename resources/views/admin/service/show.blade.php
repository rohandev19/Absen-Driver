@extends('admin.layouts.app')

@section('title', 'Detail Service Report')

@section('content')
<div class="container-fluid">


    {{-- WEB UI (Sembunyikan saat dicetak) --}}
    <div class="d-print-none">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="fw-bold mb-0 fs-4 fs-md-3"><i class="bi bi-tools"></i> Detail Laporan Service</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.service.index') }}" class="btn btn-secondary btn-sm" style="min-height:38px;">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Status Badge --}}
    <div class="card mb-3">
        <div class="card-body text-center py-3">
            @php
                $statusBadge = match($report->status) {
                    'pending' => 'bg-warning bg-opacity-10 text-warning border border-warning',
                    'pending_admin' => 'bg-warning bg-opacity-10 text-warning border border-warning',
                    'waiting_completion' => 'bg-info bg-opacity-10 text-info border border-info',
                    'approved_admin' => 'bg-primary bg-opacity-10 text-primary border border-primary',
                    'pending_customer' => 'bg-primary bg-opacity-10 text-primary border border-primary',
                    'approved_customer' => 'bg-success bg-opacity-10 text-success border border-success',
                    'revision_requested' => 'bg-danger bg-opacity-10 text-danger border border-danger',
                    'rejected' => 'bg-danger bg-opacity-10 text-danger border border-danger',
                    'rejected_admin' => 'bg-danger bg-opacity-10 text-danger border border-danger',
                    'rejected_customer' => 'bg-danger bg-opacity-10 text-danger border border-danger',
                    default => 'bg-secondary bg-opacity-10 text-secondary border border-secondary'
                };
                $statusText = match($report->status) {
                    'pending' => 'Menunggu Review Admin',
                    'pending_admin' => 'Menunggu Review Admin',
                    'waiting_completion' => 'Menunggu Kelengkapan Driver',
                    'approved_admin' => 'Disetujui Admin',
                    'pending_customer' => 'Menunggu Persetujuan Customer',
                    'approved_customer' => 'Disetujui Customer',
                    'revision_requested' => 'Revisi Diminta',
                    'rejected' => 'Ditolak',
                    'rejected_admin' => 'Ditolak Admin',
                    'rejected_customer' => 'Ditolak Customer',
                    default => $report->status
                };
            @endphp
            <h3 class="mb-0"><span class="badge {{ $statusBadge }} fs-6 rounded-pill px-4 py-2" style="font-weight: 600;">{{ $statusText }}</span></h3>
        </div>
    </div>

    <div class="row g-3">
        {{-- Left Column: Photos — full-width on mobile --}}
        <div class="col-12 col-lg-6">
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fs-6 fw-bold text-dark"><i class="bi bi-camera text-primary me-2"></i>Foto Sebelum Service</h5>
                </div>
                <div class="card-body text-center p-2 p-md-3">
                    <img src="{{ asset('storage/' . $report->vehicle_condition_photo_path) }}" 
                         alt="Kondisi Kendaraan" 
                         class="img-fluid rounded shadow mb-2"
                         style="max-height: 350px; cursor: pointer; width: 100%; object-fit: contain;"
                         onclick="window.open(this.src, '_blank')">
                    <p class="text-muted mt-2 small mb-0">Sumber Foto: <strong>{{ ucfirst($report->before_service_photo_source ?? 'Kamera') }}</strong></p>
                </div>
            </div>

            @if($report->after_service_photo_path)
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fs-6 fw-bold text-dark"><i class="bi bi-camera text-success me-2"></i>Foto Setelah Service</h5>
                </div>
                <div class="card-body text-center p-2 p-md-3">
                    <img src="{{ asset('storage/' . $report->after_service_photo_path) }}" 
                         class="img-fluid rounded shadow"
                         style="max-height: 350px; cursor: pointer; width: 100%; object-fit: contain;"
                         onclick="window.open(this.src, '_blank')">
                </div>
            </div>
            @endif

            @if($report->odometer_photo_path)
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fs-6 fw-bold text-dark"><i class="bi bi-speedometer2 text-info me-2"></i>Foto Odometer</h5>
                </div>
                <div class="card-body text-center p-2 p-md-3">
                    <img src="{{ asset('storage/' . $report->odometer_photo_path) }}" 
                         class="img-fluid rounded shadow"
                         style="max-height: 350px; cursor: pointer; width: 100%; object-fit: contain;"
                         onclick="window.open(this.src, '_blank')">
                </div>
            </div>
            @endif

            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fs-6 fw-bold text-dark"><i class="bi bi-receipt text-warning me-2"></i>Foto Kuitansi</h5>
                </div>
                <div class="card-body text-center p-2 p-md-3">
                    <img src="{{ asset('storage/' . $report->receipt_photo_path) }}" 
                         alt="Kuitansi" 
                         class="img-fluid rounded shadow"
                         style="max-height: 350px; cursor: pointer; width: 100%; object-fit: contain;"
                         onclick="window.open(this.src, '_blank')">
                    <p class="text-muted mt-2 small mb-0">Klik untuk memperbesar</p>
                </div>
            </div>
        </div>

        {{-- Right Column: Info & Actions — full-width on mobile --}}
        <div class="col-12 col-lg-6">
            {{-- Info Card --}}
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fs-6 fw-bold text-dark"><i class="bi bi-info-circle text-info me-2"></i>Informasi Laporan</h5>
                </div>
                <div class="card-body p-3">
                    {{-- Mobile: stacked info, Desktop: table --}}
                    <div class="d-none d-md-block">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <th width="40%">No. Tiket:</th>
                                <td><span class="fw-bold">{{ $report->ticket_number ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <th>Tanggal Service:</th>
                                <td>{{ $report->timestamp->format('d-m-Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Driver:</th>
                                <td>{{ $report->driver->full_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Plat Nomor:</th>
                                <td><span class="badge bg-secondary">{{ $report->vehicle->plate_number ?? 'N/A' }}</span></td>
                            </tr>
                            <tr>
                                <th>Customer:</th>
                                <td>{{ $report->customer->name ?? 'Belum di-link' }}</td>
                            </tr>
                            <tr>
                                <th>Odometer:</th>
                                <td>{{ $report->odometer ? number_format($report->odometer, 0, ',', '.') . ' KM' : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Service:</th>
                                <td>{{ $report->service_type ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kategori Kendala:</th>
                                <td>{{ $report->problem_category ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Lokasi GPS:</th>
                                <td>
                                    <a href="https://www.google.com/maps?q={{ $report->gps_location }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-geo-alt"></i> Lihat di Maps
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    {{-- Mobile: stacked list --}}
                    <div class="d-md-none">
                        <div class="mb-2">
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Tanggal Service</small>
                            {{ $report->timestamp->format('d-m-Y H:i') }}
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Driver</small>
                            {{ $report->driver->full_name ?? 'N/A' }}
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Plat Nomor</small>
                            <span class="badge bg-secondary">{{ $report->vehicle->plate_number ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Customer</small>
                            {{ $report->customer->name ?? 'Belum di-link' }}
                        </div>
                        <div>
                            <a href="https://www.google.com/maps?q={{ $report->gps_location }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary w-100" style="min-height:44px; display:flex; align-items:center; justify-content:center;">
                                <i class="bi bi-geo-alt me-1"></i> Lihat di Maps
                            </a>
                        </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold">Deskripsi Masalah:</h6>
                    <p class="text-muted">{{ $report->description }}</p>

                    @if($report->service_action)
                        <hr>
                        <h6 class="fw-bold">Tindakan Service:</h6>
                        <p class="text-muted">{{ $report->service_action }}</p>
                    @endif

                    @if($report->unit_status_after_service)
                        <hr>
                        <h6 class="fw-bold">Status Unit Setelah Service:</h6>
                        <p class="text-muted">{{ $report->unit_status_after_service }}</p>
                    @endif

                    @if($report->additional_notes)
                        <hr>
                        <h6 class="fw-bold">Catatan Tambahan Driver:</h6>
                        <p class="text-muted">{{ $report->additional_notes }}</p>
                    @endif

                    @if($report->admin_notes)
                        <hr>
                        <h6 class="fw-bold">Catatan Admin:</h6>
                        <p class="text-muted">{{ $report->admin_notes }}</p>
                    @endif

                    @if($report->rejected_reason)
                        <hr>
                        <div class="alert alert-danger mb-0">
                            <h6 class="fw-bold">Alasan Penolakan Driver/Admin:</h6>
                            <p class="mb-0">{{ $report->rejected_reason }}</p>
                        </div>
                    @endif

                    @if($report->status === 'revision_requested' && $report->customer_revision_notes)
                        <hr>
                        <div class="alert alert-info mb-0 border border-info">
                            <h6 class="fw-bold text-info"><i class="bi bi-chat-left-text me-2"></i>Catatan Klarifikasi dari Customer:</h6>
                            <p class="mb-0 mt-2 small">{{ $report->customer_revision_notes }}</p>
                            <hr class="border-info opacity-25 my-2">
                            <small class="text-info fw-semibold">Silakan perbaiki data di bawah dan ajukan ulang konfirmasi ke Customer.</small>
                        </div>
                    @endif

                    @if($report->status === 'rejected_customer' && $report->customer_rejection_reason)
                        <hr>
                        <div class="alert alert-danger mb-0 border border-danger">
                            <h6 class="fw-bold text-danger"><i class="bi bi-x-circle me-2"></i>Alasan Penolakan dari Customer:</h6>
                            <p class="mb-0 mt-2 small">{{ $report->customer_rejection_reason }}</p>
                            <hr class="border-danger opacity-25 my-2">
                            <small class="text-danger fw-semibold">Laporan ini ditolak oleh customer secara final.</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions Card --}}
            @if(in_array($report->status, ['pending', 'pending_admin', 'revision_requested']))
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="mb-0 fs-6 fw-bold text-dark"><i class="bi bi-check-circle-fill text-success me-2"></i>Aksi Admin</h5>
                        </div>
                        <div class="card-body p-4 bg-light bg-opacity-50">
                            <form action="{{ route('admin.service.approve', $report->id) }}" method="POST" id="approvalForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold">Nama Penandatangan</label>
                                        <input type="text" name="signer_name" class="form-control" value="{{ Auth::user()->name }}" required>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold">Jabatan</label>
                                        <input type="text" name="signer_role" class="form-control" value="Admin Service" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Tanda Tangan Digital (Wajib)</label>
                                    <div class="border rounded" style="position: relative; background-color: #f8f9fa; border: 2px dashed #dee2e6 !important; min-height: 200px;">
                                        <canvas id="signature-pad" class="signature-pad d-block" style="width: 100%; height: 200px; touch-action: none; cursor: crosshair;"></canvas>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearSignature" style="min-height:36px;">
                                        <i class="bi bi-eraser"></i> Hapus Tanda Tangan
                                    </button>
                                    <input type="hidden" name="signature" id="signatureInput" required>
                                </div>

                                {{-- Optional: Ganti Foto Dokumentasi (muncul jika revisi atau dirasa perlu oleh Admin) --}}
                                <div class="accordion mb-3" id="accordionPhotos">
                                    <div class="accordion-item border-secondary border-opacity-25 shadow-sm">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePhotos">
                                                <i class="bi bi-camera text-primary me-2"></i> <strong>Ubah Foto Dokumentasi (Opsional)</strong>
                                            </button>
                                        </h2>
                                        <div id="collapsePhotos" class="accordion-collapse collapse" data-bs-parent="#accordionPhotos">
                                            <div class="accordion-body bg-white">
                                                <div class="alert alert-info py-2 px-3 small border-0 bg-info bg-opacity-10">
                                                    <i class="bi bi-info-circle-fill me-1"></i> Biarkan kosong jika tidak ingin mengubah foto. Format: JPG/PNG, Maks: 10MB.
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label small fw-bold">Foto Sebelum Service (Kondisi Unit)</label>
                                                        <input type="file" name="replace_vehicle_condition_photo" class="form-control form-control-sm" accept="image/jpeg, image/png">
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label small fw-bold">Foto Setelah Service</label>
                                                        <input type="file" name="replace_after_service_photo" class="form-control form-control-sm" accept="image/jpeg, image/png">
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label small fw-bold">Foto Odometer / KM</label>
                                                        <input type="file" name="replace_odometer_photo" class="form-control form-control-sm" accept="image/jpeg, image/png">
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label small fw-bold">Foto Kuitansi / Bon</label>
                                                        <input type="file" name="replace_receipt_photo" class="form-control form-control-sm" accept="image/jpeg, image/png">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h6 class="fw-bold mb-3"><i class="bi bi-cash-coin text-warning me-2"></i>Informasi Biaya Internal</h6>
                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold">Nama Bengkel / Vendor</label>
                                        <input type="text" name="workshop_name" class="form-control form-control-sm" value="{{ $report->workshop_name ?? '' }}" required>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold">No. Invoice / Kuitansi</label>
                                        <input type="text" name="invoice_number" class="form-control form-control-sm" value="{{ $report->invoice_number ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-sm-4">
                                        <label class="form-label small fw-bold">Biaya Jasa (Rp)</label>
                                        <input type="number" name="service_cost" id="service_cost" class="form-control form-control-sm cost-input" min="0" value="{{ $report->service_cost ?? 0 }}" required>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="form-label small fw-bold">Biaya Sparepart (Rp)</label>
                                        <input type="number" name="sparepart_cost" id="sparepart_cost" class="form-control form-control-sm cost-input" min="0" value="{{ $report->sparepart_cost ?? 0 }}" required>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="form-label small fw-bold">Biaya Lainnya (Rp)</label>
                                        <input type="number" name="other_cost" id="other_cost" class="form-control form-control-sm cost-input" min="0" value="{{ $report->other_cost ?? 0 }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Total Biaya (Rp)</label>
                                    <input type="number" name="total_cost" id="total_cost" class="form-control fw-bold text-danger bg-light" readonly required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Catatan Tambahan Admin</label>
                                    <textarea name="admin_notes" class="form-control form-control-sm" rows="2" 
                                              placeholder="Catatan dari Admin untuk Customer / Driver...">{{ $report->admin_notes ?? '' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Catatan Finance / Internal (Opsional)</label>
                                    <textarea name="finance_notes" class="form-control form-control-sm" rows="2" 
                                              placeholder="Catatan tambahan khusus untuk internal...">{{ $report->finance_notes ?? '' }}</textarea>
                                </div>
                                
                                <div class="mb-3 p-3 border border-primary border-opacity-25 rounded" style="background-color: #f0f7ff;">
                                    <div class="form-check d-flex align-items-start mb-0">
                                        <input class="form-check-input flex-shrink-0 mt-1 shadow-sm border-primary" type="checkbox" id="adminConsentCheck" required style="width: 1.5rem; height: 1.5rem; cursor: pointer;">
                                        <label class="form-check-label small fw-semibold text-dark ms-3" for="adminConsentCheck" style="cursor: pointer; line-height: 1.6;">
                                            Saya menyatakan bahwa seluruh rincian laporan, dokumen, dan biaya di atas telah diperiksa kebenarannya dan siap untuk diteruskan ke Customer.
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mb-2">
                                    <button type="submit" class="btn btn-success fw-bold w-100" id="btnApprove" style="min-height:44px;">
                                        <i class="bi bi-check-circle"></i> {{ $report->status === 'revision_requested' ? 'Ajukan Ulang ke Customer' : 'Setujui & Kirim ke Customer' }}
                                    </button>
                                </div>
                            </form>
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal" style="min-height:44px;">
                                <i class="bi bi-x-circle"></i> Tolak Laporan
                            </button>
                        </div>
                    </div>
            @endif

            {{-- Export Dokumen --}}
            @if(in_array($report->status, ['approved_admin', 'pending_customer', 'approved_customer']))
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="mb-0 fs-6 fw-bold text-dark"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Export Dokumen PDF</h5>
                    </div>
                    <div class="card-body p-4 bg-light bg-opacity-50">
                        <a href="{{ route('admin.service.customer_pdf.preview', $report->id) }}" 
                           class="btn btn-outline-danger w-100 mb-2" target="_blank" style="min-height:44px; display:flex; align-items:center; justify-content:center;">
                            <i class="bi bi-eye me-1"></i> Preview PDF Customer
                        </a>

                        <a href="{{ route('admin.service.customer_pdf.download', $report->id) }}" 
                           class="btn btn-danger w-100 mb-2" style="min-height:44px; display:flex; align-items:center; justify-content:center;">
                            <i class="bi bi-download me-1"></i> Download PDF Customer
                        </a>

                        <a href="{{ route('admin.service.internal_pdf.download', $report->id) }}" 
                           class="btn btn-outline-secondary w-100 mb-2" style="min-height:44px; display:flex; align-items:center; justify-content:center;">
                            <i class="bi bi-download me-1"></i> Download PDF Internal Admin
                        </a>

                        <a href="{{ route('admin.service.finance_pdf.download', $report->id) }}" 
                           class="btn btn-outline-success w-100 mb-2" style="min-height:44px; display:flex; align-items:center; justify-content:center;">
                            <i class="bi bi-download me-1"></i> Download PDF Finance
                        </a>

                        <p class="text-muted small mt-2 mb-0">
                            Gunakan dokumen di atas sesuai peruntukannya (Berita Acara untuk ditandatangani Customer, Pengajuan Finance untuk internal).
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <form action="{{ route('admin.service.reject', $report->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fs-6">Tolak Laporan Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejected_reason" class="form-control" rows="4" 
                                  placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="min-height:44px;">Batal</button>
                    <button type="submit" class="btn btn-danger" style="min-height:44px;">Tolak Laporan</button>
                </div>
            </form>
        </div>
    </div>
    </div> <!-- End of .d-print-none -->
</div>
@endsection

@push('styles')
<style>
    @media print {
        body, [data-bs-theme="dark"] {
            background-color: #fff !important;
            color: #000 !important;
        }
        .sidebar, .topbar, .d-print-none, .btn {
            display: none !important;
        }
        .container-fluid {
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
            margin-bottom: 1rem !important;
            page-break-inside: avoid;
        }
        .card-header {
            background: transparent !important;
            border-bottom: 2px solid #000 !important;
            color: #000 !important;
            padding: 0.5rem 0 !important;
        }
        .card-header h5 {
            font-size: 14px !important;
            font-weight: bold !important;
            color: #000 !important;
        }
        .card-body {
            padding: 0.5rem 0 !important;
        }
        .badge {
            border: 1px solid #000 !important;
            color: #000 !important;
            background: transparent !important;
        }
        img {
            max-height: 250px !important;
        }
        .text-muted, .text-primary, .text-success, .text-warning, .text-info, .text-danger, .text-dark {
            color: #000 !important;
        }
        .col-lg-6 {
            width: 50% !important;
            float: left !important;
        }
        .row {
            display: flex !important;
            flex-wrap: wrap !important;
        }
        .alert {
            border: 1px solid #000 !important;
            background: transparent !important;
            color: #000 !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var canvas = document.getElementById('signature-pad');
        if (canvas) {
            var signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });

            function resizeCanvas() {
                setTimeout(function() {
                    var ratio =  Math.max(window.devicePixelRatio || 1, 1);
                    
                    var wasEmpty = signaturePad.isEmpty();
                    var data = !wasEmpty ? signaturePad.toDataURL() : null;

                    // Force canvas internal dimension to match physical display
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);

                    signaturePad.clear();
                    if (!wasEmpty && data) {
                        signaturePad.fromDataURL(data);
                    }
                }, 100);
            }

            window.addEventListener("resize", resizeCanvas);
            window.addEventListener("load", resizeCanvas);
            resizeCanvas();

            document.getElementById('clearSignature').addEventListener('click', function () {
                signaturePad.clear();
            });

            document.getElementById('approvalForm').addEventListener('submit', function(e) {
                var consentCheck = document.getElementById('adminConsentCheck');
                if (consentCheck && !consentCheck.checked) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Persetujuan Diperlukan',
                        text: 'Anda harus mencentang kotak persetujuan terlebih dahulu.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#0d6efd'
                    });
                    return;
                }

                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanda Tangan Diperlukan',
                        text: 'Mohon berikan tanda tangan digital Anda sebelum menyetujui.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#0d6efd'
                    });
                } else {
                    document.getElementById('signatureInput').value = signaturePad.toDataURL('image/png');
                    // Tampilkan loading
                    var btn = document.getElementById('btnApprove');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                }
            });

            // Calculate Total Cost
            var costInputs = document.querySelectorAll('.cost-input');
            var totalCostInput = document.getElementById('total_cost');

            function calculateTotal() {
                var total = 0;
                costInputs.forEach(function(input) {
                    var val = parseInt(input.value) || 0;
                    total += val;
                });
                if(totalCostInput) {
                    totalCostInput.value = total;
                }
            }

            costInputs.forEach(function(input) {
                input.addEventListener('input', calculateTotal);
            });
            
            // Initial calculation
            calculateTotal();
        }
        // Approval Form Double Submit Prevention
    const approvalForm = document.getElementById('approvalForm');
    const btnApprove = document.getElementById('btnApprove');
    
    if(approvalForm) {
        approvalForm.addEventListener('submit', function() {
            if(btnApprove) {
                // Biarkan event submit jalan dulu, lalu ganti state tombol
                setTimeout(function() {
                    btnApprove.disabled = true;
                    btnApprove.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                }, 50);
            }
        });
    }

    // Modal Reject Form Double Submit Prevention
    const rejectForm = document.querySelector('#rejectModal form');
    if(rejectForm) {
        rejectForm.addEventListener('submit', function() {
            const btnReject = this.querySelector('button[type="submit"]');
            if(btnReject) {
                setTimeout(function() {
                    btnReject.disabled = true;
                    btnReject.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                }, 50);
            }
        });
    }
});
</script>
@endpush
