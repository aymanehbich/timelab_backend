<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\TypingChallenge;
use App\Models\TypingText;
use App\Services\PointsService;
use Illuminate\Http\Request;

class TypingChallengeController extends Controller
{
    public function __construct(private PointsService $pointsService) {}

    /**
     * GET /api/parkinson/typing/texts
     * Returns available texts grouped by level
     */
    public function texts(Request $request)
    {
        $level = $request->query('level');

        $query = TypingText::select('id', 'level', 'title', 'word_count', 'time_limit');

        if ($level) {
            $query->where('level', $level);
        }

        $texts = $query->get()->groupBy('level');

        return response()->json([
            'data' => [
                'texts' => $texts,
                'levels' => ['beginner', 'intermediate', 'expert'],
            ]
        ]);
    }

    /**
     * POST /api/parkinson/typing/start
     * Start a typing challenge — returns the text and timer
     * Body: { level, text_id? }
     */
    public function start(Request $request)
    {
        $request->validate([
            'level' => 'required|in:beginner,intermediate,expert',
            'text_id' => 'nullable|integer|exists:typing_texts,id',
        ]);

        $user = $request->user();

        // Abandon any incomplete challenges so the user always gets a fresh text
        // at the level they actually selected (prevents the wrong-level bug).
        TypingChallenge::where('user_id', $user->id)
            ->where('completed', false)
            ->whereNull('time_used')
            ->delete();

        // Pick text
        if ($request->text_id) {
            $text = TypingText::findOrFail($request->text_id);
        } else {
            $text = TypingText::where('level', $request->level)->inRandomOrder()->first();
        }

        if (!$text) {
            return response()->json(['message' => 'Aucun texte disponible pour ce niveau.'], 404);
        }

        $challenge = TypingChallenge::create([
            'user_id'      => $user->id,
            'typing_text_id' => $text->id,
            'level'        => $text->level,
            'text_content' => $text->content,
            'time_limit'   => $text->time_limit,
            'total_words'  => $text->word_count,
            'words_typed'  => 0,
            'completed'    => false,
            'finished_before_timer' => false,
        ]);

        return response()->json([
            'data' => [
                'challenge_id' => $challenge->id,
                'text_content' => $text->content,
                'time_limit'   => $text->time_limit,
                'word_count'   => $text->word_count,
                'level'        => $text->level,
                'title'        => $text->title,
            ]
        ], 201);
    }

