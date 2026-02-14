<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GamificationController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ParkinsonController;
use App\Http\Controllers\TypingChallengeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PomodoroController;  // ← AJOUTER CETTE LIGNE

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
    Route::get('/gamification/badges', [\App\Http\Controllers\GamificationController::class, 'badges']);

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

    // Typing Challenge Routes
    Route::get('/parkinson/typing/texts', [TypingChallengeController::class, 'texts']);
    Route::post('/parkinson/typing/start', [TypingChallengeController::class, 'start']);
    Route::post('/parkinson/typing/complete', [TypingChallengeController::class, 'complete']);
    Route::get('/parkinson/typing/stats', [TypingChallengeController::class, 'stats']);
    Route::get('/parkinson/typing/history', [TypingChallengeController::class, 'history']);
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

// Eisenhower Matrix Learning Quiz
Route::middleware('auth:sanctum')->prefix('eisenhower')->group(function () {
    Route::get('/tasks', [\App\Http\Controllers\EisenhowerController::class, 'tasks']);
    Route::get('/next', [\App\Http\Controllers\EisenhowerController::class, 'next']);
    Route::post('/submit', [\App\Http\Controllers\EisenhowerController::class, 'submit']);
    Route::get('/progress', [\App\Http\Controllers\EisenhowerController::class, 'progress']);
    Route::post('/reset', [\App\Http\Controllers\EisenhowerController::class, 'reset']);
});