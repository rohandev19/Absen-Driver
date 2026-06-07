<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DutyStatusTestSeeder extends Seeder
{
    public function run()
    {
        // Driver A: is_on_duty = true, has active attendance (time_out = NULL)
        $driverA = User::firstOrCreate(
            ['email' => 'driverA@test.com'],
            [
                'name' => 'Driver A (On Duty)',
                'password' => Hash::make('password'),
                'is_on_duty' => true,
            ]
        );
        $driverA->update(['is_on_duty' => true]);

        Attendance::firstOrCreate(
            ['user_id' => $driverA->id, 'time_out' => null],
            [
                'plate_number' => 'B1234TEST',
                'time_in' => Carbon::now()->subHours(2),
                'gps_location' => '-6.200000,106.816666',
                'speedo_awal' => 1000,
                'speedo_photo_path' => 'dummy_speedo.jpg',
                'selfie_photo_path' => 'dummy_selfie.jpg',
            ]
        );

        // Driver B: is_on_duty = false, no active attendance
        $driverB = User::firstOrCreate(
            ['email' => 'driverB@test.com'],
            [
                'name' => 'Driver B (Off Duty, No History)',
                'password' => Hash::make('password'),
                'is_on_duty' => false,
            ]
        );
        $driverB->update(['is_on_duty' => false]);
        // Pastikan tidak ada attendance aktif
        Attendance::where('user_id', $driverB->id)->whereNull('time_out')->delete();

        // Driver C: is_on_duty = false, has completed attendance (time_out set)
        $driverC = User::firstOrCreate(
            ['email' => 'driverC@test.com'],
            [
                'name' => 'Driver C (Off Duty, Completed)',
                'password' => Hash::make('password'),
                'is_on_duty' => false,
            ]
        );
        $driverC->update(['is_on_duty' => false]);
        
        Attendance::firstOrCreate(
            ['user_id' => $driverC->id, 'time_out' => Carbon::now()->subHour()],
            [
                'plate_number' => 'B5678TEST',
                'time_in' => Carbon::now()->subHours(5),
                'gps_location' => '-6.200000,106.816666',
                'gps_location_out' => '-6.210000,106.820000',
                'speedo_awal' => 2000,
                'speedo_akhir' => 2050,
                'speedo_photo_path' => 'dummy_speedo_in.jpg',
                'speedo_photo_akhir_path' => 'dummy_speedo_out.jpg',
                'selfie_photo_path' => 'dummy_selfie.jpg',
                'check_ban' => 'Aman',
                'check_lampu' => 'Aman',
                'check_rem' => 'Aman',
                'catatan' => 'Test completed duty',
            ]
        );
    }
}
