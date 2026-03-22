<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profileService) {}

    public function show(): UserResource
    {
        return new UserResource($this->profileService->getProfile());
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        return new UserResource($this->profileService->updateProfile($request->validated()));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->profileService->changePassword($request->validated());

        return response()->json(['message' => 'Password changed successfully.']);
    }
}
