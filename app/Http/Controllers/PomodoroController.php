<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use App\Models\PomodoroStreak;
use App\Services\PomodoroStreakService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PomodoroController extends Controller
{
    // ═══════════════════════════════════════
    // POST /api/pomodoro/start
    // ═══════════════════════════════════════
    public function start(Request $request): JsonResponse
    {
        // ── DEBUG 1 : est-ce que la requête arrive bien ? ──
        Log::info('🍅 [START] Requête reçue', [
            'user_id'   => auth()->id(),        // null = token invalide
            'body'      => $request->all(),     // ce que le frontend envoie
            'token'     => $request->bearerToken() ? 'présent' : 'MANQUANT ❌',
        ]);

        // ── DEBUG 2 : si user_id est null → problème de token ──
        if (!auth()->id()) {
            Log::error('❌ [START] user non authentifié - token invalide');
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        // ── Validation ──
        $validated = $request->validate([
            'task_name' => 'nullable|string|max:80',
            'type'      => 'required|in:work,break,long_break',
            'duration'  => 'required|integer|min:1|max:60',
        ]);

        Log::info('✅ [START] Validation passée', $validated);

        // ── Création session ──
        try {
            $session = PomodoroSession::create([
                'user_id'      => auth()->id(),
                'task_name'    => $request->task_name,
                'type'         => $request->type,
                'duration'     => $request->duration,
                'started_at'   => now(),
                'completed'    => false,
                'interruptions'=> 0,
            ]);

            Log::info('✅ [START] Session créée en base', [
                'session_id' => $session->id,
                'task_name'  => $session->task_name,
                'type'       => $session->type,
                'user_id'    => $session->user_id,
            ]);

            return response()->json([
                'session_id' => $session->id,
                'started_at' => $session->started_at,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [START] Erreur création session', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════
    // POST /api/pomodoro/complete
    // ═══════════════════════════════════════
    public function complete(Request $request): JsonResponse
    {
        Log::info('🍅 [COMPLETE] Requête reçue', [
            'user_id'    => auth()->id(),
            'session_id' => $request->session_id,
        ]);

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

        Log::info('✅ [COMPLETE] Session marquée complétée', [
            'session_id' => $session->id,
            'type'       => $session->type,
        ]);

        $result = ['message' => 'Session complétée'];

        if ($session->type === 'work') {
            try {
                $streakData = app(PomodoroStreakService::class)
                                ->update(auth()->id());

                Log::info('✅ [COMPLETE] Streak mis à jour', $streakData);
                $result = array_merge($result, $streakData);

            } catch (\Exception $e) {
                // ── DEBUG : si le service plante (ex: colonne points manquante) ──
                Log::error('❌ [COMPLETE] Erreur StreakService', [
                    'message' => $e->getMessage(),
                ]);
                // Retourner quand même une réponse sans les points
                $result = array_merge($result, [
                    'points_earned'  => 25,
                    'multiplier'     => 1.0,
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'new_badge'      => null,
                    'streak_error'   => $e->getMessage(),
                ]);
            }
        }

        return response()->json($result);
    }

    // ═══════════════════════════════════════
    // POST /api/pomodoro/interrupt
    // ═══════════════════════════════════════
    public function interrupt(Request $request): JsonResponse
    {
        Log::info('🍅 [INTERRUPT] Requête reçue', [
            'user_id'    => auth()->id(),
            'session_id' => $request->session_id,
        ]);

        $request->validate([
            'session_id' => 'required|integer|exists:pomodoro_sessions,id',
        ]);

        try {
            $session = PomodoroSession::where('id', $request->session_id)
                                      ->where('user_id', auth()->id())
                                      ->firstOrFail();

            $session->increment('interruptions');
            $session->update(['completed_at' => now()]);

            Log::info('✅ [INTERRUPT] Session interrompue', [
                'session_id'   => $session->id,
                'interruptions'=> $session->fresh()->interruptions,
            ]);

            return response()->json([
                'message'      => 'Session interrompue',
                'interruptions'=> $session->fresh()->interruptions,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [INTERRUPT] Erreur', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════
    // GET /api/pomodoro/sessions/today
    // ═══════════════════════════════════════
    public function today(): JsonResponse
    {
        $sessions = PomodoroSession::where('user_id', auth()->id())
            ->where('type', 'work')
            ->whereDate('started_at', today())
            ->orderBy('started_at')
            ->get();

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
    // ═══════════════════════════════════════
    public function sessions(Request $request): JsonResponse
    {
        $query = PomodoroSession::where('user_id', auth()->id())
            ->where('type', 'work')
            ->orderByDesc('started_at');

        if ($request->has('date')) {
            $query->whereDate('started_at', $request->date);
        }
        if ($request->has('task')) {
            $query->where('task_name', 'like', '%' . $request->task . '%');
        }

        return response()->json($query->paginate(20));
    }

    // ═══════════════════════════════════════
    // GET /api/pomodoro/streaks
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
    // ═══════════════════════════════════════
    public function stats(): JsonResponse
    {
        $userId = auth()->id();

        $totalSessions = PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')->where('completed', true)->count();

        $totalMinutes = PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')->where('completed', true)->sum('duration');

        $totalStarted = PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')->count();

        $completionRate = $totalStarted > 0
            ? round(($totalSessions / $totalStarted) * 100) : 0;

        $taskStats = PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')->where('completed', true)
            ->selectRaw('task_name, COUNT(*) as sessions, SUM(duration) as total_minutes')
            ->groupBy('task_name')->orderByDesc('sessions')->limit(10)->get();

        $weeklyStats = PomodoroSession::where('user_id', $userId)
            ->where('type', 'work')->where('completed', true)
            ->where('started_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(started_at) as date, COUNT(*) as sessions')
            ->groupBy('date')->orderBy('date')->get();

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