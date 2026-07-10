<?php

namespace App\Services;

use App\Models\TransportCost;
use App\Models\Driver;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TransportCostDocumentService
{
    /**
     * Generate finance submission document for a single approved trip entry.
     */
    public function generateSingleFinanceSubmission(TransportCost $trip): string
    {
        $phpWord = new PhpWord();
        
        // Document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('PT Hamada Global Jaya');
        $properties->setTitle('Pengajuan Keuangan Uang Jalan - ' . $trip->id);
        
        $section = $phpWord->addSection([
            'marginTop' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        // Header Table with bottom border line
        $headerTable = $section->addTable(['align' => 'center']);
        $headerTable->addRow();
        $cell = $headerTable->addCell(9000, [
            'borderBottomSize' => 18, // 1.5 pt
            'borderBottomColor' => '1B365D',
            'paddingBottom' => 100
        ]);
        $cell->addText('PT HAMADA GLOBAL JAYA', ['bold' => true, 'size' => 16, 'color' => '1B365D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell->addText('FORM PENGGANTIAN UANG JALAN & LEMBUR DRIVER', ['bold' => true, 'size' => 11, 'color' => '5C768D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $docNo = 'HGJ/TC-FORM/' . $trip->trip_date->format('Y') . '/' . str_pad((string)$trip->id, 5, '0', STR_PAD_LEFT);
        $cell->addText('Dokumen Kontrol Internal Keuangan  •  No. Dokumen: ' . $docNo, ['italic' => true, 'size' => 9, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        
        $section->addTextBreak(1);

        $tableStyle = [
            'cellMargin' => 80,
        ];

        // General Information
        $section->addText('I. INFORMASI TRIP & PENGEMUDI', ['bold' => true, 'size' => 11, 'color' => '1B365D', 'name' => 'Calibri']);
        $table = $section->addTable($tableStyle);
        
        $this->addTableRow($table, 'Tanggal Perjalanan', $trip->trip_date->format('d-m-Y'));
        $this->addTableRow($table, 'Nama Driver', $trip->driver->full_name);
        $this->addTableRow($table, 'Project', $trip->project->name);
        $this->addTableRow($table, 'Kendaraan (Plat Nomor)', $trip->vehicle->plate_number);
        $this->addTableRow($table, 'Nomor Delivery Order (DO)', $trip->do_number);
        $this->addTableRow($table, 'Jumlah Drop Point', $trip->drop_point_count . ' Titik');
        $this->addTableRow($table, 'Lokasi Tujuan Pengiriman', $trip->delivery_location);
        
        $section->addTextBreak(1);

        // Odometer & Fuel efficiency
        $section->addText('II. DATA ODOMETER & KONSUMSI BBM', ['bold' => true, 'size' => 11, 'color' => '1B365D', 'name' => 'Calibri']);
        $tableOdo = $section->addTable($tableStyle);
        $this->addTableRow($tableOdo, 'Odometer Keberangkatan', number_format($trip->odometer_start, 0, ',', '.') . ' KM');
        $this->addTableRow($tableOdo, 'Odometer Kedatangan', number_format($trip->odometer_end, 0, ',', '.') . ' KM');
        $this->addTableRow($tableOdo, 'Total Jarak Tempuh', number_format($trip->odometer_difference, 0, ',', '.') . ' KM');
        $this->addTableRow($tableOdo, 'BBM Dikonsumsi', $trip->fuel_consumed ? number_format($trip->fuel_consumed, 2) . ' Liter' : 'N/A');
        $this->addTableRow($tableOdo, 'Rasio Efisiensi BBM', $trip->fuel_efficiency_ratio ? number_format($trip->fuel_efficiency_ratio, 2) . ' KM / Liter' : 'N/A');
        
        $section->addTextBreak(1);

        // Expense Breakdown
        $section->addText('III. RINCIAN BIAYA OPERASIONAL (UANG JALAN)', ['bold' => true, 'size' => 11, 'color' => '1B365D', 'name' => 'Calibri']);
        $tableCosts = $section->addTable($tableStyle);
        
        $this->addTableRow($tableCosts, 'Biaya Pembelian Bensin (BBM)', 'Rp ' . number_format($trip->gasoline_cost, 0, ',', '.'), false, 'FFFFFF', 'right');
        $this->addTableRow($tableCosts, 'Biaya Pembayaran Tol', 'Rp ' . number_format($trip->toll_cost, 0, ',', '.'), false, 'FFFFFF', 'right');
        $this->addTableRow($tableCosts, 'Biaya Parkir & Retribusi', 'Rp ' . number_format($trip->parking_cost, 0, ',', '.'), false, 'FFFFFF', 'right');
        $this->addTableRow($tableCosts, 'Subtotal Biaya Operasional', 'Rp ' . number_format($trip->total_cost, 0, ',', '.'), true, 'EAECEE', 'right');
        
        $section->addTextBreak(1);

        // Overtime Tracking
        $section->addText('IV. WAKTU PENYELESAIAN & UANG LEMBUR', ['bold' => true, 'size' => 11, 'color' => '1B365D', 'name' => 'Calibri']);
        $tableOvertime = $section->addTable($tableStyle);
        
        $this->addTableRow($tableOvertime, 'Jam Mulai Tugas', $trip->delivery_start_time->format('H:i'));
        $this->addTableRow($tableOvertime, 'Jam Selesai Tugas', $trip->delivery_end_time->format('H:i'));
        $this->addTableRow($tableOvertime, 'Durasi Kerja Nyata', number_format($trip->actual_delivery_hours, 2) . ' Jam');
        $this->addTableRow($tableOvertime, 'Akumulasi Jam Lembur', number_format($trip->overtime_hours, 2) . ' Jam');
        $this->addTableRow($tableOvertime, 'Tarif Lembur per Jam', 'Rp ' . number_format($trip->overtime_rate_per_hour, 0, ',', '.'), false, 'FFFFFF', 'right');
        $this->addTableRow($tableOvertime, 'Subtotal Bayaran Lembur', 'Rp ' . number_format($trip->overtime_payment, 0, ',', '.'), true, 'EAECEE', 'right');
        
        $section->addTextBreak(1);

        // Grand Total Banner
        $grandTotalTable = $section->addTable();
        $grandTotalTable->addRow();
        $cellBanner = $grandTotalTable->addCell(9000, [
            'bgColor' => '1B365D', 
            'valign' => 'center',
            'borderBottomSize' => 12, 'borderBottomColor' => '1B365D',
            'borderTopSize' => 12, 'borderTopColor' => '1B365D',
            'borderLeftSize' => 12, 'borderLeftColor' => '1B365D',
            'borderRightSize' => 12, 'borderRightColor' => '1B365D'
        ]);
        
        $totalTextRun = $cellBanner->addTextRun(['alignment' => Jc::CENTER]);
        $totalTextRun->addText('TOTAL PENGAJUAN DANA KE FINANCE: ', ['bold' => true, 'size' => 11, 'color' => 'FFFFFF', 'name' => 'Calibri']);
        
        $grandTotalAmount = $trip->total_cost + $trip->overtime_payment + $trip->bonus_driver;
        $totalTextRun->addText('Rp ' . number_format($grandTotalAmount, 0, ',', '.'), ['bold' => true, 'size' => 13, 'color' => 'F1C40F', 'name' => 'Calibri']);
        
        $section->addTextBreak(2);

        // Signatures block
        $section->addText('V. PENGESAHAN DOKUMEN & OTORISASI', ['bold' => true, 'size' => 11, 'color' => '1B365D', 'name' => 'Calibri']);
        $section->addTextBreak(1);

        $sigTable = $section->addTable();
        $sigTable->addRow();
        
        $borderStyle = [
            'borderBottomSize' => 6, 'borderBottomColor' => 'BDC3C7',
            'borderTopSize' => 6, 'borderTopColor' => 'BDC3C7',
            'borderLeftSize' => 6, 'borderLeftColor' => 'BDC3C7',
            'borderRightSize' => 6, 'borderRightColor' => 'BDC3C7'
        ];

        $cell1 = $sigTable->addCell(3000, array_merge(['valign' => 'top', 'bgColor' => 'F9FAF9'], $borderStyle));
        $cell1->addText('Diajukan Oleh (Driver),', ['size' => 9, 'color' => '2C3E50', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell1->addTextBreak(3);
        $cell1->addText($trip->driver->full_name, ['bold' => true, 'size' => 9.5, 'color' => '111827', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell1->addText('Tanggal: ' . $trip->trip_date->format('d-m-Y'), ['size' => 8.5, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);

        $cell2 = $sigTable->addCell(3000, array_merge(['valign' => 'top', 'bgColor' => 'F9FAF9'], $borderStyle));
        $cell2->addText('Disetujui Oleh (Master Admin),', ['size' => 9, 'color' => '2C3E50', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell2->addTextBreak(3);
        $adminName = $trip->financeSubmitter->name ?? $trip->approver->name ?? 'Master Admin';
        $cell2->addText($adminName, ['bold' => true, 'size' => 9.5, 'color' => '111827', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $approvedTime = $trip->submitted_to_finance_at ?? $trip->approved_at ?? now();
        $cell2->addText('Tanggal: ' . $approvedTime->format('d-m-Y H:i'), ['size' => 8.5, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);

        $cell3 = $sigTable->addCell(3000, array_merge(['valign' => 'top', 'bgColor' => 'F9FAF9'], $borderStyle));
        $cell3->addText('Diproses Oleh (Finance),', ['size' => 9, 'color' => '2C3E50', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell3->addTextBreak(3);
        $cell3->addText('___________________', ['bold' => true, 'size' => 9.5, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell3->addText('Tanggal: ____________', ['size' => 8.5, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);

        // Receipts Attachment Section
        if ($trip->gasoline_receipt_path || $trip->toll_receipt_path || $trip->parking_receipt_path) {
            $section->addPageBreak();
            $section->addText('VI. LAMPIRAN KUITANSI & BUKTI FISIK PENGELUARAN', ['bold' => true, 'size' => 12, 'color' => '1B365D', 'name' => 'Calibri']);
            $section->addTextBreak(1);

            if ($trip->gasoline_receipt_path) {
                $section->addText('1. Bukti Kuitansi Pembelian BBM / SPBU:', ['bold' => true, 'size' => 10, 'name' => 'Calibri']);
                $gasPath = storage_path('app/public/' . $trip->gasoline_receipt_path);
                if (file_exists($gasPath)) {
                    $section->addImage($gasPath, ['width' => 300, 'height' => 220, 'wrappingStyle' => 'inline']);
                } else {
                    $section->addText('[Berkas bukti bensin tidak ditemukan]', ['italic' => true, 'color' => 'FF0000']);
                }
                $section->addTextBreak(1);
            }

            if ($trip->toll_receipt_path) {
                $section->addText('2. Bukti Pembayaran Tol:', ['bold' => true, 'size' => 10, 'name' => 'Calibri']);
                $tollPath = storage_path('app/public/' . $trip->toll_receipt_path);
                if (file_exists($tollPath)) {
                    $section->addImage($tollPath, ['width' => 300, 'height' => 220, 'wrappingStyle' => 'inline']);
                } else {
                    $section->addText('[Berkas bukti tol tidak ditemukan]', ['italic' => true, 'color' => 'FF0000']);
                }
                $section->addTextBreak(1);
            }

            if ($trip->parking_receipt_path) {
                $section->addText('3. Bukti Karcis Parkir / Retribusi:', ['bold' => true, 'size' => 10, 'name' => 'Calibri']);
                $parkPath = storage_path('app/public/' . $trip->parking_receipt_path);
                if (file_exists($parkPath)) {
                    $section->addImage($parkPath, ['width' => 300, 'height' => 220, 'wrappingStyle' => 'inline']);
                } else {
                    $section->addText('[Berkas bukti parkir tidak ditemukan]', ['italic' => true, 'color' => 'FF0000']);
                }
                $section->addTextBreak(1);
            }
        }

        // Save Document
        $fileName = 'transport_docs/finance_' . $trip->id . '_' . Str::uuid() . '.docx';
        $filePath = storage_path('app/public/' . $fileName);
        
        // Ensure folder exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $phpWord->save($filePath, 'Word2007');

        return $fileName;
    }

    /**
     * Generate monthly finance recap document for a specific driver and month.
     */
    public function generateMonthlyFinanceRecap(array $recap, Driver $driver, string $month): string
    {
        $phpWord = new PhpWord();
        
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('PT Hamada Global Jaya');
        $properties->setTitle('Rekap Uang Jalan Keuangan - ' . $driver->full_name);

        $section = $phpWord->addSection([
            'marginTop' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        // Header Table with bottom border line
        $headerTable = $section->addTable(['align' => 'center']);
        $headerTable->addRow();
        $cell = $headerTable->addCell(9000, [
            'borderBottomSize' => 18, // 1.5 pt
            'borderBottomColor' => '1B365D',
            'paddingBottom' => 100
        ]);
        $cell->addText('PT HAMADA GLOBAL JAYA', ['bold' => true, 'size' => 16, 'color' => '1B365D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell->addText('REKAPITULASI BULANAN UANG JALAN & LEMBUR DRIVER', ['bold' => true, 'size' => 11, 'color' => '5C768D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        
        // Format Month
        list($year, $monthNum) = explode('-', $month);
        $monthDate = Carbon::createFromDate($year, $monthNum, 1);
        $monthLabel = $monthDate->translatedFormat('F Y');
        
        $cell->addText('Laporan Rekap Bulanan Internal Keuangan  •  Periode: ' . $monthLabel, ['italic' => true, 'size' => 9, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        
        $section->addTextBreak(1);

        $tableStyle = [
            'cellMargin' => 80,
        ];

        // Driver details
        $section->addText('DATA UTAMA PENGAJUAN', ['bold' => true, 'size' => 11, 'color' => '1B365D', 'name' => 'Calibri']);
        $tableDriver = $section->addTable($tableStyle);
        $this->addTableRow($tableDriver, 'Nama Driver', $driver->full_name);
        $this->addTableRow($tableDriver, 'Nomor Handphone', $driver->phone_number ?? '-');
        $this->addTableRow($tableDriver, 'Tingkat Kepemilikan Project', $driver->project->name ?? 'N/A');
        
        $section->addTextBreak(1);

        // Consolidated Financial totals
        $section->addText('RINGKASAN TOTAL DANA', ['bold' => true, 'size' => 11, 'color' => '1B365D', 'name' => 'Calibri']);
        $tableSummary = $section->addTable($tableStyle);
        $this->addTableRow($tableSummary, 'Total Perjalanan (Trip)', $recap['total_trips'] . ' Trip');
        $this->addTableRow($tableSummary, 'Akumulasi Jarak Tempuh', number_format($recap['total_km_traveled'], 0, ',', '.') . ' KM');
        $this->addTableRow($tableSummary, 'Konsumsi BBM Rata-rata', $recap['average_fuel_efficiency'] ? number_format($recap['average_fuel_efficiency'], 2) . ' KM / Liter' : 'N/A');
        
        $this->addTableRow($tableSummary, 'Total Biaya Bensin', 'Rp ' . number_format($recap['total_gasoline_cost'], 0, ',', '.'), false, 'FFFFFF', 'right');
        $this->addTableRow($tableSummary, 'Total Biaya Tol', 'Rp ' . number_format($recap['total_toll_cost'], 0, ',', '.'), false, 'FFFFFF', 'right');
        $this->addTableRow($tableSummary, 'Total Biaya Parkir', 'Rp ' . number_format($recap['total_parking_cost'], 0, ',', '.'), false, 'FFFFFF', 'right');
        $this->addTableRow($tableSummary, 'Total Pembayaran Lembur', 'Rp ' . number_format($recap['total_overtime_payment'], 0, ',', '.'), false, 'FFFFFF', 'right');
        $this->addTableRow($tableSummary, 'Total Bonus Driver (Telah Dihapus)', 'Rp ' . number_format($recap['total_bonus_earned'], 0, ',', '.'), false, 'FFFFFF', 'right');
        $this->addTableRow($tableSummary, 'GRAND TOTAL KLAIM', 'Rp ' . number_format($recap['grand_total'], 0, ',', '.'), true, 'EAECEE', 'right');

        $section->addTextBreak(1);

        // Detail list of trips
        $section->addText('LAMPIRAN DATA PERJALANAN DETIL', ['bold' => true, 'size' => 11, 'color' => '1B365D', 'name' => 'Calibri']);
        $tableTrips = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'BDC3C7',
            'cellMargin' => 80,
        ]);
        
        // Table Header
        $tableTrips->addRow(400);
        $tableTrips->addCell(1200, ['bgColor' => '1B365D'])->addText('Tanggal', ['bold' => true, 'color' => 'FFFFFF', 'size' => 9, 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $tableTrips->addCell(1800, ['bgColor' => '1B365D'])->addText('DO Number', ['bold' => true, 'color' => 'FFFFFF', 'size' => 9, 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $tableTrips->addCell(1000, ['bgColor' => '1B365D'])->addText('KM', ['bold' => true, 'color' => 'FFFFFF', 'size' => 9, 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $tableTrips->addCell(2000, ['bgColor' => '1B365D'])->addText('Bensin / Tol / Parkir', ['bold' => true, 'color' => 'FFFFFF', 'size' => 9, 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $tableTrips->addCell(1500, ['bgColor' => '1B365D'])->addText('Lembur', ['bold' => true, 'color' => 'FFFFFF', 'size' => 9, 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $tableTrips->addCell(1500, ['bgColor' => '1B365D'])->addText('Total', ['bold' => true, 'color' => 'FFFFFF', 'size' => 9, 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);

        foreach ($recap['trips'] as $trip) {
            $tableTrips->addRow();
            
            $cellBorder = [
                'borderBottomSize' => 6, 'borderBottomColor' => 'BDC3C7',
                'borderTopSize' => 6, 'borderTopColor' => 'BDC3C7',
                'borderLeftSize' => 6, 'borderLeftColor' => 'BDC3C7',
                'borderRightSize' => 6, 'borderRightColor' => 'BDC3C7'
            ];
            
            $tableTrips->addCell(1200, $cellBorder)->addText($trip->trip_date->format('d-m-Y'), ['size' => 9, 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
            $tableTrips->addCell(1800, $cellBorder)->addText(Str::limit($trip->do_number, 25), ['size' => 8.5, 'name' => 'Calibri']);
            $tableTrips->addCell(1000, $cellBorder)->addText(number_format($trip->odometer_difference, 0, ',', '.'), ['size' => 9, 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
            
            $costBreakdown = 'B: ' . number_format($trip->gasoline_cost / 1000, 0) . 'k / T: ' . number_format($trip->toll_cost / 1000, 0) . 'k / P: ' . number_format($trip->parking_cost / 1000, 0) . 'k';
            $tableTrips->addCell(2000, $cellBorder)->addText($costBreakdown, ['size' => 8.5, 'name' => 'Calibri']);
            $tableTrips->addCell(1500, $cellBorder)->addText('Rp ' . number_format($trip->overtime_payment, 0, ',', '.'), ['size' => 9, 'name' => 'Calibri'], ['alignment' => Jc::RIGHT]);
            
            $tripTotal = $trip->total_cost + $trip->overtime_payment + $trip->bonus_driver;
            $tableTrips->addCell(1500, $cellBorder)->addText('Rp ' . number_format($tripTotal, 0, ',', '.'), ['bold' => true, 'size' => 9, 'name' => 'Calibri'], ['alignment' => Jc::RIGHT]);
        }

        $section->addTextBreak(2);

        // Signatures
        $sigTable = $section->addTable();
        $sigTable->addRow();
        
        $borderStyle = [
            'borderBottomSize' => 6, 'borderBottomColor' => 'BDC3C7',
            'borderTopSize' => 6, 'borderTopColor' => 'BDC3C7',
            'borderLeftSize' => 6, 'borderLeftColor' => 'BDC3C7',
            'borderRightSize' => 6, 'borderRightColor' => 'BDC3C7'
        ];

        $cell1 = $sigTable->addCell(3000, array_merge(['valign' => 'top', 'bgColor' => 'F9FAF9'], $borderStyle));
        $cell1->addText('Diajukan Oleh (Driver),', ['size' => 9, 'color' => '2C3E50', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell1->addTextBreak(3);
        $cell1->addText($driver->full_name, ['bold' => true, 'size' => 9.5, 'color' => '111827', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell1->addText('Tanggal: ________________', ['size' => 8.5, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);

        $cell2 = $sigTable->addCell(3000, array_merge(['valign' => 'top', 'bgColor' => 'F9FAF9'], $borderStyle));
        $cell2->addText('Diperiksa Oleh (Admin),', ['size' => 9, 'color' => '2C3E50', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell2->addTextBreak(3);
        $cell2->addText('___________________', ['bold' => true, 'size' => 9.5, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell2->addText('Tanggal: ________________', ['size' => 8.5, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);

        $cell3 = $sigTable->addCell(3000, array_merge(['valign' => 'top', 'bgColor' => 'F9FAF9'], $borderStyle));
        $cell3->addText('Disetujui Keuangan (Finance),', ['size' => 9, 'color' => '2C3E50', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell3->addTextBreak(3);
        $cell3->addText('___________________', ['bold' => true, 'size' => 9.5, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);
        $cell3->addText('Tanggal: ________________', ['size' => 8.5, 'color' => '7F8C8D', 'name' => 'Calibri'], ['alignment' => Jc::CENTER]);

        // Save file
        $fileName = 'transport_docs/recap_' . $driver->id . '_' . $month . '_' . Str::uuid() . '.docx';
        $filePath = storage_path('app/public/' . $fileName);
        
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $phpWord->save($filePath, 'Word2007');

        return $fileName;
    }

    /**
     * Helper to add a table row.
     */
    private function addTableRow($table, string $label, string $value, bool $isTotal = false, string $bgColorVal = 'FFFFFF', string $align = 'left'): void
    {
        $table->addRow();
        
        $labelBg = $isTotal ? 'EAECEE' : 'F2F4F4';
        $valBg = $isTotal ? 'EAECEE' : $bgColorVal;
        
        $borderStyle = [
            'borderBottomSize' => 6, 'borderBottomColor' => 'BDC3C7',
            'borderTopSize' => 6, 'borderTopColor' => 'BDC3C7',
            'borderLeftSize' => 6, 'borderLeftColor' => 'BDC3C7',
            'borderRightSize' => 6, 'borderRightColor' => 'BDC3C7'
        ];
        
        $cellLabel = $table->addCell(3000, array_merge(['bgColor' => $labelBg, 'valign' => 'center'], $borderStyle));
        $cellLabel->addText($label, ['bold' => true, 'size' => 9, 'color' => '2C3E50', 'name' => 'Calibri']);
        
        $cellValue = $table->addCell(6000, array_merge(['bgColor' => $valBg, 'valign' => 'center'], $borderStyle));
        $alignment = match($align) {
            'right' => Jc::RIGHT,
            'center' => Jc::CENTER,
            default => Jc::LEFT
        };
        $cellValue->addText($value, ['size' => 9, 'color' => '111827', 'name' => 'Calibri'], ['alignment' => $alignment]);
    }
}
