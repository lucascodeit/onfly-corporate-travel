<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangeUserPasswordRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(): UserCollection
    {
        return new UserCollection($this->userService->list());
    }

    public function store(StoreUserRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $user): UserResource
    {
        return new UserResource($this->userService->find($user));
    }

    public function update(UpdateUserRequest $request, int $user): UserResource
    {
        return new UserResource($this->userService->update($user, $request->validated()));
    }

    public function destroy(int $user): JsonResponse
    {
        $this->userService->delete($user);

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function changePassword(ChangeUserPasswordRequest $request, int $user): JsonResponse
    {
        $this->userService->changePassword($user, $request->validated()['password']);

        return response()->json(['message' => 'Password changed successfully.']);
    }
}
