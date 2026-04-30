<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Vehicle;
use App\Models\EmergencyReport;
use App\Traits\FormatAttendance;

class DashboardController extends Controller
{
    use FormatAttendance;

    /**
     * Menampilkan halaman utama Dashboard
     */
    public function index(Request $request)
    {
        try {
            $now = Carbon::now();

            // === 1. STATISTIK KPI UTAMA ===
            $totalLaporan = EmergencyReport::whereDate('timestamp', $now)->count();

            $totalJarakBulanIni = Attendance::whereNotNull('time_out')
                ->whereMonth('time_out', $now->month)
                ->whereYear('time_out', $now->year)
                ->sum(DB::raw('CAST(speedo_akhir AS SIGNED) - CAST(speedo_awal AS SIGNED)'));

            // === 2. DATA DRIVER BERTUGAS ===
            $onDutyDriversRaw = Attendance::with(['driver', 'vehicle.project'])
                ->whereNull('time_out')
                ->whereNotNull('gps_location_in')
                ->orderBy('time_in', 'desc')
                ->get();

            $totalAset = Vehicle::count();
            $totalAsetUnikDipakai = $onDutyDriversRaw->pluck('vehicle_id')->unique()->count();
            $totalAsetTersedia = $totalAset - $totalAsetUnikDipakai;

            // === 3. CHART DATA ===
            [$chartLabels, $chartData] = $this->buildChartData();

            // === 4. FORMAT DATA UNTUK VIEW ===
            // FIX #3: Tambahkan type hint Attendance di semua closure map()
            $onDutyDrivers = $onDutyDriversRaw->map(fn(Attendance $item) => $this->formatAttendanceData($item));

            return view('admin.dashboard', compact(
                'onDutyDrivers',
                'totalAset',
                'totalLaporan',
                'totalJarakBulanIni',
                'totalAsetTersedia',
                'chartLabels',
                'chartData'
            ));

        } catch (\Exception $e) {
            \Log::error("Dashboard Error: " . $e->getMessage());
            return view('admin.dashboard', ['error' => 'Gagal memuat data operasional: ' . $e->getMessage()]);
        }
    }

    /**
     * API AJAX untuk auto-refresh data secara live
     */
    public function getStatus(Request $request): JsonResponse
    {
        $now = Carbon::now();

        $onDutyDriversRaw = Attendance::with(['driver', 'vehicle'])
            ->whereNull('time_out')
            ->whereNotNull('gps_location_in')
            ->orderBy('time_in', 'desc')
            ->get();

        $totalVehicle = Vehicle::count();
        $totalAsetDipakai = $onDutyDriversRaw->pluck('vehicle_id')->unique()->count();

        $latestReport = EmergencyReport::with('driver')->latest('timestamp')->first();

        [$chartLabels, $chartData] = $this->buildChartData();

        return response()->json([
            'kpi' => [
                'driverBertugas' => $onDutyDriversRaw->count(),
                'asetTersedia' => $totalVehicle - $totalAsetDipakai,
                'totalAset' => $totalVehicle,
                'totalJarakBulanIni' => number_format(
                    Attendance::whereNotNull('time_out')
                        ->whereMonth('time_out', $now->month)
                        ->whereYear('time_out', $now->year)
                        ->sum(DB::raw('CAST(speedo_akhir AS SIGNED) - CAST(speedo_awal AS SIGNED)'))
                ),
                'totalLaporan' => EmergencyReport::whereDate('timestamp', $now)->count(),
            ],

            // FIX #3: Type hint Attendance di closure getStatus()
            'onDutyDrivers' => $onDutyDriversRaw->map(fn(Attendance $item) => $this->formatAttendanceData($item)),

            'latestEmergencyReport' => $latestReport ? [
                'id' => $latestReport->id,
                'driver_name' => $latestReport->driver->full_name ?? 'N/A',
                'description' => Str::limit($latestReport->description, 50),
                'maps_link' => $latestReport->google_maps_link,
            ] : null,

            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Helper: Bangun data chart aktivitas 7 hari terakhir.
     * Dipanggil dari index() dan getStatus() — DRY principle.
     *
     * @return array [labels[], data[]]
     */
    private function buildChartData(): array
    {
        $chartDataRaw = Attendance::whereNotNull('time_out')
            ->where('time_out', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(time_out)'))
            // FIX #2: Ganti orderBy('date') → orderBy(DB::raw('DATE(time_out)'))
            // 'date' adalah alias dari SELECT — tidak reliable di MySQL strict mode
            // dan bisa menyebabkan error "Unknown column 'date' in order clause"
            ->orderBy(DB::raw('DATE(time_out)'), 'asc')
            ->select(
                DB::raw('DATE(time_out) as date'),
                DB::raw('SUM(CAST(speedo_akhir AS SIGNED) - CAST(speedo_awal AS SIGNED)) as total_km')
            )
            ->get()
            ->keyBy('date');

        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            $labels[] = $date->isoFormat('ddd');
            $data[] = $chartDataRaw->has($dateString)
                ? (int) $chartDataRaw->get($dateString)->total_km
                : 0;
        }

        return [$labels, $data];
    }
}