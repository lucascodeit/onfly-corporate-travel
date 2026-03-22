<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class NotificationService
{
    public function listForUser(User $user, ?string $filter = null): LengthAwarePaginator
    {
        return Notification::query()
            ->where('user_to_id', $user->id)
            ->when($filter === 'unread', fn ($q) => $q->where('is_read', false))
            ->when($filter === 'read', fn ($q) => $q->where('is_read', true))
            ->with(['userFrom', 'travelRequest'])
            ->latest()
            ->paginate(5);
    }

    public function unreadCount(User $user): int
    {
        return Notification::query()
            ->where('user_to_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    public function show(User $user, Notification $notification): Notification
    {
        $this->verifyOwnership($user, $notification);

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return $notification->load(['userFrom', 'travelRequest']);
    }

    public function markAsRead(User $user, Notification $notification): Notification
    {
        $this->verifyOwnership($user, $notification);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $notification->load(['userFrom', 'travelRequest']);
    }

    public function createForStatusChange(User $admin, TravelRequest $travelRequest): Notification
    {
        $status = $travelRequest->status;
        $destination = $travelRequest->destination;

        return Notification::create([
            'user_to_id' => $travelRequest->user_id,
            'user_from_id' => $admin->id,
            'request_id' => $travelRequest->id,
            'notification_type' => 'response_travel',
            'message' => "Your travel request to {$destination} was {$status}.",
        ]);
    }

    private function verifyOwnership(User $user, Notification $notification): void
    {
        if ($notification->user_to_id !== $user->id) {
            throw new AccessDeniedHttpException('You can only access your own notifications.');
        }
    }
}
