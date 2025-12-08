<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Vehicle;
use App\Models\Attendance;
use App\Models\MaintenanceLog;

/**
 * Class MaintenanceController
 * * Controller ini menangani manajemen aset kendaraan dan siklus pemeliharaannya.
 * Bertanggung jawab atas:
 * 1. Dashboard monitoring kesehatan kendaraan (berdasarkan KM).
 * 2. Kalender pengingat pajak STNK dan KIR.
 * 3. CRUD data aset kendaraan (update interval servis, tanggal pajak).
 * 4. Pencatatan riwayat servis (Maintenance Log).
 * 5. Penyelesaian masalah fisik (reset status kerusakan).
 * * @package App\Http\Controllers
 */
class MaintenanceController extends Controller
{
    // ================= DASHBOARD & CALENDAR =================

    /**
     * Menampilkan Dashboard Maintenance.
     * * Fitur Utama: Menghitung kesehatan kendaraan secara real-time.
     * * Logika:
     * - Mengambil data KM terakhir dari tabel Attendance.
     * - Membandingkan KM terakhir dengan batas servis (Service Interval).
     * - Menentukan status: 'Prima', 'Warning', atau 'Servis Sekarang'.
     * * @param Request $request
     * - search (string|null): Filter pencarian plat nomor.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $searchKeyword = $request->input('search');
        $query = Vehicle::query();

        if ($searchKeyword) {
            $query->where('plate_number', 'like', '%' . $searchKeyword . '%');
        }

        $vehicles = $query->get();

        // Ambil data absensi terakhir untuk setiap kendaraan guna mendapatkan posisi KM terkini
        $latestAttendances = Attendance::with('driver')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('attendances')->groupBy('vehicle_id');
            })->get()->keyBy('vehicle_id');

        // Mapping data untuk kalkulasi status kesehatan
        $maintenanceData = $vehicles->map(function ($mobil) use ($latestAttendances) {
            $latest = $latestAttendances->get($mobil->id);
            // Ambil speedo akhir (jika sudah checkout) atau speedo awal (jika baru checkin)
            $kmTerakhir = $latest ? ($latest->speedo_akhir ?? $latest->speedo_awal) : 0;
            
            // Variabel perhitungan servis
            $interval = $mobil->service_interval_km;
            $lastService = $mobil->last_service_km;
            $nextService = $lastService + $interval;
            $sisaKm = $nextService - $kmTerakhir;

            // Default Status
            $healthStatus = 'Prima';
            $healthColor = 'success';

            // Logika Penentuan Status Servis (Engine Health)
            if ($interval > 0) {
                if ($sisaKm <= 0) {
                    $healthStatus = 'SERVIS SEKARANG';
                    $healthColor = 'danger';
                } elseif ($sisaKm <= 1000) {
                    $healthStatus = 'Warning Servis';
                    $healthColor = 'warning'; // Kuning jika sisa kurang dari 1000 KM
                }
            }

            // Logika Penentuan Status Fisik (Ban, Rem, Lampu)
            // Jika driver melaporkan masalah pada absensi terakhir
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
                'update_terakhir' => $latest ? Carbon::parse($latest->updated_at)->diffForHumans() : '-'
            ];
        })->sortBy('sisa_km'); // Urutkan dari yang paling mendesak (sisa KM terkecil)

        return view('admin.maintenance.index', compact('maintenanceData', 'searchKeyword'));
    }

    /**
     * Menampilkan tampilan kalender.
     * @return \Illuminate\View\View
     */
    public function calendar()
    {
        return view('admin.maintenance_calendar');
    }

    /**
     * API Endpoint untuk data event Kalender.
     * * Digunakan oleh library JavaScript (FullCalendar) di frontend.
     * * Mengembalikan tanggal kadaluarsa STNK (Biru) dan KIR (Hijau).
     * * @return \Illuminate\Http\JsonResponse
     */
    public function getEvents()
    {
        $vehicles = Vehicle::all();
        $events = [];
        foreach ($vehicles as $vehicle) {
            // Event Pajak STNK
            if ($vehicle->pajak_stnk_berlaku_sampai) {
                $events[] = [
                    'title' => "STNK: " . $vehicle->plate_number,
                    'start' => $vehicle->pajak_stnk_berlaku_sampai,
                    'url' => route('admin.aset.edit', $vehicle->id),
                    'backgroundColor' => '#0d6efd' // Bootstrap Primary Blue
                ];
            }
            // Event KIR
            if ($vehicle->kir_berlaku_sampai) {
                $events[] = [
                    'title' => "KIR: " . $vehicle->plate_number,
                    'start' => $vehicle->kir_berlaku_sampai,
                    'url' => route('admin.aset.edit', $vehicle->id),
                    'backgroundColor' => '#198754' // Bootstrap Success Green
                ];
            }
        }
        return response()->json(array_filter($events));
    }

