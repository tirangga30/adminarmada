<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/login', [ApiController::class, 'login']);
    Route::get('/config', [ApiController::class, 'systemConfig']);

    // Protected API routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/profile', [ApiController::class, 'profile']);
        Route::post('/auth/logout', [ApiController::class, 'logout']);

        // Driver mobile endpoints
        Route::get('/driver/tasks', [ApiController::class, 'driverTasks']);
        Route::post('/driver/tasks/{id}/status', [ApiController::class, 'driverUpdateTaskStatus']);
        Route::post('/driver/tasks/{id}/pod', [ApiController::class, 'driverSubmitPod']);
        Route::post('/driver/emergency', [ApiController::class, 'driverEmergency']);

        // GPS location stream from smartphone
        Route::post('/tracking/location', [ApiController::class, 'trackingSubmitLocation']);
    });
});
