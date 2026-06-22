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
                            <p>Halaman Dashboard memberikan ringkasan aktivitas penyewaan armada dan konfirmasi service yang perlu ditindaklanjuti.</p>
                            <ul>
                                <li><strong>Total Unit Aktif:</strong> Jumlah kendaraan milik Hamada Logistik yang saat ini sedang Anda sewa/gunakan.</li>
                                <li><strong>Unit Butuh Servis:</strong> Jumlah kendaraan yang sedang perlu perhatian service atau pemeliharaan.</li>
                                <li><strong>Service Menunggu Konfirmasi:</strong> Jumlah laporan service kendaraan yang membutuhkan review dan tanda tangan digital dari Anda selaku penyewa.</li>
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
                            <p>Menu ini menampilkan seluruh detail kendaraan yang terhubung dengan perusahaan Anda.</p>
                            <ol>
                                <li><strong>Daftar Unit:</strong> Anda dapat melihat informasi Plat Nomor, Merk/Tipe, dan Tahun Pembuatan armada.</li>
                                <li><strong>Detail Kendaraan:</strong> Klik tombol <b>"Lihat Detail"</b> pada salah satu kendaraan untuk melihat informasi mendalam.</li>
                                <li><strong>Sertifikat Layak Jalan:</strong> Di halaman Detail, Anda dapat mendownload Sertifikat Layak Jalan (PDF) yang membuktikan bahwa armada dalam kondisi prima sebelum diserahkan kepada Anda.</li>
                                <li><strong>Riwayat Servis:</strong> Di halaman Detail, Anda juga dapat melihat riwayat perawatan kendaraan yang tersedia untuk unit tersebut.</li>
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
                            <p>Menu ini digunakan untuk meninjau laporan service yang sudah diverifikasi Admin Hamada sebelum Anda memberikan konfirmasi.</p>
                            <ol>
                                <li>Buka menu <b>Approve Service</b> atau <b>Konfirmasi Service Unit</b>.</li>
                                <li>Pilih laporan dengan status <span class="badge bg-warning text-dark">Menunggu Konfirmasi</span>.</li>
                                <li>Klik detail laporan untuk meninjau kronologi kendala, tindakan penanganan, status akhir unit, foto sebelum service, foto setelah service, foto odometer, catatan admin, dan lokasi bila tersedia.</li>
                                <li>Gunakan tombol <b>Download Draft PDF</b> bila perlu menyimpan dokumen draft sebelum konfirmasi.</li>
                                <li>Jika data belum jelas, klik <b>Minta Klarifikasi</b> lalu tuliskan pesan yang perlu dijawab/diperbaiki oleh Admin Hamada.</li>
                                <li>Jika laporan tidak dapat diterima, klik <b>Tolak Laporan</b> lalu isi alasan penolakan.</li>
                                <li>Jika laporan sudah sesuai, klik <b>Konfirmasi Laporan</b>, isi Nama Lengkap dan Jabatan, centang pernyataan, lalu tanda tangani pada kanvas digital menggunakan jari atau mouse.</li>
                                <li>Klik <b>Konfirmasi</b>. Setelah berhasil, status berubah menjadi terkonfirmasi dan PDF final dengan tanda tangan Hamada serta tanda tangan Anda dapat diunduh.</li>
                            </ol>
                            <div class="alert alert-info border-0 rounded-3 mt-3 mb-0">
                                <strong>Catatan:</strong> halaman customer tidak menampilkan biaya service, kuitansi, invoice bengkel, atau catatan finance internal. Konfirmasi ini bukan kuitansi tagihan.
                            </div>
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