    /**
     * POST /api/parkinson/typing/complete
     * Receive results and award points
     * Body: { challenge_id, words_typed, total_words, accuracy_percentage, words_per_minute, time_used }
     */
    public function complete(Request $request)
    {
        $request->validate([
            'challenge_id'       => 'required|integer|exists:typing_challenges,id',
            'words_typed'        => 'required|integer|min:0',
            'total_words'        => 'required|integer|min:1',
            'accuracy_percentage'=> 'required|numeric|min:0|max:100',
            'words_per_minute'   => 'required|numeric|min:0',
            'time_used'          => 'required|integer|min:0',
        ]);

        $user = $request->user();

        $challenge = TypingChallenge::where('id', $request->challenge_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($challenge->completed) {
            return response()->json(['message' => 'Ce défi est déjà complété.'], 422);
        }

        $completed           = $request->words_typed >= $request->total_words;
        // "finished before timer" only counts when ALL words were typed AND time was not exhausted
        $finishedBeforeTimer = $completed && $request->time_used < $challenge->time_limit;

        // Time remaining percentage (used for bonuses)
        $timeRemaining    = $challenge->time_limit - $request->time_used;
        $timeRemainingPct = $challenge->time_limit > 0 ? ($timeRemaining / $challenge->time_limit) * 100 : 0;

        // Check personal record WPM (before saving current result)
        $bestWpm = TypingChallenge::where('user_id', $user->id)
            ->where('completed', true)
            ->where('level', $challenge->level)
            ->max('words_per_minute') ?? 0;

        $isNewRecord = $completed && $request->words_per_minute > 0 && $request->words_per_minute > $bestWpm;

        // ── Points calculation ────────────────────────────────────────────
        $points = 0;
        $bonuses = [];

        if ($completed && $finishedBeforeTimer) {
            $points += 50;
            $bonuses[] = ['label' => 'Terminé avant le timer', 'value' => 50];
        }

        if ($completed && $request->accuracy_percentage == 100) {
            $points += 30;
            $bonuses[] = ['label' => 'Précision parfaite 100%', 'value' => 30];
        } elseif ($completed && $request->accuracy_percentage >= 95) {
            $points += 20;
            $bonuses[] = ['label' => 'Précision 95-99%', 'value' => 20];
        }

        if ($completed && $finishedBeforeTimer && $timeRemainingPct >= 30) {
            $points += 40;
            $bonuses[] = ['label' => 'Terminé avec 30%+ de temps restant', 'value' => 40];
        }

        if ($isNewRecord) {
            $points += 60;
            $bonuses[] = ['label' => 'Nouveau record WPM !', 'value' => 60];
        }

        // Update challenge (including points_earned)
        $challenge->update([
            'words_typed'          => $request->words_typed,
            'total_words'          => $request->total_words,
            'accuracy_percentage'  => $request->accuracy_percentage,
            'words_per_minute'     => $request->words_per_minute,
            'time_used'            => $request->time_used,
            'completed'            => $completed,
            'finished_before_timer'=> $finishedBeforeTimer,
            'points_earned'        => $points,
        ]);

        // Award points
        $this->pointsService->award($user, $points);

        // Award typing badges
        $earnedBadges = $this->awardTypingBadges($user, $challenge, $request, $completed, $finishedBeforeTimer, $timeRemainingPct);

        // Parkinson lesson based on performance
        $lesson = $this->getParkinsonLesson(
            $request->accuracy_percentage,
            $finishedBeforeTimer,
            $request->words_per_minute,
            $request->time_used,
            $challenge->time_limit
        );

        return response()->json([
            'data' => [
                'points_earned'   => $points,
                'bonuses'         => $bonuses,
                'is_new_record'   => $isNewRecord,
                'best_wpm'        => $isNewRecord ? $request->words_per_minute : $bestWpm,
                'earned_badges'   => $earnedBadges,
                'parkinson_lesson'=> $lesson,
                'stats' => [
                    'accuracy'    => $request->accuracy_percentage,
                    'wpm'         => $request->words_per_minute,
                    'time_used'   => $request->time_used,
                    'time_limit'  => $challenge->time_limit,
                    'completed'   => $completed,
                    'finished_before_timer' => $finishedBeforeTimer,
                ],
            ]
        ]);
    }

    /**
     * GET /api/parkinson/typing/stats
     * Personal statistics: average WPM, best accuracy, total challenges, WPM evolution
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        $challenges = TypingChallenge::where('user_id', $user->id)
            ->where('completed', true)
            ->get();

        $total = TypingChallenge::where('user_id', $user->id)->count();

        $averageWpm = $challenges->avg('words_per_minute') ?? 0;
        $bestWpm    = $challenges->max('words_per_minute') ?? 0;
        $bestAccuracy = $challenges->max('accuracy_percentage') ?? 0;
        $averageAccuracy = $challenges->avg('accuracy_percentage') ?? 0;

        // WPM evolution: last 10 completed challenges
        $wpmEvolution = TypingChallenge::where('user_id', $user->id)
            ->where('completed', true)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'words_per_minute', 'accuracy_percentage', 'level', 'created_at'])
            ->reverse()
            ->values();

        // By level stats
        $byLevel = [];
        foreach (['beginner', 'intermediate', 'expert'] as $level) {
            $levelChallenges = $challenges->where('level', $level);
            $byLevel[$level] = [
                'count'            => $levelChallenges->count(),
                'average_wpm'      => round($levelChallenges->avg('words_per_minute') ?? 0, 1),
                'best_wpm'         => round($levelChallenges->max('words_per_minute') ?? 0, 1),
                'average_accuracy' => round($levelChallenges->avg('accuracy_percentage') ?? 0, 1),
            ];
        }

        return response()->json([
            'data' => [
                'total_challenges' => $total,
                'completed_challenges' => $challenges->count(),
                'average_wpm'      => round($averageWpm, 1),
                'best_wpm'         => round($bestWpm, 1),
                'best_accuracy'    => round($bestAccuracy, 1),
                'average_accuracy' => round($averageAccuracy, 1),
                'wpm_evolution'    => $wpmEvolution,
                'by_level'         => $byLevel,
            ]
        ]);
    }

    /**
     * GET /api/parkinson/typing/history
     * All completed challenges for the authenticated user, newest first
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $history = TypingChallenge::where('user_id', $user->id)
            ->where('completed', true)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get([
                'id',
                'level',
                'accuracy_percentage',
                'words_per_minute',
                'time_used',
                'time_limit',
                'finished_before_timer',
                'points_earned',
                'total_words',
                'words_typed',
                'created_at',
            ]);

        return response()->json(['data' => ['history' => $history]]);
    }

    /**
     * Check conditions and award typing-specific badges to the user.
     * Returns array of newly awarded badges (name, icon, description).
     */
    private function awardTypingBadges(
        $user,
        TypingChallenge $challenge,
        Request $request,
        bool $completed,
        bool $finishedBeforeTimer,
        float $timeRemainingPct
    ): array {
        if (!$completed) return [];

        $user->load('badges');
        $ownedKeys = $user->badges->pluck('condition_key')->filter()->toArray();

        $toCheck = [];

        // 1. First Keystroke — first completed typing challenge
        $completedCount = TypingChallenge::where('user_id', $user->id)
            ->where('completed', true)
            ->count();
        if ($completedCount === 1) {
            $toCheck[] = 'typing_first';
        }

        // 2. Speed Demon — > 60 WPM
        if ($request->words_per_minute > 60) {
            $toCheck[] = 'typing_speed_demon';
        }

        // 3. Perfectionist — 100% accuracy
        if ($request->accuracy_percentage == 100) {
            $toCheck[] = 'typing_perfectionist';
        }

        // 4. Parkinson Slayer — finish with 40%+ time remaining
        if ($finishedBeforeTimer && $timeRemainingPct >= 40) {
            $toCheck[] = 'typing_parkinson_slayer';
        }

        // 5. Speed Improver — 20% WPM improvement across last 5 completed challenges
        $last5 = TypingChallenge::where('user_id', $user->id)
            ->where('completed', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->pluck('words_per_minute');

        if ($last5->count() === 5) {
            $oldest = $last5->last();
            $newest = $last5->first();
            if ($oldest > 0 && $newest >= $oldest * 1.2) {
                $toCheck[] = 'typing_speed_improver';
            }
        }

        // Award badges not already owned
        $earned = [];
        foreach ($toCheck as $key) {
            if (!in_array($key, $ownedKeys)) {
                $badge = Badge::where('condition_key', $key)->first();
                if ($badge) {
                    $user->badges()->attach($badge->id);
                    $earned[] = [
                        'name'        => $badge->name,
                        'icon'        => $badge->icon,
                        'description' => $badge->description,
                    ];
                }
            }
        }

        return $earned;
    }

    /**
     * Returns a contextual Parkinson's Law lesson based on the user's performance
     */
    private function getParkinsonLesson(
        float $accuracy,
        bool $finishedBeforeTimer,
        float $wpm,
        int $timeUsed,
        int $timeLimit
    ): array {
        $timeRatio = $timeLimit > 0 ? $timeUsed / $timeLimit : 1;

        if ($finishedBeforeTimer && $accuracy >= 90) {
            return [
                'title'   => '🎯 Loi de Parkinson maîtrisée !',
                'message' => 'Tu as terminé avant le temps imparti avec une excellente précision. C\'est exactement contre quoi lutte la Loi de Parkinson : en te donnant moins de temps, tu travailles plus efficacement !',
                'tip'     => 'Essaie de réduire ton estimation de 20% pour ton prochain défi.',
            ];
        }

        if ($finishedBeforeTimer && $accuracy < 90) {
            return [
                'title'   => '⚡ Rapide mais perfectible',
                'message' => 'Tu as fini avant le timer, mais la précision peut être améliorée. La Loi de Parkinson nous piège aussi dans la vitesse : aller vite sans qualité n\'est pas de l\'efficacité.',
                'tip'     => 'Concentre-toi sur la précision. Un travail bien fait du premier coup évite les corrections.',
            ];
        }

        if (!$finishedBeforeTimer && $timeRatio >= 0.8 && $accuracy >= 85) {
            return [
                'title'   => '⏱️ Le temps s\'étire...',
                'message' => 'Tu as utilisé presque tout ton temps disponible. C\'est la Loi de Parkinson en action : le travail s\'étale pour remplir le temps alloué. Essaie de te fixer une contrainte plus stricte.',
                'tip'     => 'La prochaine fois, réduis ton timer de 15%. La contrainte artificielle booste la concentration.',
            ];
        }

        if (!$finishedBeforeTimer) {
            return [
                'title'   => '💪 Continue, tu progresses !',
                'message' => 'Le temps était serré. C\'est normal au début ! La Loi de Parkinson dit que l\'on a tendance à sous-estimer ce qu\'on peut accomplir en peu de temps. Avec la pratique, tu seras surpris de ta vitesse.',
                'tip'     => 'Répète le même texte plusieurs fois. La familiarité réduit le temps nécessaire.',
            ];
        }

        return [
            'title'   => '✅ Bon défi !',
            'message' => 'Chaque défi de frappe t\'entraîne à travailler sous contrainte de temps — le cœur de la Loi de Parkinson.',
            'tip'     => 'Augmente progressivement la difficulté pour repousser tes limites.',
        ];
    }
}
