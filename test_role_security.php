<?php

/**
 * Quick test script to verify role-based access control
 * Run: php test_role_security.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\Route;

echo "\n=== TESTING ROLE-BASED ACCESS CONTROL ===\n\n";

// Get all admin routes
$adminRoutes = collect(Route::getRoutes())->filter(function ($route) {
    return str_starts_with($route->uri(), 'admin/') && $route->uri() !== 'admin/login' && $route->uri() !== 'admin/logout';
});

echo "Total Admin Routes (excluding login/logout): " . $adminRoutes->count() . "\n\n";

// Check if role middleware is applied
$routesWithRole = $adminRoutes->filter(function ($route) {
    $middleware = $route->middleware();
    foreach ($middleware as $m) {
        if (str_contains($m, 'role:')) {
            return true;
        }
    }
    return false;
});

$routesWithoutRole = $adminRoutes->filter(function ($route) {
    $middleware = $route->middleware();
    foreach ($middleware as $m) {
        if (str_contains($m, 'role:')) {
            return false;
        }
    }
    return true;
});

echo "✅ Routes WITH role middleware: " . $routesWithRole->count() . "\n";
echo "❌ Routes WITHOUT role middleware: " . $routesWithoutRole->count() . "\n\n";

if ($routesWithoutRole->count() > 0) {
    echo "⚠️  WARNING: The following admin routes are NOT protected by role middleware:\n";
    foreach ($routesWithoutRole->take(10) as $route) {
        echo "   - " . $route->methods()[0] . " " . $route->uri() . "\n";
    }
    echo "\n";
}

// Check customer routes
$customerRoutes = collect(Route::getRoutes())->filter(function ($route) {
    return str_starts_with($route->uri(), 'customer/');
});

$customerRoutesWithRole = $customerRoutes->filter(function ($route) {
    $middleware = $route->middleware();
    foreach ($middleware as $m) {
        if (str_contains($m, 'role:customer')) {
            return true;
        }
    }
    return false;
});

echo "Customer Routes: " . $customerRoutes->count() . "\n";
echo "✅ Customer Routes WITH role:customer middleware: " . $customerRoutesWithRole->count() . "\n\n";

if ($routesWithoutRole->count() === 0 && $customerRoutesWithRole->count() === $customerRoutes->count()) {
    echo "✅ ✅ ✅ SECURITY CHECK PASSED! All routes are properly protected.\n\n";
} else {
    echo "❌ ❌ ❌ SECURITY ISSUE DETECTED! Some routes are not properly protected.\n\n";
}
