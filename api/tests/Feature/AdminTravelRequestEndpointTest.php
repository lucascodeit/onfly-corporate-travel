<?php

namespace Tests\Feature;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTravelRequestEndpointTest extends TestCase
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

    public function test_admin_can_list_all_travel_requests(): void
    {
        $this->actingAsAdmin();

        $staffA = User::factory()->create();
        $staffB = User::factory()->create();
        TravelRequest::factory()->count(2)->for($staffA)->create();
        TravelRequest::factory()->count(3)->for($staffB)->create();

        $response = $this->getJson('/api/admin/travel-requests');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'status', 'destination', 'start_date', 'end_date', 'user', 'created_at']],
                'links',
                'meta',
            ]);
    }

    public function test_admin_can_filter_travel_requests_by_user_id(): void
    {
        $this->actingAsAdmin();

        $staffA = User::factory()->create();
        $staffB = User::factory()->create();
        TravelRequest::factory()->count(2)->for($staffA)->create();
        TravelRequest::factory()->count(3)->for($staffB)->create();

        $response = $this->getJson("/api/admin/travel-requests?user_id={$staffA->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_staff_cannot_access_admin_travel_request_endpoints(): void
    {
        $this->actingAsStaff();
        $travelRequest = TravelRequest::factory()->create(['status' => 'requested']);

        $this->getJson('/api/admin/travel-requests')->assertStatus(403);
        $this->patchJson("/api/admin/travel-requests/{$travelRequest->id}/approve")->assertStatus(403);
        $this->patchJson("/api/admin/travel-requests/{$travelRequest->id}/disapprove")->assertStatus(403);
    }

    public function test_admin_can_approve_a_requested_travel_request(): void
    {
        $admin = $this->actingAsAdmin();
        $staff = User::factory()->create();
        $travelRequest = TravelRequest::factory()->for($staff)->create(['status' => 'requested']);

        $response = $this->patchJson("/api/admin/travel-requests/{$travelRequest->id}/approve");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.admin.id', $admin->id);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'approved',
            'admin_id' => $admin->id,
        ]);
    }

    public function test_admin_can_disapprove_a_requested_travel_request(): void
    {
        $admin = $this->actingAsAdmin();
        $staff = User::factory()->create();
        $travelRequest = TravelRequest::factory()->for($staff)->create(['status' => 'requested']);

        $response = $this->patchJson("/api/admin/travel-requests/{$travelRequest->id}/disapprove");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'disapproved')
            ->assertJsonPath('data.admin.id', $admin->id);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'disapproved',
            'admin_id' => $admin->id,
        ]);
    }

    public function test_admin_can_change_approved_to_disapproved(): void
    {
        $admin = $this->actingAsAdmin();
        $staff = User::factory()->create();
        $travelRequest = TravelRequest::factory()->for($staff)->create([
            'status' => 'approved',
            'admin_id' => $admin->id,
        ]);

        $response = $this->patchJson("/api/admin/travel-requests/{$travelRequest->id}/disapprove");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'disapproved');

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'disapproved',
        ]);
    }

    public function test_admin_can_change_disapproved_to_approved(): void
    {
        $admin = $this->actingAsAdmin();
        $staff = User::factory()->create();
        $travelRequest = TravelRequest::factory()->for($staff)->create([
            'status' => 'disapproved',
            'admin_id' => $admin->id,
        ]);

        $response = $this->patchJson("/api/admin/travel-requests/{$travelRequest->id}/approve");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_cannot_approve_a_cancelled_travel_request(): void
    {
        $this->actingAsAdmin();
        $staff = User::factory()->create();
        $travelRequest = TravelRequest::factory()->for($staff)->create(['status' => 'cancelled']);

        $response = $this->patchJson("/api/admin/travel-requests/{$travelRequest->id}/approve");

        $response->assertStatus(422);
    }

    public function test_admin_cannot_disapprove_a_cancelled_travel_request(): void
    {
        $this->actingAsAdmin();
        $staff = User::factory()->create();
        $travelRequest = TravelRequest::factory()->for($staff)->create(['status' => 'cancelled']);

        $response = $this->patchJson("/api/admin/travel-requests/{$travelRequest->id}/disapprove");

        $response->assertStatus(422);
    }

    public function test_admin_can_filter_travel_requests_by_start_date(): void
    {
        $this->actingAsAdmin();

        $staff = User::factory()->create();
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-01-10', 'end_date' => '2026-01-15']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-02-10', 'end_date' => '2026-02-20']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-03-05', 'end_date' => '2026-03-15']);

        $response = $this->getJson('/api/admin/travel-requests?start_date=2026-02-01');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_filter_travel_requests_by_end_date(): void
    {
        $this->actingAsAdmin();

        $staff = User::factory()->create();
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-01-10', 'end_date' => '2026-01-15']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-02-10', 'end_date' => '2026-02-20']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-03-05', 'end_date' => '2026-03-15']);

        $response = $this->getJson('/api/admin/travel-requests?end_date=2026-02-15');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_filter_travel_requests_by_date_range(): void
    {
        $this->actingAsAdmin();

        $staff = User::factory()->create();
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-01-10', 'end_date' => '2026-01-15']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-02-10', 'end_date' => '2026-02-20']);
        TravelRequest::factory()->for($staff)->create(['start_date' => '2026-03-05', 'end_date' => '2026-03-15']);

        $response = $this->getJson('/api/admin/travel-requests?start_date=2026-02-01&end_date=2026-02-28');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_combine_user_and_date_filters(): void
    {
        $this->actingAsAdmin();

        $staffA = User::factory()->create();
        $staffB = User::factory()->create();

        TravelRequest::factory()->for($staffA)->create(['start_date' => '2026-02-10', 'end_date' => '2026-02-20']);
        TravelRequest::factory()->for($staffA)->create(['start_date' => '2026-03-05', 'end_date' => '2026-03-15']);
        TravelRequest::factory()->for($staffB)->create(['start_date' => '2026-02-12', 'end_date' => '2026-02-18']);

        $response = $this->getJson("/api/admin/travel-requests?user_id={$staffA->id}&start_date=2026-02-01&end_date=2026-02-28");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
