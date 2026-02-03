<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;

class BadgeService
{
    public function check(User $user): void
    {
        $totalPoints = $user->points()->sum('amount');
        $badges = Badge::all();

        foreach ($badges as $badge) {
            if ($totalPoints >= $badge->required_points && !$user->badges->contains($badge->id)) {
                $user->badges()->attach($badge->id);
            }
        }
    }
}