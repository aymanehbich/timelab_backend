<?php

namespace App\Services;

use App\Models\User;
use App\Models\Point;

class PointsService
{
    public function award(User $user, int $amount): void
    {
        Point::create([
            'user_id' => $user->id,
            'amount' => $amount,
        ]);

        app(BadgeService::class)->check($user);
        app(LevelService::class)->check($user);
    }
}