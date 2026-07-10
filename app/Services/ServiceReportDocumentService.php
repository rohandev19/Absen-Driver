<?php

namespace App\Services;

use App\Models\ServiceReport;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;

class ServiceReportDocumentService
{
    /**
     * Generate finance submission document (internal - includes receipt photo and costs).
     */
    public function generateFinanceSubmission(ServiceReport $report): string
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection(['marginTop' => 600, 'marginBottom' => 600, 'marginLeft' => 600, 'marginRight' => 600]);

        // Data Prep
        $dateStr = $report->timestamp->format('Ymd');
        $docNumber = "No: FIN/SVC/{$dateStr}/" . str_pad($report->id, 3, '0', STR_PAD_LEFT);

        // Header
        $section->addText('PT Hamada Global Jaya', ['bold' => true, 'size' => 18], ['alignment' => Jc::CENTER]);
        $section->addText('Divisi Keuangan & Operasional', ['size' => 12], ['alignment' => Jc::CENTER]);
        $section->addText('===========================================================================', ['bold' => true], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        $section->addText(
            'PENGAJUAN BIAYA SERVICE KENDARAAN DARURAT',
            ['bold' => true, 'size' => 14, 'underline' => 'single'],
            ['alignment' => Jc::CENTER]
        );
        $section->addText($docNumber, ['bold' => true, 'size' => 11], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        $section->addText(
            "Mohon persetujuan pencairan dana untuk perbaikan armada dengan rincian operasional sebagai berikut:",
            ['size' => 11],
            ['alignment' => Jc::BOTH]
        );
        $section->addTextBreak(1);

        // Info table
        $tableStyle = [
            'borderSize' => 6, 
            'borderColor' => '000000', 
            'cellMargin' => 40
        ];
        $phpWord->addTableStyle('Finance Info Table', $tableStyle);
        $table = $section->addTable('Finance Info Table');
        
        $this->addTableRowBAP($table, 'Tanggal Service', $report->timestamp->format('d-m-Y H:i'));
        $this->addTableRowBAP($table, 'Plat Nomor', $report->vehicle->plate_number ?? 'N/A');
        $this->addTableRowBAP($table, 'Nama Driver', $report->driver->full_name ?? 'N/A');
        $this->addTableRowBAP($table, 'Customer / Klien', $report->customer->name ?? 'Belum di-link');
        $this->addTableRowBAP($table, 'Lokasi Darurat', $report->gps_location);
        
        $section->addTextBreak(1);

        // Description
        $section->addText('Rincian Kendala Teknis:', ['bold' => true, 'size' => 12]);
        $tableDetail = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $tableDetail->addRow();
        $tableDetail->addCell(9000)->addText($report->description, ['size' => 11]);
        $section->addTextBreak(1);

        // Cost estimation placeholder
        $section->addText('Rincian Estimasi Biaya:', ['bold' => true, 'size' => 12]);
        $costTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $costTable->addRow();
        $costTable->addCell(6000, ['bgColor' => 'F2F2F2'])->addText('Keterangan / Suku Cadang', ['bold' => true], ['alignment' => Jc::CENTER]);
        $costTable->addCell(3000, ['bgColor' => 'F2F2F2'])->addText('Nominal (Rp)', ['bold' => true], ['alignment' => Jc::CENTER]);
        
        // Blank rows for manual filling if needed, or total claim
        $costTable->addRow();
        $costTable->addCell(6000)->addText('Total Klaim (Sesuai Kuitansi)', ['size' => 11]);
        $costTable->addCell(3000)->addText('..............................', ['size' => 11], ['alignment' => Jc::RIGHT]);
        $section->addTextBreak(1);

        // Receipt photo
        $section->addText('Lampiran Foto Kuitansi/Nota Pembelian:', ['bold' => true, 'size' => 12]);
        
        try {
            $receiptPath = storage_path('app/public/' . $report->receipt_photo_path);
            if (file_exists($receiptPath)) {
                $manager = new ImageManager(new GdDriver());
                $image = $manager->read($receiptPath);
                $image->scaleDown(width: 600);
                
                $tempPath = storage_path('app/temp_fin_' . Str::uuid() . '.jpg');
                $image->save($tempPath, 70);
                
                $section->addImage($tempPath, [
                    'width' => 250,
                    'height' => 180,
                    'wrappingStyle' => 'inline',
                    'alignment' => Jc::CENTER
                ]);
            } else {
                $section->addText('[Foto nota/kuitansi asli tidak dilampirkan atau rusak]', ['italic' => true, 'color' => 'FF0000'], ['alignment' => Jc::CENTER]);
            }
        } catch (\Throwable $e) {
            $section->addText('[Sistem gagal memuat pratinjau kuitansi]', ['italic' => true, 'color' => 'FF0000'], ['alignment' => Jc::CENTER]);
        }

        // Approval signatures
        $sigTable = $section->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $sigTable->addRow();
        
        // Col 1: Admin Service (Pemohon)
        $cell1 = $sigTable->addCell(4500);
        $cell1->addText('Diajukan & Diverifikasi Oleh,', ['bold' => true], ['alignment' => Jc::CENTER]);
        $cell1->addText('Divisi Operasional/Service', [], ['alignment' => Jc::CENTER]);
        $cell1->addTextBreak(1);

        if ($report->approvedByAdmin && $report->admin_signature_path && file_exists(storage_path('app/public/' . $report->admin_signature_path))) {
            $cell1->addImage(storage_path('app/public/' . $report->admin_signature_path), [
                'width' => 100,
                'height' => 50,
                'alignment' => Jc::CENTER
            ]);
        } else {
            $cell1->addTextBreak(3);
            $cell1->addText('(_______________________)', ['bold' => true], ['alignment' => Jc::CENTER]);
        }
        
        $adminName = $report->approvedByAdmin->name ?? 'Admin Service';
        $adminDate = $report->approved_at_admin ? $report->approved_at_admin->format('d-m-Y') : '..................';
        
        $cell1->addText($adminName, ['bold' => true], ['alignment' => Jc::CENTER]);
        $cell1->addText('Tgl: ' . $adminDate, ['size' => 10], ['alignment' => Jc::CENTER]);

        // Col 2: Finance (Penyetuju)
        $cell2 = $sigTable->addCell(4500);
        $cell2->addText('Disetujui Oleh,', ['bold' => true], ['alignment' => Jc::CENTER]);
        $cell2->addText('Divisi Finance', [], ['alignment' => Jc::CENTER]);
        $cell2->addTextBreak(3);
        
        $cell2->addText('(_______________________)', ['bold' => true], ['alignment' => Jc::CENTER]);
        $cell2->addText('Nama Lengkap', [], ['alignment' => Jc::CENTER]);
        $cell2->addText('Tgl: ........................', ['size' => 10], ['alignment' => Jc::CENTER]);

        // Save document
        $fileName = 'service_docs/finance_' . $report->id . '_' . Str::uuid() . '.docx';
        $filePath = storage_path('app/public/' . $fileName);
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $phpWord->save($filePath, 'Word2007');

        // Clean up temp file if exists
        if (isset($tempPath) && file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return $fileName;
    }

    /**
     * Generate customer approval document (NO receipt photo, NO cost information).
     */
    public function generateCustomerApprovalDocument(ServiceReport $report, ?string $signerName = null, ?string $signerRole = null): string
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Data Prep
        $customerName = $report->customer->name ?? 'Customer Umum';
        $adminName = $signerName ?? ($report->admin_signer_name ?? ($report->approvedByAdmin->name ?? 'Admin Service'));
        $adminRole = $signerRole ?? ($report->admin_signer_role ?? 'Admin Service');
        
        $customerSignerName = $report->customer_signer_name ?? '.......................................';
        $customerSignerRole = $report->customer_signer_role ?? '....................................';
        $customerInitial = strtoupper(substr($customerName, 0, 3));
        $dateStr = $report->timestamp->format('Ymd');
        $docNumber = "No: BAP/HL-{$customerInitial}/{$dateStr}/" . str_pad($report->id, 3, '0', STR_PAD_LEFT);

        // Header / Kop
        $section->addText('PT Hamada Global Jaya', ['bold' => true, 'size' => 18], ['alignment' => Jc::CENTER]);
        $section->addText('Jl. Contoh Alamat No. 123, Kota, Indonesia | Telp: (021) 1234567', ['size' => 10], ['alignment' => Jc::CENTER]);
        $section->addText('===========================================================================', ['bold' => true], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        $section->addText('BERITA ACARA PENYELESAIAN SERVICE KENDARAAN', ['bold' => true, 'size' => 14, 'underline' => 'single'], ['alignment' => Jc::CENTER]);
        $section->addText($docNumber, ['bold' => true, 'size' => 11], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        // Mukadimah Hukum
        \Carbon\Carbon::setLocale('id');
        $hari = \Carbon\Carbon::parse($report->timestamp)->translatedFormat('l');
        $tanggalFull = \Carbon\Carbon::parse($report->timestamp)->translatedFormat('d F Y');
        
        $section->addText(
            "Pada hari ini, {$hari}, tanggal {$tanggalFull}, bertempat di titik koordinat {$report->gps_location}, telah dilakukan perbaikan dan penanganan darurat (service) atas armada kendaraan dengan rincian sebagai berikut:",
            ['size' => 11],
            ['alignment' => Jc::BOTH]
        );
        $section->addTextBreak(1);

        // Info table
        $tableStyle = [
            'borderSize' => 6, 
            'borderColor' => '000000', 
            'cellMargin' => 80
        ];
        $phpWord->addTableStyle('Info Table', $tableStyle);
        $table = $section->addTable('Info Table');
        
        $this->addTableRowBAP($table, 'Nama Klien', $customerName);
        $this->addTableRowBAP($table, 'Plat Nomor', $report->vehicle->plate_number ?? 'N/A');
        $this->addTableRowBAP($table, 'Nama Driver', $report->driver->full_name ?? 'N/A');
        $this->addTableRowBAP($table, 'Waktu Laporan', $report->timestamp->format('d-m-Y H:i'));

        $section->addTextBreak(1);

        // Detail Pekerjaan
        $section->addText('Rincian Pekerjaan & Kondisi:', ['bold' => true, 'size' => 12]);
        $tableDetail = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $tableDetail->addRow();
        $tableDetail->addCell(4500, ['bgColor' => 'F2F2F2'])->addText('Keluhan Awal (Dari Driver)', ['bold' => true]);
        $tableDetail->addCell(4500, ['bgColor' => 'F2F2F2'])->addText('Tindakan & Catatan (Dari Admin)', ['bold' => true]);
        
        $tableDetail->addRow();
        $tableDetail->addCell(4500)->addText($report->description, ['size' => 11]);
        $tableDetail->addCell(4500)->addText($report->admin_notes ?? 'Telah diperiksa dan ditangani.', ['size' => 11]);
        $section->addTextBreak(1);

        // Bukti Visual
        $section->addText('Foto Bukti Kendaraan:', ['bold' => true, 'size' => 12]);
        
        try {
            $conditionPhotoPath = storage_path('app/public/' . $report->vehicle_condition_photo_path);
            if (file_exists($conditionPhotoPath)) {
                $manager = new ImageManager(new GdDriver());
                $image = $manager->read($conditionPhotoPath);
                $image->scaleDown(width: 800);
                
                $tempPath = storage_path('app/temp_' . Str::uuid() . '.jpg');
                $image->save($tempPath, 70);
                
                $section->addImage($tempPath, [
                    'width' => 350,
                    'height' => 250,
                    'wrappingStyle' => 'inline'
                ]);
            } else {
                $section->addText('[Foto kondisi tidak tersedia]', ['italic' => true, 'color' => 'FF0000']);
            }
        } catch (\Throwable $e) {
            $section->addText('[Error memuat foto kondisi]', ['italic' => true, 'color' => 'FF0000']);
        }

        $section->addTextBreak(1);

        // Klausul Pengesahan (Pernyataan Hukum)
        $section->addText(
            "Melalui Berita Acara ini, kedua belah pihak menyatakan bahwa pekerjaan service/penanganan darurat pada armada tersebut di atas telah SELESAI DILAKSANAKAN DENGAN BAIK. Kendaraan dinyatakan layak dan siap untuk kembali beroperasi.",
            ['size' => 11, 'bold' => true],
            ['alignment' => Jc::BOTH]
        );
        $section->addTextBreak(1);
        $section->addText(
            "Berita Acara ini merupakan dokumen sah yang mengikat kedua belah pihak dan dapat digunakan sebagai dasar penagihan biaya (invoice) atau keperluan administratif lainnya sesuai dengan perjanjian kerjasama yang berlaku.",
            ['size' => 11],
            ['alignment' => Jc::BOTH]
        );
        $section->addTextBreak(1);
        $section->addText(
            "* Catatan: Rincian biaya perbaikan dan daftar suku cadang (spare parts) yang digunakan dilampirkan secara terpisah pada dokumen Invoice / Tagihan.",
            ['size' => 10, 'italic' => true],
            ['alignment' => Jc::BOTH]
        );
        $section->addTextBreak(2);

        // Kolom Pengesahan (Tanda Tangan)
        $sigTable = $section->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $sigTable->addRow();
        
        $cell1 = $sigTable->addCell(4500);
        $cell1->addText('PIHAK PERTAMA', ['bold' => true], ['alignment' => Jc::CENTER]);
        $cell1->addText('PT Hamada Global Jaya', [], ['alignment' => Jc::CENTER]);
        $cell1->addTextBreak(1);

        if ($report->admin_signature_path && file_exists(storage_path('app/public/' . $report->admin_signature_path))) {
            $cell1->addImage(storage_path('app/public/' . $report->admin_signature_path), [
                'width' => 100,
                'height' => 50,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
        } else {
            $cell1->addTextBreak(3);
            $cell1->addText('(_______________________)', ['bold' => true], ['alignment' => Jc::CENTER]);
        }
        
        $cell1->addText('Nama: ' . $adminName, [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $cell1->addText('Jabatan: ' . $adminRole, [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $cell2 = $sigTable->addCell(4500);
        $cell2->addText('PIHAK KEDUA', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $cell2->addText($customerName, [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $cell2->addTextBreak(1);

        if ($report->customer_signature_path && file_exists(storage_path('app/public/' . $report->customer_signature_path))) {
            $cell2->addImage(storage_path('app/public/' . $report->customer_signature_path), [
                'width' => 100,
                'height' => 50,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
        } else {
            $cell2->addTextBreak(3);
            $cell2->addText('(_______________________)', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        }

        $cell2->addText('Nama: ' . $customerSignerName, [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $cell2->addText('Jabatan: ' . $customerSignerRole, [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // Save document
        $fileName = 'service_docs/customer_' . $report->id . '_' . Str::uuid() . '.docx';
        $filePath = storage_path('app/public/' . $fileName);
        
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $phpWord->save($filePath, 'Word2007');

        // Clean up temp file if exists
        if (isset($tempPath) && file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return $fileName;
    }

    /**
     * Helper to add table row with label and value.
     */
    private function addTableRow($table, string $label, string $value): void
    {
        $table->addRow();
        $table->addCell(3000)->addText($label, ['bold' => true]);
        $table->addCell(6000)->addText($value);
    }

    private function addTableRowBAP($table, string $label, string $value): void
    {
        $table->addRow();
        $table->addCell(3000, ['bgColor' => 'F2F2F2'])->addText($label, ['bold' => true]);
        $table->addCell(6000)->addText($value);
    }
}
