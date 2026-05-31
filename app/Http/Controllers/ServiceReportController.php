<?php

namespace App\Http\Controllers;

use App\Models\ServiceReport;
use App\Services\ServiceReportDocumentService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceReportController extends Controller
{
    protected $documentService;
    protected $whatsappService;

    public function __construct(
        ServiceReportDocumentService $documentService,
        WhatsAppNotificationService $whatsappService
    ) {
        $this->documentService = $documentService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display list of service reports.
     */
    public function index(Request $request)
    {
        $query = ServiceReport::with(['driver', 'vehicle', 'customer'])
            ->latest('timestamp');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(15);

        return view('admin.service.index', compact('reports'));
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
        ]);

        $report = ServiceReport::findOrFail($id);

        if ($report->status !== ServiceReport::STATUS_PENDING) {
            return back()->with('error', 'Laporan ini sudah diproses sebelumnya.');
        }

        // Handle signature image
        $signaturePath = null;
        if (!empty($validated['signature'])) {
            $imageParts = explode(";base64,", $validated['signature']);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = $imageTypeAux[1];
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'signatures/admin_' . $report->id . '_' . uniqid() . '.' . $imageType;
                
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageBase64);
                $signaturePath = $fileName;
            }
        }

        // Update report status
        $report->update([
            'status' => ServiceReport::STATUS_PENDING_CUSTOMER,
            'approved_by_admin_id' => Auth::id(),
            'approved_at_admin' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
            'admin_signature_path' => $signaturePath,
            'admin_signer_name' => $validated['signer_name'],
            'admin_signer_role' => $validated['signer_role'],
        ]);

        // Generate customer approval document
        try {
            $customerWordPath = $this->documentService->generateCustomerApprovalDocument($report, $validated['signer_name'], $validated['signer_role']);
            $report->update(['customer_word_path' => $customerWordPath]);
        } catch (\Throwable $e) {
            Log::error("Failed to generate customer document: " . $e->getMessage());
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
     * Reject service report.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejected_reason' => 'required|string|max:1000',
        ]);

        $report = ServiceReport::findOrFail($id);

        if ($report->status !== ServiceReport::STATUS_PENDING) {
            return back()->with('error', 'Laporan ini sudah diproses sebelumnya.');
        }

        $report->update([
            'status' => ServiceReport::STATUS_REJECTED,
            'rejected_reason' => $validated['rejected_reason'],
        ]);

        return redirect()->route('admin.service.index')
            ->with('success', 'Laporan service telah ditolak.');
    }

    /**
     * Export finance submission document (Word).
     */
    public function exportFinance($id)
    {
        $report = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByAdmin'])
            ->findOrFail($id);

        if ($report->status === ServiceReport::STATUS_PENDING) {
            return back()->with('error', 'Laporan harus disetujui terlebih dahulu.');
        }

        try {
            // Generate or retrieve existing finance document
            if (!$report->finance_word_path) {
                $financeWordPath = $this->documentService->generateFinanceSubmission($report);
                $report->update(['finance_word_path' => $financeWordPath]);
            }

            $filePath = storage_path('app/public/' . $report->finance_word_path);

            if (!file_exists($filePath)) {
                return back()->with('error', 'File dokumen tidak ditemukan.');
            }

            return response()->download($filePath, 'Pengajuan_Finance_' . $report->id . '.docx');

        } catch (\Throwable $e) {
            Log::error("Finance export failed: " . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor dokumen finance.');
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
