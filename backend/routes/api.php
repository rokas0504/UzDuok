<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Points\PointTransactionController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Tasks\TaskController;
use App\Http\Controllers\Users\UserController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Task routes (Protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::get('/users/children', [UserController::class, 'getChildren']);

    // Shop routes
    Route::apiResource('shop', ShopController::class)->parameters(['shop' => 'shopItem']);
    Route::post('/shop/{shopItem}/purchase', [ShopController::class, 'purchase']);
        // Point transactions routes
    Route::get('/point-transactions', [PointTransactionController::class, 'index']);
    Route::get('/point-transactions/reasons', [PointTransactionController::class, 'reasons']);
    // return route
    Route::post('/tasks/{id}/return', [TaskController::class, 'returnTask']);
});
