<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\ServiceReport;
use App\Models\Vehicle;
use App\Services\ServiceReportDocumentService;
use App\Services\ServiceReportPdfService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceReportController extends Controller
{
    protected $documentService;
    protected $pdfService;
    protected $whatsappService;

    public function __construct(
        ServiceReportDocumentService $documentService,
        ServiceReportPdfService $pdfService,
        WhatsAppNotificationService $whatsappService
    ) {
        $this->documentService = $documentService;
        $this->pdfService = $pdfService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display list of service reports.
     */
    public function index(Request $request)
    {
        $baseQuery = ServiceReport::query();

        // Hitung Statistik
        $countPendingAdmin = (clone $baseQuery)->whereIn('status', [ServiceReport::STATUS_PENDING, ServiceReport::STATUS_PENDING_ADMIN])->count();
        $countPendingCustomer = (clone $baseQuery)->where('status', ServiceReport::STATUS_PENDING_CUSTOMER)->count();
        $countApproved = (clone $baseQuery)->whereIn('status', [ServiceReport::STATUS_APPROVED_ADMIN, ServiceReport::STATUS_APPROVED_CUSTOMER])->count();
        $countIssues = (clone $baseQuery)->whereIn('status', [ServiceReport::STATUS_REVISION_REQUESTED, ServiceReport::STATUS_REJECTED_CUSTOMER, ServiceReport::STATUS_REJECTED_ADMIN, ServiceReport::STATUS_REJECTED])->count();

        // Query Utama
        $query = ServiceReport::with(['driver', 'vehicle', 'customer'])->latest('timestamp');

        // Filter Status
        if ($request->filled('status') && $request->status !== 'Semua Status') {
            $query->where('status', $request->status);
        }

        // Filter Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('timestamp', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        // Filter Plat Nomor
        if ($request->filled('plate_number')) {
            $query->whereHas('vehicle', function($q) use ($request) {
                $q->where('plate_number', 'like', '%' . $request->plate_number . '%');
            });
        }

        $reports = $query->paginate(15)->withQueryString();

        return view('admin.service.index', compact(
            'reports', 
            'countPendingAdmin', 
            'countPendingCustomer', 
            'countApproved', 
            'countIssues'
        ));
    }

    /**
     * Show form for admin manual service report entry.
     */
    public function createManual()
    {
        $drivers = Driver::orderBy('full_name')->get(['id', 'full_name', 'driver_id_nik']);
        $vehicles = Vehicle::with('project')->orderBy('plate_number')->get(['id', 'plate_number', 'type', 'project_id']);

        return view('admin.service.create', compact('drivers', 'vehicles'));
    }

    /**
     * Store admin manual service report.
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'timestamp' => 'required|date',
            'gps_location' => ['nullable', 'string', 'regex:/^[-]?\d+(\.\d+)?,\s*[-]?\d+(\.\d+)?$/'],
            'manual_location' => 'nullable|string|max:255',
            'description' => 'required|string|min:10|max:2000',
            'service_type' => 'nullable|string|max:255',
            'problem_category' => 'nullable|string|max:255',
            'service_action' => 'required|string|min:5|max:2000',
            'unit_status_after_service' => 'required|string|max:255',
            'odometer' => 'nullable|integer|min:0',
            'additional_notes' => 'nullable|string|max:2000',
            'vehicle_condition_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
            'after_service_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
            'odometer_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
            'receipt_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
            'temp_vehicle_condition_photo' => 'nullable|string|max:500',
            'temp_after_service_photo' => 'nullable|string|max:500',
            'temp_odometer_photo' => 'nullable|string|max:500',
            'temp_receipt_photo' => 'nullable|string|max:500',
        ]);

        if (!$request->filled('gps_location') && !$request->filled('manual_location')) {
            return back()->withInput()->withErrors([
                'manual_location' => 'GPS atau lokasi manual wajib diisi.',
            ]);
        }

        $vehicleConditionPath = $this->resolveManualPhotoPath($request, 'vehicle_condition_photo', 'temp_vehicle_condition_photo');
        $afterServicePath = $this->resolveManualPhotoPath($request, 'after_service_photo', 'temp_after_service_photo');
        $receiptPath = $this->resolveManualPhotoPath($request, 'receipt_photo', 'temp_receipt_photo');
        $odometerPath = $this->resolveManualPhotoPath($request, 'odometer_photo', 'temp_odometer_photo');

        if (!$vehicleConditionPath || !$afterServicePath || !$receiptPath) {
            return back()->withInput()->withErrors([
                'vehicle_condition_photo' => 'Foto sebelum service, setelah service, dan kuitansi wajib diunggah.',
            ]);
        }

        $vehicle = Vehicle::with('project')->findOrFail($validated['vehicle_id']);

        $report = ServiceReport::create([
            'ticket_number' => $this->generateTicketNumber(),
            'driver_id' => $validated['driver_id'],
            'vehicle_id' => $validated['vehicle_id'],
            'customer_id' => $vehicle->project?->customer_id,
            'timestamp' => $validated['timestamp'],
            'gps_location' => $validated['gps_location'] ?? $validated['manual_location'],
            'location_source' => !empty($validated['gps_location']) ? 'gps' : 'manual',
            'description' => $validated['description'],
            'report_source' => 'admin_manual',
            'service_type' => $validated['service_type'] ?? null,
            'problem_category' => $validated['problem_category'] ?? null,
            'odometer' => $validated['odometer'] ?? null,
            'service_action' => $validated['service_action'],
            'unit_status_after_service' => $validated['unit_status_after_service'],
            'service_completed_at' => now(),
            'additional_notes' => $validated['additional_notes'] ?? null,
            'before_service_photo_uploaded_at' => now(),
            'vehicle_condition_photo_path' => $vehicleConditionPath,
            'after_service_photo_path' => $afterServicePath,
            'after_service_photo_taken_at' => now(),
            'odometer_photo_path' => $odometerPath,
            'odometer_photo_taken_at' => $odometerPath ? now() : null,
            'receipt_photo_path' => $receiptPath,
            'receipt_photo_taken_at' => now(),
            'status' => ServiceReport::STATUS_PENDING,
        ]);

        $this->syncVehicleStatusAfterService($vehicle, $report->unit_status_after_service);

        return redirect()->route('admin.service.show', $report->id)
            ->with('success', 'Laporan service manual berhasil disimpan.');
    }

    private function resolveManualPhotoPath(Request $request, string $fileKey, string $tempKey): ?string
    {
        if ($request->hasFile($fileKey)) {
            return app(\App\Services\ImageProcessingService::class)->optimize($request->file($fileKey), 'photos');
        }

        $tempPath = $request->input($tempKey);
        if ($tempPath && Storage::disk('public')->exists($tempPath)) {
            return $tempPath;
        }

        return null;
    }

    private function generateTicketNumber(): string
    {
        $year = date('Y');
        $latestReport = ServiceReport::whereYear('created_at', $year)
            ->where('ticket_number', 'like', "LS-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latestReport && preg_match('/LS-' . $year . '-(\d+)/', $latestReport->ticket_number, $matches)) {
            $nextId = intval($matches[1]) + 1;
        } else {
            $nextId = 1;
        }

        return 'LS-' . $year . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Display detailed view of a service report.
     */
    public function show($id)
    {
        $report = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByAdmin', 'approvedByCustomer'])
            ->findOrFail($id);

        return view('admin.service.show', compact('report'));
    }

    /**
     * Approve service report by admin.
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            // SECURITY FIX: Limit base64 signature size to ~2MB
            'signature'   => 'required|string|max:2800000',
            'signer_name' => 'required|string|max:255',
            'signer_role' => 'required|string|max:255',
            'workshop_name'  => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255',
            'service_cost'   => 'required|numeric|min:0',
            'sparepart_cost' => 'required|numeric|min:0',
            'other_cost'     => 'nullable|numeric|min:0',
            'total_cost'     => 'required|numeric|min:0',
            'finance_notes'  => 'nullable|string|max:1000',
            'replace_vehicle_condition_photo' => 'nullable|image|max:10240',
            'replace_after_service_photo' => 'nullable|image|max:10240',
            'replace_odometer_photo' => 'nullable|image|max:10240',
            'replace_receipt_photo' => 'nullable|image|max:10240',
        ]);

        $report = ServiceReport::findOrFail($id);

        if (!in_array($report->status, [ServiceReport::STATUS_PENDING, ServiceReport::STATUS_PENDING_ADMIN, ServiceReport::STATUS_REVISION_REQUESTED])) {
            return back()->with('error', 'Laporan ini sudah diproses sebelumnya.');
        }

        // Handle signature image
        $signaturePath = null;
        if (!empty($validated['signature'])) {
            $imageParts = explode(";base64,", $validated['signature']);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = strtolower($imageTypeAux[1] ?? '');
                
                $allowedTypes = ['png', 'jpeg', 'jpg'];
                if (!in_array($imageType, $allowedTypes)) {
                    return back()->with('error', 'Format tanda tangan tidak valid.');
                }

                $imageBase64 = base64_decode($imageParts[1], true);
                if ($imageBase64 === false) { 
                    abort(422, 'Invalid signature data'); 
                }

                $fileName = 'signatures/admin_' . $report->id . '_' . uniqid() . '.' . ($imageType === 'jpeg' ? 'jpg' : $imageType);
                
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageBase64);
                $signaturePath = $fileName;
            }
        }

        // Handle replacement photos if uploaded
        $updateData = [
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
            'approved_by_admin_id' => Auth::id(),
            'approved_at_admin' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
            'admin_signature_path' => $signaturePath,
            'admin_signer_name' => $validated['signer_name'],
            'admin_signer_role' => $validated['signer_role'],
            'workshop_name' => $validated['workshop_name'],
            'invoice_number' => $validated['invoice_number'],
            'service_cost' => $validated['service_cost'],
            'sparepart_cost' => $validated['sparepart_cost'],
            'other_cost' => $validated['other_cost'] ?? 0,
            'total_cost' => $validated['total_cost'],
            'finance_notes' => $validated['finance_notes'] ?? null,
        ];

        if ($request->hasFile('replace_vehicle_condition_photo')) {
            $updateData['vehicle_condition_photo_path'] = app(\App\Services\ImageProcessingService::class)->optimize($request->file('replace_vehicle_condition_photo'), 'photos');
            $updateData['before_service_photo_uploaded_at'] = now();
        }
        if ($request->hasFile('replace_after_service_photo')) {
            $updateData['after_service_photo_path'] = app(\App\Services\ImageProcessingService::class)->optimize($request->file('replace_after_service_photo'), 'photos');
            $updateData['after_service_photo_taken_at'] = now();
        }
        if ($request->hasFile('replace_odometer_photo')) {
            $updateData['odometer_photo_path'] = app(\App\Services\ImageProcessingService::class)->optimize($request->file('replace_odometer_photo'), 'photos');
            $updateData['odometer_photo_taken_at'] = now();
        }
        if ($request->hasFile('replace_receipt_photo')) {
            $updateData['receipt_photo_path'] = app(\App\Services\ImageProcessingService::class)->optimize($request->file('replace_receipt_photo'), 'photos');
            $updateData['receipt_photo_taken_at'] = now();
        }

        // Update report status
        $report->update($updateData);

        // Generate PDFs
        try {
            $report->refresh();

            $customerPdfPath = $this->pdfService->generateCustomerDraft($report);
            $adminInternalPdfPath = $this->pdfService->generateAdminInternal($report);

            $report->update([
                'customer_pdf_path' => $customerPdfPath,
                'admin_internal_pdf_path' => $adminInternalPdfPath,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to generate service report PDFs after admin approve: ' . $e->getMessage());
        }

        // Send WhatsApp notification to customer
        try {
            $this->whatsappService->notifyCustomer($report);
        } catch (\Throwable $e) {
            Log::warning("WhatsApp notification to customer failed: " . $e->getMessage());
        }

        return redirect()->route('admin.service.index')
            ->with('success', 'Laporan service telah disetujui dan dikirim ke customer untuk approval.');
    }

    /**
     * Complete and approve service report by admin (takeover).
     */
    public function completeByAdmin(Request $request, $id)
    {
        $validated = $request->validate([
            // Driver completion fields
            'service_action' => 'required|string|min:5|max:2000',
            'unit_status_after_service' => 'required|string|max:255',
            'odometer' => 'nullable|integer|min:0',
            'after_service_photo' => 'required|image|mimes:jpeg,jpg,png|max:10240',
            'receipt_photo' => 'required|image|mimes:jpeg,jpg,png|max:10240',
            'odometer_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',

            // Admin approval fields
            'admin_notes' => 'nullable|string|max:1000',
            'signature'   => 'required|string|max:2800000',
            'signer_name' => 'required|string|max:255',
            'signer_role' => 'required|string|max:255',
            'workshop_name'  => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255',
            'service_cost'   => 'required|numeric|min:0',
            'sparepart_cost' => 'required|numeric|min:0',
            'other_cost'     => 'nullable|numeric|min:0',
            'total_cost'     => 'required|numeric|min:0',
            'finance_notes'  => 'nullable|string|max:1000',
        ]);

        $report = ServiceReport::findOrFail($id);

        if ($report->status !== ServiceReport::STATUS_WAITING_COMPLETION) {
            return back()->with('error', 'Laporan ini tidak dalam status menunggu kelengkapan.');
        }

        // Handle signature image
        $signaturePath = null;
        if (!empty($validated['signature'])) {
            $imageParts = explode(";base64,", $validated['signature']);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = strtolower($imageTypeAux[1] ?? '');
                
                $allowedTypes = ['png', 'jpeg', 'jpg'];
                if (!in_array($imageType, $allowedTypes)) {
                    return back()->with('error', 'Format tanda tangan tidak valid.');
                }

                $imageBase64 = base64_decode($imageParts[1], true);
                if ($imageBase64 === false) { 
                    abort(422, 'Invalid signature data'); 
                }

                $fileName = 'signatures/admin_' . $report->id . '_' . uniqid() . '.' . ($imageType === 'jpeg' ? 'jpg' : $imageType);
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageBase64);
                $signaturePath = $fileName;
            }
        }

        // Handle photos
        $afterServicePhotoPath = app(\App\Services\ImageProcessingService::class)->optimize($request->file('after_service_photo'), 'photos');
        $receiptPhotoPath = app(\App\Services\ImageProcessingService::class)->optimize($request->file('receipt_photo'), 'photos');
        $odometerPhotoPath = $request->hasFile('odometer_photo')
            ? app(\App\Services\ImageProcessingService::class)->optimize($request->file('odometer_photo'), 'photos')
            : null;

        $updateData = [
            // Driver completion data
            'service_action' => $validated['service_action'],
            'unit_status_after_service' => $validated['unit_status_after_service'],
            'service_completed_at' => now(),
            'completed_by_driver_id' => null, // completed by admin takeover
            'after_service_photo_path' => $afterServicePhotoPath,
            'after_service_photo_taken_at' => now(),
            'receipt_photo_path' => $receiptPhotoPath,
            'receipt_photo_taken_at' => now(),
            
            // Admin approval data
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
            'approved_by_admin_id' => Auth::id(),
            'approved_at_admin' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
            'admin_signature_path' => $signaturePath,
            'admin_signer_name' => $validated['signer_name'],
            'admin_signer_role' => $validated['signer_role'],
            'workshop_name' => $validated['workshop_name'],
            'invoice_number' => $validated['invoice_number'],
            'service_cost' => $validated['service_cost'],
            'sparepart_cost' => $validated['sparepart_cost'],
            'other_cost' => $validated['other_cost'] ?? 0,
            'total_cost' => $validated['total_cost'],
            'finance_notes' => $validated['finance_notes'] ?? null,
        ];

        if (array_key_exists('odometer', $validated)) {
            $updateData['odometer'] = $validated['odometer'];
        }

        if ($odometerPhotoPath) {
            $updateData['odometer_photo_path'] = $odometerPhotoPath;
            $updateData['odometer_photo_taken_at'] = now();
        }

        // Update report status
        $report->update($updateData);

        // Sync vehicle status
        $this->syncVehicleStatusAfterService($report->vehicle, $report->unit_status_after_service);

        // Generate PDFs
        try {
            $report->refresh();

            $customerPdfPath = $this->pdfService->generateCustomerDraft($report);
            $adminInternalPdfPath = $this->pdfService->generateAdminInternal($report);

            $report->update([
                'customer_pdf_path' => $customerPdfPath,
                'admin_internal_pdf_path' => $adminInternalPdfPath,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to generate service report PDFs after admin takeover: ' . $e->getMessage());
        }

        // Send WhatsApp notification to customer
        try {
            $this->whatsappService->notifyCustomer($report);
        } catch (\Throwable $e) {
            Log::warning("WhatsApp notification to customer failed after takeover: " . $e->getMessage());
        }

        return redirect()->route('admin.service.index')
            ->with('success', 'Laporan service berhasil dilengkapi oleh Admin dan dikirim ke customer untuk approval.');
    }

    private function syncVehicleStatusAfterService(?\App\Models\Vehicle $vehicle, ?string $unitStatus): void
    {
        if (!$vehicle || !$unitStatus) {
            return;
        }

        $status = strtolower($unitStatus);

        if (str_contains($status, 'aman') ||
            str_contains($status, 'selesai') ||
            str_contains($status, 'jalan') ||
            str_contains($status, 'aktif') ||
            str_contains($status, 'baik')) {
            $vehicle->update(['status' => 'Aktif']);
            return;
        }

        if (str_contains($status, 'servis') || str_contains($status, 'bengkel')) {
            $vehicle->update(['status' => 'Servis']);
            return;
        }

        $vehicle->update(['status' => 'Rusak']);
    }


    /**
     * Reject service report.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejected_reason' => 'required|string|max:1000',
        ]);

        $report = ServiceReport::findOrFail($id);

        if (!in_array($report->status, [ServiceReport::STATUS_PENDING, ServiceReport::STATUS_PENDING_ADMIN])) {
            return back()->with('error', 'Laporan ini sudah diproses sebelumnya.');
        }

        $report->update([
            'status' => ServiceReport::STATUS_REJECTED,
            'rejected_reason' => $validated['rejected_reason'],
        ]);

        return redirect()->route('admin.service.index')
            ->with('success', 'Laporan service telah ditolak.');
    }

    public function previewCustomerPdf($id)
    {
        $report = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByAdmin', 'approvedByCustomer'])
            ->findOrFail($id);

        if (!in_array($report->status, [
            ServiceReport::STATUS_PENDING_CUSTOMER,
            ServiceReport::STATUS_APPROVED_CUSTOMER,
        ])) {
            return back()->with('error', 'PDF customer belum tersedia karena laporan belum disetujui admin.');
        }

        if ($report->status === ServiceReport::STATUS_APPROVED_CUSTOMER) {
            return $this->pdfService->streamCustomerFinal($report);
        }

        return $this->pdfService->streamCustomerDraft($report);
    }

    public function downloadCustomerPdf($id)
    {
        $report = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByAdmin', 'approvedByCustomer'])
            ->findOrFail($id);

        if (!in_array($report->status, [
            ServiceReport::STATUS_PENDING_CUSTOMER,
            ServiceReport::STATUS_APPROVED_CUSTOMER,
        ])) {
            return back()->with('error', 'PDF customer belum tersedia karena laporan belum disetujui admin.');
        }

        try {
            if ($report->status === ServiceReport::STATUS_APPROVED_CUSTOMER) {
                if (!$report->customer_signed_pdf_path) {
                    $path = $this->pdfService->generateCustomerFinal($report);
                    $report->update(['customer_signed_pdf_path' => $path]);
                }

                $filePath = storage_path('app/public/' . $report->customer_signed_pdf_path);
                return response()->download($filePath, 'Persetujuan_Service_Final_' . $report->id . '.pdf');
            }

            if (!$report->customer_pdf_path) {
                $path = $this->pdfService->generateCustomerDraft($report);
                $report->update(['customer_pdf_path' => $path]);
            }

            $filePath = storage_path('app/public/' . $report->customer_pdf_path);
            return response()->download($filePath, 'Persetujuan_Service_Draft_' . $report->id . '.pdf');
        } catch (\Throwable $e) {
            Log::error('Download customer PDF failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat atau mengunduh PDF customer.');
        }
    }

    public function previewAdminInternalPdf($id)
    {
        $report = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByAdmin', 'approvedByCustomer'])
            ->findOrFail($id);

        return $this->pdfService->streamAdminInternal($report);
    }

    public function downloadAdminInternalPdf($id)
    {
        $report = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByAdmin', 'approvedByCustomer'])
            ->findOrFail($id);

        try {
            if (!$report->admin_internal_pdf_path) {
                $path = $this->pdfService->generateAdminInternal($report);
                $report->update(['admin_internal_pdf_path' => $path]);
            }

            $filePath = storage_path('app/public/' . $report->admin_internal_pdf_path);
            return response()->download($filePath, 'Laporan_Service_Internal_' . $report->id . '.pdf');
        } catch (\Throwable $e) {
            Log::error('Download admin internal PDF failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat atau mengunduh PDF internal admin.');
        }
    }

    public function previewFinancePdf($id)
    {
        $report = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByAdmin', 'approvedByCustomer'])
            ->findOrFail($id);

        if ($report->status === ServiceReport::STATUS_PENDING || $report->status === ServiceReport::STATUS_PENDING_ADMIN) {
            return back()->with('error', 'Laporan harus disetujui admin terlebih dahulu sebelum masuk finance.');
        }

        return $this->pdfService->streamFinanceSubmission($report);
    }

    public function downloadFinancePdf($id)
    {
        $report = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByAdmin', 'approvedByCustomer'])
            ->findOrFail($id);

        if ($report->status === ServiceReport::STATUS_PENDING || $report->status === ServiceReport::STATUS_PENDING_ADMIN) {
            return back()->with('error', 'Laporan harus disetujui admin terlebih dahulu sebelum masuk finance.');
        }

        try {
            if (!$report->finance_pdf_path) {
                $path = $this->pdfService->generateFinanceSubmission($report);
                $report->update(['finance_pdf_path' => $path]);
            }

            $filePath = storage_path('app/public/' . $report->finance_pdf_path);
            return response()->download($filePath, 'Pengajuan_Finance_Service_' . $report->id . '.pdf');
        } catch (\Throwable $e) {
            Log::error('Download finance PDF failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat atau mengunduh PDF finance.');
        }
    }

    /**
     * Display service reports that have been approved by customer.
     */
    public function customerApprovalsView()
    {
        $reports = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByCustomer'])
            ->where('status', ServiceReport::STATUS_APPROVED_CUSTOMER)
            ->latest('approved_at_customer')
            ->paginate(15);

        return view('admin.service.customer-approvals', compact('reports'));
    }
}
