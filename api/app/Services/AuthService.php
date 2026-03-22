<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->is_active) {
            throw new AccessDeniedHttpException('Account is inactive.');
        }

        $token = Auth::guard('api')->claims(['refreshable' => true])->attempt($credentials);

        if (! $token) {
            throw new UnauthorizedHttpException('jwt-auth', 'Invalid credentials.');
        }

        return $this->respondWithToken($token);
    }

    public function refresh(): array
    {
        $payload = JWTAuth::parseToken()->getPayload();

        if (! $payload->get('refreshable')) {
            throw new UnauthorizedHttpException('jwt-auth', 'Token cannot be refreshed.');
        }

        $newToken = Auth::guard('api')->claims(['refreshable' => false])->refresh();

        return $this->respondWithToken($newToken);
    }

    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    private function respondWithToken(string $token): array
    {
        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => Auth::guard('api')->user(),
        ];
    }
}
