<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pengajuan Finance</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; line-height: 1.5; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 0; font-size: 12px; }
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
        <p>Divisi Keuangan & Operasional</p>
    </div>

    @php
        $dateStr = $report->timestamp->format('Ymd');
        $docNumber = "No: FIN/SVC/{$dateStr}/" . str_pad($report->id, 3, '0', STR_PAD_LEFT);
    @endphp

    <div class="title">PENGAJUAN BIAYA SERVICE KENDARAAN DARURAT</div>
    <div class="doc-no">{{ $docNumber }}</div>

    <p>Mohon persetujuan pencairan dana untuk perbaikan armada dengan rincian operasional sebagai berikut:</p>

    <table class="info-table">
        <tr><th>Tanggal Service</th><td>{{ $report->timestamp->format('d-m-Y H:i') }}</td></tr>
        <tr><th>Plat Nomor</th><td>{{ $report->vehicle->plate_number ?? 'N/A' }}</td></tr>
        <tr><th>Nama Driver</th><td>{{ $report->driver->full_name ?? 'N/A' }}</td></tr>
        <tr><th>Customer / Klien</th><td>{{ $report->customer->name ?? 'Belum di-link' }}</td></tr>
        <tr><th>Lokasi Darurat</th><td>{{ $report->gps_location }}</td></tr>
    </table>

    <div class="section-title">Rincian Kendala Teknis:</div>
    <table class="info-table">
        <tr><td>{{ $report->description }}</td></tr>
    </table>

    <div class="section-title">Rincian Estimasi Biaya:</div>
    <table class="info-table">
        <tr>
            <th style="width:70%; text-align:center;">Keterangan / Suku Cadang</th>
            <th style="width:30%; text-align:center;">Nominal (Rp)</th>
        </tr>
        <tr>
            <td>Total Klaim (Sesuai Kuitansi)</td>
            <td style="text-align: right;">..............................</td>
        </tr>
    </table>

    <div class="section-title">Lampiran Foto Kuitansi/Nota Pembelian:</div>
    <div class="photo-box">
        @if($report->receipt_photo_path && file_exists(storage_path('app/public/' . $report->receipt_photo_path)))
            <img src="{{ storage_path('app/public/' . $report->receipt_photo_path) }}" class="photo-img">
        @else
            <p style="color:red; font-style:italic">[Foto nota/kuitansi asli tidak dilampirkan atau rusak]</p>
        @endif
    </div>

    <table class="signatures">
        <tr>
            <td>
                <strong>Diajukan & Diverifikasi Oleh,</strong><br>Divisi Operasional/Service
                <div class="sign-box">
                    @if($report->approvedByAdmin && $report->admin_signature_path && file_exists(storage_path('app/public/' . $report->admin_signature_path)))
                        <img src="{{ storage_path('app/public/' . $report->admin_signature_path) }}" class="sign-img">
                    @endif
                </div>
                <u>{{ $report->approvedByAdmin->name ?? 'Admin Service' }}</u><br>
                Tgl: {{ $report->approved_at_admin ? $report->approved_at_admin->format('d-m-Y') : '..................' }}
            </td>
            <td>
                <strong>Disetujui Oleh,</strong><br>Divisi Finance
                <div class="sign-box">
                    
                </div>
                <u>(_______________________)</u><br>
                Nama Lengkap<br>
                Tgl: ........................
            </td>
        </tr>
    </table>
</body>
</html>
