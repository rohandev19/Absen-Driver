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

        // --- TAMBAHAN UNTUK FITUR LIVE MAP LEAFLET ---
        $latitude = 0;
        $longitude = 0;

        // Memecah koordinat (contoh format di DB: "-6.2088, 106.8456")
        if (!empty($item->gps_location_in)) {
            $coords = explode(',', $item->gps_location_in);
            if (count($coords) >= 2) {
                $latitude = (float) trim($coords[0]);
                $longitude = (float) trim($coords[1]);
            }
        }
        // ---------------------------------------------

        return [
            'id' => $item->id, // Penting ditambahkan jika belum ada untuk modal edit
            'timestamp_masuk' => $timeIn->format('Y-m-d H:i:s'),
            'timestamp_keluar' => $item->time_out ? Carbon::parse($item->time_out)->format('Y-m-d H:i:s') : '-',

            // Format Link Standar
            'gps_masuk' => $item->gps_location_in ? 'http://maps.google.com/?q=' . urlencode($item->gps_location_in) : '#',
            'gps_keluar' => $item->gps_location_out ? 'http://maps.google.com/?q=' . urlencode($item->gps_location_out) : '#',

            // --- VARIABEL KOORDINAT UNTUK JAVASCRIPT ---
            'latitude' => $latitude,
            'longitude' => $longitude,
            // -------------------------------------------

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