<?php

namespace App\Services;

use App\Models\PomodoroStreak;
use App\Models\User;
use App\Models\Point;
use Carbon\Carbon;

class PomodoroStreakService
{
    // ═══════════════════════════════════════════
    // Méthode principale — appelée après complete()
    // ═══════════════════════════════════════════
    public function update(int $userId): array
    {
        // 1. Récupérer ou créer le streak de l'user
        $streak = PomodoroStreak::firstOrCreate(
            ['user_id' => $userId],
            [
                'current_streak'    => 0,
                'longest_streak'    => 0,
                'last_session_date' => null,
            ]
        );

        $today     = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $last      = $streak->last_session_date
                        ? $streak->last_session_date->toDateString()
                        : null;

        // ─────────────────────────────────────
        // 2. Calculer le streak
        // ─────────────────────────────────────

        // CAS 1 : déjà une session aujourd'hui → rien à faire
        if ($last === $today) {
            $multiplier  = $this->getMultiplier($streak->current_streak);
            $points      = (int)(25 * $multiplier);
            $this->givePoints($userId, $points);

            return $this->buildResult($streak, $points, $multiplier, null);
        }

        // CAS 2 : dernière session = hier → continuité
        if ($last === $yesterday) {
            $streak->current_streak += 1;
        }
        // CAS 3 : streak cassé ou première fois
        else {
            $streak->current_streak = 1;
        }

        // ─────────────────────────────────────
        // 3. Mettre à jour le record
        // ─────────────────────────────────────
        if ($streak->current_streak > $streak->longest_streak) {
            $streak->longest_streak = $streak->current_streak;
        }

        $streak->last_session_date = $today;
        $streak->save();

        // ─────────────────────────────────────
        // 4. Calculer les points avec multiplicateur
        // ─────────────────────────────────────
        $multiplier = $this->getMultiplier($streak->current_streak);
        $points     = (int)(25 * $multiplier);
        $this->givePoints($userId, $points);

        // ─────────────────────────────────────
        // 5. Vérifier si un badge est débloqué
        // ─────────────────────────────────────
        $newBadge = $this->checkAndAwardBadge($userId, $streak->current_streak);

        // ─────────────────────────────────────
        // 6. Retourner le résultat au contrôleur
        // ─────────────────────────────────────
        return $this->buildResult($streak, $points, $multiplier, $newBadge);
    }

    // ═══════════════════════════════════════════
    // Méthodes privées
    // ═══════════════════════════════════════════

    // Multiplicateur selon le streak
    private function getMultiplier(int $streak): float
    {
        if ($streak >= 30) return 5.0;
        if ($streak >= 14) return 3.0;
        if ($streak >= 7)  return 2.0;
        if ($streak >= 3)  return 1.5;
        return 1.0;
    }

    // Donner les points au user
    private function givePoints(int $userId, int $points): void
    {
Point::create([
    'user_id' => $userId,   // bigint
    'amount'  => $points,   // int (ex: 25, 37, 50...)
]);    }

    // Vérifier et attribuer un badge
    private function checkAndAwardBadge(int $userId, int $streak): ?string
    {
        $badges = [
            3   => '🔥 On Fire',
            7   => '⚡ Streak Champion',
            14  => '💎 Diamond Focus',
            30  => '👑 Focus Legend',
            100 => '🏆 Pomodoro Master',
        ];

        if (!isset($badges[$streak])) return null;

        $badgeName = $badges[$streak];
        $user      = User::find($userId);

        // firstOrCreate évite les doublons
        $user->badges()->firstOrCreate(['name' => $badgeName]);

        return $badgeName;
    }

    // Construire le tableau de résultat
    private function buildResult(
        PomodoroStreak $streak,
        int $points,
        float $multiplier,
        ?string $newBadge
    ): array {
        return [
            'points_earned'  => $points,
            'multiplier'     => $multiplier,
            'current_streak' => $streak->current_streak,
            'longest_streak' => $streak->longest_streak,
            'new_badge'      => $newBadge,
        ];
    }
}