<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $users = User::all();
        $badges = Badge::all();

        foreach ($users as $user) {
            $totalPoints = $user->points()->sum('amount');
            
            foreach ($badges as $badge) {
                if ($totalPoints >= $badge->required_points) {
                    $user->badges()->attach($badge->id);
                }
            }
        }
    }
}
