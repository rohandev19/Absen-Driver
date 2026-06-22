@extends('admin.layouts.app')

@section('title', 'Panduan Penggunaan')

@section('content')
<div class="container-fluid px-0">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-dark mb-1">Panduan Penggunaan Portal Admin</h4>
            <p class="text-muted" style="font-size: 0.9rem;">
                Panduan operasional untuk absensi, unit pengganti, service kendaraan, approval customer, dan proses internal Hamada Logistik.
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="alert alert-warning border-0 rounded-3 mb-4">
                <div class="fw-bold mb-1"><i class="bi bi-shield-lock me-2"></i>Aturan penting data customer</div>
                Customer tidak boleh menerima informasi biaya service, kuitansi, nota bengkel, atau catatan finance internal. Data biaya hanya untuk admin/finance internal.
            </div>

            <div class="accordion" id="accordionPanduan">

                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDriverApp">
                            <i class="bi bi-phone text-primary me-3 fs-5"></i> Alur Aplikasi Driver
                        </button>
                    </h2>
                    <div id="collapseDriverApp" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Alur driver di aplikasi mobile menjadi sumber data untuk absensi, laporan kendaraan, dan uang jalan.</p>
                            <ol>
                                <li><strong>Masuk tugas:</strong> driver scan QR driver, lalu scan QR unit. Jika unit belum punya QR, driver memilih input manual, mengisi plat, alasan, foto unit, GPS, odometer, selfie, dan foto speedometer.</li>
                                <li><strong>Unit manual:</strong> sistem otomatis membuat data kendaraan sementara dengan status <strong>Pending Verifikasi</strong>. Admin perlu verifikasi unit tersebut dari Daftar Aset sebelum QR dicetak dan unit dipakai berulang.</li>
                                <li><strong>Laporan kendaraan rusak:</strong> driver dapat membuat laporan rusak tanpa harus sedang absen masuk. Laporan masuk ke Service Darurat dengan status <strong>Menunggu Kelengkapan</strong>.</li>
                                <li><strong>Service selesai:</strong> setelah perbaikan selesai, driver melengkapi tindakan service, status akhir unit, foto setelah service, kuitansi internal, dan odometer bila ada. Setelah itu status menjadi <strong>Menunggu Review Admin</strong>.</li>
                                <li><strong>Pulang tugas:</strong> driver melakukan clock-out dengan odometer akhir, foto speedometer, checklist ban/lampu/rem, catatan, dan GPS bila tersedia. Jika offline, aplikasi mengirim pemulihan data saat koneksi kembali.</li>
                                <li><strong>Uang jalan:</strong> driver baru bisa mengajukan uang jalan setelah check-out pada tanggal tersebut, mengisi nomor DO, titik drop, lokasi pengiriman, biaya bensin/tol/parkir, jam kirim, dan foto struk bila ada.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                @if(Auth::user()->isMaster())
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSatu">
                            <i class="bi bi-speedometer2 text-primary me-3 fs-5"></i> 1. Dashboard Utama & Laporan Absensi
                        </button>
                    </h2>
                    <div id="collapseSatu" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Dashboard memberikan ringkasan status kehadiran driver dan status unit kendaraan.</p>
                            <ul>
                                <li><strong>Status Kehadiran:</strong> melihat driver yang sudah absen, sedang bertugas, belum pulang, atau memiliki data offline/late submission.</li>
                                <li><strong>Statistik Kendaraan:</strong> melihat jumlah unit aktif, rusak, perlu service, atau sedang diservice.</li>
                                <li><strong>Peta Persebaran:</strong> melihat titik GPS dari absensi atau laporan darurat jika data lokasi tersedia.</li>
                                <li><strong>Riwayat Driver:</strong> cek detail check-in/check-out, termasuk check-in unit pengganti tanpa QR yang ditandai sebagai input manual.</li>
                                <li><strong>Laporan Darurat:</strong> pantau laporan cepat dari driver, lalu tindak lanjuti sebagai service resmi atau tandai selesai sebagai info.</li>
                            </ul>
                        </div>
                    </div>
                </div>

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
                                <li><strong>Tambah Driver:</strong> isi NIK, nama, email, password, dan dokumen KTP/SIM.</li>
                                <li><strong>Cetak QR Code Driver:</strong> QR driver dipakai pada aplikasi mobile sebelum driver memilih unit kendaraan.</li>
                                <li><strong>Validasi SIM:</strong> pastikan masa berlaku SIM driver diperbarui agar akun tidak terkunci saat check-in.</li>
                                <li><strong>Riwayat Driver:</strong> pantau jam kerja, kilometer, unit yang dipakai, foto absensi, dan data unit manual tanpa QR.</li>
                                <li><strong>Rekap Harian & Bulanan:</strong> ekspor absensi driver ke Excel untuk kebutuhan operasional dan payroll.</li>
                            </ol>
                        </div>
                    </div>
                </div>
                @endif

                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTiga">
                            <i class="bi bi-truck text-primary me-3 fs-5"></i> {{ Auth::user()->isMaster() ? '3.' : '1.' }} Manajemen Unit Kendaraan, QR Code & Unit Pengganti
                        </button>
                    </h2>
                    <div id="collapseTiga" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Menu aset digunakan untuk menjaga data unit operasional tetap rapi, termasuk unit pengganti atau sementara.</p>
                            <ol>
                                <li><strong>Tambah Kendaraan:</strong> daftarkan plat, tipe, project/customer, odometer awal, status unit, dan tandai sebagai unit sementara bila memang unit pengganti.</li>
                                <li><strong>Unit dari input manual driver:</strong> sistem membuat record sementara berstatus <strong>Pending Verifikasi</strong>. Buka Daftar Aset, filter unit pending/sementara, cocokkan plat, project, catatan, dan foto dari riwayat driver.</li>
                                <li><strong>Verifikasi unit sementara:</strong> setelah data cocok, gunakan aksi verifikasi agar status unit berubah aktif dan QR dapat dicetak.</li>
                                <li><strong>Cetak QR Code Kendaraan:</strong> setelah unit terdaftar/terverifikasi dan QR tersedia, cetak QR lalu tempelkan di unit agar driver tidak perlu input manual lagi.</li>
                                <li><strong>Riwayat Unit:</strong> pantau pemakaian kendaraan, odometer, dan hubungan dengan laporan service.</li>
                            </ol>
                            <div class="alert alert-info border-0 rounded-3 mt-3 mb-0">
                                <strong>Catatan:</strong> input manual dari driver adalah solusi darurat saat QR belum ada di mobil. Setelah itu admin sebaiknya memverifikasi data unit dan mencetak QR.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmpat">
                            <i class="bi bi-wrench-adjustable text-primary me-3 fs-5"></i> {{ Auth::user()->isMaster() ? '4.' : '2.' }} Maintenance Mobil & Preventive Service
                        </button>
                    </h2>
                    <div id="collapseEmpat" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Maintenance digunakan untuk menjaga keandalan armada sebelum terjadi kerusakan besar.</p>
                            <ul>
                                <li><strong>Monitoring Service:</strong> lihat daftar kendaraan dan status kesehatannya.</li>
                                <li><strong>Visual Check:</strong> catat kerusakan ringan atau temuan lapangan pada unit.</li>
                                <li><strong>Daftar Komponen:</strong> tambahkan komponen seperti aki, ban, oli, dan masa pakainya berdasarkan bulan atau kilometer.</li>
                                <li><strong>Alerts:</strong> sistem memberi peringatan saat komponen mendekati jadwal penggantian.</li>
                                <li><strong>Kalender Service:</strong> lihat jadwal preventive service dalam bentuk kalender.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLima">
                            <i class="bi bi-tools text-primary me-3 fs-5"></i> {{ Auth::user()->isMaster() ? '5.' : '3.' }} Service Darurat, Kendaraan Rusak & Approval Customer
                        </button>
                    </h2>
                    <div id="collapseLima" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Alur service sekarang dipisahkan antara laporan awal kendaraan rusak, penyelesaian service, dan persetujuan customer.</p>

                            <h6 class="fw-bold text-dark mt-2">A. Laporan dari driver</h6>
                            <ol>
                                <li>Driver membuat <strong>Laporan Kendaraan Rusak</strong> dari aplikasi mobile tanpa harus absen masuk terlebih dahulu.</li>
                                <li>Laporan masuk ke menu <strong>Service Darurat -> Daftar Laporan</strong> dengan status <strong>Menunggu Kelengkapan</strong>.</li>
                                <li>Admin service mengecek kronologi, foto kondisi, plat unit, lokasi, driver, customer/project, dan tingkat urgensi.</li>
                                <li>Jika unit masuk bengkel 1-2 hari, biarkan laporan tetap menunggu sampai data service selesai dilengkapi.</li>
                            </ol>

                            <h6 class="fw-bold text-dark mt-3">B. Service selesai dari driver atau admin</h6>
                            <ol>
                                <li>Jika driver yang mengurus service, driver mengisi menu <strong>Service Selesai</strong> di aplikasi mobile. Status berubah menjadi <strong>Menunggu Review Admin</strong>.</li>
                                <li>Jika laporan awal sudah ada tetapi service diurus admin internal, buka detail laporan dan gunakan aksi ambil alih kelengkapan admin.</li>
                                <li>Jika service dibuat dari awal oleh admin, gunakan menu <strong>Service Darurat -> Input Service Manual</strong>.</li>
                                <li>Isi tindakan service, status unit setelah service, foto setelah service, dan kuitansi internal.</li>
                                <li>Kuitansi dipakai untuk kebutuhan internal/finance, bukan untuk customer.</li>
                            </ol>

                            <h6 class="fw-bold text-dark mt-3">C. Review admin dan kirim ke customer</h6>
                            <ol>
                                <li>Buka detail laporan, periksa kronologi awal, tindakan service, status unit, dan dokumentasi foto kendaraan.</li>
                                <li>Isi biaya service, sparepart, atau biaya lain hanya pada bagian internal jika diperlukan untuk finance.</li>
                                <li>Centang pernyataan bahwa laporan customer sudah diperiksa dan biaya/kuitansi tidak ditampilkan ke customer.</li>
                                <li>Admin menandatangani, lalu approve untuk membuat PDF customer dan mengirim laporan ke customer.</li>
                                <li>Customer hanya melihat kronologi, tindakan service, status unit, foto kendaraan, dan area tanda tangan approval.</li>
                                <li>Jika customer meminta klarifikasi, status menjadi <strong>Revisi Diminta</strong>. Perbaiki data/foto/catatan yang diperlukan, lalu ajukan ulang ke customer.</li>
                                <li>Jika customer menolak, laporan berstatus <strong>Ditolak Customer</strong> dan alasan penolakan tampil di detail laporan.</li>
                                <li>Jika customer menyetujui, laporan masuk ke menu <strong>Approval Customer</strong> dan dokumen final dapat diunduh.</li>
                            </ol>

                            <div class="alert alert-danger border-0 rounded-3 mt-3 mb-0">
                                <strong>Jangan kirim biaya/kuitansi ke customer.</strong> Dokumen customer memang disiapkan tanpa nominal biaya, tanpa kuitansi, dan tanpa catatan finance internal.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSembilan">
                            <i class="bi bi-shield-exclamation text-primary me-3 fs-5"></i> {{ Auth::user()->isMaster() ? '6.' : '4.' }} Laporan Darurat Cepat
                        </button>
                    </h2>
                    <div id="collapseSembilan" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Laporan darurat dipakai untuk respons cepat di lapangan, bukan otomatis menjadi dokumen customer.</p>
                            <ol>
                                <li>Buka menu <strong>Laporan Darurat</strong> untuk melihat laporan insiden dari driver.</li>
                                <li>Gunakan tombol <strong>Lokasi</strong> untuk membuka titik GPS dan tombol <strong>Foto</strong> untuk melihat bukti lapangan.</li>
                                <li>Jika kasusnya kendaraan rusak/service, klik <strong>Jadikan Service</strong>. Sistem akan membuat tiket service resmi dengan status menunggu kelengkapan.</li>
                                <li>Jika kasusnya hanya info/koordinasi dan tidak perlu approval customer, klik <strong>Tandai Selesai</strong> lalu isi catatan tindak lanjut.</li>
                                <li>Setelah menjadi laporan service, lengkapi alurnya dari menu <strong>Service Darurat -> Daftar Laporan</strong>.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDelapan">
                            <i class="bi bi-arrow-left-right text-primary me-3 fs-5"></i> {{ Auth::user()->isMaster() ? '7.' : '5.' }} Alur Unit Pengganti Saat Unit Utama Service
                        </button>
                    </h2>
                    <div id="collapseDelapan" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Unit pengganti perlu dicatat jelas agar absensi, odometer, service, dan rekapan tidak rancu.</p>
                            <ol>
                                <li>Driver tetap scan QR driver terlebih dahulu di aplikasi.</li>
                                <li>Jika unit pengganti punya QR, driver memilih <strong>Mobil sudah punya QR Code</strong> lalu scan QR unit.</li>
                                <li>Jika unit pengganti belum punya QR, driver memilih <strong>Mobil belum ada QR Code</strong>, input plat manual, alasan, dan foto unit. Sistem membuat unit sementara dengan status pending verifikasi.</li>
                                <li>Admin memeriksa Daftar Aset dan riwayat driver untuk memastikan plat manual, project, catatan, dan foto unit sesuai.</li>
                                <li>Jika unit pengganti akan dipakai lebih dari sekali, verifikasi/lengkapi unit tersebut di aset dan cetak QR.</li>
                                <li>Setelah unit utama selesai service, cek status laporan service dan pastikan unit utama boleh dipakai kembali.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                @if(Auth::user()->isMaster())
                <div class="accordion-item border-0 mb-3 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEnam">
                            <i class="bi bi-cash-coin text-primary me-3 fs-5"></i> 8. Uang Jalan (Transport Cost)
                        </button>
                    </h2>
                    <div id="collapseEnam" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Manajemen uang jalan untuk pengeluaran operasional driver.</p>
                            <ul>
                                <li>Driver mengajukan uang jalan dari aplikasi mobile setelah check-out, termasuk nomor DO, titik drop, lokasi, jam kirim, biaya bensin/tol/parkir, dan foto struk bila ada.</li>
                                <li>Data masuk ke daftar laporan dengan status pending.</li>
                                <li>Master melakukan review dan memilih approve/reject.</li>
                                <li>Laporan yang sudah approve dapat diajukan ke finance satu per satu atau secara bulk.</li>
                                <li>Rekap bulanan dan berkas pengajuan finance dapat diekspor untuk kebutuhan internal.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark bg-transparent rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTujuh">
                            <i class="bi bi-database-fill-gear text-primary me-3 fs-5"></i> 9. Kelola Master Data
                        </button>
                    </h2>
                    <div id="collapseTujuh" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                        <div class="accordion-body px-4 pb-4 pt-1 text-muted" style="font-size: 0.9rem;">
                            <p>Pengaturan dasar sistem.</p>
                            <ul>
                                <li><strong>Kelola Project:</strong> daftarkan project penyewaan armada yang sedang berjalan.</li>
                                <li><strong>Kelola Customer:</strong> buat akun perusahaan penyewa untuk akses portal customer dan approval laporan service.</li>
                                <li><strong>Kelola Pengguna:</strong> tambah akun admin master atau admin service. Admin service memiliki akses terbatas dan tidak berwenang membuka menu finance master.</li>
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
