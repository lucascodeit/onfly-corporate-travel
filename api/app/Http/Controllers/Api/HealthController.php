<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HealthResource;
use App\Services\HealthService;

class HealthController extends Controller
{
    public function __construct(
        private readonly HealthService $healthService,
    ) {}

    public function __invoke(): HealthResource
    {
        return new HealthResource($this->healthService->check());
    }
}
