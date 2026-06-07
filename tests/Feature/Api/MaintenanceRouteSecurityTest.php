<?php

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MaintenanceRouteSecurityTest extends TestCase
{
    #[Test]
    public function cron_route_rejects_requests_without_configured_token(): void
    {
        config(['services.maintenance_url_token' => null]);

        $this->getJson('/api/cron/run-schedules?token=RAHASIA123')
            ->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function migrate_route_is_disabled_in_production_even_with_valid_token(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['services.maintenance_url_token' => 'valid-token']);

        $this->getJson('/api/migrate/run-secret?token=valid-token')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Endpoint disabled in production');
    }

    #[Test]
    public function artisan_route_is_disabled_in_production_even_with_valid_token(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['services.maintenance_url_token' => 'valid-token']);

        $this->getJson('/api/artisan/run-secret?token=valid-token&command=cache:clear')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Endpoint disabled in production');
    }
}
