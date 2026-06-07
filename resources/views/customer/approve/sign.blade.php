@extends('customer.layouts.app')

@section('title', 'Konfirmasi & Tanda Tangan Laporan')

@push('styles')
<style>
    /* Stepper Styles */
    .stepper-wrapper {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }
    .stepper-wrapper::before {
        content: '';
        position: absolute;
        top: 24px;
        left: 0;
        right: 0;
        height: 2px;
        background: #e9ecef;
        z-index: 1;
    }
    .stepper-item {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }
    .stepper-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: bold;
        color: #6c757d;
        transition: all 0.3s;
    }
    .stepper-item.completed .stepper-circle {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
    }
    .stepper-item.active .stepper-circle {
        border-color: var(--bs-primary);
        color: var(--bs-primary);
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
    }
    .stepper-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: #495057;
    }
    .stepper-subtitle {
        font-size: 0.75rem;
        color: #6c757d;
    }

    /* Mobile Stepper Adjustments */
    @media (max-width: 768px) {
        .stepper-title {
            font-size: 0.7rem;
            line-height: 1.2;
            margin-top: 5px;
        }
        .stepper-subtitle {
            display: none;
        }
        .stepper-circle {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }
        .stepper-wrapper::before {
            top: 16px; 
        }
    }

    /* Signature Styles */
    #signature-pad-wrapper {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        background-color: #f8f9fa;
        min-height: 250px;
    }
    .signature-clear-btn {
        position: absolute;
        bottom: 10px;
        left: 10px;
        z-index: 10;
    }
</style>
@endpush

