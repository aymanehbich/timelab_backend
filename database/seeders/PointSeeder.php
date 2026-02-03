<?php

namespace Database\Seeders;

use App\Models\Point;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $users = User::all();

        foreach ($users as $user) {
            // Create random point entries for each user
            $pointEntries = rand(3, 10);
            for ($i = 0; $i < $pointEntries; $i++) {
                Point::create([
                    'user_id' => $user->id,
                    'amount' => rand(5, 50),
                ]);
            }
        }
    }
}
