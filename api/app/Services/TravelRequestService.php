<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TravelRequestService
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function listForUser(User $user, ?string $startDate = null, ?string $endDate = null): LengthAwarePaginator
    {
        return TravelRequest::query()
            ->where('user_id', $user->id)
            ->when($startDate, fn ($q, $d) => $q->where('end_date', '>=', $d))
            ->when($endDate, fn ($q, $d) => $q->where('start_date', '<=', $d))
            ->with('admin')
            ->latest()
            ->paginate();
    }

    public function create(User $user, array $data): TravelRequest
    {
        return TravelRequest::create([
            ...$data,
            'user_id' => $user->id,
            'status' => 'requested',
        ]);
    }

    public function listAll(?int $userId = null, ?string $startDate = null, ?string $endDate = null): LengthAwarePaginator
    {
        return TravelRequest::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($startDate, fn ($q, $d) => $q->where('end_date', '>=', $d))
            ->when($endDate, fn ($q, $d) => $q->where('start_date', '<=', $d))
            ->with(['user', 'admin'])
            ->latest()
            ->paginate();
    }

    public function approve(User $admin, TravelRequest $travelRequest): TravelRequest
    {
        if ($travelRequest->status === 'cancelled') {
            throw new UnprocessableEntityHttpException('Cancelled requests cannot be approved.');
        }

        $travelRequest->update(['status' => 'approved', 'admin_id' => $admin->id]);

        $travelRequest = $travelRequest->fresh();

        $this->notificationService->createForStatusChange($admin, $travelRequest);

        return $travelRequest;
    }

    public function disapprove(User $admin, TravelRequest $travelRequest): TravelRequest
    {
        if ($travelRequest->status === 'cancelled') {
            throw new UnprocessableEntityHttpException('Cancelled requests cannot be disapproved.');
        }

        $travelRequest->update(['status' => 'disapproved', 'admin_id' => $admin->id]);

        $travelRequest = $travelRequest->fresh();

        $this->notificationService->createForStatusChange($admin, $travelRequest);

        return $travelRequest;
    }

    public function cancel(User $user, TravelRequest $travelRequest): TravelRequest
    {
        if ($travelRequest->user_id !== $user->id) {
            throw new AccessDeniedHttpException('You can only cancel your own travel requests.');
        }

        if ($travelRequest->status !== 'requested') {
            throw new UnprocessableEntityHttpException('Only requests with status "requested" can be cancelled.');
        }

        $travelRequest->update(['status' => 'cancelled']);

        return $travelRequest->fresh();
    }
}
