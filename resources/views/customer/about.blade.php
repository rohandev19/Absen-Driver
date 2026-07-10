@extends('customer.layouts.app')

@section('title', 'About Us')

@section('content')
<div class="container-fluid py-2">
    {{-- Hero Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%); border-radius: 16px;">
                <div class="position-absolute top-0 end-0 p-3 opacity-10 d-none d-sm-block" style="font-size: 8rem; color: #fff;">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div class="card-body p-3 p-md-5 text-white position-relative">
                    {{-- Language Toggle --}}
                    <div class="position-absolute top-0 end-0 p-3" style="z-index: 5;">
                        <button id="langToggleBtn" onclick="toggleLanguage()" class="btn btn-sm border-0 d-flex align-items-center gap-2 px-3 py-1" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); border-radius: 20px; color: #fff; font-size: 0.8rem; font-weight: 600; transition: all 0.3s ease;">
                            <span id="langFlag">🇬🇧</span>
                            <span id="langLabel">EN</span>
                            <i class="bi bi-chevron-expand" style="font-size: 0.65rem;"></i>
                        </button>
                    </div>
                    <span class="badge bg-white bg-opacity-20 text-white px-3 py-1 mb-2" style="backdrop-filter: blur(5px); font-size: 0.75rem;">
                        <i class="bi bi-shield-check me-1"></i><span data-en="Official Portal" data-id="Portal Resmi">Official Portal</span>
                    </span>
                    <h1 class="fw-bold fs-3 fs-md-2 mb-2" style="line-height: 1.25;" data-en="About Customer Portal" data-id="Tentang Portal Customer">About Customer Portal</h1>
                    <p class="mb-0 text-white-50 fs-6 opacity-90" style="max-width: 600px;" data-en="A transparent, secure, and trusted fleet monitoring portal from PT Hamada Global Jaya." data-id="Portal monitoring armada yang transparan, aman, dan terpercaya dari PT Hamada Global Jaya.">A transparent, secure, and trusted fleet monitoring portal from PT Hamada Global Jaya.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tentang Perusahaan --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="About the Company" data-id="Tentang Perusahaan">About the Company</h5>
                    </div>
                    <p class="text-secondary mb-3" style="line-height: 1.7;" data-en="<strong>PT Hamada Global Jaya</strong> is a company engaged in transportation and logistics services. We are committed to providing <strong>safe, timely, and professional</strong> delivery services to all our customers." data-id="<strong>PT Hamada Global Jaya</strong> adalah perusahaan yang bergerak di bidang jasa transportasi dan logistik. Kami berkomitmen memberikan layanan pengiriman yang <strong>aman, tepat waktu, dan profesional</strong> kepada seluruh pelanggan kami."><strong>PT Hamada Global Jaya</strong> is a company engaged in transportation and logistics services. We are committed to providing <strong>safe, timely, and professional</strong> delivery services to all our customers.</p>
                    <p class="text-secondary mb-0" style="line-height: 1.7;" data-en="With a well-maintained vehicle fleet and modern monitoring systems, we ensure every unit operates in prime condition to support our customers' business needs." data-id="Dengan armada kendaraan yang terawat baik dan sistem monitoring modern, kami memastikan setiap unit beroperasi dalam kondisi prima untuk mendukung kebutuhan bisnis pelanggan.">With a well-maintained vehicle fleet and modern monitoring systems, we ensure every unit operates in prime condition to support our customers' business needs.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-laptop fs-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="About This Portal" data-id="Tentang Portal Ini">About This Portal</h5>
                    </div>
                    <p class="text-secondary mb-3" style="line-height: 1.7;" data-en="The <strong>Hamada Global Jaya</strong> Customer Portal is specially designed to provide <strong>full transparency</strong> to customers regarding the condition and reliability of leased vehicle units." data-id="Portal Customer <strong>Hamada Global Jaya</strong> dirancang khusus untuk memberikan <strong>transparansi penuh</strong> kepada pelanggan atas kondisi dan keandalan unit kendaraan sewa.">The <strong>Hamada Global Jaya</strong> Customer Portal is specially designed to provide <strong>full transparency</strong> to customers regarding the condition and reliability of leased vehicle units.</p>
                    <p class="text-secondary mb-0" style="line-height: 1.7;" data-en="Through this portal, you can monitor vehicle conditions in real-time, view maintenance history, ensure document compliance (STNK/KIR), and <strong>digitally sign</strong> service approvals directly from your phone or computer — no printing required." data-id="Melalui portal ini, Anda dapat memantau kondisi kendaraan secara real-time, melihat riwayat perawatan, memastikan kepatuhan dokumen (STNK/KIR), dan <strong>menandatangani secara digital</strong> persetujuan service langsung dari HP atau komputer Anda — tanpa perlu cetak dokumen.">Through this portal, you can monitor vehicle conditions in real-time, view maintenance history, ensure document compliance (STNK/KIR), and <strong>digitally sign</strong> service approvals directly from your phone or computer — no printing required.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- CARA APPROVE SERVICE (NEW SECTION) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; border-left: 4px solid #2563eb;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-pencil-square fs-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="How to Approve a Service Report" data-id="Cara Approve Laporan Service">How to Approve a Service Report</h5>
                    </div>
                    
                    <p class="text-secondary mb-4" style="line-height: 1.7;" data-en="When emergency vehicle repairs occur, the driver submits a service report. After the admin reviews and approves it, you will receive a notification to provide your approval. Here are the steps:" data-id="Ketika terjadi perbaikan darurat kendaraan, driver akan mengirim laporan service. Setelah admin mereview dan menyetujuinya, Anda akan menerima notifikasi untuk memberikan persetujuan. Berikut langkahnya:">When emergency vehicle repairs occur, the driver submits a service report. After the admin reviews and approves it, you will receive a notification to provide your approval. Here are the steps:</p>
                    
                    {{-- Step-by-step visual guide --}}
                    <div class="row g-3">
                        {{-- Step 1 --}}
                        <div class="col-md-6 col-lg-3">
                            <div class="text-center p-3 rounded-3 bg-light h-100 position-relative">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 36px; height: 36px; font-weight: 700;">1</div>
                                <h6 class="fw-bold text-dark mb-1" data-en="Open Notification" data-id="Buka Notifikasi">Open Notification</h6>
                                <p class="text-muted small mb-0" data-en="You receive a WhatsApp notification when a service report requires your approval. Click the link or log in to the portal." data-id="Anda menerima notifikasi WhatsApp saat laporan service memerlukan persetujuan Anda. Klik link atau login ke portal.">You receive a WhatsApp notification when a service report requires your approval. Click the link or log in to the portal.</p>
                            </div>
                        </div>

                        {{-- Step 2 --}}
                        <div class="col-md-6 col-lg-3">
                            <div class="text-center p-3 rounded-3 bg-light h-100">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 36px; height: 36px; font-weight: 700;">2</div>
                                <h6 class="fw-bold text-dark mb-1" data-en="Review Details" data-id="Periksa Detail">Review Details</h6>
                                <p class="text-muted small mb-0" data-en="Check the vehicle photo, repair description, and receipt. You can also download the draft Word document to review offline." data-id="Periksa foto kendaraan, deskripsi perbaikan, dan kuitansi. Anda juga bisa download draf dokumen Word untuk review secara offline.">Check the vehicle photo, repair description, and receipt. You can also download the draft Word document to review offline.</p>
                            </div>
                        </div>

                        {{-- Step 3 --}}
                        <div class="col-md-6 col-lg-3">
                            <div class="text-center p-3 rounded-3 bg-light h-100">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 36px; height: 36px; font-weight: 700;">3</div>
                                <h6 class="fw-bold text-dark mb-1" data-en="Digital Signature" data-id="Tanda Tangan Digital">Digital Signature</h6>
                                <p class="text-muted small mb-0" data-en="Fill in your name and title, then draw your signature directly on the screen using your finger (mobile) or mouse (desktop)." data-id="Isi nama dan jabatan Anda, lalu coret tanda tangan langsung di layar menggunakan jari (HP) atau mouse (desktop).">Fill in your name and title, then draw your signature directly on the screen using your finger (mobile) or mouse (desktop).</p>
                            </div>
                        </div>

                        {{-- Step 4 --}}
                        <div class="col-md-6 col-lg-3">
                            <div class="text-center p-3 rounded-3 bg-light h-100">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 36px; height: 36px; font-weight: 700;">4</div>
                                <h6 class="fw-bold text-dark mb-1" data-en="Done!" data-id="Selesai!">Done!</h6>
                                <p class="text-muted small mb-0" data-en="Your approval is recorded. The signed document is automatically generated and can be downloaded as proof. No printing needed!" data-id="Persetujuan Anda tercatat. Dokumen ber-tanda-tangan otomatis dibuat dan bisa didownload sebagai bukti. Tidak perlu cetak!">Your approval is recorded. The signed document is automatically generated and can be downloaded as proof. No printing needed!</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-0 small" style="border-radius: 10px;">
                        <i class="bi bi-lightbulb me-1"></i>
                        <span data-en="<strong>Tip:</strong> The entire process can be done from your phone. Make sure to use a stable internet connection when drawing your signature." data-id="<strong>Tips:</strong> Seluruh proses bisa dilakukan dari HP Anda. Pastikan koneksi internet stabil saat menggambar tanda tangan."><strong>Tip:</strong> The entire process can be done from your phone. Make sure to use a stable internet connection when drawing your signature.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fitur Portal --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-3 p-2 bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-stars fs-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="Available Features" data-id="Fitur yang Tersedia">Available Features</h5>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-speedometer2 text-primary fs-4 me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1" data-en="Monitoring Dashboard" data-id="Dashboard Monitoring">Monitoring Dashboard</h6>
                                        <p class="text-muted small mb-0" data-en="A summary of the entire vehicle fleet's health in one view." data-id="Ringkasan kesehatan seluruh armada kendaraan dalam satu tampilan.">A summary of the entire vehicle fleet's health in one view.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-truck text-primary fs-4 me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1" data-en="Vehicle Unit Details" data-id="Detail Unit Kendaraan">Vehicle Unit Details</h6>
                                        <p class="text-muted small mb-0" data-en="Complete information on each unit: health, documents, maintenance history." data-id="Informasi lengkap setiap unit: kesehatan, dokumen, riwayat perawatan.">Complete information on each unit: health, documents, maintenance history.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-pencil-square text-primary fs-4 me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1" data-en="Digital Service Approval" data-id="Approve Service Digital">Digital Service Approval</h6>
                                        <p class="text-muted small mb-0" data-en="Review and approve emergency repair reports with digital signature — no printing needed. Works on mobile and desktop." data-id="Review dan setujui laporan perbaikan darurat dengan tanda tangan digital — tanpa perlu cetak. Bisa dari HP maupun desktop.">Review and approve emergency repair reports with digital signature — no printing needed. Works on mobile and desktop.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-file-earmark-medical text-primary fs-4 me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1" data-en="Health Certificate" data-id="Sertifikat Kesehatan">Health Certificate</h6>
                                        <p class="text-muted small mb-0" data-en="Download vehicle condition certificates for audit purposes." data-id="Unduh sertifikat kondisi kendaraan untuk keperluan audit.">Download vehicle condition certificates for audit purposes.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-wrench-adjustable text-primary fs-4 me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1" data-en="Maintenance History" data-id="Riwayat Maintenance">Maintenance History</h6>
                                        <p class="text-muted small mb-0" data-en="Complete records of all services and maintenance performed." data-id="Catatan lengkap semua servis dan perawatan yang telah dilakukan.">Complete records of all services and maintenance performed.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-person-badge text-primary fs-4 me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1" data-en="Profile & Security" data-id="Profil & Keamanan">Profile & Security</h6>
                                        <p class="text-muted small mb-0" data-en="Manage your account, change password, and monitor your activity." data-id="Kelola akun, ganti password, dan pantau aktivitas Anda.">Manage your account, change password, and monitor your activity.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Keamanan & Kepercayaan --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-danger bg-opacity-10 text-danger me-3">
                            <i class="bi bi-shield-lock-fill fs-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="Security & Data Protection" data-id="Keamanan & Perlindungan Data">Security & Data Protection</h5>
                    </div>
                    <p class="text-secondary mb-3" style="line-height: 1.7;" data-en="Your data security is our top priority. This portal is equipped with multiple layers of security to ensure your information is protected." data-id="Keamanan data Anda adalah prioritas utama kami. Portal ini dilengkapi dengan berbagai lapisan keamanan untuk memastikan informasi Anda terlindungi.">Your data security is our top priority. This portal is equipped with multiple layers of security to ensure your information is protected.</p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                <div>
                                    <strong class="text-dark small" data-en="Password Encryption" data-id="Enkripsi Password">Password Encryption</strong>
                                    <p class="text-muted small mb-0" data-en="Passwords are encrypted using industry-standard Bcrypt algorithm" data-id="Password dienkripsi menggunakan algoritma Bcrypt standar industri">Passwords are encrypted using industry-standard Bcrypt algorithm</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                <div>
                                    <strong class="text-dark small">Session Security</strong>
                                    <p class="text-muted small mb-0" data-en="Automatic session regeneration on every login to prevent session hijacking" data-id="Regenerasi session otomatis setiap login untuk mencegah session hijacking">Automatic session regeneration on every login to prevent session hijacking</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                <div>
                                    <strong class="text-dark small">CSRF Protection</strong>
                                    <p class="text-muted small mb-0" data-en="Every form is protected by CSRF tokens to prevent forged requests" data-id="Setiap form dilindungi token CSRF untuk mencegah permintaan palsu">Every form is protected by CSRF tokens to prevent forged requests</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                <div>
                                    <strong class="text-dark small" data-en="Restricted Access" data-id="Akses Terbatas">Restricted Access</strong>
                                    <p class="text-muted small mb-0" data-en="You can only view vehicle data belonging to your own project" data-id="Anda hanya bisa melihat data kendaraan milik proyek Anda sendiri">You can only view vehicle data belonging to your own project</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                <div>
                                    <strong class="text-dark small">Rate Limiting</strong>
                                    <p class="text-muted small mb-0" data-en="Request throttling to prevent brute force attacks" data-id="Pembatasan permintaan untuk mencegah serangan brute force">Request throttling to prevent brute force attacks</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                <div>
                                    <strong class="text-dark small">Input Validation</strong>
                                    <p class="text-muted small mb-0" data-en="All data is validated to prevent injection attacks" data-id="Semua data divalidasi untuk mencegah serangan injeksi">All data is validated to prevent injection attacks</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <style>
                .help-card-bg {
                    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
                }
                [data-bs-theme="dark"] .help-card-bg {
                    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(30, 64, 175, 0.15) 100%);
                }
            </style>
            <div class="card border-0 shadow-sm h-100 help-card-bg" style="border-radius: 14px;">
                <div class="card-body p-4 d-flex flex-column justify-content-center text-center">
                    <div class="mb-3">
                        <img src="{{ asset('images/hamada-logo.png') }}" alt="Hamada Global Jaya" style="max-width: 120px; height: auto;" class="mb-3">
                    </div>
                    <h5 class="fw-bold text-dark mb-2" data-en="Need Help?" data-id="Butuh Bantuan?">Need Help?</h5>
                    <p class="text-secondary small mb-3" data-en="If you have any questions or issues, please contact our team." data-id="Jika Anda memiliki pertanyaan atau kendala, silakan hubungi tim kami.">If you have any questions or issues, please contact our team.</p>
                    <div class="text-start mx-auto" style="max-width: 220px;">
                        <div class="mb-2">
                            <i class="bi bi-envelope text-primary me-2"></i>
                            <small class="text-dark">admin@hamada-gj.com</small>
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-telephone text-primary me-2"></i>
                            <small class="text-dark">0812-1298-4716</small>
                        </div>
                        <div>
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            <small class="text-dark">Depok, Indonesia</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="row">
        <div class="col-12">
            <div class="text-center py-3">
                <p class="text-muted small mb-1">
                    <i class="bi bi-shield-fill-check text-success me-1"></i>
                    <span data-en="This portal is built with the latest security standards to protect your data." data-id="Portal ini dibangun dengan standar keamanan terkini untuk melindungi data Anda.">This portal is built with the latest security standards to protect your data.</span>
                </p>
                <a href="{{ route('customer.privacy') }}" class="text-primary small text-decoration-none">
                    <i class="bi bi-file-lock me-1"></i><span data-en="Read Our Privacy Policy" data-id="Baca Kebijakan Privasi Kami">Read Our Privacy Policy</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function applyLanguage(lang) {
    document.querySelectorAll('[data-en]').forEach(function(el) {
        el.innerHTML = el.getAttribute('data-' + lang);
    });
    var flag = document.getElementById('langFlag');
    var label = document.getElementById('langLabel');
    var btn = document.getElementById('langToggleBtn');
    if (flag && label) {
        flag.textContent = lang === 'en' ? '🇬🇧' : '🇮🇩';
        label.textContent = lang === 'en' ? 'EN' : 'ID';
    }
    localStorage.setItem('hamada_lang', lang);
}

function toggleLanguage() {
    var current = localStorage.getItem('hamada_lang') || 'en';
    applyLanguage(current === 'en' ? 'id' : 'en');
}

document.addEventListener('DOMContentLoaded', function() {
    applyLanguage(localStorage.getItem('hamada_lang') || 'en');
});
</script>
@endpush
