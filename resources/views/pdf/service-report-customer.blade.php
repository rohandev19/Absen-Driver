<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; line-height: 1.42; }
        .header { width: 100%; border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 14px; }
        .company { font-size: 18px; font-weight: bold; margin: 0; }
        .subtitle { font-size: 10px; color: #4b5563; margin-top: 2px; }
        .right { text-align: right; }
        .doc-title { font-size: 14px; font-weight: bold; }
        .badge { display: inline-block; border: 1px solid #111827; border-radius: 4px; padding: 4px 8px; font-size: 9px; font-weight: bold; margin-top: 4px; }
        .notice { background: #f3f4f6; border: 1px solid #d1d5db; padding: 8px; margin: 10px 0 14px; font-size: 10px; }
        .section { margin-top: 13px; page-break-inside: avoid; }
        .section-title { font-size: 12px; font-weight: bold; background: #eef2ff; border: 1px solid #c7d2fe; padding: 6px 8px; }
        table { width: 100%; border-collapse: collapse; }
        .info td { border: 1px solid #d1d5db; padding: 7px 8px; vertical-align: top; }
        .label { width: 25%; font-weight: bold; background: #f9fafb; }
        .box { border: 1px solid #d1d5db; padding: 8px; min-height: 54px; white-space: pre-line; }
        .photo td { width: 50%; border: 1px solid #d1d5db; padding: 8px; vertical-align: top; text-align: center; }
        .photo-title { font-weight: bold; text-align: left; margin-bottom: 6px; }
        .img { max-width: 235px; max-height: 170px; border: 1px solid #d1d5db; }
        .empty { height: 110px; border: 1px dashed #9ca3af; color: #6b7280; padding-top: 50px; text-align: center; }
        .signature td { width: 50%; border: 1px solid #d1d5db; padding: 10px; vertical-align: top; text-align: center; }
        .sig-img { max-width: 170px; max-height: 75px; margin: 8px 0; }
        .sig-space { height: 75px; margin: 8px 0; }
        .name { font-weight: bold; margin-top: 5px; }
        .role, .muted { color: #4b5563; font-size: 10px; }
        .footer { position: fixed; left: 0; right: 0; bottom: -12px; border-top: 1px solid #d1d5db; padding-top: 5px; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
@php
    $driverName = $report->driver->name ?? $report->driver->nama ?? '-';
    $customerName = $report->customer->name ?? $report->customer->nama ?? $report->customer->company_name ?? '-';
    $plateNumber = $report->vehicle->plate_number ?? '-';
    $serviceType = $report->service_type ?? '-';
    $problemCategory = $report->problem_category ?? '-';
    $odometer = $report->odometer ? number_format((int) $report->odometer, 0, ',', '.') . ' KM' : '-';
@endphp

<table class="header">
    <tr>
        <td style="width: 56%;">
            <div class="company">PT Hamada Global Jaya</div>
            <div class="subtitle">Dokumen persetujuan laporan service kendaraan</div>
        </td>
        <td class="right" style="width: 44%;">
            <div class="doc-title">{{ $title }}</div>
            <span class="badge">{{ $documentStatus }}</span>
        </td>
    </tr>
</table>

<div class="notice">
    Dokumen ini merupakan dokumen customer. Informasi kuitansi, harga service, biaya sparepart, dan data pembayaran internal tidak ditampilkan.
</div>

<div class="section">
    <div class="section-title">A. Informasi Laporan</div>
    <table class="info">
        <tr>
            <td class="label">Nomor Tiket</td><td>{{ $reportNumber }}</td>
            <td class="label">Tanggal Laporan</td><td>{{ optional($report->timestamp)->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Plat Nomor</td><td>{{ $plateNumber }}</td>
            <td class="label">Customer / Project</td><td>{{ $customerName }}</td>
        </tr>
        <tr>
            <td class="label">Nama Driver</td><td>{{ $driverName }}</td>
            <td class="label">Lokasi GPS</td><td>{{ $report->gps_location ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Status Sistem</td><td>{{ strtoupper(str_replace('_', ' ', $report->status ?? '-')) }}</td>
            <td class="label">Tanggal Admin Approve</td><td>{{ optional($report->approved_at_admin)->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">B. Detail Kendala</div>
    <table class="info">
        <tr>
            <td class="label">Jenis Service</td><td>{{ $serviceType }}</td>
            <td class="label">Kategori Kendala</td><td>{{ $problemCategory }}</td>
        </tr>
        <tr>
            <td class="label">KM / Odometer</td><td>{{ $odometer }}</td>
            <td class="label">Status Unit</td><td>{{ $report->unit_status_after_service ?? '-' }}</td>
        </tr>
    </table>
    <div style="height: 8px;"></div>
    <div class="box">{{ $report->description ?? '-' }}</div>
</div>

<div class="section">
    <div class="section-title">C. Tindakan Service</div>
    <div class="box">{{ $report->service_action ?? '-' }}</div>
</div>

<div class="section">
    <div class="section-title">D. Catatan Admin untuk Customer</div>
    <div class="box">{{ $report->admin_notes ?? '-' }}</div>
</div>

<div class="section">
    <div class="section-title">E. Dokumentasi Kendaraan</div>
    <table class="photo">
        <tr>
            <td>
                <div class="photo-title">Foto Sebelum Service / Foto Kendala</div>
                @if ($vehiclePhoto)
                    <img class="img" src="{{ $vehiclePhoto }}" alt="Foto sebelum service">
                @else
                    <div class="empty">Foto tidak tersedia</div>
                @endif
            </td>
            <td>
                <div class="photo-title">Foto Setelah Service</div>
                @if ($afterServicePhoto)
                    <img class="img" src="{{ $afterServicePhoto }}" alt="Foto setelah service">
                @else
                    <div class="empty">Foto tidak tersedia</div>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">F. Informasi Pembuat Laporan</div>
    <table class="info">
        <tr>
            <td class="label">Dibuat Oleh</td><td>{{ $driverName }}</td>
            <td class="label">Sumber Laporan</td><td>{{ $report->report_source === 'admin_manual' ? 'Input Manual Admin Service' : 'Aplikasi Driver' }}</td>
        </tr>
        <tr>
            <td class="label">Waktu Submit</td><td>{{ optional($report->timestamp)->format('d/m/Y H:i') ?? '-' }}</td>
            <td class="label">Keterangan</td><td>Driver tidak memerlukan tanda tangan manual karena laporan dikirim melalui akun aplikasi.</td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">G. Persetujuan</div>
    <table class="signature">
        <tr>
            <td>
                <strong>Diverifikasi oleh Admin Service</strong>
                @if ($adminSignature)
                    <div><img class="sig-img" src="{{ $adminSignature }}" alt="Tanda tangan admin"></div>
                @else
                    <div class="sig-space"></div>
                @endif
                <div class="name">{{ $report->admin_signer_name ?? '-' }}</div>
                <div class="role">{{ $report->admin_signer_role ?? 'Admin Service' }}</div>
                <div class="role">{{ optional($report->approved_at_admin)->format('d/m/Y H:i') ?? '-' }}</div>
            </td>
            <td>
                <strong>Disetujui oleh Customer</strong>
                @if ($customerSignature)
                    <div><img class="sig-img" src="{{ $customerSignature }}" alt="Tanda tangan customer"></div>
                @else
                    <div class="sig-space"></div>
                @endif
                <div class="name">{{ $report->customer_signer_name ?? '-' }}</div>
                <div class="role">{{ $report->customer_signer_role ?? 'Customer / PIC' }}</div>
                <div class="role">{{ optional($report->approved_at_customer)->format('d/m/Y H:i') ?? '-' }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    Dicetak otomatis oleh sistem pada {{ now()->format('d/m/Y H:i') }} - Nomor dokumen: {{ $reportNumber }}
</div>
</body>
</html>
