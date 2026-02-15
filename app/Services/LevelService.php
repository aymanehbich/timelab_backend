<?php

namespace App\Services;

use App\Models\User;
use App\Models\Level;

class LevelService
{
    public function check(User $user): void
    {
        $totalPoints = $user->points()->sum('amount');
        $level = Level::where('required_points', '<=', $totalPoints)
            ->orderBy('required_points', 'desc')
            ->first();

        if ($level && $user->level_id !== $level->id) {
            $user->level_id = $level->id;
            $user->save();
        }
    }
}