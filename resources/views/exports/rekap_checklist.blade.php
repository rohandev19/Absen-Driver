<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi</title>
    <style>
        body {
            font-family: 'Calibri', Arial, sans-serif;
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        .no-border {
            border: none !important;
            background-color: #ffffff;
        }

        /* Style untuk NIK */
        .nik-cell {
            text-align: center;
            mso-number-format: '\@';
            mso-style-id: 49;
        }
    </style>
</head>

<body>

    @php
        $totalDates = count($dates);
        // Kolom Tetap: NO, NIK, NAMA, ID, PROJECT, TOTAL = 6 Kolom
        $totalCols = 6 + $totalDates;

        // --- SETUP LEBAR TANDA TANGAN ---
        $ttdWidth = 4;
        $totalTtdArea = $ttdWidth * 3;
        $leftSpacer = $totalCols - $totalTtdArea;
        if ($leftSpacer < 0)
            $leftSpacer = 0;
    @endphp

    {{-- 1. JUDUL --}}
    <table>
        <tr>
            <td colspan="{{ $totalCols }}" class="no-border"
                style="font-size: 16px; font-weight: bold; text-align: center; height: 35px; vertical-align: middle;">
                REKAPITULASI ABSENSI - {{ strtoupper($projectName) }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols }}" class="no-border"
                style="text-align: center; font-style: italic; font-size: 11px;">
                PERIODE: {{ strtoupper($monthName) }}
            </td>
        </tr>
        <tr>
            <td class="no-border" colspan="{{ $totalCols }}"></td>
        </tr>
    </table>

    {{-- 2. TABEL DATA --}}
    <table>
        <thead>
            <tr>
                <th rowspan="2" width="5" style="background-color: #4472C4; color: #ffffff; font-weight: bold;">NO</th>
                <th rowspan="2" width="25" style="background-color: #4472C4; color: #ffffff; font-weight: bold;">NIK
                    (KTP)</th>
                <th rowspan="2" width="35" style="background-color: #4472C4; color: #ffffff; font-weight: bold;">NAMA
                    DRIVER</th>
                <th rowspan="2" width="15" style="background-color: #4472C4; color: #ffffff; font-weight: bold;">ID
                    BADGE</th>
                <th rowspan="2" width="20" style="background-color: #4472C4; color: #ffffff; font-weight: bold;">PROJECT
                </th>

                <th colspan="{{ $totalDates }}" style="background-color: #D9E1F2; color: #000000; font-weight: bold;">
                    TANGGAL</th>

                <th rowspan="2" width="8" style="background-color: #4472C4; color: #ffffff; font-weight: bold;">TOTAL
                </th>
            </tr>
            <tr>
                @foreach($dates as $date)
                    <th width="4" style="background-color: #D9E1F2; color: #000000; font-weight: bold;">{{ $date }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $index => $data)
                <tr style="background-color: {{ $index % 2 == 0 ? '#ffffff' : '#f3f3f3' }};">
                    <td>{{ $index + 1 }}</td>

                    {{-- PERBAIKAN UTAMA NIK: Beberapa cara yang terbukti berhasil --}}
                    <td class="nik-cell">
                        {{-- SOLUSI TERBAIK: Tambahkan prefix apostrof (') --}}
                        '{{ $data['nik_ktp'] }}
                    </td>

                    <td style="text-align: left; padding-left: 5px;">{{ $data['name'] }}</td>

                    {{-- ID Badge --}}
                    <td style="text-align: center; mso-number-format:'\@';">'{{ $data['id_driver'] }}</td>

                    <td>{{ $data['project'] }}</td>

                    @foreach($data['data'] as $status)
                        @php
                            $color = ($status == '✔' || $status == '✓') ? '#008000' : '#FF0000';
                        @endphp
                        <td style="color: {{ $color }}; font-weight: bold;">{{ $status }}</td>
                    @endforeach

                    <td style="background-color: #FFD966; font-weight: bold;">{{ $data['total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    {{-- 3. TANDA TANGAN (FULL BORDER BOX) --}}
    <table>
        {{-- JUDUL --}}
        <tr>
            <td colspan="{{ $leftSpacer }}" class="no-border"></td>

            {{-- Border: Atas, Kiri, Kanan (Bawah Kosong) --}}
            <td colspan="{{ $ttdWidth }}"
                style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: none; font-weight: bold; background-color: #fff;">
                PEMBUAT</td>
            <td colspan="{{ $ttdWidth }}"
                style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: none; font-weight: bold; background-color: #fff;">
                MENGETAHUI</td>
            <td colspan="{{ $ttdWidth }}"
                style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: none; font-weight: bold; background-color: #fff;">
                MENYETUJUI</td>
        </tr>

        {{-- SPACE (Tinggi 80px) --}}
        <tr>
            <td colspan="{{ $leftSpacer }}" class="no-border"></td>

            {{-- Border: Kiri, Kanan (Atas & Bawah Kosong) --}}
            <td colspan="{{ $ttdWidth }}"
                style="height: 80px; border-left: 1px solid #000; border-right: 1px solid #000; border-top: none; border-bottom: none; background-color: #fff;">
            </td>
            <td colspan="{{ $ttdWidth }}"
                style="height: 80px; border-left: 1px solid #000; border-right: 1px solid #000; border-top: none; border-bottom: none; background-color: #fff;">
            </td>
            <td colspan="{{ $ttdWidth }}"
                style="height: 80px; border-left: 1px solid #000; border-right: 1px solid #000; border-top: none; border-bottom: none; background-color: #fff;">
            </td>
        </tr>

        {{-- NAMA --}}
        <tr>
            <td colspan="{{ $leftSpacer }}" class="no-border"></td>

            {{-- Border: Bawah, Kiri, Kanan (Atas Kosong) --}}
            <td colspan="{{ $ttdWidth }}"
                style="border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-top: none; font-weight: bold; background-color: #fff;">
                PIC</td>
            <td colspan="{{ $ttdWidth }}"
                style="border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-top: none; font-weight: bold; background-color: #fff;">
                SUKMAWATI</td>
            <td colspan="{{ $ttdWidth }}"
                style="border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; border-top: none; font-weight: bold; background-color: #fff;">
                IMA RAHMAWWATI</td>
        </tr>
    </table>

</body>

</html>