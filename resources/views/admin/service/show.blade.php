@extends('admin.layouts.app')

@section('title', 'Detail Service Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="fw-bold mb-0 fs-4 fs-md-3"><i class="bi bi-tools"></i> Detail Laporan Service</h2>
        <div class="d-flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm d-print-none shadow-sm" style="min-height:38px;">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
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
                    'pending' => 'bg-warning text-dark',
                    'approved_admin' => 'bg-info',
                    'pending_customer' => 'bg-primary',
                    'approved_customer' => 'bg-success',
                    'rejected' => 'bg-danger',
                    default => 'bg-secondary'
                };
                $statusText = match($report->status) {
                    'pending' => 'Menunggu Review Admin',
                    'approved_admin' => 'Disetujui Admin',
                    'pending_customer' => 'Menunggu Persetujuan Customer',
                    'approved_customer' => 'Disetujui Customer',
                    'rejected' => 'Ditolak',
                    default => $report->status
                };
            @endphp
            <h3 class="mb-0"><span class="badge {{ $statusBadge }} fs-6">{{ $statusText }}</span></h3>
        </div>
    </div>

    <div class="row g-3">
        {{-- Left Column: Photos — full-width on mobile --}}
        <div class="col-12 col-lg-6">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0 fs-6"><i class="bi bi-camera"></i> Foto Kondisi Kendaraan</h5>
                </div>
                <div class="card-body text-center p-2 p-md-3">
                    <img src="{{ asset('storage/' . $report->vehicle_condition_photo_path) }}" 
                         alt="Kondisi Kendaraan" 
                         class="img-fluid rounded shadow"
                         style="max-height: 350px; cursor: pointer; width: 100%; object-fit: contain;"
                         onclick="window.open(this.src, '_blank')">
                    <p class="text-muted mt-2 small mb-0">Klik untuk memperbesar</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-warning text-dark py-2">
                    <h5 class="mb-0 fs-6"><i class="bi bi-receipt"></i> Foto Kuitansi</h5>
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
            <div class="card mb-3">
                <div class="card-header bg-info text-white py-2">
                    <h5 class="mb-0 fs-6"><i class="bi bi-info-circle"></i> Informasi Laporan</h5>
                </div>
                <div class="card-body p-3">
                    {{-- Mobile: stacked info, Desktop: table --}}
                    <div class="d-none d-md-block">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <th width="40%">Tanggal Service:</th>
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

                    @if($report->admin_notes)
                        <hr>
                        <h6 class="fw-bold">Catatan Admin:</h6>
                        <p class="text-muted">{{ $report->admin_notes }}</p>
                    @endif

                    @if($report->rejected_reason)
                        <hr>
                        <div class="alert alert-danger mb-0">
                            <h6 class="fw-bold">Alasan Penolakan:</h6>
                            <p class="mb-0">{{ $report->rejected_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions Card --}}
            @if($report->status === 'pending')
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <h5 class="mb-0 fs-6"><i class="bi bi-check-circle"></i> Aksi Admin</h5>
                        </div>
                        <div class="card-body p-3">
                            <form action="{{ route('admin.service.approve', $report->id) }}" method="POST" id="approvalForm">
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
                                    <div class="border rounded bg-light" style="position: relative;">
                                        <canvas id="signature-pad" class="signature-pad" style="touch-action: none; width: 100%; height: 200px;"></canvas>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearSignature" style="min-height:36px;">
                                        <i class="bi bi-eraser"></i> Hapus Tanda Tangan
                                    </button>
                                    <input type="hidden" name="signature" id="signatureInput" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Catatan Admin (Opsional)</label>
                                    <textarea name="admin_notes" class="form-control" rows="3" 
                                              placeholder="Tambahkan catatan untuk customer..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100 mb-2" id="btnApprove" style="min-height:44px;">
                                    <i class="bi bi-check-circle"></i> Setujui & Kirim ke Customer
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal" style="min-height:44px;">
                                <i class="bi bi-x-circle"></i> Tolak Laporan
                            </button>
                        </div>
                    </div>
            @endif

            {{-- Export Dokumen --}}
            @if(in_array($report->status, ['approved_admin', 'pending_customer', 'approved_customer']))
                <div class="card mb-3">
                    <div class="card-header bg-dark text-white py-2">
                        <h5 class="mb-0 fs-6"><i class="bi bi-file-earmark-word"></i> Export Dokumen</h5>
                    </div>
                    <div class="card-body p-3">
                        @if($report->customer_word_path)
                        <a href="{{ asset('storage/' . $report->customer_word_path) }}" 
                           class="btn btn-success w-100 mb-2" download style="min-height:44px; display:flex; align-items:center; justify-content:center;">
                            <i class="bi bi-download me-1"></i> Download Berita Acara (Customer)
                        </a>
                        @endif
                        <a href="{{ route('admin.service.export_finance', $report->id) }}" 
                           class="btn btn-primary w-100" style="min-height:44px; display:flex; align-items:center; justify-content:center;">
                            <i class="bi bi-download me-1"></i> Download Pengajuan Finance
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
</div>
@endsection

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
                var ratio =  Math.max(window.devicePixelRatio || 1, 1);
                var wasEmpty = signaturePad.isEmpty();
                var data = !wasEmpty ? signaturePad.toDataURL() : null;

                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);

                signaturePad.clear();
                if (!wasEmpty && data) {
                    signaturePad.fromDataURL(data);
                }
            }

            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            document.getElementById('clearSignature').addEventListener('click', function () {
                signaturePad.clear();
            });

            document.getElementById('approvalForm').addEventListener('submit', function (e) {
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert("Tanda tangan wajib diisi sebelum disetujui!");
                } else {
                    var dataUrl = signaturePad.toDataURL('image/png');
                    document.getElementById('signatureInput').value = dataUrl;
                }
            });
        }
    });
</script>
@endpush
