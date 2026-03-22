<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_jwt_token(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token', 'token_type', 'expires_in', 'user' => ['id', 'first_name', 'last_name', 'email', 'type']],
            ])
            ->assertJsonPath('data.token_type', 'bearer');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials.']);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create(['password' => 'secret123']);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Account is inactive.']);
    }

    public function test_refresh_token_works_once(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $token = $loginResponse->json('data.token');

        $refreshResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/refresh');

        $refreshResponse->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'token_type', 'expires_in']]);

        $newToken = $refreshResponse->json('data.token');

        $secondRefresh = $this->withHeader('Authorization', "Bearer $newToken")
            ->postJson('/api/auth/refresh');

        $secondRefresh->assertStatus(401);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $token = $loginResponse->json('data.token');

        $logoutResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout');

        $logoutResponse->assertStatus(200);

        $profileResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/profile');

        $profileResponse->assertStatus(401);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }
}