    // ================= MANAJEMEN ASET =================

    /**
     * Menampilkan daftar seluruh aset kendaraan beserta status operasionalnya.
     * * Status Operasional:
     * 1. Sedang Dipakai (Ada driver check-in, belum check-out).
     * 2. Parkir (Sudah check-out).
     * 3. Parkir Baru (Belum pernah dipakai).
     * * @param Request $request
     * @return \Illuminate\View\View
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

            // Query optimasi: Ambil attendance terakhir per kendaraan
            $latestAttendances = Attendance::with(['driver'])
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id)')->from('attendances')->groupBy('vehicle_id');
                })->get()->keyBy('vehicle_id');

            // Query optimasi: Ambil kendaraan yang sedang bertugas (time_out NULL)
            $onDutyAttendances = Attendance::with(['driver'])
                ->whereNull('time_out')
                ->get()->keyBy('vehicle_id');
            
            $today = Carbon::now()->startOfDay();

            $daftarMobil = $semuaMobil->map(function ($mobil) use ($latestAttendances, $onDutyAttendances, $today) {
                $onDuty = $onDutyAttendances->get($mobil->id);
                $latest = $latestAttendances->get($mobil->id);
                $dataAset = [];
                $km_terakhir = 0;

                // Logika Penentuan Status Operasional
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

                // Helper untuk status dokumen
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

    /**
     * Form edit data aset kendaraan.
     * * Gate: Hanya bisa diakses oleh Master Admin.
     * * @param Vehicle $vehicle
     * @return \Illuminate\View\View
     */
    public function edit(Vehicle $vehicle)
    {
        $this->authorize('is-master-admin');
        return view('admin.aset.edit', compact('vehicle'));
    }

    /**
     * Proses update data aset kendaraan.
     * * @param Request $request
     * @param Vehicle $vehicle
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Vehicle $vehicle)
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

    // ================= LOGIKA SERVIS & VISUAL =================

    /**
     * Menampilkan kondisi visual fisik kendaraan.
     * * Mengambil data checklist terakhir driver (Ban, Lampu, Rem).
     * * @param Vehicle $vehicle
     * @return \Illuminate\View\View
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

        // Jika driver melaporkan masalah, ubah status jadi danger
        if ($lastLog) {
            if ($lastLog->check_ban == 'Bermasalah') $status['ban'] = 'danger';
            if ($lastLog->check_lampu == 'Bermasalah') $status['lampu'] = 'danger';
            if ($lastLog->check_rem == 'Bermasalah') $status['rem'] = 'danger';
        }

        // Cek status mesin berdasarkan KM
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
     * Menampilkan riwayat servis kendaraan (Maintenance Logs).
     * * @param Vehicle $vehicle
     * @return \Illuminate\View\View
     */
    public function riwayatServis(Vehicle $vehicle)
    {
        $vehicle->load([
            'maintenanceLogs.recorder' => function ($query) {
                $query->select('id', 'name'); // Load siapa yang mencatat log
            }
        ]);

        $lastLog = Attendance::where('vehicle_id', $vehicle->id)->latest('time_out')->first();
        $kmSaatIni = $lastLog ? ($lastLog->speedo_akhir ?? 0) : 0;
        $nextService = $vehicle->last_service_km + $vehicle->service_interval_km;
        $sisaKm = $nextService - $kmSaatIni;

        $statusSummary = [
            'km_saat_ini' => $kmSaatIni,
            'sisa_km' => $sisaKm,
            'status' => ($sisaKm <= 0) ? 'Service Due' : 'Prima',
            'color' => ($sisaKm <= 0) ? 'danger' : 'success'
        ];

        return view('admin.aset.riwayat', compact('vehicle', 'statusSummary'));
    }

