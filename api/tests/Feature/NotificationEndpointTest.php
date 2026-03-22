<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationEndpointTest extends TestCase
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

    public function test_user_can_list_own_notifications_paginated(): void
    {
        $staff = $this->actingAsStaff();
        $admin = User::factory()->admin()->create();

        Notification::factory()->count(7)->create([
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'notification_type', 'message', 'is_read', 'read_at', 'created_at', 'user_from']],
                'links',
                'meta',
            ]);

        $page2 = $this->getJson('/api/notifications?page=2');
        $page2->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_user_can_filter_notifications_by_unread(): void
    {
        $staff = $this->actingAsStaff();
        $admin = User::factory()->admin()->create();

        Notification::factory()->count(3)->create([
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
            'is_read' => false,
        ]);

        Notification::factory()->count(2)->read()->create([
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
        ]);

        $response = $this->getJson('/api/notifications?filter=unread');

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    public function test_user_can_filter_notifications_by_read(): void
    {
        $staff = $this->actingAsStaff();
        $admin = User::factory()->admin()->create();

        Notification::factory()->count(3)->create([
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
            'is_read' => false,
        ]);

        Notification::factory()->count(2)->read()->create([
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
        ]);

        $response = $this->getJson('/api/notifications?filter=read');

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_user_cannot_see_other_users_notifications(): void
    {
        $staff = $this->actingAsStaff();
        $otherStaff = User::factory()->create();
        $admin = User::factory()->admin()->create();

        Notification::factory()->count(3)->create([
            'user_to_id' => $otherStaff->id,
            'user_from_id' => $admin->id,
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_user_can_view_notification_detail_and_it_marks_as_read(): void
    {
        $staff = $this->actingAsStaff();
        $admin = User::factory()->admin()->create();

        $notification = Notification::factory()->create([
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
            'is_read' => false,
            'read_at' => null,
        ]);

        $response = $this->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.is_read', true)
            ->assertJsonStructure([
                'data' => ['id', 'notification_type', 'message', 'is_read', 'read_at', 'created_at', 'user_from'],
            ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_user_cannot_view_other_users_notification_detail(): void
    {
        $staff = $this->actingAsStaff();
        $otherStaff = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $notification = Notification::factory()->create([
            'user_to_id' => $otherStaff->id,
            'user_from_id' => $admin->id,
        ]);

        $response = $this->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $staff = $this->actingAsStaff();
        $admin = User::factory()->admin()->create();

        $notification = Notification::factory()->create([
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
            'is_read' => false,
            'read_at' => null,
        ]);

        $response = $this->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_read', true);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $staff = $this->actingAsStaff();
        $otherStaff = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $notification = Notification::factory()->create([
            'user_to_id' => $otherStaff->id,
            'user_from_id' => $admin->id,
        ]);

        $response = $this->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(403);
    }

    public function test_unread_count_returns_correct_number(): void
    {
        $staff = $this->actingAsStaff();
        $admin = User::factory()->admin()->create();

        Notification::factory()->count(4)->create([
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
            'is_read' => false,
        ]);

        Notification::factory()->count(2)->read()->create([
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
        ]);

        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJsonPath('count', 4);
    }

    public function test_approving_travel_request_creates_notification_for_staff(): void
    {
        $admin = $this->actingAsAdmin();
        $staff = User::factory()->create();
        $travelRequest = TravelRequest::factory()->for($staff)->create(['status' => 'requested']);

        $this->patchJson("/api/admin/travel-requests/{$travelRequest->id}/approve");

        $this->assertDatabaseHas('notifications', [
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
            'request_id' => $travelRequest->id,
            'notification_type' => 'response_travel',
            'is_read' => false,
        ]);
    }

    public function test_disapproving_travel_request_creates_notification_for_staff(): void
    {
        $admin = $this->actingAsAdmin();
        $staff = User::factory()->create();
        $travelRequest = TravelRequest::factory()->for($staff)->create(['status' => 'requested']);

        $this->patchJson("/api/admin/travel-requests/{$travelRequest->id}/disapprove");

        $this->assertDatabaseHas('notifications', [
            'user_to_id' => $staff->id,
            'user_from_id' => $admin->id,
            'request_id' => $travelRequest->id,
            'notification_type' => 'response_travel',
            'is_read' => false,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_notifications(): void
    {
        $this->getJson('/api/notifications')->assertStatus(401);
        $this->getJson('/api/notifications/unread-count')->assertStatus(401);
        $this->getJson('/api/notifications/1')->assertStatus(401);
        $this->patchJson('/api/notifications/1/read')->assertStatus(401);
    }
}
