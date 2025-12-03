<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Attendance;
use App\Models\Vehicle;
use App\Models\EmergencyReport;
use App\Traits\FormatAttendance; // Menggunakan Trait

class DashboardController extends Controller
{
    use FormatAttendance; // Load Trait di sini

    public function index(Request $request)
    {
        try {
            $now = Carbon::now();

            // 1. Hitung total laporan darurat hari ini
            $totalLaporan = EmergencyReport::whereDate('timestamp', $now)->count();

            // 2. Hitung total jarak tempuh bulan ini
            $totalJarakBulanIni = Attendance::whereNotNull('time_out')
                ->whereMonth('time_out', $now->month)
                ->whereYear('time_out', $now->year)
                ->sum(DB::raw('speedo_akhir - speedo_awal'));

            // 3. Ambil driver yang sedang bertugas
            $onDutyDriversRaw = Attendance::with(['driver', 'vehicle'])
                ->whereNull('time_out')
                ->orderBy('time_in', 'desc')
                ->get();

            // 4. Hitung statistik aset
            $totalAset = Vehicle::count();
            $totalAsetUnikDipakai = $onDutyDriversRaw->pluck('vehicle_id')->unique()->count();
            $totalAsetTersedia = $totalAset - $totalAsetUnikDipakai;

            // 5. Data chart 7 hari terakhir
            $chartDataRaw = Attendance::whereNotNull('time_out')
                ->where('time_out', '>=', Carbon::now()->subDays(6)->startOfDay())
                ->groupBy(DB::raw('DATE(time_out)'))
                ->orderBy('date', 'asc')
                ->select(
                    DB::raw('DATE(time_out) as date'),
                    DB::raw('SUM(speedo_akhir - speedo_awal) as total_km')
                )
                ->get()
                ->keyBy('date');

            // 6. Format chart
            $chartLabels = [];
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dateString = $date->format('Y-m-d');
                $chartLabels[] = $date->isoFormat('ddd');
                $chartData[] = $chartDataRaw->get($dateString)->total_km ?? 0;
            }

            // 7. Format data driver (Via Trait)
            $onDutyDrivers = $onDutyDriversRaw->map(fn($item) => $this->formatAttendanceData($item));

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
            Log::error("Dashboard Error: " . $e->getMessage());
            return view('admin.dashboard', [
                'error' => 'Gagal memuat data dashboard.',
                'onDutyDrivers' => [],
                'totalAset' => 0,
                'totalLaporan' => 0,
                'totalJarakBulanIni' => 0,
                'totalAsetTersedia' => 0,
                'chartLabels' => [],
                'chartData' => [],
            ]);
        }
    }

    public function getStatus(Request $request): JsonResponse
    {
        $now = Carbon::now();
        $onDutyDriversRaw = Attendance::with(['driver', 'vehicle'])
            ->whereNull('time_out')
            ->orderBy('time_in', 'desc')
            ->get();

        $latestReport = EmergencyReport::with('driver')
            ->orderBy('timestamp', 'desc')
            ->first();

        return response()->json([
            'kpi' => [
                'driverBertugas' => $onDutyDriversRaw->count(),
                'asetTersedia' => Vehicle::count() - $onDutyDriversRaw->pluck('vehicle_id')->unique()->count(),
                'totalAset' => Vehicle::count(),
                'totalJarakBulanIni' => number_format(
                    Attendance::whereNotNull('time_out')
                        ->whereMonth('time_out', $now->month)
                        ->sum(DB::raw('speedo_akhir - speedo_awal'))
                ),
                'totalLaporan' => EmergencyReport::whereDate('timestamp', $now)->count(),
            ],
            'onDutyDrivers' => $onDutyDriversRaw->map(fn($item) => $this->formatAttendanceData($item)),
            'latestEmergencyReport' => $latestReport ? [
                'id' => $latestReport->id,
                'driver_name' => $latestReport->driver->full_name ?? 'N/A',
                'description' => Str::limit($latestReport->description, 50)
            ] : null,
        ]);
    }
}