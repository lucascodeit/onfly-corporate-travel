<?php

namespace Tests\Feature;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestEndpointTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsStaff(): User
    {
        $staff = User::factory()->create();
        $this->actingAs($staff, 'api');

        return $staff;
    }

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'api');

        return $admin;
    }

    public function test_staff_can_list_own_travel_requests(): void
    {
        $staff = $this->actingAsStaff();
        TravelRequest::factory()->count(3)->for($staff)->create();

        $response = $this->getJson('/api/travel-requests');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'status', 'destination', 'start_date', 'end_date', 'user', 'created_at']],
            ]);
    }

    public function test_staff_cannot_see_other_users_travel_requests(): void
    {
        $staff = $this->actingAsStaff();
        $otherStaff = User::factory()->create();

        TravelRequest::factory()->count(2)->for($staff)->create();
        TravelRequest::factory()->count(3)->for($otherStaff)->create();

        $response = $this->getJson('/api/travel-requests');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_cannot_access_travel_request_endpoints(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/travel-requests')->assertStatus(403);
        $this->postJson('/api/travel-requests', [])->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_travel_requests(): void
    {
        $this->getJson('/api/travel-requests')->assertStatus(401);
    }

    public function test_staff_can_create_travel_request(): void
    {
        $this->actingAsStaff();

        $response = $this->postJson('/api/travel-requests', [
            'destination' => 'São Paulo',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.destination', 'São Paulo')
            ->assertJsonPath('data.status', 'requested');

        $this->assertDatabaseHas('travel_requests', [
            'destination' => 'São Paulo',
            'status' => 'requested',
        ]);
    }

    public function test_create_travel_request_validates_required_fields(): void
    {
        $this->actingAsStaff();

        $response = $this->postJson('/api/travel-requests', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination', 'start_date', 'end_date']);
    }

    public function test_create_travel_request_validates_end_date_after_start_date(): void
    {
        $this->actingAsStaff();

        $response = $this->postJson('/api/travel-requests', [
            'destination' => 'Rio de Janeiro',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_create_travel_request_validates_destination_max_length(): void
    {
        $this->actingAsStaff();

        $response = $this->postJson('/api/travel-requests', [
            'destination' => str_repeat('A', 101),
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination']);
    }

    public function test_staff_can_cancel_requested_travel_request(): void
    {
        $staff = $this->actingAsStaff();
        $travelRequest = TravelRequest::factory()->for($staff)->create(['status' => 'requested']);

        $response = $this->patchJson("/api/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_staff_cannot_cancel_approved_travel_request(): void
    {
        $staff = $this->actingAsStaff();
        $admin = User::factory()->admin()->create();
        $travelRequest = TravelRequest::factory()->for($staff)->create([
            'status' => 'approved',
            'admin_id' => $admin->id,
        ]);

        $response = $this->patchJson("/api/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(422);
    }

    public function test_staff_cannot_cancel_disapproved_travel_request(): void
    {
        $staff = $this->actingAsStaff();
        $admin = User::factory()->admin()->create();
        $travelRequest = TravelRequest::factory()->for($staff)->create([
            'status' => 'disapproved',
            'admin_id' => $admin->id,
        ]);

        $response = $this->patchJson("/api/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(422);
    }

    public function test_staff_cannot_cancel_another_users_travel_request(): void
    {
        $this->actingAsStaff();
        $otherStaff = User::factory()->create();
        $travelRequest = TravelRequest::factory()->for($otherStaff)->create(['status' => 'requested']);

        $response = $this->patchJson("/api/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_travel_request_list_includes_admin_info(): void
    {
        $staff = $this->actingAsStaff();
        $admin = User::factory()->admin()->create();
        TravelRequest::factory()->for($staff)->create([
            'status' => 'approved',
            'admin_id' => $admin->id,
        ]);

        $response = $this->getJson('/api/travel-requests');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.admin.id', $admin->id)
            ->assertJsonPath('data.0.admin.first_name', $admin->first_name);
    }

    public function test_travel_request_list_is_paginated(): void
    {
        $staff = $this->actingAsStaff();
        TravelRequest::factory()->count(20)->for($staff)->create();

        $response = $this->getJson('/api/travel-requests');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_staff_can_filter_travel_requests_by_start_date(): void
    {
        $staff = $this->actingAsStaff();

        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-01-10', 'end_date' => '2026-01-15']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-02-10', 'end_date' => '2026-02-20']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-03-05', 'end_date' => '2026-03-15']);

        $response = $this->getJson('/api/travel-requests?start_date=2026-02-01');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_staff_can_filter_travel_requests_by_end_date(): void
    {
        $staff = $this->actingAsStaff();

        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-01-10', 'end_date' => '2026-01-15']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-02-10', 'end_date' => '2026-02-20']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-03-05', 'end_date' => '2026-03-15']);

        $response = $this->getJson('/api/travel-requests?end_date=2026-02-15');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_staff_can_filter_travel_requests_by_date_range(): void
    {
        $staff = $this->actingAsStaff();

        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-01-10', 'end_date' => '2026-01-15']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-02-10', 'end_date' => '2026-02-20']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-03-05', 'end_date' => '2026-03-15']);

        $response = $this->getJson('/api/travel-requests?start_date=2026-02-01&end_date=2026-02-28');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
