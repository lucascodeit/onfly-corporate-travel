<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserService
{
    public function list(): LengthAwarePaginator
    {
        return User::query()->paginate();
    }

    public function find(int $id): User
    {
        return User::findOrFail($id);
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);

        return $user->fresh();
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->travelRequests()->exists()) {
            throw new ConflictHttpException('Cannot delete user with travel requests.');
        }

        $user->delete();
    }

    public function changePassword(int $id, string $password): User
    {
        $user = User::findOrFail($id);
        $user->update(['password' => Hash::make($password)]);

        return $user;
    }
}
