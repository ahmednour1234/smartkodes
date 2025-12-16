<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\FormController;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Route;

// API v1 routes
Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('/reset-password', [UserController::class, 'resetPassword']);

    // Protected routes (JWT authentication)
    Route::middleware('auth:api')->group(function () {
        // Authentication routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'user']);

        // User routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/me', [UserController::class, 'me']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::post('/set-passcode', [UserController::class, 'setPasscode']);
            Route::post('/verify-passcode', [UserController::class, 'verifyPasscode']);
        });

        // Work Order routes
        Route::prefix('work-orders')->group(function () {
            Route::get('/', [WorkOrderController::class, 'index']);
            Route::get('/{id}', [WorkOrderController::class, 'show']);
            Route::get('/{id}/map', [WorkOrderController::class, 'getMapUrl']);
            Route::get('/{id}/directions', [WorkOrderController::class, 'getDirectionsUrl']);
            Route::get('/{workOrder}/forms/{form}', [WorkOrderController::class, 'getForm']);
            Route::post('/{workOrder}/submit-form', [WorkOrderController::class, 'submitForm']);
        });

        // Form routes
        Route::prefix('forms')->group(function () {
            Route::get('/', [FormController::class, 'index']);
            Route::get('/{id}', [FormController::class, 'show']);
            Route::put('/{form}/records/{record}', [FormController::class, 'updateFormData']);
        });
    });
});

// Test route to verify API is working
Route::get('/test', function () {
    $responseService = app(ApiResponseService::class);
    return $responseService->success(null, 'API is working');
});
