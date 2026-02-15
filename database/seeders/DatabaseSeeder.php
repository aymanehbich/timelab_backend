<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            LevelSeeder::class,
            BadgeSeeder::class,
            // Add your other seeders here as needed
            PointSeeder::class,
            UserBadgeSeeder::class,
            TypingTextsSeeder::class, // Make sure this matches your seeder class name
        ]);
    }
}
