<?php

use Illuminate\Support\Facades\Route;
use Kennofizet\PackagesCore\Controllers\AuthController;
use Kennofizet\PackagesCore\Controllers\ZoneController;
use Kennofizet\PackagesCore\Middleware\ValidateRewardPlayToken;
use Kennofizet\PackagesCore\Middleware\ValidatorRequestMiddleware;
use Kennofizet\PackagesCore\Middleware\EnsureUserIsManager;

$apiPrefix = config('packages-core.api_prefix', 'api/rewardplay');
$rateLimit = config('packages-core.rate_limit', 60);

// Protected routes (token required)
Route::prefix($apiPrefix)->middleware([
    'api',
    'throttle:' . $rateLimit . ',1',
    ValidateRewardPlayToken::class,
    ValidatorRequestMiddleware::class,
])->group(function () {
    // Auth
    Route::get('/auth/check', [AuthController::class, 'checkUser']);

    // Player: zones the user belongs to
    Route::get('/player/zones', [ZoneController::class, 'index']);

    // Player: zones the user can manage
    Route::get('/player/managed-zones', [ZoneController::class, 'managed']);
});

// Protected routes (token + manager required)
Route::prefix($apiPrefix)->middleware([
    'api',
    'throttle:' . $rateLimit . ',1',
    ValidateRewardPlayToken::class,
    ValidatorRequestMiddleware::class,
    EnsureUserIsManager::class,
])->group(function () {
    // Zone CRUD
    Route::get('/zones', [ZoneController::class, 'list']);
    Route::post('/zones', [ZoneController::class, 'store']);
    Route::patch('/zones/{id}', [ZoneController::class, 'update']);
    Route::put('/zones/{id}', [ZoneController::class, 'update']);
    Route::delete('/zones/{id}', [ZoneController::class, 'destroy']);

    // Zone user assignment
    Route::get('/zones/{id}/users', [ZoneController::class, 'users']);
    Route::post('/zones/{id}/users', [ZoneController::class, 'assignUser']);
    Route::delete('/zones/{id}/users/{userId}', [ZoneController::class, 'removeUser']);
});