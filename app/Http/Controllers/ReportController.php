<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Attendance;
use App\Models\Driver;
use App\Models\EmergencyReport;
use App\Exports\RekapAbsensiChecklistExport;
use App\Traits\FormatAttendance; // Menggunakan Trait

class ReportController extends Controller
{
    use FormatAttendance; // Load Trait

    private $perPage = 25;

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

        if (!empty($selectedDriverId)) {
            $query->where('driver_id', $selectedDriverId);
        }

        // Menggunakan method dari Trait dalam pagination
        $historyPaginatorRaw = $query->paginate($this->perPage);
        $historyPaginator = $historyPaginatorRaw->through(fn($item) => $this->formatAttendanceData($item));

        return view('admin.riwayat_driver', compact('historyPaginator', 'startDate', 'endDate', 'allDrivers', 'selectedDriverId'));
    }

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

    public function exportBulananChecklist(Request $request)
    {
        $month = $request->input('bulan', Carbon::now()->format('Y-m'));
        return Excel::download(
            new RekapAbsensiChecklistExport(Carbon::parse($month)->month, Carbon::parse($month)->year),
            'rekap-checklist-' . $month . '.xlsx'
        );
    }
}