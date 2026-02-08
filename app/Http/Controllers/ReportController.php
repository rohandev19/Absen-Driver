<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use App\Models\Attendance;
use App\Models\Driver;
use App\Models\Project;
use App\Models\EmergencyReport;
use App\Exports\RekapAbsensiChecklistExport;
use App\Exports\RiwayatDriverExport;
use App\Traits\FormatAttendance;

class ReportController extends Controller
{
    use FormatAttendance;

    private $perPage = 25;

    public function __construct()
    {
        // Middleware: Hanya Master Admin yang boleh akses updateKm
        $this->middleware('can:is-master-admin')->only('updateKm');
    }

    /**
     * FITUR BARU: Update KM (FIXED & ACTIVE)
     */
    public function updateKm(Request $request, $id)
    {
        $request->validate([
            'speedo_awal' => 'required|numeric|min:0',
            'speedo_akhir' => 'required|numeric|min:0',
        ]);

        $attendance = Attendance::findOrFail($id);

        // 1. Update Data Absensi (UTAMA)
        $attendance->update([
            'speedo_awal' => $request->speedo_awal,
            'speedo_akhir' => $request->speedo_akhir,
            'catatan' => $attendance->catatan . ' [KM DIKOREKSI MASTER ADMIN]',
        ]);

        // 2. Sinkronisasi Master Kendaraan (SUDAH DIAKTIFKAN)
        $vehicle = $attendance->vehicle;

        if ($vehicle) {
            $lastLog = Attendance::where('vehicle_id', $vehicle->id)
                ->orderBy('time_out', 'desc')
                ->first();

            // Update KM Master Kendaraan jika ini adalah log terakhir
            if ($lastLog && $lastLog->id == $attendance->id) {
                $vehicle->update(['current_km' => $request->speedo_akhir]);
            }
        }

        return back()->with('success', 'Data KM berhasil dikoreksi.');
    }

    // --- FUNGSI LAINNYA ---

    public function riwayatDriver(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $selectedDriverId = $request->input('driver_id');
        $selectedProjectId = $request->input('project_id');

        $filterEnd = Carbon::parse($endDate)->endOfDay();
        $filterStart = Carbon::parse($startDate)->startOfDay();

        $allDrivers = Driver::orderBy('full_name')->get();
        $projects = Project::orderBy('name')->get();

        $query = Attendance::with(['driver.project', 'vehicle'])
            ->whereNotNull('time_out')
            ->whereBetween('time_in', [$filterStart, $filterEnd])
            ->orderBy('time_in', 'desc');

        if (!empty($selectedDriverId)) {
            $query->where('driver_id', $selectedDriverId);
        }

        if (!empty($selectedProjectId)) {
            $query->whereHas('driver', function ($q) use ($selectedProjectId) {
                $q->where('project_id', $selectedProjectId);
            });
        }

        $historyPaginator = $query->paginate($this->perPage)
            ->through(function ($item) {
                $formatted = $this->formatAttendanceData($item);
                // Data mentah untuk Modal Edit
                $formatted['id'] = $item->id;
                $formatted['raw_speedo_awal'] = $item->speedo_awal;
                $formatted['raw_speedo_akhir'] = $item->speedo_akhir;
                return $formatted;
            });

        return view('admin.riwayat_driver', compact(
            'historyPaginator',
            'startDate',
            'endDate',
            'allDrivers',
            'projects',
            'selectedDriverId',
            'selectedProjectId'
        ));
    }

    public function exportRiwayatDriver(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $selectedDriverId = $request->input('driver_id');
        $selectedProjectId = $request->input('project_id');

        $fileName = 'riwayat_driver_' . $startDate . '_sd_' . $endDate . '.xlsx';

        return Excel::download(
            new RiwayatDriverExport($startDate, $endDate, $selectedDriverId, $selectedProjectId),
            $fileName
        );
    }

