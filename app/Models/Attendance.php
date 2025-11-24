<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara massal (Mass Assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Kolom Check-in
        'driver_id',
        'vehicle_id',
        'time_in',
        'gps_location_in',
        'selfie_photo_path',
        'speedo_photo_awal_path',
        'condition_photo_1_path',
        'condition_photo_2_path',
        'speedo_awal',

        // Kolom Check-out (agar bisa di-update nanti)
        'time_out',
        'speedo_photo_akhir_path',
        'catatan',
        'check_ban',
        'check_lampu',
        'check_rem',
        'speedo_akhir',
    ];

    /**
     * Relasi: Satu Absensi dimiliki oleh satu Driver.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Relasi: Satu Absensi dimiliki oleh satu Mobil.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}