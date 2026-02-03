<?php

namespace App\Http\Controllers;

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

        return response()->json($users);
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
    
}
