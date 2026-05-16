<?php

namespace App\Exports;

use App\Models\Vehicle;
use App\Services\VehicleHealthService;
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

class MaintenanceDashboardExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $filters;
    protected $healthService;
    protected $rowNumber = 1;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
        $this->healthService = new VehicleHealthService();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Vehicle::with(['project', 'latestAttendance']);

        // Apply filters
        if (!empty($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (!empty($this->filters['search'])) {
            $query->where('plate_number', 'like', '%' . $this->filters['search'] . '%');
        }

        if (!empty($this->filters['status_filter'])) {
            $statusFilter = $this->filters['status_filter'];
            $query->where(function($q) use ($statusFilter) {
                if ($statusFilter === 'danger') {
                    $q->where('health_status_code', 'physical_issue')
                      ->orWhereRaw('(current_km - last_service_km) >= service_interval_km');
                } elseif ($statusFilter === 'warning') {
                    $q->whereRaw('(current_km - last_service_km) >= (service_interval_km - 1000)')
                      ->whereRaw('(current_km - last_service_km) < service_interval_km');
                } elseif ($statusFilter === 'safe') {
                    $q->whereRaw('(current_km - last_service_km) < (service_interval_km - 1000)')
                      ->where('health_status_code', '!=', 'physical_issue');
                }
            });
        }

        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Plat Nomor',
            'Tipe Kendaraan',
            'Project',
            'Health Score',
            'Status Kesehatan',
            'KM Terakhir',
            'KM Servis Terakhir',
            'Interval Servis',
            'Sisa KM',
            'Last Update',
            'Status Code',
        ];
    }

    /**
     * @param mixed $vehicle
     * @return array
     */
    public function map($vehicle): array
    {
        $score = $this->healthService->calculateHealthScore($vehicle);
        
        // Determine status
        if ($vehicle->health_status_code === 'physical_issue') {
            $status = 'Isu Fisik';
        } elseif ($score >= 75) {
            $status = 'Prima';
        } elseif ($score >= 40) {
            $status = 'Segera Servis';
        } else {
            $status = 'Telat Servis';
        }

        $sisaKm = $vehicle->service_interval_km - ($vehicle->current_km - $vehicle->last_service_km);

        return [
            $this->rowNumber++,
            $vehicle->plate_number,
            $vehicle->type,
            $vehicle->project->name ?? 'Pool',
            round($score, 1),
            $status,
            number_format($vehicle->current_km),
            number_format($vehicle->last_service_km),
            number_format($vehicle->service_interval_km),
            number_format($sisaKm),
            $vehicle->latestAttendance ? $vehicle->latestAttendance->updated_at->format('d/m/Y H:i') : '-',
            $vehicle->health_status_code ?? 'normal',
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
        return 'Maintenance Dashboard';
    }
}
