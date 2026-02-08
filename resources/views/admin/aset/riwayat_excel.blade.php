<!DOCTYPE html>
<html>

<head>
    <title>Export Riwayat Servis</title>
</head>

<body>
    {{-- Judul Laporan --}}
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td colspan="4" style="font-size: 16px; font-weight: bold; text-align: center;">
                BUKU RIWAYAT SERVIS KENDARAAN
            </td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center;">
                PT HAMADA LOGISTIK
            </td>
        </tr>
        <tr>
            <td></td>
        </tr> {{-- Spasi Kosong --}}
        <tr>
            <td style="font-weight: bold;">Plat Nomor</td>
            <td colspan="3">: {{ $vehicle->plate_number }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tipe/Model</td>
            <td colspan="3">: {{ $vehicle->type }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tgl Export</td>
            <td colspan="3">: {{ date('d F Y') }}</td>
        </tr>
    </table>
    <br>

    {{-- Tabel Data --}}
    <table border="1" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #d1e7dd;"> {{-- Warna Hijau Muda --}}
                <th style="width: 120px; text-align: center; height: 30px; vertical-align: middle;">TANGGAL</th>
                <th style="width: 350px; text-align: center; vertical-align: middle;">KETERANGAN PENGERJAAN</th>
                <th style="width: 100px; text-align: center; vertical-align: middle;">KM SERVIS</th>
                <th style="width: 150px; text-align: center; vertical-align: middle;">DICATAT OLEH</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="text-align: center; vertical-align: top; padding: 5px;">
                        {{ \Carbon\Carbon::parse($log->service_date)->format('d/m/Y') }}
                    </td>
                    <td style="vertical-align: top; padding: 5px;">
                        {{ $log->description }}
                    </td>
                    <td style="text-align: right; vertical-align: top; padding: 5px;">
                        {{ number_format($log->km_at_service) }}
                    </td>
                    <td style="text-align: center; vertical-align: top; padding: 5px;">
                        {{ $log->recorder->name ?? 'Team' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">Belum ada riwayat servis.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>