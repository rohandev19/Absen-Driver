<?php

use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rute untuk halaman web, termasuk dashboard admin.
|
*/

// Rute untuk menampilkan halaman utama generator QR code
Route::get('/admin/qr-generator', [QrCodeController::class, 'show'])->name('qr.show');

// Rute untuk memproses pembuatan QR Code dari input form
Route::post('/admin/qr-generator', [QrCodeController::class, 'generate'])->name('qr.generate');


// Halaman default (opsional, bisa Anda hapus atau ubah)
Route::get('/', function () {
    return 'Selamat Datang! Akses /admin/qr-generator untuk membuat QR Code.';
});