@section('content')
<div class="container-fluid mb-5">
    
    {{-- Header --}}
    <div class="mb-4 d-flex align-items-center">
        <a href="{{ route('customer.approve.show', $report->id) }}" class="btn btn-sm btn-white border me-3 shadow-sm rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-1 fs-5 text-dark">Konfirmasi Service Unit</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item text-muted">Detail Laporan</li>
                    <li class="breadcrumb-item active" aria-current="page">Konfirmasi Laporan</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">
            <a href="#" class="text-decoration-none text-primary small fw-semibold d-none d-md-inline"><i class="bi bi-question-circle me-1"></i> Bantuan</a>
            <div class="d-flex align-items-center bg-white border rounded-pill px-2 px-md-3 py-1 shadow-sm">
                <div class="text-end me-2 d-none d-sm-block">
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
    <div class="card border-0 shadow-sm mb-4 bg-white">
        <div class="card-body p-3 p-md-4 pt-4 pt-md-5 pb-3 pb-md-4">
            <div class="stepper-wrapper w-100 w-md-75 mx-auto">
                <div class="stepper-item completed">
                    <div class="stepper-circle"><i class="bi bi-check-lg"></i></div>
                    <div class="stepper-title">Periksa</div>
                    <div class="stepper-subtitle">Selesai</div>
                </div>
                <div class="stepper-item active">
                    <div class="stepper-circle">2</div>
                    <div class="stepper-title text-primary">Konfirmasi</div>
                    <div class="stepper-subtitle">Sedang Berlangsung</div>
                </div>
                <div class="stepper-item">
                    <div class="stepper-circle">3</div>
                    <div class="stepper-title">Selesai</div>
                    <div class="stepper-subtitle">Menunggu</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Container --}}
    <form action="{{ route('customer.approve.upload', $report->id) }}" method="POST" id="signature-form">
        @csrf
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 p-md-5">
                <div class="mb-4">
                    <h5 class="fw-bold text-dark">Langkah 2 dari 3</h5>
                    <h3 class="fw-bold text-dark">Konfirmasi & Tanda Tangan Digital</h3>
                    <p class="text-muted small">Lengkapi data penandatanganan Anda dan berikan tanda tangan digital untuk mengonfirmasi laporan ini.</p>
                </div>

                <div class="row g-4 mb-4">
                    {{-- Pernyataan Konfirmasi --}}
                    <div class="col-12 col-lg-6">
                        <div class="alert alert-primary bg-primary bg-opacity-10 border-0 p-4 h-100 mb-0 rounded-3">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle-fill me-2"></i>Pernyataan Konfirmasi</h6>
                            <p class="small text-dark mb-3" style="line-height: 1.6;">
                                Dengan memberikan konfirmasi ini, Anda menyatakan bahwa laporan kendala dan penanganan unit telah diterima dan diketahui.
                            </p>
                            <p class="small text-dark mb-0 fw-semibold">
                                Konfirmasi ini <span class="text-primary text-decoration-underline">bukan</span> merupakan kuitansi tagihan atau rincian invoice pembayaran service.
                            </p>
                        </div>
                    </div>
                    
                    {{-- Form Inputs --}}
                    <div class="col-12 col-lg-6">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-muted">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light" name="signer_name" value="{{ Auth::user()->name }}" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-muted">Jabatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light" name="signer_role" placeholder="Contoh: Admin Operasional" required>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-3 border border-primary border-opacity-25 rounded" style="background-color: #f0f7ff;">
                            <div class="form-check d-flex align-items-start mb-0">
                                <input class="form-check-input flex-shrink-0 mt-1 shadow-sm border-primary" type="checkbox" id="consentCheck" required style="width: 1.5rem; height: 1.5rem; cursor: pointer;">
                                <label class="form-check-label small fw-semibold text-dark ms-3" for="consentCheck" style="cursor: pointer; line-height: 1.6;">
                                    Saya telah membaca seluruh informasi laporan dan pernyataan di atas dengan benar, serta memahami bahwa konfirmasi ini <span class="text-primary text-decoration-underline">bukan kuitansi tagihan atau rincian invoice</span>.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Signature Pad Area --}}
                <div class="mb-2">
                    <label class="form-label fw-bold text-dark fs-6 mb-3">Tanda Tangan Digital <span class="text-danger">*</span></label>
                </div>
                <div class="row g-4">
                    <div class="col-12 col-lg-8">
                        <div class="border rounded" style="position: relative; background-color: #f8f9fa; border: 2px dashed #dee2e6 !important; min-height: 250px;">
                            <canvas id="signature-pad" style="touch-action: none; width: 100%; height: 250px; cursor: crosshair; display: block;"></canvas>
                            <button type="button" class="btn btn-sm btn-white border shadow-sm text-secondary position-absolute" style="bottom: 10px; left: 10px; z-index: 10;" id="clear-signature">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Tanda Tangan
                            </button>
                        </div>
                        <input type="hidden" name="signature" id="signature-input" required>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="card border border-info border-opacity-25 bg-info bg-opacity-10 h-100 shadow-none">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-info mb-3">Petunjuk</h6>
                                <ul class="small text-dark ps-3 mb-0" style="line-height: 1.8;">
                                    <li>Gunakan mouse atau sentuh layar untuk membuat tanda tangan Anda pada area di samping.</li>
                                    <li>Pastikan tanda tangan terlihat jelas.</li>
                                    <li>Klik tombol "Reset Tanda Tangan" jika ingin mengulang.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer Actions Bar --}}
        <div class="card border-0 shadow-sm mt-4 bg-white sticky-bottom" style="bottom: 0px; z-index: 1000;">
            <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <a href="{{ route('customer.approve.show', $report->id) }}" class="btn btn-white border px-4 fw-semibold shadow-sm w-100 w-md-auto text-nowrap">
                    <i class="bi bi-arrow-left me-2"></i> <span class="d-inline d-md-none">Kembali</span><span class="d-none d-md-inline">Kembali ke Detail</span>
                </a>

                <div class="d-flex flex-column flex-md-row flex-wrap gap-2 justify-content-end w-100 w-md-auto">
                    <button type="button" class="btn btn-outline-primary px-4 fw-semibold shadow-sm w-100 w-md-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#clarifyModal">
                        <i class="bi bi-pencil-square me-2"></i> Klarifikasi
                    </button>
                    <button type="button" class="btn btn-outline-danger px-4 fw-semibold shadow-sm w-100 w-md-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle me-2"></i> Tolak
                    </button>
                    <button type="button" id="btn-submit" class="btn btn-success px-4 px-md-5 fw-semibold shadow-sm text-white w-100 w-md-auto text-nowrap">
                        <i class="bi bi-check-circle me-2"></i> Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Modals for Reject & Clarify --}}
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
                    <p class="small text-muted mb-3">Silakan tuliskan alasan Anda menolak hasil service ini.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="customer_rejection_reason" class="form-control bg-light" rows="4" required></textarea>
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
                    <p class="small text-muted mb-3">Tuliskan pesan klarifikasi untuk Admin mengenai laporan service ini.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pesan Klarifikasi <span class="text-danger">*</span></label>
                        <textarea name="customer_revision_notes" class="form-control bg-light" rows="4" required></textarea>
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
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        const clearButton = document.getElementById('clear-signature');
        const form = document.getElementById('signature-form');
        const signatureInput = document.getElementById('signature-input');
        const submitButton = document.getElementById('btn-submit');
        const consentCheck = document.getElementById('consentCheck');
        
        function resizeCanvas() {
            setTimeout(function() {
                const ratio =  Math.max(window.devicePixelRatio || 1, 1);
                
                // Force canvas internal dimension to match its physical CSS display
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                
                // Only clear if empty to avoid wiping a signature on scroll/resize
                if (signaturePad && signaturePad.isEmpty()) {
                    signaturePad.clear(); 
                }
            }, 100);
        }

        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: 'rgb(0, 0, 0)'
        });

        window.addEventListener("resize", resizeCanvas);
        // Run on load and after short delay to ensure layout is complete
        window.addEventListener("load", resizeCanvas);
        resizeCanvas();

        clearButton.addEventListener('click', function () {
            signaturePad.clear();
        });

        submitButton.addEventListener('click', function (e) {
            e.preventDefault();
            
            if (!consentCheck.checked) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Persetujuan Diperlukan',
                    text: 'Anda harus mencentang kotak pernyataan telah membaca informasi.',
                    confirmButtonText: 'Oke',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            if (signaturePad.isEmpty()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanda Tangan Diperlukan',
                    text: 'Silakan berikan tanda tangan digital Anda terlebih dahulu.',
                    confirmButtonText: 'Oke',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            // check required inputs
            const requiredInputs = form.querySelectorAll('input[required]');
            let isValid = true;
            requiredInputs.forEach(input => {
                // Lewati checkbox dan input hidden (seperti signature)
                if(input.type !== 'checkbox' && input.type !== 'hidden') {
                    if(!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                }
            });

            if(!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Formulir Belum Lengkap',
                    text: 'Mohon lengkapi semua isian teks yang wajib diisi (kolom merah).',
                    confirmButtonText: 'Oke',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            
            signatureInput.value = signaturePad.toDataURL('image/png');
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
            
            form.submit();
        });
    });
</script>
@endpush
