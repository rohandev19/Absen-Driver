<?php

namespace App\Http\Controllers;

use App\Models\ServiceReport;
use App\Services\ServiceReportDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerApprovalController extends Controller
{
    protected $documentService;

    public function __construct(ServiceReportDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display list of service reports for customer approval.
     */
    public function index()
    {
        $customerId = Auth::user()->customer_id;

        if (!$customerId) {
            abort(403, 'User tidak terhubung dengan customer manapun.');
        }

        $reports = ServiceReport::with(['driver', 'vehicle', 'approvedByAdmin'])
            ->where('customer_id', $customerId)
            ->whereIn('status', [ServiceReport::STATUS_PENDING_CUSTOMER, ServiceReport::STATUS_APPROVED_CUSTOMER])
            ->latest('timestamp')
            ->paginate(15);

        return view('customer.approve.index', compact('reports'));
    }

    /**
     * Display detailed view of a service report (read-only for customer).
     */
    public function show($id)
    {
        $customerId = Auth::user()->customer_id;

        $report = ServiceReport::with(['driver', 'vehicle', 'approvedByAdmin', 'approvedByCustomer'])
            ->where('customer_id', $customerId)
            ->findOrFail($id);

        return view('customer.approve.show', compact('report'));
    }

    /**
     * Download customer approval document (Word).
     */
    public function downloadApprovalDoc($id)
    {
        $customerId = Auth::user()->customer_id;

        $report = ServiceReport::where('customer_id', $customerId)->findOrFail($id);

        // Generate document if not exists
        if (!$report->customer_word_path) {
            try {
                $customerWordPath = $this->documentService->generateCustomerApprovalDocument($report);
                $report->update(['customer_word_path' => $customerWordPath]);
            } catch (\Exception $e) {
                Log::error("Failed to generate customer document: " . $e->getMessage());
                return back()->with('error', 'Gagal menggenerate dokumen persetujuan.');
            }
        }

        $filePath = storage_path('app/public/' . $report->customer_word_path);

        if (!file_exists($filePath)) {
            return back()->with('error', 'File dokumen tidak ditemukan.');
        }

        return response()->download($filePath, 'Persetujuan_Service_' . $report->id . '.docx');
    }

    /**
     * Upload signed document from customer.
     */
    public function uploadSignedDocument(Request $request, $id)
    {
        $customerId = Auth::user()->customer_id;

        $report = ServiceReport::where('customer_id', $customerId)
            ->where('status', ServiceReport::STATUS_PENDING_CUSTOMER)
            ->findOrFail($id);

        $validated = $request->validate([
            // SECURITY FIX: Limit base64 signature size to ~2MB to prevent memory abuse
            'signature' => 'required|string|max:2800000',
            'signer_name' => 'required|string|max:255',
            'signer_role' => 'required|string|max:255',
        ]);

        try {
            // Start processing signature and document generation
            $signatureData = $validated['signature'];
            $imageParts = explode(";base64,", $signatureData);
            
            if (count($imageParts) !== 2) {
                return back()->with('error', 'Format tanda tangan tidak valid.');
            }
            
            $imageBase64 = base64_decode($imageParts[1]);
            $fileName = 'signatures/customer_' . $report->id . '_' . uniqid() . '.png';
            
            \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageBase64);

            $report->update([
                'status' => ServiceReport::STATUS_APPROVED_CUSTOMER,
                'approved_by_customer_id' => Auth::id(),
                'approved_at_customer' => now(),
                'customer_signature_path' => $fileName,
                'customer_signer_name' => $validated['signer_name'],
                'customer_signer_role' => $validated['signer_role'],
            ]);
            
            // Regenerate the document so it includes both signatures
            $customerWordPath = $this->documentService->generateCustomerApprovalDocument(
                $report,
                $report->admin_signer_name,
                $report->admin_signer_role
            );
            
            $report->update([
                'customer_signed_document_path' => $customerWordPath,
            ]);

            return redirect()->route('customer.approve.index')
                ->with('success', 'Berhasil menyetujui laporan service darurat secara digital.');
        } catch (\Exception $e) {
            Log::error("Gagal memproses persetujuan customer: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat memproses persetujuan.');
        }
    }
}
