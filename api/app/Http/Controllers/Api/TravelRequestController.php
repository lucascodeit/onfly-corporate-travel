<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\StoreTravelRequestRequest;
use App\Http\Resources\TravelRequestCollection;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelRequestController extends Controller
{
    public function __construct(private readonly TravelRequestService $travelRequestService) {}

    public function index(Request $request): TravelRequestCollection
    {
        return new TravelRequestCollection(
            $this->travelRequestService->listForUser(
                Auth::user(),
                $request->query('start_date'),
                $request->query('end_date'),
                $request->query('status'),
            )
        );
    }

    public function store(StoreTravelRequestRequest $request): JsonResponse
    {
        $travelRequest = $this->travelRequestService->create(Auth::user(), $request->validated());

        return (new TravelRequestResource($travelRequest->load('admin')))
            ->response()
            ->setStatusCode(201);
    }

    public function cancel(TravelRequest $travelRequest): TravelRequestResource
    {
        $travelRequest = $this->travelRequestService->cancel(Auth::user(), $travelRequest);

        return new TravelRequestResource($travelRequest->load('admin'));
    }
}
