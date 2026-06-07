<?php

namespace App\Http\Controllers;

use App\Models\ServiceReport;
use App\Services\ServiceReportDocumentService;
use App\Services\ServiceReportPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerApprovalController extends Controller
{
    protected $documentService;
    protected $pdfService;

    public function __construct(
        ServiceReportDocumentService $documentService,
        ServiceReportPdfService $pdfService
    ) {
        $this->documentService = $documentService;
        $this->pdfService = $pdfService;
    }

    /**
     * Display list of service reports for customer approval.
     */
    public function index(Request $request)
    {
        $customerId = Auth::user()->customer_id;

        if (!$customerId) {
            abort(403, 'User tidak terhubung dengan customer manapun.');
        }

        $baseQuery = ServiceReport::where('customer_id', $customerId)
            ->whereIn('status', [
                ServiceReport::STATUS_PENDING_CUSTOMER,
                ServiceReport::STATUS_APPROVED_CUSTOMER,
                ServiceReport::STATUS_REVISION_REQUESTED,
                ServiceReport::STATUS_REJECTED_CUSTOMER
            ]);

        // Hitung Statistik
        $countPending = (clone $baseQuery)->where('status', ServiceReport::STATUS_PENDING_CUSTOMER)->count();
        $countApproved = (clone $baseQuery)->where('status', ServiceReport::STATUS_APPROVED_CUSTOMER)->count();
        $countClarification = (clone $baseQuery)->where('status', ServiceReport::STATUS_REVISION_REQUESTED)->count();
        $countRejected = (clone $baseQuery)->where('status', ServiceReport::STATUS_REJECTED_CUSTOMER)->count();

        // Terapkan Filter
        $query = (clone $baseQuery)->with(['driver', 'vehicle', 'approvedByAdmin']);

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

        $reports = $query->latest('timestamp')->paginate(15)->withQueryString();

        return view('customer.approve.index', compact(
            'reports', 
            'countPending', 
            'countApproved', 
            'countClarification', 
            'countRejected'
        ));
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
     * Tampilkan halaman tanda tangan.
     */
    public function sign($id)
    {
        $customerId = Auth::user()->customer_id;

        $report = ServiceReport::with(['vehicle'])
            ->where('customer_id', $customerId)
            ->findOrFail($id);

        if ($report->status !== ServiceReport::STATUS_PENDING_CUSTOMER) {
            return redirect()->route('customer.approve.show', $report->id)
                ->with('error', 'Laporan ini tidak dalam status menunggu konfirmasi.');
        }

        return view('customer.approve.sign', compact('report'));
    }

    /**
     * Tampilkan halaman sukses.
     */
    public function success($id)
    {
        $customerId = Auth::user()->customer_id;

        $report = ServiceReport::with(['vehicle', 'approvedByCustomer'])
            ->where('customer_id', $customerId)
            ->findOrFail($id);

        if ($report->status !== ServiceReport::STATUS_APPROVED_CUSTOMER) {
            return redirect()->route('customer.approve.show', $report->id);
        }

        return view('customer.approve.success', compact('report'));
    }

    public function downloadApprovalDoc($id)
    {
        $customerId = Auth::user()->customer_id;

        $report = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByAdmin', 'approvedByCustomer'])
            ->where('customer_id', $customerId)
            ->findOrFail($id);

        if ($report->status === ServiceReport::STATUS_APPROVED_CUSTOMER) {
            if (!$report->customer_signed_pdf_path) {
                $finalPdfPath = $this->pdfService->generateCustomerFinal($report);
                $report->update(['customer_signed_pdf_path' => $finalPdfPath]);
            }

            $filePath = storage_path('app/public/' . $report->customer_signed_pdf_path);
            return response()->download($filePath, 'Persetujuan_Service_Final_' . $report->id . '.pdf');
        }

        if ($report->status !== ServiceReport::STATUS_PENDING_CUSTOMER) {
            return back()->with('error', 'Dokumen approval belum tersedia.');
        }

        if (!$report->customer_pdf_path) {
            $draftPdfPath = $this->pdfService->generateCustomerDraft($report);
            $report->update(['customer_pdf_path' => $draftPdfPath]);
        }

        $filePath = storage_path('app/public/' . $report->customer_pdf_path);
        return response()->download($filePath, 'Persetujuan_Service_Draft_' . $report->id . '.pdf');
    }

    public function previewApprovalPdf($id)
    {
        $customerId = Auth::user()->customer_id;

        $report = ServiceReport::with(['driver', 'vehicle', 'customer', 'approvedByAdmin', 'approvedByCustomer'])
            ->where('customer_id', $customerId)
            ->findOrFail($id);

        if ($report->status === ServiceReport::STATUS_APPROVED_CUSTOMER) {
            return $this->pdfService->streamCustomerFinal($report);
        }

        if ($report->status !== ServiceReport::STATUS_PENDING_CUSTOMER) {
            abort(403, 'Dokumen approval belum tersedia.');
        }

        return $this->pdfService->streamCustomerDraft($report);
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
            $report->refresh();
            $customerSignedPdfPath = $this->pdfService->generateCustomerFinal($report);
            
            $report->update([
                'customer_signed_pdf_path' => $customerSignedPdfPath,
            ]);

            return redirect()->route('customer.approve.success', $report->id)
                ->with('success', 'Persetujuan service berhasil diproses.');
        } catch (\Exception $e) {
            Log::error("Gagal memproses persetujuan customer: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat memproses persetujuan.');
        }
    }

    /**
     * Tolak persetujuan dari customer.
     */
    public function reject(Request $request, $id)
    {
        $customerId = Auth::user()->customer_id;

        $report = ServiceReport::where('customer_id', $customerId)
            ->where('status', ServiceReport::STATUS_PENDING_CUSTOMER)
            ->findOrFail($id);

        $validated = $request->validate([
            'customer_rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $report->update([
                'status' => ServiceReport::STATUS_REJECTED_CUSTOMER,
                'customer_rejection_reason' => $validated['customer_rejection_reason'],
                'rejected_by_role' => 'customer',
                'rejected_at' => now(),
            ]);

            return redirect()->route('customer.approve.show', $report->id)
                ->with('success', 'Laporan service telah berhasil ditolak.');
        } catch (\Exception $e) {
            Log::error("Gagal memproses penolakan customer: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat memproses penolakan.');
        }
    }

    /**
     * Minta klarifikasi dari customer.
     */
    public function clarify(Request $request, $id)
    {
        $customerId = Auth::user()->customer_id;

        $report = ServiceReport::where('customer_id', $customerId)
            ->where('status', ServiceReport::STATUS_PENDING_CUSTOMER)
            ->findOrFail($id);

        $validated = $request->validate([
            'customer_revision_notes' => 'required|string|max:500',
        ]);

        try {
            $report->update([
                'status' => ServiceReport::STATUS_REVISION_REQUESTED,
                'customer_revision_notes' => $validated['customer_revision_notes'],
                'revision_requested_at' => now(),
            ]);

            return redirect()->route('customer.approve.show', $report->id)
                ->with('success', 'Permintaan klarifikasi telah berhasil dikirim ke Admin.');
        } catch (\Exception $e) {
            Log::error("Gagal memproses klarifikasi customer: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat memproses permintaan klarifikasi.');
        }
    }
}
