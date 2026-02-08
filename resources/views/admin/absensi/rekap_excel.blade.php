<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi</title>
    <style>
        /* 1. SETTING HALAMAN AGAR MUAT */
        @page {
            size: A4 landscape;
            margin: 5mm;
            /* Margin diperkecil jadi 5mm agar area isi lebih luas */
        }

        body {
            font-family: 'Calibri', Arial, sans-serif;
            font-size: 9px;
            /* FONT DIPERKECIL dari 11px ke 9px */
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            /* Memaksa tabel mengikuti lebar yg ditentukan */
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 2px;
            /* PADDING DITIPISKAN dari 5px ke 2px */
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
            /* Mencegah teks meluber */
            white-space: nowrap;
            /* Mencegah teks turun baris (wrapping) */
        }

        /* STYLE WARNA HEADER */
        .header-blue {
            background-color: #4472C4;
            color: #ffffff;
            font-weight: bold;
        }

        .header-sub {
            background-color: #D9E1F2;
            color: #000000;
            font-weight: bold;
        }

        .row-even {
            background-color: #ffffff;
        }

        .row-odd {
            background-color: #f3f3f3;
        }

        .col-total {
            background-color: #FFD966;
            font-weight: bold;
            border: 1px solid #000000;
        }

        /* SIMBOL */
        .symbol-check {
            color: #008000;
            font-weight: bold;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        .symbol-cross {
            color: #FF0000;
            font-weight: bold;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        /* FORMAT TEKS KHUSUS */
        .text-left {
            text-align: left !important;
            padding-left: 4px;
        }

        .text-id {
            mso-number-format: '\@';
        }

        /* Format Text untuk Excel */

        /* STYLE TANDA TANGAN */
        .ttd-box-title {
            border: 1px solid #000;
            font-weight: bold;
            background-color: #fff;
            text-align: center;
        }

        .ttd-box-space {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            height: 60px;
            /* Tinggi dikurangi */
            background-color: #fff;
        }

        .ttd-box-name {
            border: 1px solid #000;
            border-top: none;
            font-weight: bold;
            text-align: center;
            background-color: #fff;
            vertical-align: bottom;
        }

        .no-border {
            border: none !important;
            background-color: #ffffff;
        }
    </style>
</head>

<body>

    @php
        $totalDays = $periode->count();
        // Total Kolom: No(1) + NIK(1) + Nama(1) + ID(1) + Pol(1) + Type(1) + Total(1) = 7 + Tanggal
        $totalCols = 7 + $totalDays;

        // --- SETUP TANDA TANGAN ---
        $ttdWidth = 4;
        $totalTtdArea = $ttdWidth * 3;
        $leftSpacer = $totalCols - $totalTtdArea;
        if ($leftSpacer < 0)
            $leftSpacer = 0;
    @endphp

    {{-- JUDUL LAPORAN --}}
    <table>
        <tr>
            <td colspan="{{ $totalCols }}" class="no-border"
                style="font-size: 14px; font-weight: bold; text-align: center; height: 30px;">
                ABSENSI DRIVER {{ $projectName }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols }}" class="no-border"
                style="text-align: center; font-style: italic; font-size: 10px;">
                PERIODE: {{ strtoupper($startDate->translatedFormat('F Y')) }}
            </td>
        </tr>
        <tr>
            <td class="no-border" colspan="{{ $totalCols }}" style="height: 5px;"></td>
        </tr>
    </table>

    {{-- TABEL DATA --}}
    <table>
        <thead>
            <tr>
                {{-- Lebar kolom diset manual dengan width (px/%) agar Excel tidak menebak-nebak --}}
                <th rowspan="2" width="25" class="header-blue">NO</th>
                <th rowspan="2" width="80" class="header-blue">NIK</th>
                <th rowspan="2" width="120" class="header-blue">NAMA DRIVER</th>
                <th rowspan="2" width="50" class="header-blue">ID DRIVER</th>
                <th rowspan="2" width="60" class="header-blue">NO POL</th>
                <th rowspan="2" width="60" class="header-blue">TYPE</th>

                <th colspan="{{ $totalDays }}" class="header-sub" style="border: 1px solid black;">TANGGAL</th>

                <th rowspan="2" width="30" class="header-blue">TOTAL</th>
            </tr>
            <tr>
                @foreach($periode as $date)
                    {{-- Kolom tanggal dibuat sekecil mungkin --}}
                    <th width="20" class="header-sub">{{ $date->day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($dataRekap as $index => $row)
                <tr class="{{ $index % 2 == 0 ? 'row-even' : 'row-odd' }}">
                    <td>{{ $index + 1 }}</td>

                    {{-- NIK FORMAT TEXT --}}
                    <td class="text-id">{{ $row['nik_ktp'] }}</td>

                    <td class="text-left">{{ $row['nama'] }}</td>
                    <td class="text-id">{{ $row['id_driver'] }}</td>
                    <td>{{ $row['no_pol'] }}</td>
                    <td>{{ $row['type'] }}</td>

                    @foreach($periode as $date)
                        @php 
                                                $key = $date->format('Y-m-d');
                            $val = $row['harian'][$key] ?? '';
                        @endphp
                            <td class="{{ $val == '✓' ? 'symbol-check' : 'symbol-cross' }}">{{ $val }}</td>
                    @endforeach

                        <td class="col-total">{{ $row['total'] }}</td>
                    </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    {{-- TANDA TANGAN --}}
    <table>
        <tr>
            <td colspan="{{ $leftSpacer }}" class="no-border"></td>
            <td colspan="{{ $ttdWidth }}" class="ttd-box-title">PEMBUAT</td>
            <td colspan="{{ $ttdWidth }}" class="ttd-box-title">MENGETAHUI</td>
            <td colspan="{{ $ttdWidth }}" class="ttd-box-title">MENYETUJUI</td>
        </tr>
        <tr>
            <td colspan="{{ $leftSpacer }}" class="no-border"></td>
            <td colspan="{{ $ttdWidth }}" class="ttd-box-space"></td>
            <td colspan="{{ $ttdWidth }}" class="ttd-box-space"></td>
            <td colspan="{{ $ttdWidth }}" class="ttd-box-space"></td>
        </tr>
        <tr>
            <td colspan="{{ $leftSpacer }}" class="no-border"></td>
            <td colspan="{{ $ttdWidth }}" class="ttd-box-name">PIC</td>
            <td colspan="{{ $ttdWidth }}" class="ttd-box-name">SUKMAWATI</td>
            <td colspan="{{ $ttdWidth }}" class="ttd-box-name">IMA RAHMAWWATI</td>
       
 </tr>
    </table>

</body>
</html>