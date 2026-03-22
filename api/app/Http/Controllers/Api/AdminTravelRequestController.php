<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TravelRequestCollection;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTravelRequestController extends Controller
{
    public function __construct(private readonly TravelRequestService $travelRequestService) {}

    public function index(Request $request): TravelRequestCollection
    {
        return new TravelRequestCollection(
            $this->travelRequestService->listAll($request->query('user_id'))
        );
    }

    public function approve(TravelRequest $travelRequest): TravelRequestResource
    {
        $travelRequest = $this->travelRequestService->approve(Auth::user(), $travelRequest);

        return new TravelRequestResource($travelRequest->load(['user', 'admin']));
    }

    public function disapprove(TravelRequest $travelRequest): TravelRequestResource
    {
        $travelRequest = $this->travelRequestService->disapprove(Auth::user(), $travelRequest);

        return new TravelRequestResource($travelRequest->load(['user', 'admin']));
    }
}
