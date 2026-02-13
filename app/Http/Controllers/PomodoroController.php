<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use App\Models\PomodoroStreak;
use App\Services\PomodoroStreakService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PomodoroController extends Controller
{
    // ═══════════════════════════════════════
    // POST /api/pomodoro/start
    // Appelé quand l'user clique "start"
    // ═══════════════════════════════════════
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'task_name' => 'nullable|string|max:80',
            'type'      => 'required|in:work,break,long_break',
            'duration'  => 'required|integer|min:1|max:60',
        ]);

        $session = PomodoroSession::create([
            'user_id'      => auth()->id(),
            'task_name'    => $request->task_name,
            'type'         => $request->type,
            'duration'     => $request->duration,
            'started_at'   => now(),
            'completed'    => false,
            'interruptions'=> 0,
        ]);

        return response()->json([
            'session_id' => $session->id,
            'started_at' => $session->started_at,
        ]);
    }

    // ═══════════════════════════════════════
    // POST /api/pomodoro/complete
    // Appelé quand le timer arrive à 0
    // ═══════════════════════════════════════
    public function complete(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|integer|exists:pomodoro_sessions,id',
        ]);

        $session = PomodoroSession::where('id', $request->session_id)
                                  ->where('user_id', auth()->id())
                                  ->firstOrFail();

        $session->update([
            'completed'    => true,
            'completed_at' => now(),
        ]);

        // Streak + points seulement pour les sessions "work"
        $result = ['message' => 'Session complétée'];

        if ($session->type === 'work') {
            $streakData = app(PomodoroStreakService::class)
                            ->update(auth()->id());

            $result = array_merge($result, $streakData);
            // streakData contient :
            // points_earned, multiplier, current_streak,
            // longest_streak, new_badge
        }

        return response()->json($result);
    }

    // ═══════════════════════════════════════
    // POST /api/pomodoro/interrupt
    // Appelé quand l'user clique reset
    // ═══════════════════════════════════════
    public function interrupt(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|integer|exists:pomodoro_sessions,id',
        ]);

        $session = PomodoroSession::where('id', $request->session_id)
                                  ->where('user_id', auth()->id())
                                  ->firstOrFail();

        $session->increment('interruptions');
        $session->update(['completed_at' => now()]);

        return response()->json([
            'message'      => 'Session interrompue',
            'interruptions'=> $session->fresh()->interruptions,
        ]);
    }

    // ═══════════════════════════════════════
    // GET /api/pomodoro/sessions/today
    // Sessions du jour groupées par tâche
    // ═══════════════════════════════════════
    public function today(): JsonResponse
    {
        $sessions = PomodoroSession::where('user_id', auth()->id())
            ->where('type', 'work')
            ->whereDate('started_at', today())
            ->orderBy('started_at')
            ->get();

        // Grouper par task_name
        $grouped = $sessions->groupBy('task_name')
            ->map(function ($group) {
                return [
                    'task_name'          => $group->first()->task_name ?? 'Sans nom',
                    'total_sessions'     => $group->count(),
                    'completed_sessions' => $group->where('completed', true)->count(),
                    'total_minutes'      => $group->where('completed', true)->sum('duration'),
                    'sessions'           => $group->values(),
                ];
            })->values();

        return response()->json([
            'tasks'          => $grouped,
            'total_sessions' => $sessions->where('completed', true)->count(),
            'total_minutes'  => $sessions->where('completed', true)->sum('duration'),
        ]);
    }

    // ═══════════════════════════════════════
    // GET /api/pomodoro/sessions
    // Historique complet (avec filtre date)
    // ═══════════════════════════════════════
    public function sessions(Request $request): JsonResponse
    {
        $query = PomodoroSession::where('user_id', auth()->id())
            ->where('type', 'work')
            ->orderByDesc('started_at');

        // Filtre optionnel par date
        if ($request->has('date')) {
            $query->whereDate('started_at', $request->date);
        }

        // Filtre optionnel par task_name
        if ($request->has('task')) {
            $query->where('task_name', 'like', '%' . $request->task . '%');
        }

        $sessions = $query->paginate(20);

        return response()->json($sessions);
    }

    // ═══════════════════════════════════════
    // GET /api/pomodoro/streaks
    // Streak actuel de l'user
    // ═══════════════════════════════════════
    public function streaks(): JsonResponse
    {
        $streak = PomodoroStreak::firstOrCreate(
            ['user_id' => auth()->id()],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        return response()->json([
            'current_streak'    => $streak->current_streak,
            'longest_streak'    => $streak->longest_streak,
            'last_session_date' => $streak->last_session_date,
            'is_active'         => $streak->is_active,
        ]);
    }

    // ═══════════════════════════════════════
    // GET /api/pomodoro/stats
    // Statistiques globales
    // ═══════════════════════════════════════
    public function stats(): JsonResponse
    {
        $userId = auth()->id();

        // Total sessions complétées
        $totalSessions = PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')
            ->where('completed', true)
            ->count();

        // Total minutes de focus
        $totalMinutes = PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')
            ->where('completed', true)
            ->sum('duration');

        // Taux de complétion
        $totalStarted = PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')
            ->count();

        $completionRate = $totalStarted > 0
            ? round(($totalSessions / $totalStarted) * 100)
            : 0;

        // Stats par tâche
        $taskStats = PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')
            ->where('completed', true)
            ->selectRaw('
                task_name,
                COUNT(*) as sessions,
                SUM(duration) as total_minutes
            ')
            ->groupBy('task_name')
            ->orderByDesc('sessions')
            ->limit(10)
            ->get();

        // Sessions des 7 derniers jours (pour graphique)
        $weeklyStats = PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')
            ->where('completed', true)
            ->where('started_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(started_at) as date, COUNT(*) as sessions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'total_sessions'  => $totalSessions,
            'total_minutes'   => $totalMinutes,
            'total_hours'     => round($totalMinutes / 60, 1),
            'completion_rate' => $completionRate,
            'task_stats'      => $taskStats,
            'weekly_stats'    => $weeklyStats,
        ]);
    }
}