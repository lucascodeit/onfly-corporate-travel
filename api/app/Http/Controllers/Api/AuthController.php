<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): AuthResource
    {
        $data = $this->authService->login($request->validated());

        return new AuthResource($data);
    }

    public function refresh(): AuthResource
    {
        $data = $this->authService->refresh();

        return new AuthResource($data);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json(['message' => 'Successfully logged out.']);
    }
}
