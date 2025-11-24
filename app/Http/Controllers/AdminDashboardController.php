<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

// --- MODEL KITA ---
use App\Models\Attendance;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\EmergencyReport;

// --- DIPERLUKAN UNTUK KPI CARD & GRAFIK ---
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    private $perPage = 25;

    /**
     * Menampilkan dashboard utama (KPI dan Driver Aktif)
     */
    public function dashboard(Request $request)
    {
        try {
            // === DATA STATISTIK (KPI) ===
            $now = Carbon::now();
            $totalLaporan = EmergencyReport::whereDate('timestamp', $now)->count();

            $totalJarakBulanIni = Attendance::whereNotNull('time_out')
                ->whereMonth('time_out', $now->month)
                ->whereYear('time_out', $now->year)
                ->sum(DB::raw('speedo_akhir - speedo_awal'));

            $onDutyDriversRaw = Attendance::with(['driver', 'vehicle'])
                ->whereNull('time_out')
                ->orderBy('time_in', 'desc')
                ->get();

            $totalAset = Vehicle::count();
            $totalAsetUnikDipakai = $onDutyDriversRaw->pluck('vehicle_id')->unique()->count();
            $totalAsetTersedia = $totalAset - $totalAsetUnikDipakai;

            // === DATA GRAFIK 7 HARI ===
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
            // === AKHIR DATA GRAFIK ===


            // Proses mapping data driver aktif
            $onDutyDrivers = $onDutyDriversRaw->map(fn($item) => $this->formatAttendanceData($item));

            // Kirim semua data (termasuk data KPI baru) ke view
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
            Log::error("Gagal memuat dashboard SQL: " . $e->getMessage());
            $error = 'Gagal memuat data dari Database. Coba lagi nanti. Error: ' . $e->getMessage();

            return view('admin.dashboard', [
                'error' => $error,
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
     * Menampilkan halaman riwayat driver (terpisah)
     */
    public function riwayatDriver(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $selectedDriverId = $request->input('driver_id');

        try {
            $filterEnd = Carbon::parse($endDate)->endOfDay();
            $filterStart = Carbon::parse($startDate)->startOfDay();
        } catch (\Exception $e) {
            $filterEnd = Carbon::now()->endOfDay();
            $filterStart = Carbon::now()->subDays(30)->startOfDay();
        }

        try {
            $allDrivers = Driver::orderBy('full_name')->get();

            $query = Attendance::with(['driver', 'vehicle'])
                ->whereNotNull('time_out')
                ->whereBetween('time_in', [$filterStart, $filterEnd])
                ->orderBy('time_in', 'desc');

            if (!empty($selectedDriverId)) {
                $query->where('driver_id', $selectedDriverId);
            }

            $historyPaginatorRaw = $query->paginate($this->perPage);

            $historyPaginator = $historyPaginatorRaw->through(fn($item) => $this->formatAttendanceData($item));

            return view('admin.riwayat_driver', compact(
                'historyPaginator',
                'startDate',
                'endDate',
                'allDrivers',
                'selectedDriverId'
            ));

        } catch (\Exception $e) {
            Log::error("Gagal memuat riwayat driver SQL: " . $e->getMessage());
            $error = 'Gagal memuat data riwayat. Error: ' . $e->getMessage();

            return view('admin.riwayat_driver', [
                'error' => $error,
                'historyPaginator' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'allDrivers' => [],
                'selectedDriverId' => null,
            ]);
        }
    }

    /**
     * Menampilkan laporan darurat
     */
    public function laporanDarurat()
    {
        $laporanMasalah = []; // Inisialisasi
        try {
            $laporanMasalahRaw = EmergencyReport::with(['driver', 'vehicle'])
                ->orderBy('timestamp', 'desc')
                ->get();

            $laporanMasalah = $laporanMasalahRaw->map(function ($laporan) {
                // --- PERBAIKAN LINK GOOGLE MAPS ---
                // Menggunakan format ?q=lat,long
                $mapsUrl = 'https://www.google.com/maps?q=' . $laporan->gps_location;

                return [
                    'timestamp' => Carbon::parse($laporan->timestamp)->format('Y-m-d H:i:s'),
                    'driver_name' => $laporan->driver->full_name ?? 'N/A',
                    'plate_number' => $laporan->vehicle->plate_number ?? 'N/A',
                    'deskripsi' => $laporan->description,
                    'lokasi_gps' => $mapsUrl, // <-- SUDAH DIPERBAIKI
                    'link_foto' => $laporan->proof_photo_path ? Storage::url($laporan->proof_photo_path) : '#',
                ];
            });

            return view('admin.laporan_darurat', compact('laporanMasalah'));

        } catch (\Exception $e) {
            Log::error("Gagal memuat laporan darurat SQL: " . $e->getMessage());
            $error = 'Gagal memuat data laporan darurat. Error: ' . $e->getMessage();
            return view('admin.laporan_darurat', compact('error', 'laporanMasalah'));
        }
    }

    /**
     * Menampilkan riwayat unit
     */
    public function riwayatUnit(Request $request)
    {
        $filterPlat = $request->input('plate_number', '');

        try {
            $query = Attendance::with(['driver', 'vehicle'])
                ->whereNotNull('time_out')
                ->orderBy('time_out', 'desc');

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

        } catch (\Exception $e) {
            Log::error("Gagal memuat riwayat unit SQL: " . $e->getMessage());
            $error = 'Gagal memuat data riwayat unit. Error: ' . $e->getMessage();
            $checklistPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
            return view('admin.riwayat_unit', compact('error', 'checklistPaginator', 'filterPlat'));
        }
    }

    /**
     * Menampilkan daftar aset + HITUNG SERVIS & PAJAK (Termasuk LOGIKA PENCARIAN)
     */
    public function daftarAset(Request $request)
    {
        $daftarMobil = []; // Inisialisasi
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
                    $query->selectRaw('MAX(id)')
                        ->from('attendances')
                        ->groupBy('vehicle_id');
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
                    $dataAset = [
                        'status' => 'Parkir (Baru)',
                        'driver_terakhir' => '-',
                        'tgl_terakhir' => '-'
                    ];
                }

                $interval = $mobil->service_interval_km;
                $km_servis_terakhir = $mobil->last_service_km;
                $status_servis = ['badge' => 'secondary', 'text' => 'N/A'];
                $km_servis_berikutnya = 0;
                $sisa_km = 0;

                if ($interval > 0) {
                    $km_servis_berikutnya = $km_servis_terakhir + $interval;
                    $sisa_km = $km_servis_berikutnya - $km_terakhir;

                    if ($sisa_km <= 0) {
                        $status_servis = ['badge' => 'danger', 'text' => 'SERVIS SEKARANG'];
                    } elseif ($sisa_km <= 1000) {
                        $status_servis = ['badge' => 'warning', 'text' => 'Segera Servis'];
                    } else {
                        $status_servis = ['badge' => 'success', 'text' => 'Aman'];
                    }
                }

                $status_stnk = $this->hitungStatusTanggal($mobil->pajak_stnk_berlaku_sampai, $today);
                $status_kir = $this->hitungStatusTanggal($mobil->kir_berlaku_sampai, $today);

                return array_merge([
                    'id' => $mobil->id,
                    'plat_nomor' => $mobil->plate_number,
                    'jenis_mobil' => $mobil->type,
                ], $dataAset, [
                    'km_terakhir' => $km_terakhir,
                    'km_servis_berikutnya' => $km_servis_berikutnya > 0 ? $km_servis_berikutnya : '-',
                    'sisa_km' => $interval > 0 ? $sisa_km : '-',
                    'status_servis' => $status_servis,
                    'status_stnk' => $status_stnk,
                    'status_kir' => $status_kir,
                ]);

            })->sortBy('plat_nomor')->values();

            return view('admin.daftar_aset', compact('daftarMobil', 'searchKeyword'));

        } catch (\Exception $e) {
            Log::error("Gagal memuat daftar aset SQL: " . $e->getMessage());
            $error = 'Gagal memuat data daftar aset. Error: ' . $e->getMessage();
            return view('admin.daftar_aset', compact('error', 'daftarMobil', 'searchKeyword'));
        }
    }

    /**
     * Menangani "Catat Servis"
     */
    public function catatServis(Request $request, Vehicle $vehicle)
    {
        $this->authorize('is-master-admin');
        $validated = $request->validate([
            'km_servis_saat_ini' => 'required|integer|min:0'
        ], [
            'km_servis_saat_ini.required' => 'Kolom KM servis wajib diisi.',
            'km_servis_saat_ini.integer' => 'KM harus berupa angka.'
        ]);

        try {
            $vehicle->last_service_km = $validated['km_servis_saat_ini'];
            $vehicle->save();

            return redirect()
                ->route('admin.daftar_aset')
                ->with('success', 'Servis untuk ' . $vehicle->plate_number . ' berhasil dicatat di KM ' . $validated['km_servis_saat_ini']);

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.daftar_aset')
                ->with('error', 'Gagal mencatat servis: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form Edit Aset
     */
    public function editAset(Vehicle $vehicle)
    {
        $this->authorize('is-master-admin');
        return view('admin.aset.edit', compact('vehicle'));
    }

    /**
     * Menyimpan data dari form Edit Aset
     */
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
            $vehicle->type = $validated['type'];
            $vehicle->service_interval_km = $validated['service_interval_km'];
            $vehicle->last_service_km = $validated['last_service_km'];
            $vehicle->pajak_stnk_berlaku_sampai = $validated['pajak_stnk_berlaku_sampai'];
            $vehicle->kir_berlaku_sampai = $validated['kir_berlaku_sampai'];

            $vehicle->save();

            return redirect()
                ->route('admin.daftar_aset')
                ->with('success', 'Data mobil ' . $vehicle->plate_number . ' berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.aset.edit', $vehicle->id)
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan Rekap Harian
     */
    public function rekapHarian(Request $request)
    {
        $selectedDate = $request->input('tanggal', Carbon::now()->format('Y-m-d'));
        $rekapData = []; // Inisialisasi

        try {
            $filterDate = Carbon::parse($selectedDate);

            $rekapDataRaw = Attendance::with(['driver', 'vehicle'])
                ->whereNotNull('time_out')
                ->whereDate('time_out', $filterDate)
                ->orderBy('time_in', 'asc')
                ->get();

            $rekapData = $rekapDataRaw->map(fn($item) => $this->formatAttendanceData($item));

            return view('admin.rekap_harian', compact('rekapData', 'selectedDate'));

        } catch (\Exception $e) {
            Log::error("Gagal memuat rekap harian SQL: " . $e->getMessage());
            $error = 'Gagal memuat data rekap harian. Error: ' . $e->getMessage();
            return view('admin.rekap_harian', compact('error', 'rekapData', 'selectedDate'));
        }
    }

    /**
     * Menampilkan Rekap Bulanan
     */
    public function rekapBulanan(Request $request)
    {
        $selectedMonth = $request->input('bulan', Carbon::now()->format('Y-m'));
        $rekapDriver = [];
        $rekapUnit = [];

        try {
            $filterMonthStart = Carbon::parse($selectedMonth)->startOfMonth();
            $filterMonthEnd = Carbon::parse($selectedMonth)->endOfMonth();

            $rekapDataRaw = Attendance::with(['driver', 'vehicle'])
                ->whereNotNull('time_out')
                ->whereBetween('time_out', [$filterMonthStart, $filterMonthEnd])
                ->get();

            $rekapDriver = $rekapDataRaw->groupBy('driver.full_name')
                ->map(function ($attendances, $driverName) {
                    return [
                        'jumlah_tugas' => $attendances->count(),
                        'total_km' => $attendances->sum(fn($a) => ($a->speedo_akhir ?? 0) - ($a->speedo_awal ?? 0))
                    ];
                })->sortKeys();

            $rekapUnit = $rekapDataRaw->groupBy('vehicle.plate_number')
                ->map(function ($attendances, $platNomor) {
                    return [
                        'jumlah_tugas' => $attendances->count(),
                        'total_km' => $attendances->sum(fn($a) => ($a->speedo_akhir ?? 0) - ($a->speedo_awal ?? 0))
                    ];
                })->sortKeys();

            return view('admin.rekap_bulanan', compact('rekapDriver', 'rekapUnit', 'selectedMonth'));

        } catch (\Exception $e) {
            Log::error("Gagal memuat rekap bulanan SQL: " . $e->getMessage());
            $error = 'Gagal memuat data rekap bulanan. Error: ' . $e->getMessage();
            return view('admin.rekap_bulanan', compact('error', 'rekapDriver', 'rekapUnit', 'selectedMonth'));
        }
    }


    /**
     * Mengambil data status dashboard untuk auto-refresh (AJAX)
     */
    public function getDashboardStatus(Request $request): JsonResponse
    {
        try {
            // Logika ini MENG-COPY logika dari method dashboard()
            $now = Carbon::now();
            $totalLaporan = EmergencyReport::whereDate('timestamp', $now)->count();

            $totalJarakBulanIni = Attendance::whereNotNull('time_out')
                ->whereMonth('time_out', $now->month)
                ->whereYear('time_out', $now->year)
                ->sum(DB::raw('speedo_akhir - speedo_awal'));

            $onDutyDriversRaw = Attendance::with(['driver', 'vehicle'])
                ->whereNull('time_out')
                ->orderBy('time_in', 'desc')
                ->get();

            $totalAset = Vehicle::count();
            $totalAsetUnikDipakai = $onDutyDriversRaw->pluck('vehicle_id')->unique()->count();
            $totalAsetTersedia = $totalAset - $totalAsetUnikDipakai;

            // Format data driver untuk dikirim
            $onDutyDrivers = $onDutyDriversRaw->map(fn($item) => $this->formatAttendanceData($item));

            // === Ambil Laporan Terakhir ===
            $latestReport = EmergencyReport::with('driver')->orderBy('timestamp', 'desc')->first();
            $formattedReport = null;
            if ($latestReport) {
                $formattedReport = [
                    'id' => $latestReport->id,
                    'driver_name' => $latestReport->driver->full_name ?? 'N/A',
                    'description' => Str::limit($latestReport->description, 50),
                ];
            }
            // === Akhir Laporan ===

            // Kembalikan sebagai JSON
            return response()->json([
                'kpi' => [
                    'driverBertugas' => count($onDutyDrivers),
                    'asetTersedia' => $totalAsetTersedia,
                    'totalAset' => $totalAset,
                    'totalJarakBulanIni' => number_format($totalJarakBulanIni),
                    'totalLaporan' => $totalLaporan,
                ],
                'onDutyDrivers' => $onDutyDrivers,
                'latestEmergencyReport' => $formattedReport,
            ]);

        } catch (\Exception $e) {
            Log::error("Gagal mengambil data status dashboard (AJAX): " . $e->getMessage());
            // Kirim error sebagai JSON
            return response()->json(['error' => 'Gagal mengambil data server'], 500);
        }
    }


    // --- HELPER FUNCTIONS ---

    /**
     * Helper untuk menghitung status tanggal
     */
    private function hitungStatusTanggal($tanggalKadaluwarsa, $today)
    {
        if (empty($tanggalKadaluwarsa)) {
            return ['badge' => 'secondary', 'text' => 'N/A'];
        }

        try {
            $tanggal = Carbon::parse($tanggalKadaluwarsa)->startOfDay();
            $sisaHari = $today->diffInDays($tanggal, false);

            if ($sisaHari < 0) {
                return ['badge' => 'danger', 'text' => 'MATI (Lewat ' . abs($sisaHari) . ' hari)'];
            } elseif ($sisaHari <= 30) {
                return ['badge' => 'warning', 'text' => 'Aktif (Sisa ' . $sisaHari . ' hari)'];
            } else {
                return ['badge' => 'success', 'text' => 'Aktif (' . $tanggal->format('d-m-Y') . ')'];
            }
        } catch (\Exception $e) {
            return ['badge' => 'secondary', 'text' => 'Error Tanggal'];
        }
    }

    /**
     * Helper untuk memformat data absensi
     */
    private function formatAttendanceData(Attendance $item)
    {
        $jarak = ($item->speedo_akhir ?? 0) - ($item->speedo_awal ?? 0);

        $totalJamKerja = '-';
        if ($item->time_out) {
            $timeIn = Carbon::parse($item->time_in);
            $timeOut = Carbon::parse($item->time_out);

            // === PERBAIKAN LOGIKA JAM KERJA ===
            // Pastikan kita menghitung selisih absolut (selalu positif)
            $totalMenit = $timeIn->diffInMinutes($timeOut, true);
            // === AKHIR PERBAIKAN ===

            $jam = floor($totalMenit / 60);
            $menit = $totalMenit % 60;

            $totalJamKerja = "{$jam} jam {$menit} menit";
        }

        // --- PERBAIKAN LINK GOOGLE MAPS ---
        // Format ?q=lat,long
        $mapsUrl = 'https://www.google.com/maps?q=' . $item->gps_location_in;

        return [
            'timestamp_masuk' => Carbon::parse($item->time_in)->format('Y-m-d H:i:s'),
            'timestamp_keluar' => $item->time_out ? Carbon::parse($item->time_out)->format('Y-m-d H:i:s') : '-',
            'gps_masuk' => $mapsUrl, // <-- SUDAH DIPERBAIKI
            'driver_name' => $item->driver->full_name ?? 'N/A',
            'plate_number' => $item->vehicle->plate_number ?? 'N/A',
            'speedo_awal' => $item->speedo_awal ?? 0,
            'speedo_akhir' => $item->speedo_akhir ?? 0,

            'jarak_tempuh' => $jarak,
            'total_jam_kerja' => $totalJamKerja,

            'link_selfie' => $item->selfie_photo_path ? Storage::url($item->selfie_photo_path) : '#',
            'link_speedo_awal' => $item->speedo_photo_awal_path ? Storage::url($item->speedo_photo_awal_path) : '#',
            'link_speedo_akhir' => $item->speedo_photo_akhir_path ? Storage::url($item->speedo_photo_akhir_path) : '#',
        ];
    }
}