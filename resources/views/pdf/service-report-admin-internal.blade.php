<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; line-height: 1.42; }
        .header { width: 100%; border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 14px; }
        .company { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 10px; color: #4b5563; }
        .right { text-align: right; }
        .doc-title { font-size: 14px; font-weight: bold; }
        .badge { display: inline-block; border: 1px solid #991b1b; color: #991b1b; border-radius: 4px; padding: 4px 8px; font-size: 9px; font-weight: bold; margin-top: 4px; }
        .notice { background: #fef2f2; border: 1px solid #fecaca; padding: 8px; margin: 10px 0 14px; font-size: 10px; color: #7f1d1d; }
        .section { margin-top: 13px; page-break-inside: avoid; }
        .section-title { font-size: 12px; font-weight: bold; background: #f3f4f6; border: 1px solid #d1d5db; padding: 6px 8px; }
        table { width: 100%; border-collapse: collapse; }
        .info td { border: 1px solid #d1d5db; padding: 7px 8px; vertical-align: top; }
        .label { width: 25%; font-weight: bold; background: #f9fafb; }
        .box { border: 1px solid #d1d5db; padding: 8px; min-height: 54px; white-space: pre-line; }
        .photo td { width: 50%; border: 1px solid #d1d5db; padding: 8px; vertical-align: top; text-align: center; }
        .photo-title { font-weight: bold; text-align: left; margin-bottom: 6px; }
        .img { max-width: 235px; max-height: 160px; border: 1px solid #d1d5db; }
        .empty { height: 105px; border: 1px dashed #9ca3af; color: #6b7280; padding-top: 45px; text-align: center; }
        .signature td { width: 50%; border: 1px solid #d1d5db; padding: 10px; vertical-align: top; text-align: center; }
        .sig-img { max-width: 170px; max-height: 75px; margin: 8px 0; }
        .sig-space { height: 75px; margin: 8px 0; }
        .name { font-weight: bold; margin-top: 5px; }
        .role { color: #4b5563; font-size: 10px; }
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
    $serviceCost = $report->service_cost ?? null;
    $sparepartCost = $report->sparepart_cost ?? null;
    $otherCost = $report->other_cost ?? null;
    $totalCost = $report->total_cost ?? (($serviceCost ?? 0) + ($sparepartCost ?? 0) + ($otherCost ?? 0));
    $rupiah = fn($value) => $value === null ? '-' : 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

<table class="header">
    <tr>
        <td style="width: 56%;">
            <div class="company">PT Hamada Logistik</div>
            <div class="subtitle">Dokumen internal laporan service kendaraan</div>
        </td>
        <td class="right" style="width: 44%;">
            <div class="doc-title">{{ $title }}</div>
            <span class="badge">{{ $documentStatus }}</span>
        </td>
    </tr>
</table>

<div class="notice">
    Dokumen ini bersifat internal. Dokumen ini boleh memuat kuitansi, nominal biaya, dan informasi pengajuan finance. Jangan dikirim ke customer.
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
            <td class="label">GPS</td><td>{{ $report->gps_location ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Status</td><td>{{ strtoupper(str_replace('_', ' ', $report->status ?? '-')) }}</td>
            <td class="label">Admin Approve</td><td>{{ optional($report->approved_at_admin)->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">B. Detail Kendala dan Service</div>
    <table class="info">
        <tr>
            <td class="label">Jenis Service</td><td>{{ $serviceType }}</td>
            <td class="label">Kategori Kendala</td><td>{{ $problemCategory }}</td>
        </tr>
        <tr>
            <td class="label">Odometer</td><td>{{ $odometer }}</td>
            <td class="label">Status Unit</td><td>{{ $report->unit_status_after_service ?? '-' }}</td>
        </tr>
    </table>
    <div style="height: 8px;"></div>
    <div class="box"><strong>Deskripsi Kendala:</strong><br>{{ $report->description ?? '-' }}</div>
    <div style="height: 8px;"></div>
    <div class="box"><strong>Tindakan Service:</strong><br>{{ $report->service_action ?? '-' }}</div>
</div>

<div class="section">
    <div class="section-title">C. Informasi Biaya Internal</div>
    <table class="info">
        <tr>
            <td class="label">Nama Bengkel/Vendor</td><td>{{ $report->workshop_name ?? '-' }}</td>
            <td class="label">Nomor Invoice/Kuitansi</td><td>{{ $report->invoice_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Biaya Jasa</td><td>{{ $rupiah($serviceCost) }}</td>
            <td class="label">Biaya Sparepart</td><td>{{ $rupiah($sparepartCost) }}</td>
        </tr>
        <tr>
            <td class="label">Biaya Lainnya</td><td>{{ $rupiah($otherCost) }}</td>
            <td class="label">Total Biaya</td><td><strong>{{ $rupiah($totalCost ?: null) }}</strong></td>
        </tr>
    </table>
    <div style="height: 8px;"></div>
    <div class="box"><strong>Catatan Finance/Internal:</strong><br>{{ $report->finance_notes ?? '-' }}</div>
</div>

<div class="section">
    <div class="section-title">D. Dokumentasi Lengkap</div>
    <table class="photo">
        <tr>
            <td>
                <div class="photo-title">Foto Sebelum Service / Kendala</div>
                @if ($vehiclePhoto)<img class="img" src="{{ $vehiclePhoto }}" alt="Foto sebelum service">@else<div class="empty">Tidak tersedia</div>@endif
            </td>
            <td>
                <div class="photo-title">Foto Setelah Service</div>
                @if ($afterServicePhoto)<img class="img" src="{{ $afterServicePhoto }}" alt="Foto setelah service">@else<div class="empty">Tidak tersedia</div>@endif
            </td>
        </tr>
        <tr>
            <td>
                <div class="photo-title">Foto KM / Odometer</div>
                @if ($odometerPhoto)<img class="img" src="{{ $odometerPhoto }}" alt="Foto odometer">@else<div class="empty">Tidak tersedia</div>@endif
            </td>
            <td>
                <div class="photo-title">Foto Kuitansi / Bukti Internal</div>
                @if ($receiptPhoto)<img class="img" src="{{ $receiptPhoto }}" alt="Foto kuitansi">@else<div class="empty">Tidak tersedia</div>@endif
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">E. Catatan Admin Service</div>
    <div class="box">{{ $report->admin_notes ?? '-' }}</div>
</div>

<div class="section">
    <div class="section-title">F. Persetujuan</div>
    <table class="signature">
        <tr>
            <td>
                <strong>Admin Service</strong>
                @if ($adminSignature)<div><img class="sig-img" src="{{ $adminSignature }}" alt="TTD admin"></div>@else<div class="sig-space"></div>@endif
                <div class="name">{{ $report->admin_signer_name ?? '-' }}</div>
                <div class="role">{{ $report->admin_signer_role ?? 'Admin Service' }}</div>
                <div class="role">{{ optional($report->approved_at_admin)->format('d/m/Y H:i') ?? '-' }}</div>
            </td>
            <td>
                <strong>Customer / PIC</strong>
                @if ($customerSignature)<div><img class="sig-img" src="{{ $customerSignature }}" alt="TTD customer"></div>@else<div class="sig-space"></div>@endif
                <div class="name">{{ $report->customer_signer_name ?? '-' }}</div>
                <div class="role">{{ $report->customer_signer_role ?? 'Customer / PIC' }}</div>
                <div class="role">{{ optional($report->approved_at_customer)->format('d/m/Y H:i') ?? '-' }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="footer">Dicetak otomatis oleh sistem pada {{ now()->format('d/m/Y H:i') }} - Nomor dokumen: {{ $reportNumber }}</div>
</body>
</html>
