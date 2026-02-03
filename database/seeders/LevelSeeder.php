<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $levels = [
            ['name' => 'Beginner', 'required_points' => 0],
            ['name' => 'Intermediate', 'required_points' => 100],
            ['name' => 'Advanced', 'required_points' => 500],
            ['name' => 'Expert', 'required_points' => 1000],
            ['name' => 'Master', 'required_points' => 2500],
        ];

        foreach ($levels as $level) {
            Level::create($level);
        }
    }
}
