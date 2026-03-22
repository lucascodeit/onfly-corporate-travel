<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_healthy_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson('/api/healthy');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'laravel_version',
                    'mysql_version',
                    'database_connection_status',
                ],
            ])
            ->assertJsonPath('data.database_connection_status', 'Success');
    }

    public function test_healthy_endpoint_contains_laravel_version(): void
    {
        $response = $this->getJson('/api/healthy');

        $response->assertStatus(200)
            ->assertJsonPath('data.laravel_version', app()->version());
    }

    public function test_healthy_endpoint_reports_error_on_database_failure(): void
    {
        $default = config('database.default');
        config(["database.connections.{$default}.database" => '/nonexistent/path.db']);
        DB::purge($default);

        $response = $this->getJson('/api/healthy');

        $response->assertStatus(200)
            ->assertJsonPath('data.database_connection_status', 'Error')
            ->assertJsonPath('data.mysql_version', null);
    }
}
