<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GamificationController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ParkinsonController;
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

// Parkinson's Law Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/parkinson/challenges', [ParkinsonController::class, 'index']);
    Route::post('/parkinson/challenges', [ParkinsonController::class, 'store']);
    Route::post('/parkinson/challenges/{id}/start', [ParkinsonController::class, 'start']);
    Route::post('/parkinson/challenges/{id}/complete', [ParkinsonController::class, 'complete']);
    Route::get('/parkinson/accuracy', [ParkinsonController::class, 'accuracy']);
    Route::get('/parkinson/stats', [ParkinsonController::class, 'stats']);
});

//Pomodoro

Route::middleware('auth:sanctum')->prefix('pomodoro')->group(function () {

    // Actions timer
    Route::post('/start',      [PomodoroController::class, 'start']);
    Route::post('/complete',   [PomodoroController::class, 'complete']);
    Route::post('/interrupt',  [PomodoroController::class, 'interrupt']);

    // Données
    Route::get('/sessions',         [PomodoroController::class, 'sessions']);
    Route::get('/sessions/today',   [PomodoroController::class, 'today']);
    Route::get('/streaks',          [PomodoroController::class, 'streaks']);
    Route::get('/stats',            [PomodoroController::class, 'stats']);
});