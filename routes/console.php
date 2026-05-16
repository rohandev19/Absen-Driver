<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| File ini tempat mendefinisikan perintah berbasis terminal (Artisan Command).
| Perintah ini tidak diakses via browser, tapi via CLI (Command Line Interface).
| Contoh penggunaan: php artisan inspire
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Jadwal otomatis untuk preventive maintenance system
|
*/

// Update component status setiap hari jam 00:00
Schedule::command('maintenance:update-component-status')->daily();

// Generate alerts setiap 6 jam
Schedule::command('maintenance:generate-alerts')->everySixHours();

// Generate schedules setiap hari jam 01:00
Schedule::command('maintenance:generate-schedules')->dailyAt('01:00');