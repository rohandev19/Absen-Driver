<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Driver;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RekapAbsensiChecklistExport implements FromView, ShouldAutoSize, WithTitle, WithStyles
{
    protected $bulan;
    protected $tahun;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        // 1. Tentukan jumlah hari dalam bulan tersebut
        $daysInMonth = Carbon::createFromDate($this->tahun, $this->bulan)->daysInMonth;
        $dates = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dates[] = $i;
        }

        // 2. Ambil semua driver
        $drivers = Driver::orderBy('full_name')->get();

        // 3. Ambil data absensi bulan ini
        $attendances = Attendance::whereMonth('time_out', $this->bulan)
            ->whereYear('time_out', $this->tahun)
            ->whereNotNull('time_out')
            ->get()
            ->groupBy('driver_id');

        // 4. Mapping data menjadi matriks
        $matrix = [];
        foreach ($drivers as $driver) {
            $row = [];
            // Ambil absensi milik driver ini
            $driverAtt = $attendances->get($driver->id);

            // Loop tanggal 1 s/d 30/31
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $isPresent = false;

                if ($driverAtt) {
                    // Cek apakah ada absensi di tanggal ini
                    $check = $driverAtt->filter(function ($item) use ($day) {
                        return Carbon::parse($item->time_out)->day == $day;
                    })->first();

                    if ($check)
                        $isPresent = true;
                }

                $row[$day] = $isPresent ? '✔' : '✖';
            }

            // Hitung total hadir
            // Hitung total hadir (BARU - HANYA MENGHITUNG JUMLAH HARI YANG ADA CEKLISNYA)
// Kita filter array $row untuk mencari yang isinya '✔', lalu hitung jumlahnya.
            $totalHadir = collect($row)->filter(function ($status) {
                return $status === '✔';
            })->count();
            
            $matrix[] = [
                'name' => $driver->full_name,
                'data' => $row,
                'total' => $totalHadir
            ];
        }

        return view('exports.rekap_checklist', [
            'dates' => $dates,
            'matrix' => $matrix,
            'monthName' => Carbon::createFromDate($this->tahun, $this->bulan)->translatedFormat('F Y')
        ]);
    }

    public function title(): string
    {
        return 'Checklist Absensi';
    }

    // FUNGSI UNTUK MEMBERI WARNA HIJAU/MERAH DI EXCEL
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style Header (Hitam Putih)
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '212529']],
            'alignment' => ['horizontal' => 'center']
        ]);

        // Rata Tengah untuk semua data tanggal
        $sheet->getStyle('B2:' . $highestColumn . $highestRow)->getAlignment()->setHorizontal('center');

        // Loop untuk mewarnai Centang dan Silang
        for ($row = 2; $row <= $highestRow; $row++) {
            for ($col = 2; $col <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn) - 1; $col++) {
                $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $cellValue = $sheet->getCell($colString . $row)->getValue();

                if ($cellValue == '✔') {
                    // Warna Hijau
                    $sheet->getStyle($colString . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('008000'));
                    $sheet->getStyle($colString . $row)->getFont()->setBold(true);
                } elseif ($cellValue == '✖') {
                    // Warna Merah
                    $sheet->getStyle($colString . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0000'));
                }
            }
        }
    }
}