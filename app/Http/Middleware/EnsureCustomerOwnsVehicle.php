<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureCustomerOwnsVehicle
 * 
 * Verifies that the authenticated customer can only access vehicles
 * belonging to their own projects. Prevents cross-customer data leakage.
 * 
 * Chain: Customer → customer_id ← Project → project_id ← Vehicle
 */
class EnsureCustomerOwnsVehicle
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isCustomer() || !$user->customer_id) {
            abort(403, 'Akses ditolak.');
        }

        // Check if route has a {vehicle} parameter
        $vehicle = $request->route('vehicle');

        if ($vehicle) {
            // Load the vehicle's project to check customer ownership
            $vehicle->loadMissing('project');

            if (!$vehicle->project || $vehicle->project->customer_id !== $user->customer_id) {
                abort(403, 'Anda tidak memiliki akses ke kendaraan ini.');
            }
        }

        return $next($request);
    }
}
