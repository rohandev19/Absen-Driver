<!DOCTYPE html>
<html>

<head>
    <title>Export Riwayat Servis</title>
</head>

<body>
    {{-- Judul Laporan --}}
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td colspan="6" style="font-size: 16px; font-weight: bold; text-align: center;">
                BUKU RIWAYAT SERVIS KENDARAAN
            </td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: center;">
                PT Hamada Logistik
            </td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Plat Nomor</td>
            <td colspan="5">: {{ $vehicle->plate_number }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tipe/Model</td>
            <td colspan="5">: {{ $vehicle->type }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tgl Export</td>
            <td colspan="5">: {{ date('d F Y') }}</td>
        </tr>
    </table>
    <br>

    {{-- Tabel Data --}}
    <table border="1" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #d1e7dd;">
                <th style="width: 120px; text-align: center; height: 30px; vertical-align: middle;">TANGGAL SELESAI</th>
                <th style="width: 200px; text-align: center; vertical-align: middle;">KOMPONEN</th>
                <th style="width: 200px; text-align: center; vertical-align: middle;">BENGKEL</th>
                <th style="width: 100px; text-align: center; vertical-align: middle;">KM SERVIS</th>
                <th style="width: 150px; text-align: center; vertical-align: middle;">BIAYA AKTUAL (Rp)</th>
                <th style="width: 300px; text-align: center; vertical-align: middle;">CATATAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="text-align: center; vertical-align: top; padding: 5px;">
                        {{ \Carbon\Carbon::parse($log->completed_at ?? $log->scheduled_date)->format('d/m/Y') }}
                    </td>
                    <td style="vertical-align: top; padding: 5px;">
                        {{ $log->component ? $log->component->component_name : 'General Checkup' }}
                    </td>
                    <td style="vertical-align: top; padding: 5px;">
                        {{ $log->workshop_name ?: '-' }}
                    </td>
                    <td style="text-align: right; vertical-align: top; padding: 5px;">
                        {{ number_format($log->scheduled_km) }}
                    </td>
                    <td style="text-align: right; vertical-align: top; padding: 5px;">
                        {{ number_format($log->actual_cost ?: $log->estimated_cost) }}
                    </td>
                    <td style="vertical-align: top; padding: 5px;">
                        {{ $log->notes ?: '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Belum ada riwayat servis yang diselesaikan
                        dari jadwal.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
