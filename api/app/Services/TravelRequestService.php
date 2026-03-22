<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TravelRequestService
{
    public function listForUser(User $user): LengthAwarePaginator
    {
        return TravelRequest::query()
            ->where('user_id', $user->id)
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
