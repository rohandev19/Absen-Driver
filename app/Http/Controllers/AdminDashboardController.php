<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

// --- MODEL ---
use App\Models\Attendance;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\EmergencyReport;

// --- UTILITIES ---
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Exports\RekapAbsensiChecklistExport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class AdminDashboardController
 * * Controller Utama untuk Panel Admin Web.
 * * Mengelola tampilan Dashboard, Statistik (KPI), Grafik, Laporan,
 * serta fitur Manajemen Aset Kendaraan (Servis & Pajak).
 * * @package App\Http\Controllers
 */
class AdminDashboardController extends Controller
{
    private $perPage = 25; // Jumlah item per halaman (Pagination)

    /**
     * Menampilkan Halaman Dashboard Utama (Home).
     * * Statistik KPI dan Grafik Aktivitas Driver.
     */
    public function dashboard(Request $request)
    {
        try {
            // === DATA STATISTIK (KPI) ===
            $now = Carbon::now();

            // Hitung laporan darurat hari ini
            $totalLaporan = EmergencyReport::whereDate('timestamp', $now)->count();

            // Hitung total jarak (KM) bulan ini
            $totalJarakBulanIni = Attendance::whereNotNull('time_out')
                ->whereMonth('time_out', $now->month)
                ->whereYear('time_out', $now->year)
                ->sum(DB::raw('speedo_akhir - speedo_awal'));

            // Ambil driver yang sedang bertugas
            $onDutyDriversRaw = Attendance::with(['driver', 'vehicle'])
                ->whereNull('time_out')
                ->orderBy('time_in', 'desc')
                ->get();

            // Hitung ketersediaan aset
            $totalAset = Vehicle::count();
            $totalAsetUnikDipakai = $onDutyDriversRaw->pluck('vehicle_id')->unique()->count();
            $totalAsetTersedia = $totalAset - $totalAsetUnikDipakai;

            // === DATA GRAFIK 7 HARI TERAKHIR ===
            $chartDataRaw = Attendance::whereNotNull('time_out')
                ->where('time_out', '>=', Carbon::now()->subDays(6)->startOfDay())
                ->groupBy(DB::raw('DATE(time_out)'))
                ->orderBy('date', 'asc')
                ->select(DB::raw('DATE(time_out) as date'), DB::raw('SUM(speedo_akhir - speedo_awal) as total_km'))
                ->get()
                ->keyBy('date');

            $chartLabels = [];
            $chartData = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dateString = $date->format('Y-m-d');
                $chartLabels[] = $date->isoFormat('ddd');
                $chartData[] = $chartDataRaw->get($dateString)->total_km ?? 0;
            }

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

    /**
     * [FITUR BARU] Dashboard Khusus Maintenance (Bengkel).
     * Fokus: Menampilkan sisa jarak servis dan status kesehatan per mobil.
     */
    public function maintenanceDashboard(Request $request)
    {
        $searchKeyword = $request->input('search');

        $query = Vehicle::query();
        if ($searchKeyword) {
            $query->where('plate_number', 'like', '%' . $searchKeyword . '%');
        }
        $vehicles = $query->get();

        // Ambil data absensi terakhir (untuk cek KM terakhir)
        $latestAttendances = Attendance::with('driver')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('attendances')->groupBy('vehicle_id');
            })->get()->keyBy('vehicle_id');

        // Mapping Data Fokus Maintenance
        $maintenanceData = $vehicles->map(function ($mobil) use ($latestAttendances) {
            $latest = $latestAttendances->get($mobil->id);
            // Jika sedang jalan, ambil speedo_awal. Jika parkir, ambil speedo_akhir.
            $kmTerakhir = $latest ? ($latest->speedo_akhir ?? $latest->speedo_awal) : 0;

            // Hitung Sisa KM Servis
            $interval = $mobil->service_interval_km;
            $lastService = $mobil->last_service_km;
            $nextService = $lastService + $interval;

            // Sisa KM = Target Servis - KM Saat Ini
            $sisaKm = $nextService - $kmTerakhir;

            // Status Kesehatan (Prioritas)
            $healthStatus = 'Prima';
            $healthColor = 'success';

            if ($interval > 0) {
                if ($sisaKm <= 0) {
                    $healthStatus = 'SERVIS SEKARANG';
                    $healthColor = 'danger';
                } elseif ($sisaKm <= 1000) {
                    $healthStatus = 'Warning Servis';
                    $healthColor = 'warning';
                }
            }

            // Cek Laporan Kerusakan Terakhir (Ban/Rem/Lampu) dari Absensi
            if ($latest && ($latest->check_ban == 'Bermasalah' || $latest->check_rem == 'Bermasalah' || $latest->check_lampu == 'Bermasalah')) {
                $healthStatus = 'Perlu Perbaikan Fisik';
                $healthColor = 'danger';
            }

            return [
                'id' => $mobil->id,
                'plat' => $mobil->plate_number,
                'jenis' => $mobil->type,
                'km_saat_ini' => $kmTerakhir,
                'km_servis_terakhir' => $lastService,
                'km_servis_berikutnya' => $interval > 0 ? $nextService : '-',
                'sisa_km' => $interval > 0 ? $sisaKm : '-',
                'status_kesehatan' => $healthStatus,
                'warna_status' => $healthColor,
                'update_terakhir' => $latest ? \Carbon\Carbon::parse($latest->updated_at)->diffForHumans() : '-'
            ];
        })->sortBy('sisa_km'); // Urutkan dari yang paling butuh servis (Sisa KM terkecil)

