<?php

namespace App\Exports;

use App\Models\MaintenanceAlert;
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

class MaintenanceAlertsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
        $query = MaintenanceAlert::with(['vehicle.project', 'component']);

        // Apply filters
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['alert_type'])) {
            $query->where('alert_type', $this->filters['alert_type']);
        }

        return $query->orderBy('triggered_at', 'desc')->get();
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
            'Komponen',
            'Tipe Alert',
            'Pesan',
            'Status',
            'Tanggal Trigger',
            'Acknowledged At',
            'Acknowledged By',
            'Resolved At',
            'Resolution Notes',
        ];
    }

    /**
     * @param mixed $alert
     * @return array
     */
    public function map($alert): array
    {
        return [
            $this->rowNumber++,
            $alert->vehicle->plate_number ?? '-',
            $alert->vehicle->project->name ?? 'Pool',
            $alert->component->component_name ?? '-',
            strtoupper($alert->alert_type),
            $alert->message,
            ucfirst($alert->status),
            $alert->triggered_at->format('d/m/Y H:i'),
            $alert->acknowledged_at ? $alert->acknowledged_at->format('d/m/Y H:i') : '-',
            $alert->acknowledged_by ?? '-',
            $alert->resolved_at ? $alert->resolved_at->format('d/m/Y H:i') : '-',
            $alert->resolution_notes ?? '-',
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
        return 'Maintenance Alerts';
    }
}
