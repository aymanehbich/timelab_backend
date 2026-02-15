<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add description column to badges table if it doesn't exist
        if (!Schema::hasColumn('badges', 'description')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->text('description')->nullable()->after('name');
            });
        }

        // Insert Parkinson's Law badges (only if they don't exist)
        // IMPORTANT: required_points = 999999 pour éviter l'auto-attribution par BadgeService
        // Ces badges sont attribués uniquement par la logique spécifique Parkinson
        // Add condition_key column early so Parkinson badges can use it
        if (!Schema::hasColumn('badges', 'condition_key')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->string('condition_key')->nullable()->unique()->after('required_points');
            });
        }

        $badgesToInsert = [
            [
                'name' => 'First Timer',
                'description' => 'Bienvenue dans le monde de l\'estimation !',
                'icon' => '🎯',
                'required_points' => 999999,
                'condition_key' => 'parkinson_first_timer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Precision Master',
                'description' => 'Tu es incroyablement précis !',
                'icon' => '🎖️',
                'required_points' => 999999,
                'condition_key' => 'parkinson_precision',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Efficiency Expert',
                'description' => 'Tu maîtrises l\'art de l\'estimation !',
                'icon' => '🏆',
                'required_points' => 999999,
                'condition_key' => 'parkinson_efficiency',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marathon Runner',
                'description' => 'Ta persévérance est impressionnante !',
                'icon' => '🏃',
                'required_points' => 999999,
                'condition_key' => 'parkinson_marathon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Speed Demon',
                'description' => 'Tu est plus rapide que prévu !',
                'icon' => '⚡',
                'required_points' => 999999,
                'condition_key' => 'parkinson_speed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert each badge only if it doesn't already exist
        foreach ($badgesToInsert as $badge) {
            $exists = DB::table('badges')->where('name', $badge['name'])->exists();
            if (!$exists) {
                DB::table('badges')->insert($badge);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Parkinson's Law badges
        DB::table('badges')->whereIn('name', [
            'First Timer',
            'Precision Master',
            'Efficiency Expert',
            'Marathon Runner',
            'Speed Demon',
        ])->delete();

        // Remove description column if it exists
        if (Schema::hasColumn('badges', 'description')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
