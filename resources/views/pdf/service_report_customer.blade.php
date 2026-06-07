<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Service Darurat - {{ $report->ticket_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 0; font-size: 14px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px 0; }
        .section-title { font-size: 14px; font-weight: bold; border-bottom: 1px solid #ddd; margin-bottom: 10px; padding-bottom: 5px; }
        .content { margin-bottom: 20px; }
        .photos { text-align: center; margin-bottom: 20px; page-break-inside: avoid; }
        .photos img { max-width: 45%; max-height: 250px; margin: 5px; border: 1px solid #ccc; }
        .signature-table { width: 100%; text-align: center; margin-top: 40px; page-break-inside: avoid; }
        .signature-table td { width: 33%; vertical-align: bottom; height: 100px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT HAMADA LOGISTIK</h1>
        <p>Laporan Service Kendaraan (Customer)</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%"><strong>No. Tiket</strong></td>
            <td width="30%">: {{ $report->ticket_number ?? '-' }}</td>
            <td width="20%"><strong>Tanggal Service</strong></td>
            <td width="30%">: {{ $report->timestamp->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Plat Nomor</strong></td>
            <td>: {{ $report->vehicle->plate_number ?? '-' }}</td>
            <td><strong>Odometer</strong></td>
            <td>: {{ $report->odometer ? number_format($report->odometer, 0, ',', '.') . ' KM' : '-' }}</td>
        </tr>
        <tr>
            <td><strong>Nama Driver</strong></td>
            <td>: {{ $report->driver->full_name ?? '-' }}</td>
            <td><strong>Customer / Project</strong></td>
            <td>: {{ $report->customer->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Jenis Service</strong></td>
            <td>: {{ $report->service_type ?? '-' }}</td>
            <td><strong>Kategori Kendala</strong></td>
            <td>: {{ $report->problem_category ?? '-' }}</td>
        </tr>
    </table>

    <div class="content">
        <div class="section-title">Deskripsi Masalah</div>
        <p>{{ $report->description }}</p>
    </div>

    @if($report->service_action)
    <div class="content">
        <div class="section-title">Tindakan Service</div>
        <p>{{ $report->service_action }}</p>
    </div>
    @endif

    @if($report->unit_status_after_service)
    <div class="content">
        <div class="section-title">Status Unit Setelah Service</div>
        <p>{{ $report->unit_status_after_service }}</p>
    </div>
    @endif

    <div class="photos">
        <div class="section-title" style="text-align: left;">Dokumentasi Foto</div>
        @if($report->vehicle_condition_photo_path)
            <img src="{{ storage_path('app/public/' . $report->vehicle_condition_photo_path) }}" alt="Foto Sebelum">
        @endif
        @if($report->after_service_photo_path)
            <img src="{{ storage_path('app/public/' . $report->after_service_photo_path) }}" alt="Foto Sesudah">
        @endif
    </div>

    <table class="signature-table">
        <tr>
            <td>
                <p>Admin Service,</p>
                <br><br><br>
                <p><strong>{{ $report->admin_signer_name ?? '(.........................)' }}</strong></p>
            </td>
            <td>
                <p>Customer,</p>
                <br><br><br>
                <p><strong>{{ $report->customer_signer_name ?? '(.........................)' }}</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
