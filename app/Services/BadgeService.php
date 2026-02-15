<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;

class BadgeService
{
    public function check(User $user): void
    {
        // Badges are awarded by specific logic in each controller
        // (EisenhowerController, ParkinsonController, etc.)
    }
}