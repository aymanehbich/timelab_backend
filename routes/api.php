<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GamificationController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['message' => 'API is running!'];
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
// User Profile Route
Route::get('/user', [UserController::class, 'profile'])->middleware('auth:sanctum');

// Gamification Routes
Route::middleware('auth:sanctum')->group(function () {

    // Stats & Leaderboard
    Route::get('/stats', [GamificationController::class, 'stats']);
    Route::get('/leaderboard', [GamificationController::class, 'leaderboard']);

    // Points
    Route::post('/award-points', [PointsController::class, 'award']);
    Route::get('/points-history', [PointsController::class, 'history']);

    // Badges
    Route::get('/user-badges', function () {
        return auth()->user()->badges;
    });

    // Levels
    Route::get('/level', [GamificationController::class, 'level']);
});