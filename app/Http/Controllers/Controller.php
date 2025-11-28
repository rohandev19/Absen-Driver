<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class Controller
 * * Base Controller (Controller Dasar) bawaan Laravel.
 * * Semua controller lain (DriverController, AuthController, dll) mewarisi (extend) class ini
 * agar bisa menggunakan fitur standar seperti Validasi ($this->validate)
 * dan Otorisasi ($this->authorize).
 * * @package App\Http\Controllers
 */
abstract class Controller extends BaseController
{
    // Mengaktifkan fitur Otorisasi (Policy/Gate) dan Validasi Request
    use AuthorizesRequests, ValidatesRequests;
}