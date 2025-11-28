<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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