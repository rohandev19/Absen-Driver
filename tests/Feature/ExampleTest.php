<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // KOREKSI: Karena kita redirect ke login, statusnya adalah 302 (Found/Redirect)
        $response->assertStatus(302);
    }
}