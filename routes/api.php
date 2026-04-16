<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\GroupMemberController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes (public)
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes (require auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Group management
        Route::get('/groups', [GroupController::class, 'index']);
        Route::post('/groups', [GroupController::class, 'store']);
        Route::post('/groups/join', [GroupController::class, 'join']);

        // Group-scoped routes
        Route::middleware('ensure.group.member')->group(function () {
            Route::get('/groups/{group}', [GroupController::class, 'show']);
            Route::get('/groups/{group}/members', [GroupController::class, 'members']);
            Route::post('/groups/{group}/members/deactivate', [GroupMemberController::class, 'deactivate']);
            Route::post('/groups/{group}/members/reactivate', [GroupMemberController::class, 'reactivate']);
        });
    });
});
