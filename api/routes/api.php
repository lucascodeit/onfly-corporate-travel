<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AdminTravelRequestController;
use App\Http\Controllers\Api\TravelRequestController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/healthy', HealthController::class);

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'changePassword']);
    });

    Route::middleware('admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::put('/users/{user}/password', [UserController::class, 'changePassword']);

        Route::prefix('admin/travel-requests')->group(function () {
            Route::get('/', [AdminTravelRequestController::class, 'index']);
            Route::patch('/{travelRequest}/approve', [AdminTravelRequestController::class, 'approve']);
            Route::patch('/{travelRequest}/disapprove', [AdminTravelRequestController::class, 'disapprove']);
        });
    });

    Route::middleware('staff')->prefix('travel-requests')->group(function () {
        Route::get('/', [TravelRequestController::class, 'index']);
        Route::post('/', [TravelRequestController::class, 'store']);
        Route::patch('/{travelRequest}/cancel', [TravelRequestController::class, 'cancel']);
    });
});
