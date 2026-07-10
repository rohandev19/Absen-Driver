@extends('customer.layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="container-fluid py-2">
    {{-- Hero Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%); border-radius: 16px;">
                <div class="position-absolute top-0 end-0 p-3 opacity-10 d-none d-sm-block" style="font-size: 8rem; color: #fff;">
                    <i class="bi bi-file-lock"></i>
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
                        <i class="bi bi-shield-lock me-1"></i><span data-en="Privacy Guaranteed" data-id="Privasi Terjamin">Privacy Guaranteed</span>
                    </span>
                    <h1 class="fw-bold fs-3 fs-md-2 mb-2" style="line-height: 1.25;" data-en="Privacy Policy" data-id="Kebijakan Privasi">Privacy Policy</h1>
                    <p class="mb-0 text-white-50 fs-6 opacity-90" style="max-width: 600px;" data-en="We are committed to protecting the privacy and security of your personal data." data-id="Kami berkomitmen melindungi privasi dan keamanan data pribadi Anda.">We are committed to protecting the privacy and security of your personal data.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Konten Kebijakan Privasi --}}
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            {{-- Terakhir Diperbarui --}}
            <div class="d-flex align-items-center mb-4 p-3 rounded-3" style="background: #f0f9ff;">
                <i class="bi bi-calendar-check text-primary me-2"></i>
                <small class="text-secondary"><span data-en="Last updated: {{ date('F d, Y') }}" data-id="Terakhir diperbarui: {{ date('d F Y') }}">Last updated: {{ date('F d, Y') }}</span></small>
            </div>

            {{-- Section 1 --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; min-width: 36px;">
                            <strong>1</strong>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="Data We Collect" data-id="Data yang Kami Kumpulkan">Data We Collect</h5>
                    </div>
                    <p class="text-secondary mb-3" style="line-height: 1.8;" data-en="In order to provide the fleet monitoring portal service, we collect and store the following data:" data-id="Dalam rangka memberikan layanan portal monitoring armada, kami mengumpulkan dan menyimpan data berikut:">In order to provide the fleet monitoring portal service, we collect and store the following data:</p>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-dot text-primary fs-4 me-1" style="line-height: 1;"></i>
                            <span class="text-secondary" data-en="<strong class='text-dark'>Account Information:</strong> Name, email address, and encrypted password used for login." data-id="<strong class='text-dark'>Informasi Akun:</strong> Nama, alamat email, dan password terenkripsi yang Anda gunakan untuk login."><strong class="text-dark">Account Information:</strong> Name, email address, and encrypted password used for login.</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-dot text-primary fs-4 me-1" style="line-height: 1;"></i>
                            <span class="text-secondary" data-en="<strong class='text-dark'>Company Information:</strong> Name and address of the partner company linked to your account." data-id="<strong class='text-dark'>Informasi Perusahaan:</strong> Nama dan alamat perusahaan rekanan yang terhubung dengan akun Anda."><strong class="text-dark">Company Information:</strong> Name and address of the partner company linked to your account.</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-dot text-primary fs-4 me-1" style="line-height: 1;"></i>
                            <span class="text-secondary" data-en="<strong class='text-dark'>Activity Data:</strong> Service approval records and your interactions on the portal." data-id="<strong class='text-dark'>Data Aktivitas:</strong> Catatan persetujuan service dan interaksi Anda di portal."><strong class="text-dark">Activity Data:</strong> Service approval records and your interactions on the portal.</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="bi bi-dot text-primary fs-4 me-1" style="line-height: 1;"></i>
                            <span class="text-secondary" data-en="<strong class='text-dark'>Technical Data:</strong> Session information to maintain the security of your login session." data-id="<strong class='text-dark'>Data Teknis:</strong> Informasi session untuk menjaga keamanan sesi login Anda."><strong class="text-dark">Technical Data:</strong> Session information to maintain the security of your login session.</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Section 2 --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; min-width: 36px;">
                            <strong>2</strong>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="How Data is Used" data-id="Bagaimana Data Digunakan">How Data is Used</h5>
                    </div>
                    <p class="text-secondary mb-3" style="line-height: 1.8;" data-en="The collected data is used <strong>exclusively</strong> for the following purposes:" data-id="Data yang dikumpulkan digunakan <strong>secara eksklusif</strong> untuk tujuan berikut:">The collected data is used <strong>exclusively</strong> for the following purposes:</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-check2-circle text-success me-2 mt-1"></i>
                                    <div>
                                        <strong class="text-dark small" data-en="Authentication & Authorization" data-id="Otentikasi & Otorisasi">Authentication & Authorization</strong>
                                        <p class="text-muted small mb-0" data-en="Verifying your identity during login and restricting access according to your rights." data-id="Memverifikasi identitas Anda saat login dan membatasi akses sesuai hak Anda.">Verifying your identity during login and restricting access according to your rights.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-check2-circle text-success me-2 mt-1"></i>
                                    <div>
                                        <strong class="text-dark small" data-en="Fleet Monitoring" data-id="Monitoring Armada">Fleet Monitoring</strong>
                                        <p class="text-muted small mb-0" data-en="Displaying vehicle data connected to your company's project." data-id="Menampilkan data kendaraan yang terhubung dengan proyek perusahaan Anda.">Displaying vehicle data connected to your company's project.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-check2-circle text-success me-2 mt-1"></i>
                                    <div>
                                        <strong class="text-dark small" data-en="Service Approval" data-id="Persetujuan Service">Service Approval</strong>
                                        <p class="text-muted small mb-0" data-en="Processing emergency repair approvals from the customer side." data-id="Memproses persetujuan perbaikan darurat dari pihak pelanggan.">Processing emergency repair approvals from the customer side.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-check2-circle text-success me-2 mt-1"></i>
                                    <div>
                                        <strong class="text-dark small" data-en="Service Improvement" data-id="Peningkatan Layanan">Service Improvement</strong>
                                        <p class="text-muted small mb-0" data-en="Helping us improve portal quality and user experience." data-id="Membantu kami meningkatkan kualitas portal dan pengalaman pengguna.">Helping us improve portal quality and user experience.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3 --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; min-width: 36px;">
                            <strong>3</strong>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="Your Data Protection" data-id="Perlindungan Data Anda">Your Data Protection</h5>
                    </div>
                    <p class="text-secondary mb-3" style="line-height: 1.8;" data-en="We implement various technical and organizational measures to protect your data:" data-id="Kami menerapkan berbagai langkah teknis dan organisasional untuk melindungi data Anda:">We implement various technical and organizational measures to protect your data:</p>
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="ps-0" style="width: 40px;"><i class="bi bi-lock-fill text-primary"></i></td>
                                    <td><strong class="text-dark" data-en="Password Encryption" data-id="Enkripsi Password">Password Encryption</strong></td>
                                    <td class="text-secondary small" data-en="Passwords are stored with Bcrypt hashing (12 rounds), never stored in plain text." data-id="Password disimpan dengan hashing Bcrypt (12 rounds), tidak pernah disimpan dalam bentuk teks biasa.">Passwords are stored with Bcrypt hashing (12 rounds), never stored in plain text.</td>
                                </tr>
                                <tr>
                                    <td class="ps-0"><i class="bi bi-shield-fill-check text-primary"></i></td>
                                    <td><strong class="text-dark">CSRF Protection</strong></td>
                                    <td class="text-secondary small" data-en="Every form is protected by CSRF tokens to prevent cross-site request forgery attacks." data-id="Setiap form dilindungi oleh token CSRF untuk mencegah serangan cross-site request forgery.">Every form is protected by CSRF tokens to prevent cross-site request forgery attacks.</td>
                                </tr>
                                <tr>
                                    <td class="ps-0"><i class="bi bi-key-fill text-primary"></i></td>
                                    <td><strong class="text-dark">Session Security</strong></td>
                                    <td class="text-secondary small" data-en="Sessions are regenerated on every login and stored securely in the server database." data-id="Session diregenerasi setiap login, dan disimpan dengan aman di database server.">Sessions are regenerated on every login and stored securely in the server database.</td>
                                </tr>
                                <tr>
                                    <td class="ps-0"><i class="bi bi-person-lock text-primary"></i></td>
                                    <td><strong class="text-dark">Role-Based Access</strong></td>
                                    <td class="text-secondary small" data-en="Access is restricted based on roles. Customers can only view their own project data." data-id="Akses dibatasi berdasarkan peran (role). Customer hanya bisa melihat data proyek mereka sendiri.">Access is restricted based on roles. Customers can only view their own project data.</td>
                                </tr>
                                <tr>
                                    <td class="ps-0"><i class="bi bi-speedometer text-primary"></i></td>
                                    <td><strong class="text-dark">Rate Limiting</strong></td>
                                    <td class="text-secondary small" data-en="Request throttling per minute to prevent brute force and DDoS attacks." data-id="Pembatasan jumlah permintaan per menit untuk mencegah serangan brute force dan DDoS.">Request throttling per minute to prevent brute force and DDoS attacks.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Section 4 --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; min-width: 36px;">
                            <strong>4</strong>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="Cookies & Sessions" data-id="Cookie & Session">Cookies & Sessions</h5>
                    </div>
                    <p class="text-secondary mb-3" style="line-height: 1.8;" data-en="This portal uses cookies and sessions to maintain security and ease of use:" data-id="Portal ini menggunakan cookie dan session untuk menjaga keamanan dan kenyamanan penggunaan:">This portal uses cookies and sessions to maintain security and ease of use:</p>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-cookie text-warning me-2 mt-1"></i>
                            <span class="text-secondary" data-en="<strong class='text-dark'>Session Cookie:</strong> Used to recognize your login session. This cookie is automatically deleted when you logout or when the session expires." data-id="<strong class='text-dark'>Session Cookie:</strong> Digunakan untuk mengenali sesi login Anda. Cookie ini otomatis dihapus saat Anda logout atau session berakhir."><strong class="text-dark">Session Cookie:</strong> Used to recognize your login session. This cookie is automatically deleted when you logout or when the session expires.</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-bookmark-check text-warning me-2 mt-1"></i>
                            <span class="text-secondary" data-en="<strong class='text-dark'>Remember Me:</strong> If you check &quot;Remember me&quot; during login, the cookie will be stored longer so you don't need to re-login every day." data-id="<strong class='text-dark'>Remember Me:</strong> Jika Anda mencentang &quot;Ingat saya&quot; saat login, cookie akan disimpan lebih lama agar Anda tidak perlu login ulang setiap hari."><strong class="text-dark">Remember Me:</strong> If you check "Remember me" during login, the cookie will be stored longer so you don't need to re-login every day.</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="bi bi-shield-exclamation text-warning me-2 mt-1"></i>
                            <span class="text-secondary" data-en="<strong class='text-dark'>CSRF Token:</strong> An automatic security token to verify that every request from you is authentic." data-id="<strong class='text-dark'>CSRF Token:</strong> Token keamanan otomatis untuk memverifikasi setiap permintaan Anda adalah asli."><strong class="text-dark">CSRF Token:</strong> An automatic security token to verify that every request from you is authentic.</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Section 5 --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; min-width: 36px;">
                            <strong>5</strong>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="Your Rights as a User" data-id="Hak Anda Sebagai Pengguna">Your Rights as a User</h5>
                    </div>
                    <p class="text-secondary mb-3" style="line-height: 1.8;" data-en="As a user of this portal, you have the following rights:" data-id="Sebagai pengguna portal ini, Anda memiliki hak-hak berikut:">As a user of this portal, you have the following rights:</p>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start p-2">
                                <i class="bi bi-arrow-right-circle text-primary me-2 mt-1"></i>
                                <span class="text-secondary small" data-en="<strong class='text-dark'>Right of Access:</strong> View personal data stored in your account." data-id="<strong class='text-dark'>Hak Akses:</strong> Melihat data pribadi yang tersimpan di akun Anda."><strong class="text-dark">Right of Access:</strong> View personal data stored in your account.</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start p-2">
                                <i class="bi bi-arrow-right-circle text-primary me-2 mt-1"></i>
                                <span class="text-secondary small" data-en="<strong class='text-dark'>Right of Correction:</strong> Request correction of inaccurate data." data-id="<strong class='text-dark'>Hak Koreksi:</strong> Meminta perbaikan data yang tidak akurat."><strong class="text-dark">Right of Correction:</strong> Request correction of inaccurate data.</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start p-2">
                                <i class="bi bi-arrow-right-circle text-primary me-2 mt-1"></i>
                                <span class="text-secondary small" data-en="<strong class='text-dark'>Change Password:</strong> Change your password at any time through the profile menu." data-id="<strong class='text-dark'>Ganti Password:</strong> Mengubah password kapan saja melalui menu profil."><strong class="text-dark">Change Password:</strong> Change your password at any time through the profile menu.</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start p-2">
                                <i class="bi bi-arrow-right-circle text-primary me-2 mt-1"></i>
                                <span class="text-secondary small" data-en="<strong class='text-dark'>Right to Inquire:</strong> Contact us for questions regarding your data." data-id="<strong class='text-dark'>Hak Pertanyaan:</strong> Menghubungi kami untuk pertanyaan terkait data Anda."><strong class="text-dark">Right to Inquire:</strong> Contact us for questions regarding your data.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 6 --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; min-width: 36px;">
                            <strong>6</strong>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" data-en="Third-Party Data Sharing" data-id="Pembagian Data ke Pihak Ketiga">Third-Party Data Sharing</h5>
                    </div>
                    <div class="p-3 rounded-3" style="background: #fef2f2; border-left: 4px solid #dc2626;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-x-circle-fill text-danger me-2 mt-1"></i>
                            <p class="text-secondary mb-0" style="line-height: 1.8;" data-en="We do <strong class='text-danger'>NOT</strong> sell, trade, or share your personal data with any third parties. Your data is only used for the internal service needs of PT Hamada Global Jaya." data-id="Kami <strong class='text-danger'>TIDAK</strong> menjual, memperdagangkan, atau membagikan data pribadi Anda kepada pihak ketiga mana pun. Data Anda hanya digunakan untuk keperluan layanan internal PT Hamada Global Jaya.">We do <strong class="text-danger">NOT</strong> sell, trade, or share your personal data with any third parties. Your data is only used for the internal service needs of PT Hamada Global Jaya.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kontak --}}
            <style>
                .help-card-bg {
                    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
                }
                [data-bs-theme="dark"] .help-card-bg {
                    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(30, 64, 175, 0.15) 100%);
                }
            </style>
            <div class="card border-0 shadow-sm help-card-bg" style="border-radius: 14px;">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-envelope-paper text-primary fs-2 mb-2"></i>
                    <h5 class="fw-bold text-dark mb-2" data-en="Have Questions?" data-id="Ada Pertanyaan?">Have Questions?</h5>
                    <p class="text-secondary small mb-3" data-en="If you have questions about this privacy policy, please contact us at:" data-id="Jika Anda memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi kami di:">If you have questions about this privacy policy, please contact us at:</p>
                    <p class="text-primary fw-semibold mb-0">
                        <i class="bi bi-envelope me-1"></i> admin@hamada-gj.com
                    </p>
                </div>
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
