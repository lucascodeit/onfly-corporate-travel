<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserEndpointTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'api');

        return $admin;
    }

    private function actingAsStaff(): User
    {
        $staff = User::factory()->create();
        $this->actingAs($staff, 'api');

        return $staff;
    }

    public function test_admin_can_list_staff_users(): void
    {
        $this->actingAsAdmin();
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'first_name', 'last_name', 'email', 'type', 'is_active']]]);
    }

    public function test_staff_cannot_list_users(): void
    {
        $this->actingAsStaff();

        $response = $this->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_staff_user(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'staff',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.first_name', 'John')
            ->assertJsonPath('data.email', 'john@example.com');

        $this->assertDatabaseHas('users', ['email' => 'john@example.com', 'type' => 'staff']);
    }

    public function test_staff_cannot_create_user(): void
    {
        $this->actingAsStaff();

        $response = $this->postJson('/api/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'staff',
        ]);

        $response->assertStatus(403);
    }

    public function test_create_user_validates_required_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password']);
    }

    public function test_create_user_validates_unique_email(): void
    {
        $this->actingAsAdmin();
        $existing = User::factory()->create();

        $response = $this->postJson('/api/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $existing->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'staff',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_show_user(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_admin_can_update_user(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}", [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $user->email,
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Updated')
            ->assertJsonPath('data.is_active', false);
    }

    public function test_admin_can_delete_user(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_can_change_user_password(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}/password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);
    }

    public function test_staff_cannot_delete_user(): void
    {
        $this->actingAsStaff();
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(403);
    }
}
