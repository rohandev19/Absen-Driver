<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RiwayatDriverExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $driverId;
    protected $projectId;

    // Constructor menerima semua parameter filter
    public function __construct($startDate, $endDate, $driverId = null, $projectId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->driverId = $driverId;
        $this->projectId = $projectId;
    }

    public function view(): View
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // Query Data
        $query = Attendance::with(['driver.project', 'vehicle'])
            ->whereNotNull('time_out')
            ->whereBetween('time_in', [$start, $end])
            ->orderBy('time_in', 'asc'); // Urut dari tanggal terlama

        // Filter Driver
        if ($this->driverId) {
            $query->where('driver_id', $this->driverId);
        }

        // Filter Project
        if ($this->projectId) {
            $query->whereHas('driver', function ($q) {
                $q->where('project_id', $this->projectId);
            });
        }

        $data = $query->get();

        // Load View Khusus Excel
        return view('admin.report.export_riwayat_driver', [
            'data' => $data,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalRecords' => $data->count()
        ]);
    }

    // Style tambahan agar rapi (Bold Header)
    public function styles(Worksheet $sheet)
    {
        return [
            // Baris 1 (Judul)
            1 => ['font' => ['bold' => true, 'size' => 14]],
            // Baris 2 (Periode)
            2 => ['font' => ['italic' => true]],
            // Baris 4 (Header Tabel)
            4 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FFE0E0E0']]],
        ];
    }
}