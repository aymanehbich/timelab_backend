<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $badges = [
            ['name' => 'First Steps', 'required_points' => 10],
            ['name' => 'Rising Star', 'required_points' => 100],
            ['name' => 'Achiever', 'required_points' => 500],
            ['name' => 'Champion', 'required_points' => 1000],
            ['name' => 'Legend', 'required_points' => 5000],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
}
