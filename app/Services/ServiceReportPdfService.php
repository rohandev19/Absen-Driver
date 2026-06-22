<?php

namespace App\Services;

use App\Models\ServiceReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ServiceReportPdfService
{
    /**
     * CUSTOMER PDF - Draft, tanpa kuitansi dan tanpa harga.
     * Dibuat setelah admin approve, sebelum customer tanda tangan.
     */
    public function generateCustomerDraft(ServiceReport $report): string
    {
        $fileName = $this->reportNumber($report) . '-customer-draft.pdf';
        $path = 'service-reports/customer/draft/' . $fileName;

        return $this->savePdf(
            view: 'pdf.service-report-customer',
            report: $report,
            path: $path,
            extra: [
                'title' => 'DRAFT PERSETUJUAN LAPORAN SERVICE',
                'documentStatus' => 'DRAFT - MENUNGGU PERSETUJUAN CUSTOMER',
                'isFinal' => false,
            ]
        );
    }

    /**
     * CUSTOMER PDF - Final, tanpa kuitansi dan tanpa harga.
     * Dibuat setelah customer approve dan tanda tangan.
     */
    public function generateCustomerFinal(ServiceReport $report): string
    {
        $fileName = $this->reportNumber($report) . '-customer-final.pdf';
        $path = 'service-reports/customer/final/' . $fileName;

        return $this->savePdf(
            view: 'pdf.service-report-customer',
            report: $report,
            path: $path,
            extra: [
                'title' => 'BUKTI PERSETUJUAN LAPORAN SERVICE',
                'documentStatus' => 'FINAL - DISETUJUI CUSTOMER',
                'isFinal' => true,
            ]
        );
    }

    public function streamCustomerDraft(ServiceReport $report)
    {
        return $this->makePdf('pdf.service-report-customer', $report, [
            'title' => 'DRAFT PERSETUJUAN LAPORAN SERVICE',
            'documentStatus' => 'DRAFT - MENUNGGU PERSETUJUAN CUSTOMER',
            'isFinal' => false,
        ])->stream($this->reportNumber($report) . '-customer-draft.pdf');
    }

    public function streamCustomerFinal(ServiceReport $report)
    {
        return $this->makePdf('pdf.service-report-customer', $report, [
            'title' => 'BUKTI PERSETUJUAN LAPORAN SERVICE',
            'documentStatus' => 'FINAL - DISETUJUI CUSTOMER',
            'isFinal' => true,
        ])->stream($this->reportNumber($report) . '-customer-final.pdf');
    }

    /**
     * ADMIN INTERNAL PDF.
     * Boleh menampilkan kuitansi, biaya, catatan internal, dan bukti lengkap.
     */
    public function generateAdminInternal(ServiceReport $report): string
    {
        $fileName = $this->reportNumber($report) . '-admin-internal.pdf';
        $path = 'service-reports/admin/internal/' . $fileName;

        return $this->savePdf(
            view: 'pdf.service-report-admin-internal',
            report: $report,
            path: $path,
            extra: [
                'title' => 'LAPORAN SERVICE INTERNAL',
                'documentStatus' => 'INTERNAL - ADMIN SERVICE',
                'isFinal' => $report->status === ServiceReport::STATUS_APPROVED_CUSTOMER,
            ]
        );
    }

    public function streamAdminInternal(ServiceReport $report)
    {
        return $this->makePdf('pdf.service-report-admin-internal', $report, [
            'title' => 'LAPORAN SERVICE INTERNAL',
            'documentStatus' => 'INTERNAL - ADMIN SERVICE',
            'isFinal' => $report->status === ServiceReport::STATUS_APPROVED_CUSTOMER,
        ])->stream($this->reportNumber($report) . '-admin-internal.pdf');
    }

    /**
     * FINANCE PDF.
     * Untuk pengajuan/review finance internal. Customer tidak melihat dokumen ini.
     */
    public function generateFinanceSubmission(ServiceReport $report): string
    {
        $fileName = $this->reportNumber($report) . '-finance.pdf';
        $path = 'service-reports/finance/' . $fileName;

        return $this->savePdf(
            view: 'pdf.service-report-finance',
            report: $report,
            path: $path,
            extra: [
                'title' => 'PENGAJUAN FINANCE SERVICE KENDARAAN',
                'documentStatus' => 'INTERNAL - FINANCE',
                'isFinal' => $report->status === ServiceReport::STATUS_APPROVED_CUSTOMER,
            ]
        );
    }

    public function streamFinanceSubmission(ServiceReport $report)
    {
        return $this->makePdf('pdf.service-report-finance', $report, [
            'title' => 'PENGAJUAN FINANCE SERVICE KENDARAAN',
            'documentStatus' => 'INTERNAL - FINANCE',
            'isFinal' => $report->status === ServiceReport::STATUS_APPROVED_CUSTOMER,
        ])->stream($this->reportNumber($report) . '-finance.pdf');
    }

    private function savePdf(string $view, ServiceReport $report, string $path, array $extra = []): string
    {
        $pdf = $this->makePdf($view, $report, $extra);

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    private function makePdf(string $view, ServiceReport $report, array $extra = [])
    {
        $isCustomerView = $view === 'pdf.service-report-customer';

        $report->loadMissing([
            'driver',
            'vehicle',
            'customer',
            'approvedByAdmin',
            'approvedByCustomer',
        ]);

        return Pdf::loadView($view, array_merge($extra, [
            'report' => $report,
            'reportNumber' => $this->reportNumber($report),

            // Foto customer: sengaja tidak mengirim receiptPhoto ke customer template.
            'vehiclePhoto' => $this->imageDataUri($report->vehicle_condition_photo_path),
            'afterServicePhoto' => $this->imageDataUri($report->after_service_photo_path ?? null),
            'odometerPhoto' => $this->imageDataUri($report->odometer_photo_path ?? null),

            // Foto internal: hanya dipakai admin/finance.
            'receiptPhoto' => $isCustomerView ? null : $this->imageDataUri($report->receipt_photo_path),

            // Signature.
            'adminSignature' => $this->imageDataUri($report->admin_signature_path),
            'customerSignature' => $this->imageDataUri($report->customer_signature_path),
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

    private function reportNumber(ServiceReport $report): string
    {
        if (!empty($report->ticket_number)) {
            return $report->ticket_number;
        }

        return 'LS-' . now()->format('Y') . '-' . str_pad((string) $report->id, 5, '0', STR_PAD_LEFT);
    }
}
