<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator; // <-- TAMBAHKAN BARIS INI
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --- TAMBAHKAN BARIS INI ---
        // Memberitahu Laravel untuk menggunakan template Bootstrap 5
        // untuk semua pagination.
        Paginator::useBootstrapFive();
    }
}