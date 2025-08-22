<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PointsController;
use App\Http\Controllers\Api\ProfilingController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Profile routes
    Route::get('profile', [ProfilingController::class, 'getProfile']);
    Route::get('profiling-questions', [ProfilingController::class, 'getQuestions']);
    Route::post('profile', [ProfilingController::class, 'updateProfile'])->middleware('throttle:profile');
    
    // Wallet routes
    Route::get('wallet', [WalletController::class, 'getWallet']);
    
    // Points routes
    Route::get('points/transactions', [PointsController::class, 'getTransactions']);
    Route::post('points/claim', [PointsController::class, 'claimPoints'])->middleware('throttle:points');
    
    // Stats routes
    Route::prefix('stats')->group(function () {
        Route::get('daily', [StatsController::class, 'getDailyStats']);
        Route::get('total', [StatsController::class, 'getTotalStats']);
        Route::get('{date}', [StatsController::class, 'getStatsForDate']);
        Route::post('{date}/refresh', [StatsController::class, 'refreshStatsForDate'])->middleware('throttle:admin');
    });
});
