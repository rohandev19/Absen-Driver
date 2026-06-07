<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        $speedoAwal = $this->faker->numberBetween(20000, 80000);

        return [
            'driver_id' => Driver::factory(),
            'vehicle_id' => Vehicle::factory(),
            'time_in' => now()->subHours(9),
            'gps_location_in' => '-6.200000, 106.816666',
            'selfie_photo_path' => 'attendance/selfie.jpg',
            'speedo_photo_awal_path' => 'attendance/speedo_awal.jpg',
            'condition_photo_1_path' => 'attendance/condition_1.jpg',
            'condition_photo_2_path' => 'attendance/condition_2.jpg',
            'speedo_awal' => $speedoAwal,
            'time_out' => now()->subHour(),
            'gps_location_out' => '-6.210000, 106.826666',
            'speedo_photo_akhir_path' => 'attendance/speedo_akhir.jpg',
            'catatan' => null,
            'check_ban' => 'Baik',
            'check_lampu' => 'Baik',
            'check_rem' => 'Baik',
            'speedo_akhir' => $speedoAwal + $this->faker->numberBetween(50, 250),
            'is_offline_recovery' => false,
            'is_late_submission' => false,
        ];
    }
}
