<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\PlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/memberships', [MembershipController::class, 'index']);
    Route::get('/memberships/{membership}', [MembershipController::class, 'show']);

    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/members', [MemberController::class, 'index']);
        Route::get('/members/{member}', [MemberController::class, 'show']);
        Route::patch('/members/{member}', [MemberController::class, 'update']);
        Route::post('/plans', [PlanController::class, 'store']);
        Route::match(['put', 'patch'], '/plans/{plan}', [PlanController::class, 'update']);
        Route::post('/members/{member}/memberships', [MembershipController::class, 'enroll']);
        Route::patch('/memberships/{membership}/cancel', [MembershipController::class, 'cancel']);
        Route::post('/members/{member}/check-ins', [CheckInController::class, 'store']);
        Route::get('/check-ins', [CheckInController::class, 'index']);
        Route::get('/dashboard', DashboardController::class);
    });
});
