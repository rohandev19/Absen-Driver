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

            // PERBAIKAN: Format link Google Maps yang valid agar bisa diklik
            'gps_masuk' => $item->gps_location_in ? 'https://www.google.com/maps?q=' . $item->gps_location_in : '#',

            'driver_name' => $item->driver->full_name ?? 'N/A',
            'plate_number' => $item->vehicle->plate_number ?? 'N/A',
            'speedo_awal' => $item->speedo_awal ?? 0,
            'speedo_akhir' => $item->speedo_akhir ?? 0,
            'jarak_tempuh' => ($item->speedo_akhir ?? 0) - ($item->speedo_awal ?? 0),
            'total_jam_kerja' => $totalJamKerja,

            // Menggunakan Storage::url untuk path gambar
            'link_selfie' => $item->selfie_photo_path ? Storage::url($item->selfie_photo_path) : '#',
            'link_speedo_awal' => $item->speedo_photo_awal_path ? Storage::url($item->speedo_photo_awal_path) : '#',
            'link_speedo_akhir' => $item->speedo_photo_akhir_path ? Storage::url($item->speedo_photo_akhir_path) : '#',
        ];
    }
}