<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationCollection;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(Request $request): NotificationCollection
    {
        return new NotificationCollection(
            $this->notificationService->listForUser(Auth::user(), $request->query('filter'))
        );
    }

    public function show(Notification $notification): NotificationResource
    {
        $notification = $this->notificationService->show(Auth::user(), $notification);

        return new NotificationResource($notification);
    }

    public function markAsRead(Notification $notification): NotificationResource
    {
        $notification = $this->notificationService->markAsRead(Auth::user(), $notification);

        return new NotificationResource($notification);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->notificationService->unreadCount(Auth::user()),
        ]);
    }
}
