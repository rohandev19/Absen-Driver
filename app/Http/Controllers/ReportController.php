<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use App\Models\Attendance;
use App\Models\Driver;
use App\Models\EmergencyReport;
use App\Exports\RekapAbsensiChecklistExport;
use App\Traits\FormatAttendance;

/**
 * Class ReportController
 * * Controller ini bertanggung jawab untuk menangani segala bentuk pelaporan
 * dalam sistem manajemen driver dan kendaraan PT Hamada Logistik.
 * * Fitur mencakup:
 * - Riwayat perjalanan driver
 * - Riwayat checklist kondisi unit kendaraan
 * - Laporan darurat (emergency)
 * - Rekapitulasi harian dan bulanan
 * - Ekspor data ke Excel
 *
 * @package App\Http\Controllers
 */
class ReportController extends Controller
{
    /**
     * Trait FormatAttendance digunakan untuk menstandarisasi 
     * format data absensi (jam masuk, jam keluar, durasi, dll).
     */
    use FormatAttendance;

    /**
     * @var int Jumlah item yang ditampilkan per halaman untuk pagination.
     */
    private $perPage = 25;

    /**
     * Menampilkan halaman Riwayat Driver.
     * * Method ini memfilter data absensi berdasarkan rentang tanggal 
     * dan driver tertentu, lalu menampilkannya dengan pagination.
     *
     * @param Request $request
     * - start_date (string|null): Tanggal awal filter (Y-m-d). Default: 30 hari lalu.
     * - end_date (string|null): Tanggal akhir filter (Y-m-d). Default: Hari ini.
     * - driver_id (int|null): ID driver untuk filter spesifik.
     * * @return \Illuminate\View\View
     */
    public function riwayatDriver(Request $request)
    {
        // Set default tanggal jika tidak ada input
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $selectedDriverId = $request->input('driver_id');

        // Konversi ke format Carbon untuk query database
        $filterEnd = Carbon::parse($endDate)->endOfDay();
        $filterStart = Carbon::parse($startDate)->startOfDay();

        // Ambil list driver untuk dropdown filter
        $allDrivers = Driver::orderBy('full_name')->get();

        // Query Builder
        $query = Attendance::with(['driver', 'vehicle'])
            ->whereNotNull('time_out') // Hanya ambil yang sudah selesai (checkout)
            ->whereBetween('time_in', [$filterStart, $filterEnd])
            ->orderBy('time_in', 'desc');

        // Filter tambahan jika driver dipilih
        if (!empty($selectedDriverId)) {
            $query->where('driver_id', $selectedDriverId);
        }

        // Eksekusi pagination dan format data menggunakan Trait
        $historyPaginatorRaw = $query->paginate($this->perPage);
        $historyPaginator = $historyPaginatorRaw->through(fn($item) => $this->formatAttendanceData($item));

        return view('admin.riwayat_driver', compact('historyPaginator', 'startDate', 'endDate', 'allDrivers', 'selectedDriverId'));
    }

