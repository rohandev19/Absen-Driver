@extends('admin.layouts.app')

@section('title', 'Panduan Penggunaan')

@section('content')
<div class="container-fluid px-0">
    <!-- header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-dark mb-1">Panduan Penggunaan Portal Admin</h4>
            <p class="text-muted" style="font-size: 0.9rem;">Pelajari cara menggunakan berbagai fitur yang tersedia di portal operasional Hamada Logistik.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="accordion" id="accordionPanduan">

                @if(Auth::user()->isMaster())
                <!-- 1. Dashboard Utama -->
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSatu">
                            <i class="bi bi-speedometer2 text-primary me-3 fs-5"></i> 1. Dashboard Utama & Laporan Absensi
                        </button>
                    </h2>
                    <div id="collapseSatu" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Dashboard memberikan ringkasan status kehadiran driver dan status unit kendaraan secara Real-Time.</p>
                            <ul>
                                <li><strong>Status Kehadiran:</strong> Anda dapat melihat jumlah driver yang sudah absen hari ini, yang belum pulang, yang absen di luar kantor (Offline/Late Submission), dan lain-lain.</li>
                                <li><strong>Statistik Kendaraan:</strong> Menampilkan jumlah kendaraan operasional yang sehat, butuh servis, atau sedang diservis.</li>
                                <li><strong>Peta Persebaran:</strong> Jika klik fitur Tracking/Peta, Anda dapat melihat lokasi driver berdasarkan titik GPS saat mereka melakukan absensi atau menekan tombol darurat.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 2. Manajemen Driver -->
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDua">
                            <i class="bi bi-person-badge text-primary me-3 fs-5"></i> 2. Manajemen Driver & QR Code
                        </button>
                    </h2>
                    <div id="collapseDua" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Menu ini digunakan untuk mendaftarkan dan mengelola data driver.</p>
                            <ol>
                                <li><strong>Tambah Driver:</strong> Klik "Tambah Driver" lalu masukkan NIK, Nama, Email, Password, dan unggah dokumen KTP/SIM.</li>
                                <li><strong>Cetak QR Code Driver:</strong> Setiap driver akan otomatis dibuatkan QR Code. QR Code ini digunakan oleh Security atau Anda sendiri untuk mengecek status driver (apakah mereka sedang aktif bertugas atau tidak) cukup dengan melakukan scan menggunakan HP.</li>
                                <li><strong>Riwayat Driver:</strong> Melihat rincian jam kerja, jam masuk, jam pulang, dan estimasi waktu kerja (Hour of Service) dari masing-masing driver secara spesifik.</li>
                                <li><strong>Rekap Harian & Bulanan:</strong> Menu untuk melihat dan mengunduh rekapan absensi driver ke dalam format Excel untuk keperluan penggajian.</li>
                            </ol>
                        </div>
                    </div>
                </div>
                @endif

                <!-- 3. Manajemen Unit Kendaraan -->
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTiga">
                            <i class="bi bi-truck text-primary me-3 fs-5"></i> 3. Manajemen Unit Kendaraan & QR Code
                        </button>
                    </h2>
                    <div id="collapseTiga" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Manajemen aset unit armada/kendaraan yang disewakan ke pihak Customer.</p>
                            <ol>
                                <li><strong>Tambah Kendaraan:</strong> Mendaftarkan kendaraan baru, menautkannya ke Customer/Project tertentu, dan menginput Odometer awal.</li>
                                <li><strong>Cetak QR Code Kendaraan:</strong> Setiap kendaraan memiliki QR Code. Jika QR ini di-scan, Anda bisa melihat Odometer terakhir, riwayat servis, dan detail kendaraan secara instan. Cetak QR Code ini dan tempelkan di kaca atau dashboard mobil.</li>
                                <li><strong>Riwayat Unit:</strong> Memantau penggunaan kendaraan berdasarkan laporan dari aplikasi driver.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- 4. Maintenance Mobil -->
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmpat">
                            <i class="bi bi-wrench-adjustable text-primary me-3 fs-5"></i> 4. Maintenance Mobil & Preventive Servis
                        </button>
                    </h2>
                    <div id="collapseEmpat" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Menu sentral untuk mengelola keandalan armada (Fleet Management).</p>
                            <ul>
                                <li><strong>Monitoring Servis:</strong> Halaman awal untuk melihat daftar kendaraan. Anda dapat mengklik "Visual Check" untuk mencatat kerusakan ringan.</li>
                                <li><strong>Daftar Komponen:</strong> Pada halaman Visual Check kendaraan, Anda dapat menambahkan komponen (misal: Aki, Ban, Oli) lengkap dengan masa pakainya (berdasarkan Bulan atau Kilometer).</li>
                                <li><strong>Peringatan (Alerts):</strong> Jika masa pakai komponen (misal: Oli) sudah mendekati batas kilometer, sistem akan otomatis mengirim notifikasi WhatsApp ke Anda dan memunculkannya di halaman Alerts.</li>
                                <li><strong>Kalender Servis:</strong> Jadwal servis rutin dapat dilihat secara visual berbentuk kalender bulanan.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 5. Service Darurat -->
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLima">
                            <i class="bi bi-tools text-primary me-3 fs-5"></i> 5. Service Darurat & E-Signature Customer
                        </button>
                    </h2>
                    <div id="collapseLima" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Alur penanganan jika driver melaporkan kendaraan rusak/mogok di jalan melalui aplikasi mobile.</p>
                            <ol>
                                <li>Laporan dari driver akan masuk ke menu <strong>Laporan Darurat</strong>.</li>
                                <li>Mekanik dikirim, lalu Admin membuat <strong>Laporan Service Darurat</strong> (Menu Service Darurat -> Buat Laporan). Masukkan rincian biaya, suku cadang, dan upload struk/nota.</li>
                                <li>Admin menandatangani laporan secara digital menggunakan fitur <strong>E-Signature</strong>.</li>
                                <li>Laporan akan berubah status menjadi "Menunggu Customer". Notifikasi WhatsApp otomatis terkirim ke Customer (Penyewa mobil).</li>
                                <li>Customer menyetujui dan menandatangani lewat portal Customer.</li>
                                <li>Setelah ditandatangani kedua belah pihak, Anda dapat <strong>Mencetak Dokumen PDF (Export Finance)</strong> yang valid sebagai bukti penagihan biaya ke Customer atau klaim ke manajemen.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                @if(Auth::user()->isMaster())
                <!-- 6. Uang Jalan -->
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEnam">
                            <i class="bi bi-cash-coin text-primary me-3 fs-5"></i> 6. Uang Jalan (Transport Cost)
                        </button>
                    </h2>
                    <div id="collapseEnam" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Manajemen uang saku/perjalanan untuk driver.</p>
                            <ul>
                                <li>Driver mengajukan pengeluaran uang jalan dari aplikasi mobile (termasuk foto struk tol, bensin, dll).</li>
                                <li>Data akan masuk ke <strong>Daftar Laporan</strong> dengan status Pending.</li>
                                <li>Anda (Master) wajib melakukan <strong>Review (Approve/Reject)</strong> atas setiap biaya yang diajukan.</li>
                                <li>Laporan yang sudah di-Approve dapat <strong>Diserahkan ke Finance</strong>. Tombol "Submit to Finance" akan memindahkan statusnya, dan mencetak dokumen pencairan dana.</li>
                                <li>Menu <strong>Rekap Bulanan</strong> memungkinkan ekspor seluruh pengeluaran uang jalan driver per bulan ke dalam Excel.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 7. Kelola Master -->
                <div class="accordion-item border-0 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTujuh">
                            <i class="bi bi-database-fill-gear text-primary me-3 fs-5"></i> 7. Kelola Master Data
                        </button>
                    </h2>
                    <div id="collapseTujuh" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Pengaturan fundamental sistem:</p>
                            <ul>
                                <li><strong>Kelola Project:</strong> Daftarkan project penyewaan armada yang sedang berlangsung. Ini digunakan sebagai referensi saat driver melakukan operasional.</li>
                                <li><strong>Kelola Customer:</strong> Mendaftarkan akun untuk perusahaan penyewa. Akun inilah yang digunakan oleh Customer untuk login ke Portal Customer dan melakukan Approve Service Darurat.</li>
                                <li><strong>Kelola Pengguna:</strong> Menambah akun Admin baru (Bisa berupa Admin Master atau Admin Service/Mekanik). Admin Service memiliki akses terbatas (tidak bisa melihat keuangan/uang jalan).</li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
