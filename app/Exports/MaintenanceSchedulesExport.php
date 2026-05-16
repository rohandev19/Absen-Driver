<?php

namespace App\Exports;

use App\Models\MaintenanceSchedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MaintenanceSchedulesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $filters;
    protected $rowNumber = 1;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = MaintenanceSchedule::with(['vehicle.project', 'component']);

        // Apply filters
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['priority'])) {
            $query->where('priority', $this->filters['priority']);
        }

        if (!empty($this->filters['vehicle_id'])) {
            $query->where('vehicle_id', $this->filters['vehicle_id']);
        }

        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        return $query->orderBy('scheduled_date', 'asc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Plat Nomor',
            'Project',
            'Tanggal Jadwal',
            'Tipe Maintenance',
            'Prioritas',
            'Status',
            'Komponen',
            'Deskripsi',
            'Estimasi Biaya',
            'Biaya Aktual',
            'Tanggal Selesai',
            'Catatan',
        ];
    }

    /**
     * @param mixed $schedule
     * @return array
     */
    public function map($schedule): array
    {
        return [
            $this->rowNumber++,
            $schedule->vehicle->plate_number ?? '-',
            $schedule->vehicle->project->name ?? 'Pool',
            $schedule->scheduled_date->format('d/m/Y'),
            ucfirst($schedule->type),
            ucfirst($schedule->priority),
            ucfirst(str_replace('_', ' ', $schedule->status)),
            $schedule->component->component_name ?? 'General',
            $schedule->description ?? '-',
            'Rp ' . number_format($schedule->estimated_cost ?? 0, 0, ',', '.'),
            $schedule->actual_cost ? 'Rp ' . number_format($schedule->actual_cost, 0, ',', '.') : '-',
            $schedule->completed_at ? $schedule->completed_at->format('d/m/Y H:i') : '-',
            $schedule->notes ?? '-',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1890FF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Maintenance Schedules';
    }
}
