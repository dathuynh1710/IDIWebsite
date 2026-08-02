<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_api_status(): void
    {
        $response = $this->getJson('/api/health');

        $response
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'application' => 'IDI Seafood API',
                'environment' => 'testing',
            ])
            ->assertJsonStructure(['timestamp']);
    }

    public function test_health_endpoint_allows_the_frontend_origin(): void
    {
        $response = $this
            ->withHeader('Origin', 'http://localhost:5173')
            ->getJson('/api/health');

        $response
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }
}
