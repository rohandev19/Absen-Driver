<table>
    <thead>
        <tr>
            <td colspan="11" style="text-align: center; font-weight: bold;">LAPORAN RIWAYAT AKTIVITAS DRIVER</td>
        </tr>
        <tr>
            <td colspan="11" style="text-align: center; font-style: italic;">
                Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}
                s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
            </td>
        </tr>
        <tr>
            <td></td>
        </tr>

        <tr>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">No</th>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">Tanggal</th>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">Nama Driver</th>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">Project / Divisi</th>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">Plat Nomor</th>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">Metode Unit</th>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">Verifikasi Unit</th>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">Jam Masuk</th>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">Jam Keluar</th>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">Total Jam</th>
            <th style="border: 1px solid black; text-align: center; background-color: #f0f0f0;">Jarak (KM)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $row)
                <tr>
                    <td style="border: 1px solid black; text-align: center;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid black;">{{ \Carbon\Carbon::parse($row->time_in)->format('d-m-Y') }}</td>
                    <td style="border: 1px solid black;">{{ $row->driver->full_name ?? '-' }}</td>
                    <td style="border: 1px solid black;">{{ $row->driver->project->name ?? 'Pool / Umum' }}</td>
                    <td style="border: 1px solid black; text-align: center;">{{ $row->vehicle->plate_number ?? '-' }}</td>
                    <td style="border: 1px solid black; text-align: center;">
                        {{ ($row->vehicle_entry_method ?? 'qr') === 'manual' ? 'Manual Tanpa QR' : 'QR' }}
                    </td>
                    <td style="border: 1px solid black; text-align: center;">
                        {{ ($row->vehicle_verification_status ?? 'verified') === 'pending' ? 'Pending' : 'Terverifikasi' }}
                    </td>
                    <td style="border: 1px solid black; text-align: center;">
                        {{ \Carbon\Carbon::parse($row->time_in)->format('H:i') }}
                    </td>
                    <td style="border: 1px solid black; text-align: center;">
                        {{ \Carbon\Carbon::parse($row->time_out)->format('H:i') }}
                    </td>
            @php
                $diff = \Carbon\Carbon::parse($row->time_in)->diff(\Carbon\Carbon::parse($row->time_out))->format('%H:%I');
            @endphp
                    <td style="border: 1px solid black; text-align: center;">{{ $diff }}</td>
                    <td style="border: 1px solid black; text-align: center;">{{ ($row->speedo_akhir - $row->speedo_awal) }}</td>
                </tr>
        @endforeach
    </tbody>
</table>
