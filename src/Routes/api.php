<?php

use Illuminate\Support\Facades\Route;
use Kennofizet\PackagesCore\Controllers\AuthController;
use Kennofizet\PackagesCore\Controllers\ZoneController;

$apiPrefix = config('packages-core.api_prefix', 'api/rewardplay');
$rateLimit = config('packages-core.rate_limit', 60);

Route::prefix($apiPrefix)->middleware([
    'throttle:' . $rateLimit . ',1',
    \Kennofizet\PackagesCore\Middleware\ValidatorRequestMiddleware::class,
    \Kennofizet\PackagesCore\Middleware\ValidateRewardPlayToken::class,
])->group(function () {
    // Auth
    Route::get('/auth/check', [AuthController::class, 'checkUser']);

    // Zones — current user's zones (core logic only)
    Route::get('/player/zones', [ZoneController::class, 'index']);
});