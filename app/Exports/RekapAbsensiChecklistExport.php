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
    protected $projectId;
    protected $projectName;

    public function __construct($bulan, $tahun, $projectId = null, $projectName = 'SEMUA PROJECT')
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->projectId = $projectId;
        $this->projectName = $projectName;
    }

    public function view(): View
    {
        // 1. Tentukan jumlah hari dalam bulan
        $daysInMonth = Carbon::createFromDate($this->tahun, $this->bulan)->daysInMonth;
        $dates = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dates[] = $i;
        }

        // 2. Ambil Driver (Load relasi project agar nama project muncul)
        $query = Driver::with('project')->orderBy('full_name');

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        $drivers = $query->get();

        // 3. Ambil data absensi bulan ini
        // FIX: Group by time_in (check-in date) instead of time_out for accurate
        // multi-day shift reporting. A shift that starts on Jan 26 and ends on
        // Jan 27 should be counted as "worked on Jan 26", not Jan 27.
        $attendances = Attendance::whereMonth('time_in', $this->bulan)
            ->whereYear('time_in', $this->tahun)
            ->whereNotNull('time_out')
            ->get()
            ->groupBy('driver_id');

        // 4. Mapping data menjadi matriks
        $matrix = [];
        foreach ($drivers as $driver) {
            $row = [];
            $driverAtt = $attendances->get($driver->id);

            // Loop tanggal 1 s/d 30/31
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $isPresent = false;

                if ($driverAtt) {
                    // FIX: Check by time_in (check-in date) for accurate multi-day shift reporting
                    $check = $driverAtt->filter(function ($item) use ($day) {
                        return Carbon::parse($item->time_in)->day == $day;
                    })->first();

                    if ($check)
                        $isPresent = true;
                }
                $row[$day] = $isPresent ? '✔' : '✖';
            }

            // Hitung total hadir
            $totalHadir = collect($row)->filter(fn($s) => $s === '✔')->count();

            // --- PERBAIKAN DI SINI (MENAMBAHKAN DATA YANG HILANG) ---
            $matrix[] = [
                'name' => $driver->full_name,
                'nik_ktp' => $driver->nik_ktp ?? '-',          // <--- Fix Error: Undefined array key "nik_ktp"
                'id_driver' => $driver->driver_id_nik ?? '-',    // <--- Fix Error: Undefined array key "id_driver"
                'project' => $driver->project->name ?? '-',    // <--- Fix Error: Undefined array key "project"
                'data' => $row,
                'total' => $totalHadir
            ];
        }

        // 5. Kirim Data ke View
        return view('exports.rekap_checklist', [
            'dates' => $dates,
            'matrix' => $matrix,
            'monthName' => Carbon::createFromDate($this->tahun, $this->bulan)->translatedFormat('F Y'),
            'projectName' => $this->projectName, // <--- Fix Error: Undefined variable $projectName
            'totalCols' => count($dates) + 5   // Update colspan (+5 untuk kolom identitas tambahan)
        ]);
    }

    public function title(): string
    {
        return 'Checklist Absensi';
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style Header
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '212529']],
            'alignment' => ['horizontal' => 'center']
        ]);

        // Rata Tengah
        $sheet->getStyle('B2:' . $highestColumn . $highestRow)->getAlignment()->setHorizontal('center');

        // Warna Centang/Silang
        for ($row = 2; $row <= $highestRow; $row++) {
            for ($col = 2; $col <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn) - 1; $col++) {
                $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $cellValue = $sheet->getCell($colString . $row)->getValue();

                if ($cellValue == '✔') {
                    $sheet->getStyle($colString . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('008000'));
                    $sheet->getStyle($colString . $row)->getFont()->setBold(true);
                } elseif ($cellValue == '✖') {
                    $sheet->getStyle($colString . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0000'));
                }
            }
        }
    }
}