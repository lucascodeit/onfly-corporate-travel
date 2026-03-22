<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.first_name', $user->first_name)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->putJson('/api/profile', [
            'first_name' => 'NewFirst',
            'last_name' => 'NewLast',
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'NewFirst')
            ->assertJsonPath('data.last_name', 'NewLast');
    }

    public function test_user_cannot_update_profile_with_taken_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->putJson('/api/profile', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $other->email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_change_own_password(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword']);
        $this->actingAs($user, 'api');

        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword']);
        $this->actingAs($user, 'api');

        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_change_password_validates_confirmation(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword']);
        $this->actingAs($user, 'api');

        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }
}