    /**
     * Menampilkan halaman Riwayat Unit (Checklist Kendaraan).
     * * Fokus pada kondisi fisik kendaraan (ban, lampu, rem, speedo)
     * saat driver melakukan checkout/selesai tugas.
     *
     * @param Request $request
     * - plate_number (string|null): Filter berdasarkan plat nomor (partial search).
     * * @return \Illuminate\View\View
     */
    public function riwayatUnit(Request $request)
    {
        $filterPlat = $request->input('plate_number', '');

        $query = Attendance::with(['driver', 'vehicle'])
            ->whereNotNull('time_out')
            ->orderBy('time_out', 'desc');

        // Filter menggunakan whereHas untuk mencari di tabel relasi (vehicles)
        if (!empty($filterPlat)) {
            $query->whereHas('vehicle', function ($q) use ($filterPlat) {
                $q->where('plate_number', 'like', '%' . $filterPlat . '%');
            });
        }

        $checklistPaginatorRaw = $query->paginate($this->perPage);

        // Transformasi data spesifik untuk kebutuhan view checklist
        $checklistPaginator = $checklistPaginatorRaw->through(function ($item) {
            return [
                'timestamp_keluar' => Carbon::parse($item->time_out)->format('Y-m-d H:i:s'),
                'driver_name' => $item->driver->full_name ?? 'N/A', // Null Coalescing Operator untuk safety
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

    /**
     * Menampilkan daftar Laporan Darurat.
     * * Mengambil data dari tabel emergency_reports, termasuk lokasi GPS
     * dan foto bukti kejadian.
     *
     * @return \Illuminate\View\View
     */
    public function laporanDarurat()
    {
        $laporanMasalahRaw = EmergencyReport::with(['driver', 'vehicle'])
            ->orderBy('timestamp', 'desc')
            ->get(); // Menggunakan get() karena asumsi data emergency tidak sebanyak data absensi

        $laporanMasalah = $laporanMasalahRaw->map(function ($laporan) {
            return [
                'timestamp' => Carbon::parse($laporan->timestamp)->format('Y-m-d H:i:s'),
                'driver_name' => $laporan->driver->full_name ?? 'N/A',
                'plate_number' => $laporan->vehicle->plate_number ?? 'N/A',
                'deskripsi' => $laporan->description,
                
                // PERBAIKAN DI SINI: Menggunakan URL resmi Google Maps
                'lokasi_gps' => 'https://maps.google.com/?q=' . $laporan->gps_location, 
                
                'link_foto' => $laporan->proof_photo_path ? Storage::url($laporan->proof_photo_path) : '#',
            ];
        });

        return view('admin.laporan_darurat', compact('laporanMasalah'));
    }

    /**
     * Menampilkan Rekap Harian Absensi.
     * * Menampilkan detail aktivitas semua driver pada satu tanggal spesifik.
     *
     * @param Request $request
     * - tanggal (string): Tanggal yang ingin dilihat (Y-m-d).
     * * @return \Illuminate\View\View
     */
    public function rekapHarian(Request $request)
    {
        $selectedDate = $request->input('tanggal', Carbon::now()->format('Y-m-d'));

        $rekapDataRaw = Attendance::with(['driver', 'vehicle'])
            ->whereNotNull('time_out')
            ->whereDate('time_out', Carbon::parse($selectedDate)) // whereDate mengabaikan jam/menit
            ->orderBy('time_in', 'asc')
            ->get();

        $rekapData = $rekapDataRaw->map(fn($item) => $this->formatAttendanceData($item));

        return view('admin.rekap_harian', compact('rekapData', 'selectedDate'));
    }

    /**
     * Menampilkan Rekap Bulanan (Statistik).
     * * Mengelompokkan data absensi dalam satu bulan menjadi dua kategori:
     * 1. Performa per Driver (Total tugas & Total KM)
     * 2. Penggunaan per Unit Kendaraan (Total tugas & Total KM)
     *
     * @param Request $request
     * - bulan (string): Bulan yang dipilih (format: Y-m, contoh: 2024-12).
     * * @return \Illuminate\View\View
     */
    public function rekapBulanan(Request $request)
    {
        $selectedMonth = $request->input('bulan', Carbon::now()->format('Y-m'));

        // Tentukan range tanggal awal dan akhir bulan
        $start = Carbon::parse($selectedMonth)->startOfMonth();
        $end = Carbon::parse($selectedMonth)->endOfMonth();

        $data = Attendance::with(['driver', 'vehicle'])
            ->whereNotNull('time_out')
            ->whereBetween('time_out', [$start, $end])
            ->get();

        // Grouping data berdasarkan nama driver
        $rekapDriver = $data->groupBy('driver.full_name')
            ->map(fn($group) => [
                'jumlah_tugas' => $group->count(),
                'total_km' => $group->sum(fn($a) => ($a->speedo_akhir ?? 0) - ($a->speedo_awal ?? 0))
            ])->sortKeys();

        // Grouping data berdasarkan plat nomor
        $rekapUnit = $data->groupBy('vehicle.plate_number')
            ->map(fn($group) => [
                'jumlah_tugas' => $group->count(),
                'total_km' => $group->sum(fn($a) => ($a->speedo_akhir ?? 0) - ($a->speedo_awal ?? 0))
            ])->sortKeys();

        return view('admin.rekap_bulanan', compact('rekapDriver', 'rekapUnit', 'selectedMonth'));
    }

    /**
     * Mengunduh Laporan Bulanan (Excel).
     * * Menggunakan library Maatwebsite Excel untuk generate file .xlsx
     * berdasarkan class export yang sudah didefinisikan.
     *
     * @param Request $request
     * - bulan (string): Bulan yang akan diexport (Y-m).
     * * @return BinaryFileResponse File Excel untuk didownload browser.
     */
    public function exportBulananChecklist(Request $request): BinaryFileResponse
    {
        $month = $request->input('bulan', Carbon::now()->format('Y-m'));

        return Excel::download(
            new RekapAbsensiChecklistExport(Carbon::parse($month)->month, Carbon::parse($month)->year),
            'rekap-checklist-' . $month . '.xlsx'
        );
    }
}