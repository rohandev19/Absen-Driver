<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara Service</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; line-height: 1.5; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 0; font-size: 10px; }
        .title { text-align: center; font-weight: bold; font-size: 14px; text-decoration: underline; margin-top: 15px; }
        .doc-no { text-align: center; font-size: 11px; margin-bottom: 20px; }
        table.info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.info-table th, table.info-table td { border: 1px solid #000; padding: 6px; }
        table.info-table th { background-color: #f2f2f2; width: 30%; text-align: left; }
        .section-title { font-weight: bold; margin-top: 15px; margin-bottom: 5px; }
        .signatures { width: 100%; margin-top: 50px; }
        .signatures td { width: 50%; text-align: center; vertical-align: bottom; }
        .sign-box { height: 80px; }
        .sign-img { max-height: 60px; max-width: 150px; }
        .photo-box { text-align: center; margin: 10px 0; }
        .photo-img { max-height: 250px; max-width: 350px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT Hamada Global Jaya</h1>
        <p>Jl. Contoh Alamat No. 123, Kota, Indonesia | Telp: (021) 1234567</p>
    </div>

    @php
        $customerName = $report->customer->name ?? 'Customer Umum';
        $customerInitial = strtoupper(substr($customerName, 0, 3));
        $dateStr = $report->timestamp->format('Ymd');
        $docNumber = "No: BAP/HL-{$customerInitial}/{$dateStr}/" . str_pad($report->id, 3, '0', STR_PAD_LEFT);
        
        \Carbon\Carbon::setLocale('id');
        $hari = \Carbon\Carbon::parse($report->timestamp)->translatedFormat('l');
        $tanggalFull = \Carbon\Carbon::parse($report->timestamp)->translatedFormat('d F Y');
    @endphp

    <div class="title">BERITA ACARA PENYELESAIAN SERVICE KENDARAAN</div>
    <div class="doc-no">{{ $docNumber }}</div>

    <p>Pada hari ini, <strong>{{ $hari }}</strong>, tanggal <strong>{{ $tanggalFull }}</strong>, bertempat di titik koordinat {{ $report->gps_location }}, telah dilakukan perbaikan dan penanganan darurat (service) atas armada kendaraan dengan rincian sebagai berikut:</p>

    <table class="info-table">
        <tr><th>Nama Klien</th><td>{{ $customerName }}</td></tr>
        <tr><th>Plat Nomor</th><td>{{ $report->vehicle->plate_number ?? 'N/A' }}</td></tr>
        <tr><th>Nama Driver</th><td>{{ $report->driver->full_name ?? 'N/A' }}</td></tr>
        <tr><th>Waktu Laporan</th><td>{{ $report->timestamp->format('d-m-Y H:i') }}</td></tr>
    </table>

    <div class="section-title">Rincian Pekerjaan & Kondisi:</div>
    <table class="info-table">
        <tr>
            <th style="width:50%">Keluhan Awal (Dari Driver)</th>
            <th style="width:50%">Tindakan & Catatan (Dari Admin)</th>
        </tr>
        <tr>
            <td>{{ $report->description }}</td>
            <td>{{ $report->admin_notes ?? 'Telah diperiksa dan ditangani.' }}</td>
        </tr>
    </table>

    <div class="section-title">Foto Bukti Kendaraan:</div>
    <div class="photo-box">
        @if($report->vehicle_condition_photo_path && file_exists(storage_path('app/public/' . $report->vehicle_condition_photo_path)))
            <img src="{{ storage_path('app/public/' . $report->vehicle_condition_photo_path) }}" class="photo-img">
        @else
            <p style="color:red; font-style:italic">[Foto kondisi tidak tersedia]</p>
        @endif
    </div>

    <p><strong>Melalui Berita Acara ini, kedua belah pihak menyatakan bahwa pekerjaan service/penanganan darurat pada armada tersebut di atas telah SELESAI DILAKSANAKAN DENGAN BAIK. Kendaraan dinyatakan layak dan siap untuk kembali beroperasi.</strong></p>
    <p>Berita Acara ini merupakan dokumen sah yang mengikat kedua belah pihak dan dapat digunakan sebagai dasar penagihan biaya (invoice) atau keperluan administratif lainnya sesuai dengan perjanjian kerjasama yang berlaku.</p>
    <p style="font-size: 10px; font-style: italic;">* Catatan: Rincian biaya perbaikan dan daftar suku cadang (spare parts) yang digunakan dilampirkan secara terpisah pada dokumen Invoice / Tagihan.</p>

    <table class="signatures">
        <tr>
            <td>
                <strong>PIHAK PERTAMA</strong><br>PT Hamada Global Jaya
                <div class="sign-box">
                    @if($report->admin_signature_path && file_exists(storage_path('app/public/' . $report->admin_signature_path)))
                        <img src="{{ storage_path('app/public/' . $report->admin_signature_path) }}" class="sign-img">
                    @endif
                </div>
                <u>{{ $adminName }}</u><br>
                Jabatan: {{ $adminRole }}
            </td>
            <td>
                <strong>PIHAK KEDUA</strong><br>{{ $customerName }}
                <div class="sign-box">
                    @if($report->customer_signature_path && file_exists(storage_path('app/public/' . $report->customer_signature_path)))
                        <img src="{{ storage_path('app/public/' . $report->customer_signature_path) }}" class="sign-img">
                    @endif
                </div>
                <u>{{ $report->customer_signer_name ?? '.......................................' }}</u><br>
                Jabatan: {{ $report->customer_signer_role ?? '....................................' }}
            </td>
        </tr>
    </table>
</body>
</html>
