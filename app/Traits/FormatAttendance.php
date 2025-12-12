<?php

namespace App\Traits;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

trait FormatAttendance
{
    /**
     * Helper: Format data absensi untuk tampilan konsisten
     */
    protected function formatAttendanceData(Attendance $item)
    {
        $timeIn = Carbon::parse($item->time_in);
        $totalJamKerja = '-';

        // Hitung durasi kerja jika sudah checkout
        if ($item->time_out) {
            $totalMenit = $timeIn->diffInMinutes(Carbon::parse($item->time_out), true);
            $totalJamKerja = floor($totalMenit / 60) . " jam " . ($totalMenit % 60) . " menit";
        }

        return [
            'timestamp_masuk' => $timeIn->format('Y-m-d H:i:s'),
            'timestamp_keluar' => $item->time_out ? Carbon::parse($item->time_out)->format('Y-m-d H:i:s') : '-',

            // --- PERBAIKAN FINAL DI SINI ---
            // Format Link Standar: https://www.google.com/maps?q=-6.xxxx,106.xxxx
            'gps_masuk' => $item->gps_location_in ? 'https://www.google.com/maps?q=' . $item->gps_location_in : '#',
            'gps_keluar' => $item->gps_location_out ? 'https://www.google.com/maps?q=' . $item->gps_location_out : '#',
            // -------------------------------

            'driver_name' => $item->driver->full_name ?? 'N/A',
            'plate_number' => $item->vehicle->plate_number ?? 'N/A',

            // Casting ke integer agar aman saat kalkulasi
            'speedo_awal' => (int) ($item->speedo_awal ?? 0),
            'speedo_akhir' => (int) ($item->speedo_akhir ?? 0),
            'jarak_tempuh' => (int) ($item->speedo_akhir ?? 0) - (int) ($item->speedo_awal ?? 0),

            'total_jam_kerja' => $totalJamKerja,

            'link_selfie' => $item->selfie_photo_path ? Storage::url($item->selfie_photo_path) : '#',
            'link_speedo_awal' => $item->speedo_photo_awal_path ? Storage::url($item->speedo_photo_awal_path) : '#',
            'link_speedo_akhir' => $item->speedo_photo_akhir_path ? Storage::url($item->speedo_photo_akhir_path) : '#',
        ];
    }
}