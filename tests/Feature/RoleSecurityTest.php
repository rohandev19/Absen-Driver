<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class RoleSecurityTest extends TestCase
{
    /**
     * Test that all admin routes are protected by role middleware.
     */
    public function test_admin_routes_are_protected_by_role_middleware()
    {
        $adminRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'admin/') 
                && $route->uri() !== 'admin/login' 
                && $route->uri() !== 'admin/logout';
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

        $this->assertCount(
            0, 
            $routesWithoutRole, 
            "There are admin routes not protected by role middleware: " . 
            $routesWithoutRole->map(fn($r) => $r->uri())->implode(', ')
        );
    }

    /**
     * Test that all customer routes are protected by customer role middleware.
     */
    public function test_customer_routes_are_protected_by_customer_role()
    {
        $customerRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'customer/');
        });

        $customerRoutesWithoutRole = $customerRoutes->filter(function ($route) {
            $middleware = $route->middleware();
            foreach ($middleware as $m) {
                if (str_contains($m, 'role:customer')) {
                    return false; // has role
                }
            }
            return true; // doesn't have role
        });

        $this->assertCount(
            0, 
            $customerRoutesWithoutRole, 
            "There are customer routes not protected by customer role middleware: " . 
            $customerRoutesWithoutRole->map(fn($r) => $r->uri())->implode(', ')
        );
    }
}
