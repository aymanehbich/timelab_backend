<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\TimeEstimation;
use App\Models\User;
use App\Models\Badge;
use App\Models\UserBadge;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ParkinsonController extends Controller
{
    protected $pointsService;

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * GET /api/parkinson/challenges
     * Liste des défis de l'utilisateur
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status'); // Filtrer par status

        $query = Challenge::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $challenges = $query->get();

        return response()->json([
            'success' => true,
            'challenges' => $challenges
        ]);
    }

    /**
     * POST /api/parkinson/challenges
     * Créer un nouveau défi
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_duration' => 'required|integer|min:1'
        ]);

        $user = Auth::user();

        $challenge = Challenge::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'estimated_duration' => $validated['estimated_duration'],
            'status' => 'in_progress'
        ]);

        // Créer une estimation initiale
        TimeEstimation::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'estimated_time' => $validated['estimated_duration']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Défi créé avec succès',
            'challenge' => $challenge
        ], 201);
    }

    /**
     * POST /api/parkinson/challenges/{id}/start
     * Démarrer un défi
     */
    public function start($id)
    {
        $user = Auth::user();
        $challenge = Challenge::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($challenge->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Ce défi ne peut pas être démarré'
            ], 400);
        }

        $challenge->started_at = now();
        $challenge->save();

        return response()->json([
            'success' => true,
            'message' => 'Défi démarré',
            'challenge' => $challenge
        ]);
    }

    /**
     * POST /api/parkinson/challenges/{id}/complete
     * Terminer un défi et calculer la précision
     */
    public function complete(Request $request, $id)
    {
        $validated = $request->validate([
            'actual_duration' => 'required|integer|min:1',
            'success' => 'required|boolean'
        ]);

        $user = Auth::user();
        $challenge = Challenge::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($challenge->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Ce défi est déjà terminé'
            ], 400);
        }

        // Mettre à jour le défi
        $challenge->actual_duration = $validated['actual_duration'];
        $challenge->completed_at = now();
        $challenge->status = $validated['success'] ? 'completed' : 'failed';

        // Calculer la précision
        $challenge->calculateAccuracy();

        // IMPORTANT: Sauvegarder le challenge avant de vérifier les badges
        $challenge->save();

        // Mettre à jour l'estimation
        $estimation = TimeEstimation::where('challenge_id', $challenge->id)->first();
        if ($estimation) {
            $estimation->actual_time = $validated['actual_duration'];
            $estimation->calculateDifference();
        }

        // Attribution de points basée sur la précision
        $pointsData = $this->calculatePointsForAccuracy($challenge->accuracy_percentage, $validated['success']);

        if ($pointsData['points'] > 0) {
            $this->pointsService->award($user, $pointsData['points']);
        }

        // Vérifier et attribuer les badges (après sauvegarde)
        $newBadges = $this->checkParkinsonBadges($user, $challenge);

        return response()->json([
            'success' => true,
            'message' => 'Défi terminé',
            'challenge' => $challenge,
            'points_earned' => $pointsData['points'],
            'accuracy_level' => $pointsData['level'],
            'accuracy_emoji' => $pointsData['emoji'],
            'new_badges' => $newBadges, // Badges gagnés lors de cette complétion
            'debug' => [ // Info de débogage
                'completed_count' => Challenge::where('user_id', $user->id)->where('status', 'completed')->count(),
                'accuracy' => $challenge->accuracy_percentage,
                'speed_ratio' => $challenge->actual_duration / $challenge->estimated_duration
            ]
        ]);
    }

    /**
     * GET /api/parkinson/accuracy
     * Précision globale de l'utilisateur
     */
    public function accuracy()
    {
        $user = Auth::user();

        $completedChallenges = Challenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('accuracy_percentage')
            ->get();

        if ($completedChallenges->isEmpty()) {
            return response()->json([
                'success' => true,
                'average_accuracy' => 0,
                'total_challenges' => 0
            ]);
        }

        $averageAccuracy = $completedChallenges->avg('accuracy_percentage');

        return response()->json([
            'success' => true,
            'average_accuracy' => round($averageAccuracy, 2),
            'total_challenges' => $completedChallenges->count(),
            'high_accuracy_count' => $completedChallenges->where('accuracy_percentage', '>=', 90)->count()
        ]);
    }

    /**
     * GET /api/parkinson/stats
     * Statistiques détaillées
     */
    public function stats()
    {
        $user = Auth::user();

        $totalChallenges = Challenge::where('user_id', $user->id)->count();
        $completedChallenges = Challenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->get();

        $inProgressChallenges = Challenge::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->count();

        $failedChallenges = Challenge::where('user_id', $user->id)
            ->where('status', 'failed')
            ->count();

        $averageAccuracy = $completedChallenges->avg('accuracy_percentage') ?? 0;

        $totalEstimated = $completedChallenges->sum('estimated_duration');
        $totalActual = $completedChallenges->sum('actual_duration');

        $averageEstimated = $completedChallenges->avg('estimated_duration') ?? 0;
        $averageActual = $completedChallenges->avg('actual_duration') ?? 0;

        // Tendance : sous-estimation ou sur-estimation
        $tendency = 'neutral';
        if ($totalActual > $totalEstimated) {
            $tendency = 'underestimating'; // Sous-estime le temps
        } elseif ($totalActual < $totalEstimated) {
            $tendency = 'overestimating'; // Sur-estime le temps
        }

        return response()->json([
            'success' => true,
            'stats' => [
                'total_challenges' => $totalChallenges,
                'completed' => $completedChallenges->count(),
                'in_progress' => $inProgressChallenges,
                'failed' => $failedChallenges,
                'average_accuracy' => round($averageAccuracy, 2),
                'completion_rate' => $totalChallenges > 0
                    ? round(($completedChallenges->count() / $totalChallenges) * 100, 2)
                    : 0,
                'time_stats' => [
                    'total_estimated_minutes' => $totalEstimated,
                    'total_actual_minutes' => $totalActual,
                    'average_estimated_minutes' => round($averageEstimated, 2),
                    'average_actual_minutes' => round($averageActual, 2),
                    'tendency' => $tendency
                ],
                'recent_challenges' => Challenge::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
            ]
        ]);
    }

    /**
     * Calculer les points en fonction de la précision
     * Système de points par palier de précision
     */
    private function calculatePointsForAccuracy($accuracy, $success)
    {
        // Si le défi a échoué, points d'encouragement uniquement
        if (!$success) {
            return [
                'points' => 25,
                'level' => 'participation',
                'emoji' => '💪'
            ];
        }

        // Sinon, points basés sur la précision
        if ($accuracy >= 95) {
            return [
                'points' => 150,
                'level' => 'excellent',
                'emoji' => '🔥'
            ];
        } elseif ($accuracy >= 90) {
            return [
                'points' => 120,
                'level' => 'superb',
                'emoji' => '⭐'
            ];
        } elseif ($accuracy >= 80) {
            return [
                'points' => 100,
                'level' => 'great',
                'emoji' => '✅'
            ];
        } elseif ($accuracy >= 70) {
            return [
                'points' => 75,
                'level' => 'good',
                'emoji' => '👍'
            ];
        } else {
            return [
                'points' => 50,
                'level' => 'keep_going',
                'emoji' => '💪'
            ];
        }
    }

    /**
     * Vérifier et attribuer tous les badges Parkinson
     * Retourne la liste des badges nouvellement gagnés
     */
    private function checkParkinsonBadges(User $user, Challenge $challenge)
    {
        $newBadges = [];

        if ($this->checkFirstTimerBadge($user)) {
            $newBadges[] = 'First Timer';
        }
        if ($this->checkPrecisionMasterBadge($user, $challenge)) {
            $newBadges[] = 'Precision Master';
        }
        if ($this->checkEfficiencyExpertBadge($user)) {
            $newBadges[] = 'Efficiency Expert';
        }
        if ($this->checkMarathonRunnerBadge($user)) {
            $newBadges[] = 'Marathon Runner';
        }
        if ($this->checkSpeedDemonBadge($user, $challenge)) {
            $newBadges[] = 'Speed Demon';
        }

        return $newBadges;
    }

    /**
     * Badge "First Timer" 🎯
     * Compléter son premier défi
     */
    private function checkFirstTimerBadge(User $user)
    {
        $completedCount = Challenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        if ($completedCount === 1) {
            return $this->awardBadge($user, 'First Timer');
        }
        return false;
    }

    /**
     * Badge "Precision Master" 🎖️
     * Atteindre 95% de précision sur un défi réussi
     */
    private function checkPrecisionMasterBadge(User $user, Challenge $challenge)
    {
        if ($challenge->status === 'completed' && $challenge->accuracy_percentage >= 95) {
            return $this->awardBadge($user, 'Precision Master');
        }
        return false;
    }

    /**
     * Badge "Marathon Runner" 🏃
     * Compléter 50 défis au total
     */
    private function checkMarathonRunnerBadge(User $user)
    {
        $completedCount = Challenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        if ($completedCount >= 50) {
            return $this->awardBadge($user, 'Marathon Runner');
        }
        return false;
    }

    /**
     * Badge "Speed Demon" ⚡
     * Terminer un défi réussi 20% plus vite que l'estimation
     */
    private function checkSpeedDemonBadge(User $user, Challenge $challenge)
    {
        if ($challenge->status === 'completed' && $challenge->actual_duration <= $challenge->estimated_duration * 0.8) {
            return $this->awardBadge($user, 'Speed Demon');
        }
        return false;
    }

    /**
     * Méthode utilitaire pour attribuer un badge
     * Retourne true si le badge a été nouvellement attribué
     */
    private function awardBadge(User $user, string $badgeName)
    {
        $badge = Badge::where('name', $badgeName)->first();

        if ($badge) {
            $userBadge = UserBadge::where('user_id', $user->id)
                ->where('badge_id', $badge->id)
                ->first();

            if (!$userBadge) {
                UserBadge::create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                ]);
                return true; // Badge nouvellement attribué
            }
        }
        return false; // Badge déjà possédé ou non trouvé
    }

    /**
     * Badge "Efficiency Expert" 🏆
     * Maintenir 80% de précision moyenne sur 10 défis
     */
    private function checkEfficiencyExpertBadge(User $user)
    {
        // Récupérer les 10 derniers défis complétés
        $recentChallenges = Challenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('accuracy_percentage')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        // Vérifier si l'utilisateur a au moins 10 défis
        if ($recentChallenges->count() < 10) {
            return false;
        }

        // Calculer la moyenne de précision
        $averageAccuracy = $recentChallenges->avg('accuracy_percentage');

        // Vérifier si la précision moyenne est >= 80%
        if ($averageAccuracy >= 80) {
            return $this->awardBadge($user, 'Efficiency Expert');
        }
        return false;
    }
}
