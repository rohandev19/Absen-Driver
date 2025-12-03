<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// --- MODEL ---
use App\Models\Attendance;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\EmergencyReport;
use App\Models\MaintenanceLog;

// --- UTILITIES ---
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Exports\RekapAbsensiChecklistExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminDashboardController extends Controller
{
    private $perPage = 25; // Konstanta untuk pagination

    // ============================================
    // METHOD DASHBOARD UTAMA
    // ============================================

    /**
     * Menampilkan dashboard utama admin dengan semua statistik penting
     * Fungsi: Dashboard real-time untuk monitoring operasional harian
     */
    public function dashboard(Request $request)
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

            // 3. Ambil driver yang sedang bertugas (belum check-out)
            $onDutyDriversRaw = Attendance::with(['driver', 'vehicle'])
                ->whereNull('time_out')
                ->orderBy('time_in', 'desc')
                ->get();

            // 4. Hitung statistik aset
            $totalAset = Vehicle::count();
            $totalAsetUnikDipakai = $onDutyDriversRaw->pluck('vehicle_id')->unique()->count();
            $totalAsetTersedia = $totalAset - $totalAsetUnikDipakai;

            // 5. Data chart 7 hari terakhir untuk jarak tempuh
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

            // 6. Format data chart untuk frontend
            $chartLabels = [];
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dateString = $date->format('Y-m-d');
                $chartLabels[] = $date->isoFormat('ddd'); // Nama hari (Sen, Sel, Rab, dll)
                $chartData[] = $chartDataRaw->get($dateString)->total_km ?? 0;
            }

            // 7. Format data driver yang sedang bertugas
            $onDutyDrivers = $onDutyDriversRaw->map(fn($item) => $this->formatAttendanceData($item));

            // 8. Kirim semua data ke view dashboard
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
            // Error handling jika terjadi masalah
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

    // ============================================
    // METHOD DASHBOARD PEMELIHARAAN
    // ============================================

    /**
     * Menampilkan dashboard pemeliharaan kendaraan
     * Fungsi: Monitoring kesehatan semua kendaraan dengan peringatan servis
     */
    public function maintenanceDashboard(Request $request)
    {
        $searchKeyword = $request->input('search');
        $query = Vehicle::query();

        if ($searchKeyword) {
            $query->where('plate_number', 'like', '%' . $searchKeyword . '%');
        }

        $vehicles = $query->get();

        // Ambil data absensi terbaru setiap kendaraan
        $latestAttendances = Attendance::with('driver')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('attendances')->groupBy('vehicle_id');
            })->get()->keyBy('vehicle_id');

        // Hitung status kesehatan setiap kendaraan
        $maintenanceData = $vehicles->map(function ($mobil) use ($latestAttendances) {
            $latest = $latestAttendances->get($mobil->id);
            $kmTerakhir = $latest ? ($latest->speedo_akhir ?? $latest->speedo_awal) : 0;
            $interval = $mobil->service_interval_km;
            $lastService = $mobil->last_service_km;
            $nextService = $lastService + $interval;
            $sisaKm = $nextService - $kmTerakhir;

            // Tentukan status kesehatan berdasarkan sisa KM servis
            $healthStatus = 'Prima';
            $healthColor = 'success'; // Bootstrap color class

            if ($interval > 0) {
                if ($sisaKm <= 0) {
                    $healthStatus = 'SERVIS SEKARANG';
                    $healthColor = 'danger';
                } elseif ($sisaKm <= 1000) {
                    $healthStatus = 'Warning Servis';
                    $healthColor = 'warning';
                }
            }

            // Override status jika ada kerusakan fisik
            if (
                $latest && ($latest->check_ban == 'Bermasalah' ||
                    $latest->check_rem == 'Bermasalah' ||
                    $latest->check_lampu == 'Bermasalah')
            ) {
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
        })->sortBy('sisa_km'); // Urutkan berdasarkan sisa KM (paling mendesak di atas)

        return view('admin.maintenance.index', compact('maintenanceData', 'searchKeyword'));
    }

    // ============================================
    // METHOD VISUAL CHECK KENDARAAN
    // ============================================

    /**
     * Menampilkan halaman pemeriksaan visual kendaraan
     * 
     * FUNGSI UTAMA: 
     * - Memberikan tampilan visual yang intuitif tentang kondisi kendaraan
     * - Menggabungkan data dari laporan driver dan data servis
     * - Memudahkan teknisi/admin untuk melihat kondisi kendaraan sekilas
     * - Menjadi dashboard kesehatan kendaraan yang user-friendly
     * 
     * ALASAN PEMBUATAN:
     * 1. Kebutuhan Visual: Teknisi dan admin membutuhkan tampilan visual yang cepat
     *    untuk memahami kondisi kendaraan tanpa membaca tabel data
     * 2. Integrasi Data: Menggabungkan dua sumber data:
     *    a. Laporan kerusakan fisik dari driver (ban, lampu, rem)
     *    b. Data servis berkala berdasarkan kilometer
     * 3. Prioritisasi Perbaikan: Warna merah/kuning/hijau membantu menentukan
     *    prioritas perbaikan yang mendesak
     * 4. Preventif Maintenance: Deteksi dini masalah sebelum menjadi serius
     * 5. Komunikasi Tim: Memudahkan komunikasi antara driver, teknisi, dan admin
     *    tentang kondisi kendaraan
     * 
     * LOGIKA Warna Status:
     * - Hijau (success): Kondisi baik, tidak ada masalah
     * - Kuning (warning): Perlu perhatian (mendekati servis rutin)
     * - Merah (danger): Perlu tindakan segera (rusak atau lewat servis)
     */
    public function visualCheck(Vehicle $vehicle)
    {
        // 1. AMBIL DATA ABSENSI TERAKHIR
        // Mengambil data checklist dari driver pada penggunaan terakhir
        $lastLog = Attendance::where('vehicle_id', $vehicle->id)
            ->whereNotNull('time_out')  // Hanya yang sudah selesai (sudah checklist)
            ->orderBy('time_out', 'desc')  // Ambil yang paling baru
            ->first();

        // 2. DEFAULT STATUS: SEMUA KONDISI BAIK
        // Set default semua komponen berwarna hijau (aman)
        $status = [
            'ban' => 'success',      // Bootstrap class: btn-success (hijau)
            'lampu' => 'success',    // Menunjukkan kondisi baik
            'rem' => 'success',      // Siap digunakan
            'mesin' => 'success'     // Servis masih dalam interval
        ];

        // 3. CEK KERUSAKAN FISIK DARI LAPORAN DRIVER
        // Driver melaporkan kondisi ban, lampu, rem saat check-out
        if ($lastLog) {
            if ($lastLog->check_ban == 'Bermasalah') {
                $status['ban'] = 'danger';  // btn-danger (merah) = perlu perbaikan
            }
            if ($lastLog->check_lampu == 'Bermasalah') {
                $status['lampu'] = 'danger';
            }
            if ($lastLog->check_rem == 'Bermasalah') {
                $status['rem'] = 'danger';
            }
        }

        // 4. CEK SERVIS MESIN BERDASARKAN KILOMETER
        // Perhitungan matematis berdasarkan jarak tempuh
        if ($vehicle->service_interval_km > 0) {
            $kmTerakhir = $lastLog->speedo_akhir ?? 0;
            $kmBerjalan = $kmTerakhir - $vehicle->last_service_km;

            // Jika sudah melewati interval servis yang ditentukan
            if ($kmBerjalan >= $vehicle->service_interval_km) {
                $status['mesin'] = 'danger'; // btn-danger = perlu servis segera
            }
        }

        // 5. KIRIM DATA KE VIEW VISUAL CHECK
        // View akan menampilkan indikator warna untuk setiap komponen
        return view('admin.aset.visual', compact('vehicle', 'status', 'lastLog'));
    }

    // ============================================
    // METHOD RIWAYAT SERVIS
    // ============================================

    /**
     * Menampilkan riwayat servis kendaraan tertentu
     */
    public function riwayatServis(Vehicle $vehicle)
    {
        // Eager loading riwayat servis dengan data pencatat
        $vehicle->load([
            'maintenanceLogs.recorder' => function ($query) {
                $query->select('id', 'name');
            }
        ]);

        // Ambil data kilometer terakhir
        $lastLog = Attendance::where('vehicle_id', $vehicle->id)->latest('time_out')->first();
        $kmSaatIni = $lastLog ? ($lastLog->speedo_akhir ?? 0) : 0;

        // Hitung sisa kilometer servis
        $nextService = $vehicle->last_service_km + $vehicle->service_interval_km;
        $sisaKm = $nextService - $kmSaatIni;

        // Ringkasan status untuk ditampilkan
        $statusSummary = [
            'km_saat_ini' => $kmSaatIni,
            'sisa_km' => $sisaKm,
            'status' => ($sisaKm <= 0) ? 'Service Due' : 'Prima',
            'color' => ($sisaKm <= 0) ? 'danger' : 'success'
        ];

        return view('admin.aset.riwayat', compact('vehicle', 'statusSummary'));
    }

    // ============================================
    // METHOD CATAT SERVIS (LOGIKA PENTING)
    // ============================================

    /**
     * Mencatat servis baru atau arsip servis lama
     * Membedakan antara servis baru (reset interval) dan arsip masa lalu
     */
    public function catatServis(Request $request, Vehicle $vehicle)
    {
        $this->authorize('is-master-admin'); // Hanya master admin

        $validated = $request->validate([
            'km_servis_saat_ini' => 'required|integer|min:0',
            'service_date' => 'required|date',
            'description' => 'required|string|max:500',
        ]);

        $kmInput = (int) $validated['km_servis_saat_ini'];
        $kmTerakhirTercatat = (int) $vehicle->last_service_km;

        // --- SKENARIO 1: INPUT RIWAYAT MASA LALU (BACKDATE) ---
        // Jika input KM lebih kecil dari data terakhir, ini adalah arsip lama
        if ($kmInput < $kmTerakhirTercatat) {
            try {
                MaintenanceLog::create([
                    'vehicle_id' => $vehicle->id,
                    'service_date' => $validated['service_date'],
                    'km_at_service' => $validated['km_servis_saat_ini'],
                    'description' => $validated['description'] . ' (Arsip Susulan)',
                    'recorded_by_user_id' => auth()->id(),
                ]);

                return back()->with(
                    'success',
                    "Arsip riwayat lama (KM {$kmInput}) berhasil disimpan. " .
                    "Status mobil tidak berubah karena data saat ini sudah lebih baru ({$kmTerakhirTercatat} Km)."
                );
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal menyimpan arsip: ' . $e->getMessage());
            }
        }

        // --- SKENARIO 2: INPUT SERVIS BARU (MAJU) ---

        // Validasi kewajaran: cegah input error
        $lastLog = Attendance::where('vehicle_id', $vehicle->id)
            ->whereNotNull('speedo_akhir')
            ->latest('time_out')
            ->first();

        $kmAktualMobil = $lastLog ? $lastLog->speedo_akhir : 0;

        if ($kmAktualMobil > 0 && $kmInput > ($kmAktualMobil + 1000)) {
            return back()->with(
                'error',
                "Gagal: KM Input ({$kmInput}) terlalu jauh di atas Odometer Mobil saat ini ({$kmAktualMobil}). " .
                "Mohon cek apakah salah ketik?"
            );
        }

        try {
            DB::transaction(function () use ($request, $vehicle, $validated) {
                // 1. Simpan ke Riwayat Log
                MaintenanceLog::create([
                    'vehicle_id' => $vehicle->id,
                    'service_date' => $validated['service_date'],
                    'km_at_service' => $validated['km_servis_saat_ini'],
                    'description' => $validated['description'],
                    'recorded_by_user_id' => auth()->id(),
                ]);

                // 2. Update Status Kendaraan (Reset Interval)
                $vehicle->last_service_km = $validated['km_servis_saat_ini'];
                $vehicle->save();
            });

            return back()->with(
                'success',
                "Servis baru tercatat! Status KM {$vehicle->plate_number} " .
                "telah direset ke {$validated['km_servis_saat_ini']}."
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat servis: ' . $e->getMessage());
        }
    }

    // ============================================
    // METHOD RESET MASALAH KENDARAAN
    // ============================================

    /**
     * Reset status kerusakan kendaraan (tandai sudah diperbaiki)
     */
    public function resolveIssue(Request $request, Vehicle $vehicle)
    {
        $this->authorize('is-master-admin');
        $lastLog = Attendance::where('vehicle_id', $vehicle->id)
            ->whereNotNull('time_out')
            ->orderBy('time_out', 'desc')
            ->first();
        if ($lastLog) {
            $lastLog->update([
                'check_ban' => 'Aman',
                'check_lampu' => 'Aman',
                'check_rem' => 'Aman',
                'catatan' => $lastLog->catatan . ' [DIPERBAIKI ADMIN PADA ' . Carbon::now()->format('d-m-Y H:i') . ']'
            ]);
            return back()->with(
                'success',
                "Status kerusakan mobil {$vehicle->plate_number} " .
                "berhasil direset (Sudah Diperbaiki)."
            );
        }
        return back()->with('error', 'Tidak ada data laporan untuk diperbaiki.');
    }

    // ============================================
    // METHOD EDIT ASET
    // ============================================

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

    // ============================================
    // METHOD DAFTAR ASET
    // ============================================

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
                $status_stnk = $this->hitungStatusTanggal($mobil->pajak_stnk_berlaku_sampai, $today);
                $status_kir = $this->hitungStatusTanggal($mobil->kir_berlaku_sampai, $today);
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

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Helper: Hitung status tanggal untuk STNK/KIR
     */
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
     * Helper: Format data absensi untuk tampilan konsisten
     */
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

    // ============================================
    // METHOD REKAP HARIAN
    // ============================================

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

    // ============================================
    // METHOD REKAP BULANAN
    // ============================================

    public function rekapBulanan(Request $request)
    {
        $selectedMonth = $request->input('bulan', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($selectedMonth)->startOfMonth();
        $end = Carbon::parse($selectedMonth)->endOfMonth();
        $data = Attendance::with(['driver', 'vehicle'])
            ->whereNotNull('time_out')
            ->whereBetween('time_out', [$start, $end])
            ->get();
        $rekapDriver = $data->groupBy('driver.full_name')
            ->map(fn($group) => [
                'jumlah_tugas' => $group->count(),
                'total_km' => $group->sum(fn($a) => ($a->speedo_akhir ?? 0) - ($a->speedo_awal ?? 0))
            ])->sortKeys();
        $rekapUnit = $data->groupBy('vehicle.plate_number')
            ->map(fn($group) => [
                'jumlah_tugas' => $group->count(),
                'total_km' => $group->sum(fn($a) => ($a->speedo_akhir ?? 0) - ($a->speedo_awal ?? 0))
            ])->sortKeys();
        return view('admin.rekap_bulanan', compact('rekapDriver', 'rekapUnit', 'selectedMonth'));
    }

    // ============================================
    // METHOD KALENDER PEMELIHARAAN
    // ============================================

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
                $events[] = [
                    'title' => "STNK: " . $vehicle->plate_number,
                    'start' => $vehicle->pajak_stnk_berlaku_sampai,
                    'url' => route('admin.aset.edit', $vehicle->id),
                    'backgroundColor' => '#0d6efd'
                ];
            }
            if ($vehicle->kir_berlaku_sampai) {
                $events[] = [
                    'title' => "KIR: " . $vehicle->plate_number,
                    'start' => $vehicle->kir_berlaku_sampai,
                    'url' => route('admin.aset.edit', $vehicle->id),
                    'backgroundColor' => '#198754'
                ];
            }
        }
        return response()->json(array_filter($events));
    }

    // ============================================
    // METHOD EKSPOR EXCEL
    // ============================================

    public function exportBulananChecklist(Request $request)
    {
        $month = $request->input('bulan', Carbon::now()->format('Y-m'));
        return Excel::download(
            new RekapAbsensiChecklistExport(Carbon::parse($month)->month, Carbon::parse($month)->year),
            'rekap-checklist-' . $month . '.xlsx'
        );
    }

    // ============================================
    // METHOD API DASHBOARD (REAL-TIME)
    // ============================================

    public function getDashboardStatus(Request $request): JsonResponse
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

    // ============================================
    // METHOD RIWAYAT DRIVER
    // ============================================

    public function riwayatDriver(Request $request)
    {
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

    // ============================================
    // METHOD RIWAYAT UNIT
    // ============================================

    public function riwayatUnit(Request $request)
    {
        $filterPlat = $request->input('plate_number', '');
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
    }

    // ============================================
    // METHOD LAPORAN DARURAT
    // ============================================

    public function laporanDarurat()
    {
        $laporanMasalahRaw = EmergencyReport::with(['driver', 'vehicle'])
            ->orderBy('timestamp', 'desc')
            ->get();
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
}