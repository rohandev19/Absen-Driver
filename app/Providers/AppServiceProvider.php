<?php

namespace App\Providers;

use App\Services\Audit\AuditOrchestrator;
use Illuminate\Pagination\Paginator; // <-- TAMBAHKAN BARIS INI
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register AuditOrchestrator as a singleton
        $this->app->singleton(AuditOrchestrator::class, function ($app) {
            return new AuditOrchestrator(
                securityEngine: null, // Will be configured when security engine is implemented
                performanceEngine: null, // Will be configured when performance engine is implemented
                databaseEngine: null, // Will be configured when database engine is implemented
                codeQualityEngine: null, // Will be configured when code quality engine is implemented
            );
        });
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