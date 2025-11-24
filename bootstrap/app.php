<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php', // Baris ini memastikan semua rute di api.php mendapat awalan /api
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // --- TAMBAHKAN BLOK INI ---
        // Ini akan mengarahkan user yang BELUM login
        // ke halaman login admin yang sudah kita buat.
        $middleware->redirectTo(
            guests: fn() => route('admin.login')
        );
        // --- AKHIR BLOK TAMBAHAN ---
    
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();