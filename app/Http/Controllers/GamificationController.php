<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Level;
use App\Models\User;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    //
    public function stats()
    {
        $user = auth()->user();

        $totalPoints = $user->points()->sum('amount');

        // Calculate level based on points
        $level = Level::where('required_points', '<=', $totalPoints)
            ->orderBy('required_points', 'desc')
            ->first();
        return response()->json([
            'total_points' => $user->points()->sum('amount'),
            'badges' => $user->badges,
            'level' => $level,
        ]);
    }

    public function leaderboard()
    {
        $users = User::withSum('points', 'amount')
            ->orderByDesc('points_sum_amount')
            ->take(10)
            ->get();

        // Format the leaderboard data
        $leaderboard = $users->map(function ($user, $index) {
            $totalPoints = $user->points_sum_amount ?? 0;

            // Calculate level based on points
            $level = Level::where('required_points', '<=', $totalPoints)
                ->orderBy('required_points', 'desc')
                ->first();

            return [
                'rank' => $index + 1,
                'name' => $user->name,
                'points' => $totalPoints,
                'level' => $level ? $level->id : 1,
            ];
        });

        return response()->json($leaderboard);
    }

    public function level()
    {
        $user = auth()->user();
        $totalPoints = $user->points()->sum('amount');

        $level = Level::where('required_points', '<=', $totalPoints)
            ->orderBy('required_points', 'desc')
            ->first();

        return response()->json($level);
    }

    public function badges()
    {
        return response()->json(Badge::all());
    }
    
}
