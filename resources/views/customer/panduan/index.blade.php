@extends('customer.layouts.app')

@section('title', 'Panduan Penggunaan')

@section('content')
<div class="container-fluid px-0">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-dark mb-1">Panduan Penggunaan Portal Customer</h4>
            <p class="text-muted" style="font-size: 0.9rem;">Pelajari cara menggunakan berbagai fitur yang tersedia di portal Hamada Logistik khusus untuk pelanggan.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="accordion" id="accordionPanduan">

                <!-- 1. Dashboard -->
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSatu">
                            <i class="bi bi-speedometer2 text-primary me-3 fs-5"></i> 1. Dashboard Utama
                        </button>
                    </h2>
                    <div id="collapseSatu" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Halaman Dashboard memberikan Anda ringkasan singkat (overview) mengenai aktivitas penyewaan armada Anda.</p>
                            <ul>
                                <li><strong>Total Unit Aktif:</strong> Jumlah kendaraan milik Hamada Logistik yang saat ini sedang Anda sewa/gunakan.</li>
                                <li><strong>Unit Butuh Servis:</strong> Jumlah kendaraan yang saat ini sedang dalam proses perbaikan darurat atau pemeliharaan rutin.</li>
                                <li><strong>Service Menunggu Approval:</strong> Jumlah laporan servis kendaraan yang membutuhkan Tanda Tangan/Persetujuan (Approval) dari Anda selaku penyewa.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 2. Unit Kendaraan -->
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDua">
                            <i class="bi bi-truck text-primary me-3 fs-5"></i> 2. Menu Unit Kendaraan
                        </button>
                    </h2>
                    <div id="collapseDua" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Menu ini menampilkan seluruh detail kendaraan yang Anda sewa.</p>
                            <ol>
                                <li><strong>Daftar Unit:</strong> Anda dapat melihat informasi Plat Nomor, Merk/Tipe, dan Tahun Pembuatan armada.</li>
                                <li><strong>Detail Kendaraan:</strong> Klik tombol <b>"Lihat Detail"</b> pada salah satu kendaraan untuk melihat informasi mendalam.</li>
                                <li><strong>Sertifikat Layak Jalan:</strong> Di halaman Detail, Anda dapat mendownload Sertifikat Layak Jalan (PDF) yang membuktikan bahwa armada dalam kondisi prima sebelum diserahkan kepada Anda.</li>
                                <li><strong>Riwayat Servis:</strong> Di halaman Detail, Anda juga dapat melihat kapan saja kendaraan tersebut masuk bengkel dan komponen apa saja yang diganti. Hal ini menjamin transparansi perawatan unit.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- 3. Approve Service -->
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTiga">
                            <i class="bi bi-clipboard-check text-primary me-3 fs-5"></i> 3. Approve Service (Tanda Tangan Digital)
                        </button>
                    </h2>
                    <div id="collapseTiga" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Jika armada Anda mengalami masalah di jalan dan mekanik Hamada datang untuk memperbaikinya, Anda sebagai pihak penyewa akan diminta memberikan persetujuan Tanda Tangan atas layanan tersebut.</p>
                            <ol>
                                <li>Buka menu <b>Approve Service</b>.</li>
                                <li>Pilih laporan servis yang statusnya <span class="badge bg-warning text-dark">Menunggu Tanda Tangan Customer</span>.</li>
                                <li>Klik <b>Review & Tanda Tangan</b>.</li>
                                <li>Anda akan melihat rincian masalah, foto sebelum/sesudah perbaikan, dan keterangan teknisi.</li>
                                <li>Gunakan fitur <b>E-Signature (Kanvas Tanda Tangan)</b> di layar untuk menandatangani dokumen menggunakan jari (di HP) atau mouse (di Laptop).</li>
                                <li>Masukkan Nama Terang dan Jabatan Anda.</li>
                                <li>Klik tombol <b>Simpan & Setujui Dokumen</b>.</li>
                                <li>Setelah disetujui, Anda dapat mengunduh dokumen PDF lengkap yang sudah dibubuhi tanda tangan pihak Hamada maupun tanda tangan Anda.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- 4. Pengaturan Akun -->
                <div class="accordion-item border-0 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmpat">
                            <i class="bi bi-person-gear text-primary me-3 fs-5"></i> 4. Profil & Ganti Password
                        </button>
                    </h2>
                    <div id="collapseEmpat" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Untuk alasan keamanan, Anda disarankan untuk mengganti password secara berkala.</p>
                            <ul>
                                <li><strong>Profil:</strong> Melihat nama akun, email, dan detail perusahaan Anda yang terdaftar di sistem.</li>
                                <li><strong>Ganti Password:</strong> Klik menu Ganti Password, masukkan password lama Anda, lalu masukkan password baru Anda dua kali. Klik "Simpan Perubahan".</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
