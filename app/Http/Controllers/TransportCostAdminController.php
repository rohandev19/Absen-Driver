<?php

namespace App\Http\Controllers;

use App\Models\TransportCost;
use App\Models\Driver;
use App\Models\Project;
use App\Services\TransportCostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransportCostAdminController extends Controller
{
    public function __construct(
        private TransportCostService $transportCostService,
        private \App\Services\TransportCostDocumentService $documentService
    ) {}

    /**
     * Dashboard with metrics
     * GET /admin/transport-costs/dashboard
     */
    public function dashboard(Request $request)
    {
        $projectId = $request->input('project_id');
        $month = $request->input('month', now()->format('Y-m'));
        
        list($year, $monthNum) = explode('-', $month);

        $query = TransportCost::whereYear('trip_date', $year)
            ->whereMonth('trip_date', $monthNum);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $metrics = [
            'total_costs' => $query->sum('gasoline_cost') + $query->sum('toll_cost') + $query->sum('parking_cost'),
            'total_overtime' => $query->sum('overtime_payment'),
            'total_bonus' => $query->sum('bonus_driver'),
            'pending_count' => $query->where('approval_status', 'pending')->count(),
            'approved_count' => $query->where('approval_status', 'approved')->count(),
        ];

        // Average efficiency per driver
        $driverStats = TransportCost::selectRaw('driver_id, AVG(fuel_efficiency_ratio) as avg_efficiency, SUM(overtime_hours) as total_overtime')
            ->whereYear('trip_date', $year)
            ->whereMonth('trip_date', $monthNum)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->whereNotNull('fuel_efficiency_ratio')
            ->groupBy('driver_id')
            ->with('driver:id,full_name')
            ->get();

        $projects = Project::all();

        return view('admin.transport-costs.dashboard', compact('metrics', 'driverStats', 'projects', 'projectId', 'month'));
    }

    /**
     * Trip entry list with filters
     * GET /admin/transport-costs
     */
    public function index(Request $request)
    {
        $query = TransportCost::with(['driver', 'vehicle', 'project', 'financeSubmitter']);

        // Filters
        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        if ($request->filled('finance_status')) {
            if ($request->finance_status == 'submitted') {
                $query->where('submitted_to_finance', true);
            } elseif ($request->finance_status == 'not_submitted') {
                $query->where('submitted_to_finance', false)->where('approval_status', 'approved');
            }
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('trip_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('trip_date', '<=', $request->date_to);
        }

        $trips = $query->orderBy('trip_date', 'desc')->paginate(20);
        $projects = Project::all();
        $drivers = Driver::all();

        return view('admin.transport-costs.index', compact('trips', 'projects', 'drivers'));
    }

    /**
     * Trip entry detail
     * GET /admin/transport-costs/{id}
     */
    public function show($id)
    {
        $trip = TransportCost::with(['driver', 'vehicle', 'project', 'attendance', 'approver', 'financeSubmitter'])
            ->findOrFail($id);

        return view('admin.transport-costs.show', compact('trip'));
    }

    /**
     * Approve trip entry
     * POST /admin/transport-costs/{id}/approve
     */
    public function approve($id)
    {
        try {
            $trip = TransportCost::findOrFail($id);
            $this->transportCostService->approve($trip, Auth::id());

            return redirect()->back()->with('success', 'Trip entry berhasil disetujui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject trip entry
     * POST /admin/transport-costs/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter',
        ]);

        try {
            $trip = TransportCost::findOrFail($id);
            $this->transportCostService->reject($trip, Auth::id(), $request->rejection_reason);

            return redirect()->back()->with('success', 'Trip entry berhasil ditolak');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Submit approved trip entry to finance
     * POST /admin/transport-costs/{id}/submit-to-finance
     */
    public function submitToFinance($id)
    {
        try {
            $trip = TransportCost::findOrFail($id);
            if ($trip->approval_status !== 'approved') {
                return redirect()->back()->with('error', 'Laporan harus disetujui oleh admin terlebih dahulu sebelum diajukan ke finance.');
            }

            if ($trip->submitted_to_finance) {
                return redirect()->back()->with('error', 'Laporan sudah diajukan ke finance sebelumnya.');
            }

            $trip->update([
                'submitted_to_finance' => true,
                'submitted_to_finance_at' => now(),
                'submitted_to_finance_by' => Auth::id(),
            ]);

            return redirect()->back()->with('success', 'Laporan uang jalan berhasil diajukan ke finance.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk submit approved trip entries to finance
     * POST /admin/transport-costs/bulk-submit-to-finance
     */
    public function bulkSubmitToFinance(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu laporan untuk diajukan.');
        }

        try {
            $trips = TransportCost::whereIn('id', $ids)
                ->where('approval_status', 'approved')
                ->where('submitted_to_finance', false)
                ->get();

            if ($trips->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada laporan valid yang dapat diajukan (laporan harus disetujui admin dan belum diajukan).');
            }

            $count = $trips->count();
            foreach ($trips as $trip) {
                $trip->update([
                    'submitted_to_finance' => true,
                    'submitted_to_finance_at' => now(),
                    'submitted_to_finance_by' => Auth::id(),
                ]);
            }

            return redirect()->back()->with('success', "$count laporan uang jalan berhasil diajukan ke finance.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export finance submission document (Word)
     * GET /admin/transport-costs/{id}/export-finance
     */
    public function exportFinance($id)
    {
        try {
            $trip = TransportCost::with(['driver', 'vehicle', 'project', 'financeSubmitter', 'approver'])->findOrFail($id);

            if (!$trip->submitted_to_finance) {
                $trip->update([
                    'submitted_to_finance' => true,
                    'submitted_to_finance_at' => now(),
                    'submitted_to_finance_by' => Auth::id(),
                ]);
            }

            // Generate or retrieve existing document path
            if (!$trip->finance_word_path) {
                $financeWordPath = $this->documentService->generateSingleFinanceSubmission($trip);
                $trip->update(['finance_word_path' => $financeWordPath]);
            }

            $filePath = storage_path('app/public/' . $trip->finance_word_path);
            
            if (!file_exists($filePath)) {
                $financeWordPath = $this->documentService->generateSingleFinanceSubmission($trip);
                $trip->update(['finance_word_path' => $financeWordPath]);
                $filePath = storage_path('app/public/' . $financeWordPath);
            }

            return response()->download($filePath, 'Pengajuan_Finance_Uang_Jalan_' . $trip->id . '.docx');
        } catch (\Exception $e) {
            \Log::error("Finance transport cost export failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor berkas pengajuan keuangan: ' . $e->getMessage());
        }
    }

    /**
     * Monthly recap
     * GET /admin/transport-costs/recap
     */
    public function recap(Request $request)
    {
        $driverId = $request->input('driver_id');
        $projectId = $request->input('project_id');
        $month = $request->input('month', now()->format('Y-m'));
        
        list($year, $monthNum) = explode('-', $month);

        if ($driverId) {
            $recap = $this->transportCostService->getMonthlyRecap($driverId, $year, $monthNum);
            $driver = Driver::find($driverId);
        } else {
            $recap = null;
            $driver = null;
        }

        $drivers = Driver::when($projectId, fn($q) => $q->where('project_id', $projectId))->get();
        $projects = Project::all();

        return view('admin.transport-costs.recap', compact('recap', 'driver', 'drivers', 'projects', 'month', 'driverId', 'projectId'));
    }

    /**
     * Export monthly finance recap (Word)
     * GET /admin/transport-costs/recap/export-finance
     */
    public function exportFinanceRecap(Request $request)
    {
        $driverId = $request->input('driver_id');
        $month = $request->input('month', now()->format('Y-m'));

        if (!$driverId) {
            return redirect()->back()->with('error', 'Silakan pilih driver terlebih dahulu.');
        }

        try {
            list($year, $monthNum) = explode('-', $month);
            $recap = $this->transportCostService->getMonthlyRecap($driverId, $year, $monthNum);
            $driver = Driver::with('project')->findOrFail($driverId);

            if (empty($recap['trips']) || $recap['total_trips'] == 0) {
                return redirect()->back()->with('error', 'Tidak ada data perjalanan disetujui untuk driver ini pada bulan tersebut.');
            }

            $recapWordPath = $this->documentService->generateMonthlyFinanceRecap($recap, $driver, $month);
            $filePath = storage_path('app/public/' . $recapWordPath);

            return response()->download($filePath, 'Rekap_Finance_Bulanan_' . Str::slug($driver->full_name) . '_' . $month . '.docx');
        } catch (\Exception $e) {
            \Log::error("Finance monthly recap export failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor berkas rekap bulanan: ' . $e->getMessage());
        }
    }
}