    public function riwayatUnit(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $filterPlat = $request->input('plate_number');
        $selectedProjectId = $request->input('project_id');
        $selectedType = $request->input('type'); // <--- BARU: Input Type

        $filterStart = Carbon::parse($startDate)->startOfDay();
        $filterEnd = Carbon::parse($endDate)->endOfDay();

        // Data Pendukung Dropdown
        $projects = Project::orderBy('name')->get();
        // Ambil list type unik dari tabel vehicles
        $types = \App\Models\Vehicle::select('type')->distinct()->orderBy('type')->pluck('type');

        // --- QUERY UTAMA ---
        $queryBase = Attendance::with(['driver', 'vehicle'])
            ->whereNotNull('time_out')
            ->whereBetween('time_out', [$filterStart, $filterEnd])
            // 1. Filter Plat Nomor
            ->when($filterPlat, function ($q, $plat) {
                return $q->whereHas('vehicle', function ($subQ) use ($plat) {
                    $subQ->where('plate_number', 'like', "%{$plat}%");
                });
            })
            // 2. Filter Project
            ->when($selectedProjectId, function ($q, $projId) {
                return $q->whereHas('vehicle', function ($subQ) use ($projId) {
                    $subQ->where('project_id', $projId);
                });
            })
            // 3. Filter Type Mobil (BARU)
            ->when($selectedType, function ($q, $type) {
                return $q->whereHas('vehicle', function ($subQ) use ($type) {
                    $subQ->where('type', $type);
                });
            });

        // Hitung Statistik
        $statsRaw = $queryBase->get();
        $rankingUnit = $statsRaw->groupBy('vehicle.plate_number')
            ->map(function ($group) {
                return $group->sum(fn($row) => max(0, ($row->speedo_akhir ?? 0) - ($row->speedo_awal ?? 0)));
            })
            ->sortDesc();

        $topUnitPlate = $rankingUnit->keys()->first() ?? '-';
        $topUnitKm = $rankingUnit->first() ?? 0;
        $totalJarakPeriode = $rankingUnit->sum();
        $totalTrip = $statsRaw->count();

        // Ambil Data Tabel (Pagination)
        $checklistPaginator = $queryBase->orderBy('time_out', 'desc')
            ->paginate($this->perPage)
            ->through(function ($item) {
                $awal = $item->speedo_awal ?? 0;
                $akhir = $item->speedo_akhir ?? 0;
                $jarak = max(0, $akhir - $awal);

                return [
                    'timestamp_keluar' => Carbon::parse($item->time_out)->format('Y-m-d H:i:s'),
                    'driver_name' => $item->driver->full_name ?? 'N/A (Deleted)',
                    'plate_number' => $item->vehicle->plate_number ?? 'N/A',
                    'project_name' => $item->vehicle->project->name ?? 'Pool',
                    'vehicle_type' => $item->vehicle->type ?? '-', // Kirim info type ke view
                    'speedo_awal' => number_format($awal),
                    'speedo_akhir' => number_format($akhir),
                    'total_jarak' => number_format($jarak),
                    'link_speedo_akhir' => $item->speedo_photo_akhir_path ? Storage::url($item->speedo_photo_akhir_path) : '#',
                    'cek_ban' => $item->check_ban ?? '-',
                    'cek_lampu' => $item->check_lampu ?? '-',
                    'cek_rem' => $item->check_rem ?? '-',
                    'catatan' => $item->catatan ?? '-',
                ];
            });

        return view('admin.riwayat_unit', compact(
            'checklistPaginator',
            'filterPlat',
            'startDate',
            'endDate',
            'topUnitPlate',
            'topUnitKm',
            'totalJarakPeriode',
            'totalTrip',
            'projects',
            'selectedProjectId',
            'types',
            'selectedType' // <--- Kirim variable baru
        ));
    }
    public function laporanDarurat()
    {
        $laporanMasalahRaw = EmergencyReport::with(['driver', 'vehicle'])
            ->orderBy('timestamp', 'desc')
            ->get();

        $laporanMasalah = $laporanMasalahRaw->map(function ($laporan) {
            $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($laporan->gps_location);
            return [
                'timestamp' => Carbon::parse($laporan->timestamp)->format('Y-m-d H:i:s'),
                'driver_name' => $laporan->driver->full_name ?? 'N/A',
                'plate_number' => $laporan->vehicle->plate_number ?? 'N/A',
                'deskripsi' => $laporan->description,
                'lokasi_gps' => $mapsUrl,
                'link_foto' => $laporan->proof_photo_path ? Storage::url($laporan->proof_photo_path) : '#',
            ];
        });

        return view('admin.laporan_darurat', compact('laporanMasalah'));
    }

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

    // --- REKAP BULANAN (FIXED: TAMBAH VARIABEL PROJECTS) ---
    public function rekapBulanan(Request $request)
    {
        // 1. Ambil Input
        $selectedMonth = $request->input('bulan', Carbon::now()->format('Y-m'));
        $selectedProjectId = $request->input('project_id'); // Filter Project

        // 2. Ambil List Project (WAJIB ADA untuk Dropdown Filter)
        $projects = Project::orderBy('name')->get();

        // 3. Setup Query
        $start = Carbon::parse($selectedMonth)->startOfMonth();
        $end = Carbon::parse($selectedMonth)->endOfMonth();

        $query = Attendance::with(['driver', 'vehicle'])
            ->whereNotNull('time_out')
            ->whereBetween('time_out', [$start, $end]);

        // 4. Terapkan Filter Project (Jika dipilih)
        if ($selectedProjectId) {
            $query->whereHas('driver', function ($q) use ($selectedProjectId) {
                $q->where('project_id', $selectedProjectId);
            });
        }

        $data = $query->get();

        // 5. Hitung Statistik
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

        // 6. Kirim ke View (Termasuk $projects)
        return view('admin.rekap_bulanan', compact(
            'rekapDriver',
            'rekapUnit',
            'selectedMonth',
            'projects'
        ));
    }

    public function exportBulananChecklist(Request $request): BinaryFileResponse
    {
        $month = $request->input('bulan', Carbon::now()->format('Y-m'));
        // Note: Anda bisa menambahkan filter project ke export di sini jika diperlukan nanti
        return Excel::download(
            new RekapAbsensiChecklistExport(Carbon::parse($month)->month, Carbon::parse($month)->year),
            'rekap-checklist-' . $month . '.xlsx'
        );
    }
}