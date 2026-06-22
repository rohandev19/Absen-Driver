<?php

namespace App\Services;

use App\Models\MaintenanceSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class MaintenanceSchedulePdfService
{
    /**
     * FINANCE PDF.
     * Untuk pengajuan/review finance internal.
     */
    public function generateFinanceSubmission(MaintenanceSchedule $schedule): string
    {
        $fileName = $this->reportNumber($schedule) . '-finance.pdf';
        $path = 'maintenance-schedules/finance/' . $fileName;

        return $this->savePdf(
            view: 'pdf.maintenance-schedule-finance',
            schedule: $schedule,
            path: $path,
            extra: [
                'title' => 'PENGAJUAN FINANCE MAINTENANCE KENDARAAN',
                'documentStatus' => 'INTERNAL - FINANCE',
            ]
        );
    }

    public function streamFinanceSubmission(MaintenanceSchedule $schedule)
    {
        return $this->makePdf('pdf.maintenance-schedule-finance', $schedule, [
            'title' => 'PENGAJUAN FINANCE MAINTENANCE KENDARAAN',
            'documentStatus' => 'INTERNAL - FINANCE',
        ])->stream($this->reportNumber($schedule) . '-finance.pdf');
    }

    private function savePdf(string $view, MaintenanceSchedule $schedule, string $path, array $extra = []): string
    {
        $pdf = $this->makePdf($view, $schedule, $extra);

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    private function makePdf(string $view, MaintenanceSchedule $schedule, array $extra = [])
    {
        $schedule->loadMissing([
            'vehicle',
            'component',
            'completedBy',
        ]);

        return Pdf::loadView($view, array_merge($extra, [
            'schedule' => $schedule,
            'reportNumber' => $this->reportNumber($schedule),

            'receiptPhoto' => $this->imageDataUri($schedule->receipt_photo_path),
            'odometerPhoto' => $this->imageDataUri($schedule->odometer_photo_path),
            'adminSignature' => $this->imageDataUri($schedule->admin_signature_path),
        ]))
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);
    }

    private function imageDataUri(?string $relativePath): ?string
    {
        if (!$relativePath) {
            return null;
        }

        $relativePath = ltrim($relativePath, '/');

        if (!Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        $absolutePath = Storage::disk('public')->path($relativePath);

        if (!is_file($absolutePath)) {
            return null;
        }

        $mimeType = mime_content_type($absolutePath) ?: 'image/png';
        $base64 = base64_encode(file_get_contents($absolutePath));

        return "data:{$mimeType};base64,{$base64}";
    }

    private function reportNumber(MaintenanceSchedule $schedule): string
    {
        return 'MS-' . now()->format('Y') . '-' . str_pad((string) $schedule->id, 5, '0', STR_PAD_LEFT);
    }
}
