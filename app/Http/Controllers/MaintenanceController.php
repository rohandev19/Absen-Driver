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

class MaintenanceController extends Controller
{
    // ================= DASHBOARD & CALENDAR =================

    public function index(Request $request)
    {
        $searchKeyword = $request->input('search');
        $query = Vehicle::query();

        if ($searchKeyword) {
            $query->where('plate_number', 'like', '%' . $searchKeyword . '%');
        }

        $vehicles = $query->get();

        $latestAttendances = Attendance::with('driver')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('attendances')->groupBy('vehicle_id');
            })->get()->keyBy('vehicle_id');

        $maintenanceData = $vehicles->map(function ($mobil) use ($latestAttendances) {
            $latest = $latestAttendances->get($mobil->id);
            $kmTerakhir = $latest ? ($latest->speedo_akhir ?? $latest->speedo_awal) : 0;
            $interval = $mobil->service_interval_km;
            $lastService = $mobil->last_service_km;
            $nextService = $lastService + $interval;
            $sisaKm = $nextService - $kmTerakhir;

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
        })->sortBy('sisa_km');

        return view('admin.maintenance.index', compact('maintenanceData', 'searchKeyword'));
    }

    public function calendar()
    {
        return view('admin.maintenance_calendar');
    }

    public function getEvents()
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

    // ================= MANAJEMEN ASET =================

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

    public function edit(Vehicle $vehicle)
    {
        $this->authorize('is-master-admin');
        return view('admin.aset.edit', compact('vehicle'));
    }

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

    public function riwayatServis(Vehicle $vehicle)
    {
        $vehicle->load([
            'maintenanceLogs.recorder' => function ($query) {
                $query->select('id', 'name');
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

        // Skenario 1: Backdate (Arsip)
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

        // Skenario 2: Servis Baru
        $lastLog = Attendance::where('vehicle_id', $vehicle->id)
            ->whereNotNull('speedo_akhir')
            ->latest('time_out')
            ->first();

        $kmAktualMobil = $lastLog ? $lastLog->speedo_akhir : 0;

        if ($kmAktualMobil > 0 && $kmInput > ($kmAktualMobil + 1000)) {
            return back()->with('error', "Gagal: KM Input terlalu jauh di atas Odometer Mobil.");
        }

        try {
            DB::transaction(function () use ($request, $vehicle, $validated) {
                MaintenanceLog::create([
                    'vehicle_id' => $vehicle->id,
                    'service_date' => $validated['service_date'],
                    'km_at_service' => $validated['km_servis_saat_ini'],
                    'description' => $validated['description'],
                    'recorded_by_user_id' => auth()->id(),
                ]);

                $vehicle->last_service_km = $validated['km_servis_saat_ini'];
                $vehicle->save();
            });

            return back()->with('success', "Servis baru tercatat! KM direset.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat servis: ' . $e->getMessage());
        }
    }

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
     * Helper: Hitung status tanggal untuk STNK/KIR
     * (Private karena hanya dipakai di controller ini)
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
}