        return view('admin.maintenance.index', compact('maintenanceData', 'searchKeyword'));
    }

    /**
     * Menampilkan Visual Health Check (Digital Twin - 3D Schematic).
     */
    public function visualCheck(Vehicle $vehicle)
    {
        $lastLog = Attendance::where('vehicle_id', $vehicle->id)
            ->whereNotNull('time_out')
            ->orderBy('time_out', 'desc')
            ->first();

        $status = [
            'ban' => 'success',
            'lampu' => 'success',
            'rem' => 'success',
            'mesin' => 'success'
        ];

        if ($lastLog) {
            if ($lastLog->check_ban == 'Bermasalah')
                $status['ban'] = 'danger';
            if ($lastLog->check_lampu == 'Bermasalah')
                $status['lampu'] = 'danger';
            if ($lastLog->check_rem == 'Bermasalah')
                $status['rem'] = 'danger';
        }

        if ($vehicle->service_interval_km > 0) {
            $kmTerakhir = $lastLog->speedo_akhir ?? 0;
            $kmBerjalan = $kmTerakhir - $vehicle->last_service_km;
            if ($kmBerjalan >= $vehicle->service_interval_km) {
                $status['mesin'] = 'danger';
            }
        }

        return view('admin.aset.visual', compact('vehicle', 'status', 'lastLog'));
    }

    /**
     * Menampilkan Daftar Aset (Administrasi).
     * * Digunakan untuk data legalitas (STNK/KIR) dan tipe mobil.
     */
    public function daftarAset(Request $request)
    {
        $searchKeyword = $request->input('search');

        try {
            $query = Vehicle::query();
            if ($searchKeyword) {
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('plate_number', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('type', 'like', '%' . $searchKeyword . '%');
                });
            }
            $semuaMobil = $query->get();

            $latestAttendances = Attendance::with(['driver'])
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id)')->from('attendances')->groupBy('vehicle_id');
                })->get()->keyBy('vehicle_id');

            $onDutyAttendances = Attendance::with(['driver'])
                ->whereNull('time_out')
                ->get()->keyBy('vehicle_id');

            $today = Carbon::now()->startOfDay();

            $daftarMobil = $semuaMobil->map(function ($mobil) use ($latestAttendances, $onDutyAttendances, $today) {
                $onDuty = $onDutyAttendances->get($mobil->id);
                $latest = $latestAttendances->get($mobil->id);

                $dataAset = [];
                $km_terakhir = 0;

                if ($onDuty) {
                    $km_terakhir = $onDuty->speedo_awal;
                    $dataAset = [
                        'status' => 'Sedang Dipakai',
                        'driver_terakhir' => $onDuty->driver->full_name ?? 'N/A',
                        'tgl_terakhir' => 'Check-in: ' . Carbon::parse($onDuty->time_in)->format('Y-m-d H:i')
                    ];
                } elseif ($latest) {
                    $km_terakhir = $latest->speedo_akhir ?? 0;
                    $dataAset = [
                        'status' => 'Parkir',
                        'driver_terakhir' => $latest->driver->full_name ?? 'N/A',
                        'tgl_terakhir' => 'Check-out: ' . ($latest->time_out ? Carbon::parse($latest->time_out)->format('Y-m-d H:i') : '-')
                    ];
                } else {
                    $dataAset = ['status' => 'Parkir (Baru)', 'driver_terakhir' => '-', 'tgl_terakhir' => '-'];
                }

                // Hitung Status Dokumen
                $status_stnk = $this->hitungStatusTanggal($mobil->pajak_stnk_berlaku_sampai, $today);
                $status_kir = $this->hitungStatusTanggal($mobil->kir_berlaku_sampai, $today);

                // Note: Data servis di sini disederhanakan, detailnya pindah ke Maintenance Dashboard

                return array_merge([
                    'id' => $mobil->id,
                    'plat_nomor' => $mobil->plate_number,
                    'jenis_mobil' => $mobil->type,
                ], $dataAset, [
                    'km_terakhir' => $km_terakhir,
                    'status_stnk' => $status_stnk,
                    'status_kir' => $status_kir,
                ]);

            })->sortBy('plat_nomor')->values();

            return view('admin.daftar_aset', compact('daftarMobil', 'searchKeyword'));

        } catch (\Exception $e) {
            Log::error("Daftar Aset Error: " . $e->getMessage());
            return view('admin.daftar_aset', ['error' => 'Gagal memuat aset.', 'daftarMobil' => [], 'searchKeyword' => $searchKeyword]);
        }
    }

    // --- Helper Functions untuk Maintenance ---

    public function catatServis(Request $request, Vehicle $vehicle)
    {
        $this->authorize('is-master-admin');
        $validated = $request->validate([
            'km_servis_saat_ini' => 'required|integer|min:0'
        ]);

        try {
            $vehicle->last_service_km = $validated['km_servis_saat_ini'];
            $vehicle->save();

            // Redirect kembali ke halaman sebelumnya (bisa maintenance atau daftar aset)
            return back()->with('success', "Servis {$vehicle->plate_number} tercatat di KM {$validated['km_servis_saat_ini']}");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat servis: ' . $e->getMessage());
        }
    }

    public function editAset(Vehicle $vehicle)
    {
        $this->authorize('is-master-admin');
        return view('admin.aset.edit', compact('vehicle'));
    }

    public function updateAset(Request $request, Vehicle $vehicle)
    {
        $this->authorize('is-master-admin');
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'service_interval_km' => 'required|integer|min:0',
            'last_service_km' => 'required|integer|min:0',
            'pajak_stnk_berlaku_sampai' => 'nullable|date',
            'kir_berlaku_sampai' => 'nullable|date',
        ]);

        try {
            $vehicle->update($validated);
            return redirect()->route('admin.daftar_aset')->with('success', 'Data aset diperbarui.');
        } catch (\Exception $e) {
            return redirect()->route('admin.aset.edit', $vehicle->id)->with('error', 'Update gagal.');
        }
    }

    // --- Monitoring & Laporan Lainnya ---

    public function riwayatDriver(Request $request)
    {
        // (Logic sama seperti sebelumnya, diringkas di sini agar tidak terlalu panjang)
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $selectedDriverId = $request->input('driver_id');

        $filterEnd = Carbon::parse($endDate)->endOfDay();
        $filterStart = Carbon::parse($startDate)->startOfDay();
        $allDrivers = Driver::orderBy('full_name')->get();

        $query = Attendance::with(['driver', 'vehicle'])
            ->whereNotNull('time_out')
            ->whereBetween('time_in', [$filterStart, $filterEnd])
            ->orderBy('time_in', 'desc');

        if (!empty($selectedDriverId))
            $query->where('driver_id', $selectedDriverId);

        $historyPaginatorRaw = $query->paginate($this->perPage);
        $historyPaginator = $historyPaginatorRaw->through(fn($item) => $this->formatAttendanceData($item));

        return view('admin.riwayat_driver', compact('historyPaginator', 'startDate', 'endDate', 'allDrivers', 'selectedDriverId'));
    }

    public function riwayatUnit(Request $request)
    {
        $filterPlat = $request->input('plate_number', '');
        $query = Attendance::with(['driver', 'vehicle'])->whereNotNull('time_out')->orderBy('time_out', 'desc');
        if (!empty($filterPlat)) {
            $query->whereHas('vehicle', function ($q) use ($filterPlat) {
                $q->where('plate_number', 'like', '%' . $filterPlat . '%');
            });
        }
        $checklistPaginatorRaw = $query->paginate($this->perPage);

        $checklistPaginator = $checklistPaginatorRaw->through(function ($item) {
            return [
                'timestamp_keluar' => Carbon::parse($item->time_out)->format('Y-m-d H:i:s'),
                'driver_name' => $item->driver->full_name ?? 'N/A',
                'plate_number' => $item->vehicle->plate_number ?? 'N/A',
                'speedo_akhir' => $item->speedo_akhir ?? 0,
                'link_speedo_akhir' => $item->speedo_photo_akhir_path ? Storage::url($item->speedo_photo_akhir_path) : '#',
                'cek_ban' => $item->check_ban ?? '-',
                'cek_lampu' => $item->check_lampu ?? '-',
                'cek_rem' => $item->check_rem ?? '-',
                'catatan' => $item->catatan ?? '-',
            ];
        });

        return view('admin.riwayat_unit', compact('checklistPaginator', 'filterPlat'));
    }

    public function laporanDarurat()
    {
        $laporanMasalahRaw = EmergencyReport::with(['driver', 'vehicle'])->orderBy('timestamp', 'desc')->get();
        $laporanMasalah = $laporanMasalahRaw->map(function ($laporan) {
            return [
                'timestamp' => Carbon::parse($laporan->timestamp)->format('Y-m-d H:i:s'),
                'driver_name' => $laporan->driver->full_name ?? 'N/A',
                'plate_number' => $laporan->vehicle->plate_number ?? 'N/A',
                'deskripsi' => $laporan->description,
                'lokasi_gps' => 'https://www.google.com/maps?q=' . $laporan->gps_location,
                'link_foto' => $laporan->proof_photo_path ? Storage::url($laporan->proof_photo_path) : '#',
            ];
        });
        return view('admin.laporan_darurat', compact('laporanMasalah'));
    }

    // --- Rekap & Kalender ---

    public function rekapHarian(Request $request)
    {
        $selectedDate = $request->input('tanggal', Carbon::now()->format('Y-m-d'));
        $rekapDataRaw = Attendance::with(['driver', 'vehicle'])
            ->whereNotNull('time_out')
            ->whereDate('time_out', Carbon::parse($selectedDate))
            ->orderBy('time_in', 'asc')
            ->get();
        $rekapData = $rekapDataRaw->map(fn($item) => $this->formatAttendanceData($item));
        return view('admin.rekap_harian', compact('rekapData', 'selectedDate'));
    }

    public function rekapBulanan(Request $request)
    {
        $selectedMonth = $request->input('bulan', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($selectedMonth)->startOfMonth();
        $end = Carbon::parse($selectedMonth)->endOfMonth();
        $data = Attendance::with(['driver', 'vehicle'])
            ->whereNotNull('time_out')
            ->whereBetween('time_out', [$start, $end])
            ->get();

        $rekapDriver = $data->groupBy('driver.full_name')->map(fn($group) => [
            'jumlah_tugas' => $group->count(),
            'total_km' => $group->sum(fn($a) => ($a->speedo_akhir ?? 0) - ($a->speedo_awal ?? 0))
        ])->sortKeys();

        $rekapUnit = $data->groupBy('vehicle.plate_number')->map(fn($group) => [
            'jumlah_tugas' => $group->count(),
            'total_km' => $group->sum(fn($a) => ($a->speedo_akhir ?? 0) - ($a->speedo_awal ?? 0))
        ])->sortKeys();

        return view('admin.rekap_bulanan', compact('rekapDriver', 'rekapUnit', 'selectedMonth'));
    }

    public function maintenanceCalendar()
    {
        return view('admin.maintenance_calendar');
    }

    public function getMaintenanceEvents()
    {
        $vehicles = Vehicle::all();
        $events = [];
        foreach ($vehicles as $vehicle) {
            if ($vehicle->pajak_stnk_berlaku_sampai) {
                $events[] = $this->formatCalendarEvent("STNK: " . $vehicle->plate_number, $vehicle->pajak_stnk_berlaku_sampai, $vehicle->id);
            }
            if ($vehicle->kir_berlaku_sampai) {
                $events[] = $this->formatCalendarEvent("KIR: " . $vehicle->plate_number, $vehicle->kir_berlaku_sampai, $vehicle->id);
            }
        }
        return response()->json(array_filter($events));
    }

    public function exportBulananChecklist(Request $request)
    {
        $month = $request->input('bulan', Carbon::now()->format('Y-m'));
        return Excel::download(
            new RekapAbsensiChecklistExport(Carbon::parse($month)->month, Carbon::parse($month)->year),
            'rekap-checklist-' . $month . '.xlsx'
        );
    }

    public function getDashboardStatus(Request $request): JsonResponse
    {
        // Live Update (AJAX)
        $now = Carbon::now();
        $onDutyDriversRaw = Attendance::with(['driver', 'vehicle'])->whereNull('time_out')->orderBy('time_in', 'desc')->get();
        $latestReport = EmergencyReport::with('driver')->orderBy('timestamp', 'desc')->first();

        return response()->json([
            'kpi' => [
                'driverBertugas' => $onDutyDriversRaw->count(),
                'asetTersedia' => Vehicle::count() - $onDutyDriversRaw->pluck('vehicle_id')->unique()->count(),
                'totalAset' => Vehicle::count(),
                'totalJarakBulanIni' => number_format(Attendance::whereNotNull('time_out')->whereMonth('time_out', $now->month)->sum(DB::raw('speedo_akhir - speedo_awal'))),
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

    // --- PRIVATE HELPERS ---

    private function formatCalendarEvent($title, $dateStr, $vehicleId)
    {
        $date = Carbon::parse($dateStr);
        $daysLeft = now()->diffInDays($date, false);
        $color = '#0d6efd';
        if ($daysLeft < 0)
            $color = '#dc3545';
        elseif ($daysLeft <= 30)
            $color = '#ffc107';

        return [
            'title' => $title,
            'start' => $date->format('Y-m-d'),
            'backgroundColor' => $color,
            'borderColor' => $color,
            'url' => route('admin.aset.edit', $vehicleId)
        ];
    }

    private function hitungStatusTanggal($tanggal, $today)
    {
        if (!$tanggal)
            return ['badge' => 'secondary', 'text' => 'N/A'];
        $target = Carbon::parse($tanggal)->startOfDay();
        $sisa = $today->diffInDays($target, false);
        if ($sisa < 0)
            return ['badge' => 'danger', 'text' => 'MATI (Lewat ' . abs($sisa) . ' hari)'];
        if ($sisa <= 30)
            return ['badge' => 'warning', 'text' => 'Aktif (Sisa ' . $sisa . ' hari)'];
        return ['badge' => 'success', 'text' => 'Aktif (' . $target->format('d-m-Y') . ')'];
    }
    /**
     * Memperbaiki status fisik kendaraan secara manual oleh Admin.
     * Digunakan setelah mekanik selesai memperbaiki kerusakan (Ban/Rem/Lampu).
     * Mengubah status laporan terakhir dari 'Bermasalah' menjadi 'Aman'.
     */
    public function resolveIssue(Request $request, Vehicle $vehicle)
    {
        $this->authorize('is-master-admin');

        // 1. Cari laporan terakhir driver untuk mobil ini (yang menyebabkan status merah)
        $lastLog = Attendance::where('vehicle_id', $vehicle->id)
            ->whereNotNull('time_out')
            ->orderBy('time_out', 'desc')
            ->first();

        if ($lastLog) {
            // 2. Update status checklist menjadi 'Aman'
            // Ini akan membuat Dashboard & Visual 3D kembali Hijau
            $lastLog->update([
                'check_ban' => 'Aman',
                'check_lampu' => 'Aman',
                'check_rem' => 'Aman',
                // Tambahkan jejak audit di catatan
                'catatan' => $lastLog->catatan . ' [DIPERBAIKI ADMIN PADA ' . Carbon::now()->format('d-m-Y H:i') . ']'
            ]);

            return back()->with('success', "Status kerusakan mobil {$vehicle->plate_number} berhasil direset (Sudah Diperbaiki).");
        }

        return back()->with('error', 'Tidak ada data laporan untuk diperbaiki.');
    }
    private function formatAttendanceData(Attendance $item)
    {
        $timeIn = Carbon::parse($item->time_in);
        $totalJamKerja = '-';
        if ($item->time_out) {
            $totalMenit = $timeIn->diffInMinutes(Carbon::parse($item->time_out), true);
            $totalJamKerja = floor($totalMenit / 60) . " jam " . ($totalMenit % 60) . " menit";
        }
        return [
            'timestamp_masuk' => $timeIn->format('Y-m-d H:i:s'),
            'timestamp_keluar' => $item->time_out ? Carbon::parse($item->time_out)->format('Y-m-d H:i:s') : '-',
            'gps_masuk' => 'https://www.google.com/maps?q=' . $item->gps_location_in,
            'driver_name' => $item->driver->full_name ?? 'N/A',
            'plate_number' => $item->vehicle->plate_number ?? 'N/A',
            'speedo_awal' => $item->speedo_awal ?? 0,
            'speedo_akhir' => $item->speedo_akhir ?? 0,
            'jarak_tempuh' => ($item->speedo_akhir ?? 0) - ($item->speedo_awal ?? 0),
            'total_jam_kerja' => $totalJamKerja,
            'link_selfie' => $item->selfie_photo_path ? Storage::url($item->selfie_photo_path) : '#',
            'link_speedo_awal' => $item->speedo_photo_awal_path ? Storage::url($item->speedo_photo_awal_path) : '#',
            'link_speedo_akhir' => $item->speedo_photo_akhir_path ? Storage::url($item->speedo_photo_akhir_path) : '#',
        ];
    }
}
