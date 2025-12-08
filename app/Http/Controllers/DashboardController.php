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
use App\Traits\FormatAttendance;

/**
 * Class DashboardController
 * * Controller ini berfungsi sebagai halaman utama (Landing Page) untuk Admin.
 * * Bertanggung jawab untuk:
 * 1. Mengumpulkan statistik operasional (KPI) harian dan bulanan.
 * 2. Menyiapkan data visualisasi grafik (Chart).
 * 3. Menyediakan API endpoint untuk update data dashboard secara real-time (AJAX).
 * * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Trait FormatAttendance digunakan untuk memformat data driver yang sedang bertugas
     * agar tampilan waktu dan statusnya konsisten.
     */
    use FormatAttendance;

    /**
     * Menampilkan Halaman Dashboard Utama.
     * * Method ini memuat semua data awal yang diperlukan saat halaman pertama kali dibuka.
     * * Data yang dimuat meliputi:
     * - KPI (Total Laporan, Total Jarak, Ketersediaan Aset).
     * - Daftar Driver yang sedang aktif (On Duty).
     * - Data Grafik kinerja 7 hari terakhir.
     * * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $now = Carbon::now();

            // 1. KPI: Hitung total laporan darurat hari ini
            $totalLaporan = EmergencyReport::whereDate('timestamp', $now)->count();

            // 2. KPI: Hitung total jarak tempuh bulan ini (Akumulasi KM)
            // Menggunakan DB::raw untuk operasi aritmatika SQL (akhir - awal)
            $totalJarakBulanIni = Attendance::whereNotNull('time_out')
                ->whereMonth('time_out', $now->month)
                ->whereYear('time_out', $now->year)
                ->sum(DB::raw('speedo_akhir - speedo_awal'));

            // 3. Status Operasional: Ambil driver yang sedang bertugas (belum checkout)
            $onDutyDriversRaw = Attendance::with(['driver', 'vehicle'])
                ->whereNull('time_out')
                ->orderBy('time_in', 'desc')
                ->get();

            // 4. Statistik Aset: Hitung rasio ketersediaan kendaraan
            $totalAset = Vehicle::count();
            // pluck('vehicle_id')->unique(): Menghitung kendaraan unik yang sedang dipakai
            $totalAsetUnikDipakai = $onDutyDriversRaw->pluck('vehicle_id')->unique()->count();
            $totalAsetTersedia = $totalAset - $totalAsetUnikDipakai;

            // 5. Data Chart: Statistik 7 hari terakhir
            // Grouping berdasarkan tanggal untuk sumbu X grafik
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

            // 6. Format Chart: Pastikan setiap hari dalam 7 hari terakhir ada datanya
            // (Isi dengan 0 jika tidak ada aktivitas pada hari tersebut)
            $chartLabels = [];
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dateString = $date->format('Y-m-d');
                $chartLabels[] = $date->isoFormat('ddd'); // Contoh label: Sen, Sel, Rab
                $chartData[] = $chartDataRaw->get($dateString)->total_km ?? 0; // Null coalescing ke 0
            }

            // 7. Format data driver (Menggunakan Trait)
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
            // Return view dengan state error agar halaman tidak blank putih
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

    /**
     * API Endpoint untuk Real-time Updates (AJAX).
     * * Method ini dipanggil oleh JavaScript (misalnya setiap 30 detik) 
     * untuk memperbarui angka-angka di dashboard tanpa perlu refresh halaman.
     * * @param Request $request
     * @return JsonResponse Data dalam format JSON:
     * - kpi: Statistik utama
     * - onDutyDrivers: List driver aktif terbaru
     * - latestEmergencyReport: Notifikasi laporan darurat terbaru
     */
    public function getStatus(Request $request): JsonResponse
    {
        $now = Carbon::now();
        
        // Ambil data driver aktif terbaru
        $onDutyDriversRaw = Attendance::with(['driver', 'vehicle'])
            ->whereNull('time_out')
            ->orderBy('time_in', 'desc')
            ->get();

        // Ambil laporan darurat paling baru untuk notifikasi popup/alert
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
            // Data tabel driver di-refresh via JSON
            'onDutyDrivers' => $onDutyDriversRaw->map(fn($item) => $this->formatAttendanceData($item)),
            // Data untuk notifikasi sidebar/navbar
            'latestEmergencyReport' => $latestReport ? [
                'id' => $latestReport->id,
                'driver_name' => $latestReport->driver->full_name ?? 'N/A',
                'description' => Str::limit($latestReport->description, 50)
            ] : null,
        ]);
    }
}