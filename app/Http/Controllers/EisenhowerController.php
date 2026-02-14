<?php

namespace App\Http\Controllers;

use App\Models\EisenhowerTask;
use App\Models\EisenhowerAttempt;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EisenhowerController extends Controller
{
    private const UNLOCK_THRESHOLD = 70; // % correct needed to unlock next level

    // GET /api/eisenhower/tasks?level=1 — list predefined tasks, optionally filtered by level
    public function tasks(Request $request)
    {
        $query = EisenhowerTask::query();

        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        $tasks = $query->get(['id', 'title', 'description', 'quadrant', 'explanation', 'level']);
        return response()->json($tasks);
    }

    // GET /api/eisenhower/next?level=1 — get a random unattempted task for the user at a given level
    public function next(Request $request)
    {
        $request->validate([
            'level' => 'required|integer|between:1,3',
        ]);

        $level = (int) $request->level;
        $userId = Auth::id();

        // Check if level is unlocked
        if ($level > 1 && !$this->isLevelUnlocked($userId, $level)) {
            return response()->json([
                'message' => 'You need to pass level ' . ($level - 1) . ' first (70% correct).',
                'locked' => true,
            ], 403);
        }

        $attemptedIds = EisenhowerAttempt::where('user_id', $userId)
            ->pluck('eisenhower_task_id');

        $task = EisenhowerTask::where('level', $level)
            ->whereNotIn('id', $attemptedIds)
            ->inRandomOrder()
            ->first(['id', 'title', 'description', 'level']);

        if (!$task) {
            return response()->json([
                'message' => 'You have completed all tasks for level ' . $level . '!',
                'completed' => true,
                'level' => $level,
            ]);
        }

        return response()->json($task);
    }

    // POST /api/eisenhower/submit — user submits their quadrant choice
    public function submit(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:eisenhower_tasks,id',
            'chosen_quadrant' => 'required|integer|between:1,4',
        ]);

        $task = EisenhowerTask::findOrFail($request->task_id);
        $userId = Auth::id();

        // Check if level is unlocked
        if ($task->level > 1 && !$this->isLevelUnlocked($userId, $task->level)) {
            return response()->json([
                'message' => 'You need to pass level ' . ($task->level - 1) . ' first.',
                'locked' => true,
            ], 403);
        }

        // Prevent duplicate attempts
        $existing = EisenhowerAttempt::where('user_id', $userId)
            ->where('eisenhower_task_id', $task->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already answered this task.',
                'is_correct' => $existing->is_correct,
                'correct_quadrant' => $task->quadrant,
                'explanation' => $task->explanation,
                'points_earned' => $existing->points_earned,
            ], 409);
        }

        $isCorrect = $request->chosen_quadrant == $task->quadrant;
        $pointsEarned = 0;

        if ($isCorrect) {
            $pointsMap = [1 => 10, 2 => 10, 3 => 10, 4 => 10];
            $pointsEarned = $pointsMap[$task->quadrant];

            app(PointsService::class)->award(Auth::user(), $pointsEarned);
        }

        EisenhowerAttempt::create([
            'user_id' => $userId,
            'eisenhower_task_id' => $task->id,
            'chosen_quadrant' => $request->chosen_quadrant,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
        ]);

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_quadrant' => $task->quadrant,
            'chosen_quadrant' => (int) $request->chosen_quadrant,
            'explanation' => $task->explanation,
            'points_earned' => $pointsEarned,
            'level' => $task->level,
        ]);
    }

    // GET /api/eisenhower/progress?level=1 — user's quiz progress (per level or overall)
    public function progress(Request $request)
    {
        $userId = Auth::id();

        $levels = [];
        for ($lvl = 1; $lvl <= 3; $lvl++) {
            $taskIds = EisenhowerTask::where('level', $lvl)->pluck('id');
            $totalTasks = $taskIds->count();
            $attempted = EisenhowerAttempt::where('user_id', $userId)->whereIn('eisenhower_task_id', $taskIds)->count();
            $correct = EisenhowerAttempt::where('user_id', $userId)->whereIn('eisenhower_task_id', $taskIds)->where('is_correct', true)->count();
            $points = (int) EisenhowerAttempt::where('user_id', $userId)->whereIn('eisenhower_task_id', $taskIds)->sum('points_earned');
            $percentage = $attempted > 0 ? round(($correct / $attempted) * 100, 1) : 0;

            $levels[] = [
                'level' => $lvl,
                'total_tasks' => $totalTasks,
                'attempted' => $attempted,
                'correct' => $correct,
                'incorrect' => $attempted - $correct,
                'remaining' => $totalTasks - $attempted,
                'score_percentage' => $percentage,
                'total_points_earned' => $points,
                'unlocked' => $lvl === 1 || $this->isLevelUnlocked($userId, $lvl),
                'passed' => $percentage >= self::UNLOCK_THRESHOLD && $attempted === $totalTasks,
            ];
        }

        $totalAttempted = EisenhowerAttempt::where('user_id', $userId)->count();
        $totalCorrect = EisenhowerAttempt::where('user_id', $userId)->where('is_correct', true)->count();
        $totalPoints = (int) EisenhowerAttempt::where('user_id', $userId)->sum('points_earned');

        return response()->json([
            'overall' => [
                'total_tasks' => EisenhowerTask::count(),
                'attempted' => $totalAttempted,
                'correct' => $totalCorrect,
                'incorrect' => $totalAttempted - $totalCorrect,
                'score_percentage' => $totalAttempted > 0 ? round(($totalCorrect / $totalAttempted) * 100, 1) : 0,
                'total_points_earned' => $totalPoints,
            ],
            'levels' => $levels,
        ]);
    }

    // POST /api/eisenhower/reset — reset user's progress
    public function reset()
    {
        $userId = auth()->id();
        EisenhowerAttempt::where('user_id', $userId)->delete();
        return response()->json(['message' => 'Progress reset successfully.']);
    }

    // Check if a level is unlocked for a user
    private function isLevelUnlocked(int $userId, int $level): bool
    {
        if ($level <= 1) return true;

        $prevLevel = $level - 1;
        $prevTaskIds = EisenhowerTask::where('level', $prevLevel)->pluck('id');
        $totalPrev = $prevTaskIds->count();

        if ($totalPrev === 0) return true;

        $attemptedPrev = EisenhowerAttempt::where('user_id', $userId)
            ->whereIn('eisenhower_task_id', $prevTaskIds)
            ->count();

        // Must attempt all tasks in previous level
        if ($attemptedPrev < $totalPrev) return false;

        $correctPrev = EisenhowerAttempt::where('user_id', $userId)
            ->whereIn('eisenhower_task_id', $prevTaskIds)
            ->where('is_correct', true)
            ->count();

        $percentage = ($correctPrev / $totalPrev) * 100;

        return $percentage >= self::UNLOCK_THRESHOLD;
    }
}
