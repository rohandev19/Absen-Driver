@extends('customer.layouts.app')

@section('title', 'Detail Service Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="fw-bold mb-0 fs-5 fs-md-4"><i class="bi bi-file-earmark-text"></i> Detail Laporan Service</h2>
        <div class="d-flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm d-print-none shadow-sm" style="min-height:40px; display:flex; align-items:center;">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
            <a href="{{ route('customer.approve.index') }}" class="btn btn-secondary btn-sm" style="min-height:40px; display:flex; align-items:center;">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Status Badge --}}
    <div class="card mb-3">
        <div class="card-body text-center py-3">
            @if($report->status === 'pending_customer')
                <span class="badge bg-warning text-dark fs-6 px-3 py-2">Menunggu Persetujuan Anda</span>
            @elseif($report->status === 'approved_customer')
                <span class="badge bg-success fs-6 px-3 py-2">Disetujui pada {{ $report->approved_at_customer->format('d-m-Y H:i') }}</span>
            @endif
        </div>
    </div>

    <div class="row g-3">
        {{-- Photo Column — full width on mobile --}}
        <div class="col-12 col-lg-6">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0 fs-6"><i class="bi bi-camera"></i> Foto Kondisi Kendaraan</h5>
                </div>
                <div class="card-body text-center p-2">
                    <img src="{{ asset('storage/' . $report->vehicle_condition_photo_path) }}" 
                         alt="Kondisi Kendaraan" 
                         class="img-fluid rounded shadow"
                         style="max-height: 300px; cursor: pointer; width: 100%; object-fit: contain;"
                         onclick="window.open(this.src, '_blank')">
                    <p class="text-muted mt-1 small mb-0">Klik untuk memperbesar</p>
                </div>
            </div>
        </div>

        {{-- Info & Actions Column — full width on mobile --}}
        <div class="col-12 col-lg-6">
            {{-- Info Card --}}
            <div class="card mb-3">
                <div class="card-header bg-info text-white py-2">
                    <h5 class="mb-0 fs-6"><i class="bi bi-info-circle"></i> Informasi Laporan</h5>
                </div>
                <div class="card-body p-3">
                    {{-- Mobile-friendly stacked info --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Tanggal</small>
                            <span class="small fw-semibold">{{ $report->timestamp->format('d-m-Y H:i') }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Plat Nomor</small>
                            <span class="badge bg-secondary">{{ $report->vehicle->plate_number ?? 'N/A' }}</span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Driver</small>
                            <span class="small">{{ $report->driver->full_name ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <hr class="my-2">

                    <h6 class="fw-bold small">Deskripsi Pekerjaan:</h6>
                    <p class="text-muted small mb-0">{{ $report->description }}</p>

                    @if($report->admin_notes)
                        <hr class="my-2">
                        <h6 class="fw-bold small">Catatan dari Admin:</h6>
                        <p class="text-muted small mb-0">{{ $report->admin_notes }}</p>
                    @endif
                </div>
            </div>

            {{-- Approval Actions --}}
            @if($report->status === 'pending_customer')
                <div class="card mb-3 border-warning">
                    <div class="card-header bg-warning text-dark py-2">
                        <h5 class="mb-0 fs-6"><i class="bi bi-exclamation-triangle"></i> Persetujuan Diperlukan</h5>
                    </div>
                    <div class="card-body p-3">
                        <p class="mb-3 small">
                            Untuk menyetujui service ini, silakan klik tombol di bawah. Anda dapat membaca draf dokumen Word terlebih dahulu, lalu menandatangani secara digital di layar.
                        </p>
                        
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#approveModal" style="min-height:48px; font-size:1rem;">
                            <i class="bi bi-check-circle me-1"></i> Approve Service
                        </button>
                    </div>
                </div>
            @elseif($report->status === 'approved_customer')
                <div class="card border-success mb-3">
                    <div class="card-header bg-success text-white py-2">
                        <h5 class="mb-0 fs-6"><i class="bi bi-check-circle"></i> Sudah Disetujui</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <small class="text-muted d-block fw-bold">Disetujui oleh:</small>
                                {{ $report->approvedByCustomer->name ?? '-' }}
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block fw-bold">Tanggal:</small>
                                {{ $report->approved_at_customer->format('d-m-Y H:i') }}
                            </div>
                        </div>

                        @if($report->customer_signed_document_path)
                            <a href="{{ asset('storage/' . $report->customer_signed_document_path) }}" 
                               class="btn btn-success w-100" 
                               download
                               style="min-height:44px; display:flex; align-items:center; justify-content:center;">
                                <i class="bi bi-download me-1"></i> Download Dokumen Ber-Tanda-Tangan
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Approve Modal — FULLSCREEN on mobile for better signature experience --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-fullscreen-md-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title fs-6">Approve Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div id="step1">
                    <h6 class="fw-bold mb-3">Step 1: Baca Draf Dokumen (Opsional)</h6>
                    <p class="small">Silakan download file Word di bawah ini untuk membaca rincian perbaikan dan mengecek tanda tangan Admin.</p>
                    
                    <a href="{{ route('customer.approve.download', $report->id) }}" 
                       class="btn btn-primary w-100 mb-3" 
                       id="downloadBtn"
                       style="min-height:48px; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-download me-1"></i> Download Draf Dokumen Word
                    </a>

                    <div class="alert alert-info small py-2 mb-3">
                        <i class="bi bi-info-circle"></i> Jika rincian sudah sesuai, lanjut ke Step 2 untuk tanda tangan.
                    </div>

                    <button type="button" class="btn btn-success w-100" onclick="showStep2()" style="min-height:48px; font-size:1rem;">
                        Lanjut ke Step 2 <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

                <div id="step2" style="display: none;">
                    <h6 class="fw-bold mb-3">Step 2: Isi Data dan Tanda Tangan Digital</h6>
                    <p class="small mb-3">Lengkapi nama, jabatan, dan coret tanda tangan Anda di bawah ini.</p>

                    <form action="{{ route('customer.approve.upload', $report->id) }}" 
                          method="POST" 
                          id="approvalForm">
                        @csrf
                        
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-bold mb-1">Nama Penandatangan</label>
                                <input type="text" name="signer_name" class="form-control" value="{{ Auth::user()->name }}" required style="min-height:44px;">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-bold mb-1">Jabatan</label>
                                <input type="text" name="signer_role" class="form-control" value="Perwakilan Customer" required style="min-height:44px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Tanda Tangan Digital (Wajib)</label>
                            <div class="border rounded bg-white position-relative" style="touch-action: none;">
                                <canvas id="signature-pad" class="signature-pad" style="width: 100%; height: 220px; touch-action: none; cursor: crosshair; display: block;"></canvas>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="clear-signature" style="min-height:36px;">
                                <i class="bi bi-eraser"></i> Hapus Tanda Tangan
                            </button>
                        </div>
                        
                        <input type="hidden" name="signature" id="signature-input" required>

                        <button type="button" id="btn-submit" class="btn btn-success w-100" style="min-height:48px; font-size:1rem;">
                            <i class="bi bi-check2-circle me-1"></i> Setujui Laporan
                        </button>
                    </form>

                    <button type="button" class="btn btn-secondary w-100 mt-2" onclick="showStep1()" style="min-height:44px;">
                        <i class="bi bi-arrow-left"></i> Kembali ke Step 1
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    var signaturePad = null;
    var canvas = null;

    function initSignaturePad() {
        canvas = document.getElementById('signature-pad');
        if (!canvas) return;

        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            var wasEmpty = signaturePad ? signaturePad.isEmpty() : true;
            var data = !wasEmpty ? signaturePad.toDataURL() : null;

            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);

            if (signaturePad) {
                signaturePad.clear();
                if (!wasEmpty && data) {
                    signaturePad.fromDataURL(data);
                }
            }
        }

        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 1)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 1,
            maxWidth: 3
        });

        window.addEventListener('resize', resizeCanvas);
        
        // Delay resize to ensure modal is fully rendered
        setTimeout(resizeCanvas, 150);

        document.getElementById('clear-signature').addEventListener('click', function () {
            signaturePad.clear();
        });

        document.getElementById('btn-submit').addEventListener('click', function (e) {
            if (signaturePad.isEmpty()) {
                alert("Silakan isi Tanda Tangan terlebih dahulu!");
                return false;
            }

            document.getElementById('signature-input').value = signaturePad.toDataURL('image/png');
            document.getElementById('approvalForm').submit();
        });
    }

    function showStep2() {
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        // Reinitialize signature pad after step change
        setTimeout(function() {
            if (!signaturePad) {
                initSignaturePad();
            } else {
                var ratio = Math.max(window.devicePixelRatio || 1, 1);
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
        }, 200);
    }

    function showStep1() {
        document.getElementById('step1').style.display = 'block';
        document.getElementById('step2').style.display = 'none';
    }

    // Initialize when modal is shown
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('approveModal');
        if (modalEl) {
            modalEl.addEventListener('shown.bs.modal', function() {
                if (!signaturePad) {
                    initSignaturePad();
                }
            });
        }
    });
</script>
@endpush