    /**
     * Mencatat Servis Baru (Core Logic Maintenance).
     * * Menangani dua skenario:
     * 1. Arsip Susulan: Jika admin input KM masa lalu (< KM servis terakhir), hanya simpan log tanpa reset.
     * 2. Servis Baru: Jika admin input KM baru, simpan log DAN update 'last_service_km' di kendaraan.
     * * * Juga melakukan validasi agar admin tidak salah input KM yang terlalu jauh dari Odometer asli.
     * * @param Request $request
     * @param Vehicle $vehicle
     * @return \Illuminate\Http\RedirectResponse
     */
    public function catatServis(Request $request, Vehicle $vehicle)
    {
        $this->authorize('is-master-admin');

        $validated = $request->validate([
            'km_servis_saat_ini' => 'required|integer|min:0',
            'service_date' => 'required|date',
            'description' => 'required|string|max:500',
        ]);

        $kmInput = (int) $validated['km_servis_saat_ini'];
        $kmTerakhirTercatat = (int) $vehicle->last_service_km;

        // Skenario 1: Backdate (Arsip data lama yang lupa diinput)
        // Jika input KM lebih kecil dari data servis terakhir di database
        if ($kmInput < $kmTerakhirTercatat) {
            MaintenanceLog::create([
                'vehicle_id' => $vehicle->id,
                'service_date' => $validated['service_date'],
                'km_at_service' => $validated['km_servis_saat_ini'],
                'description' => $validated['description'] . ' (Arsip Susulan)',
                'recorded_by_user_id' => auth()->id(),
            ]);
            return back()->with('success', "Arsip riwayat lama disimpan.");
        }

        // Skenario 2: Servis Baru (Reset hitungan servis)
        
        // Validasi: Cek Odometer real di lapangan (dari data absensi terakhir)
        $lastLog = Attendance::where('vehicle_id', $vehicle->id)
            ->whereNotNull('speedo_akhir')
            ->latest('time_out')
            ->first();

        $kmAktualMobil = $lastLog ? $lastLog->speedo_akhir : 0;

        // Cegah Human Error: Jika input KM servis jauh lebih besar (>1000km) dari KM mobil saat ini
        if ($kmAktualMobil > 0 && $kmInput > ($kmAktualMobil + 1000)) {
            return back()->with('error', "Gagal: KM Input terlalu jauh di atas Odometer Mobil.");
        }

        try {
            DB::transaction(function () use ($request, $vehicle, $validated) {
                // 1. Buat Log
                MaintenanceLog::create([
                    'vehicle_id' => $vehicle->id,
                    'service_date' => $validated['service_date'],
                    'km_at_service' => $validated['km_servis_saat_ini'],
                    'description' => $validated['description'],
                    'recorded_by_user_id' => auth()->id(),
                ]);

                // 2. Update data induk kendaraan (Reset Service Interval)
                $vehicle->last_service_km = $validated['km_servis_saat_ini'];
                $vehicle->save();
            });

            return back()->with('success', "Servis baru tercatat! KM direset.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat servis: ' . $e->getMessage());
        }
    }

    /**
     * Menyelesaikan Masalah Fisik (Override Admin).
     * * Digunakan jika mobil sudah diperbaiki bengkel, admin mereset status
     * 'Bermasalah' menjadi 'Aman' pada log absensi terakhir.
     * * @param Request $request
     * @param Vehicle $vehicle
     * @return \Illuminate\Http\RedirectResponse
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
            return back()->with('success', "Status kerusakan mobil berhasil direset.");
        }
        return back()->with('error', 'Tidak ada data laporan untuk diperbaiki.');
    }

    /**
     * Helper Private: Menghitung status tanggal kadaluarsa.
     * * @param string|null $tanggal Tanggal kadaluarsa (Y-m-d)
     * @param Carbon $today Tanggal hari ini
     * @return array Array berisi warna badge dan teks status
     */
    private function hitungStatusTanggal($tanggal, $today)
    {
        if (!$tanggal)
            return ['badge' => 'secondary', 'text' => 'N/A'];
        
        $target = Carbon::parse($tanggal)->startOfDay();
        $sisa = $today->diffInDays($target, false); // false = return negatif jika lewat

        if ($sisa < 0)
            return ['badge' => 'danger', 'text' => 'MATI (Lewat ' . abs($sisa) . ' hari)'];
        
        if ($sisa <= 30)
            return ['badge' => 'warning', 'text' => 'Aktif (Sisa ' . $sisa . ' hari)'];
        
        return ['badge' => 'success', 'text' => 'Aktif (' . $target->format('d-m-Y') . ')'];
    }
}