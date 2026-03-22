<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function getProfile(): User
    {
        return Auth::user();
    }

    public function updateProfile(array $data): User
    {
        $user = Auth::user();
        $user->update($data);

        return $user->fresh();
    }

    public function changePassword(array $data): void
    {
        $user = Auth::user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update(['password' => Hash::make($data['new_password'])]);
    }
}
