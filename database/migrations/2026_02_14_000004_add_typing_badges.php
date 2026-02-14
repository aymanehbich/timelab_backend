<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add condition_key column (used to identify badges awarded by specific logic)
        if (!Schema::hasColumn('badges', 'condition_key')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->string('condition_key')->nullable()->unique()->after('required_points');
            });
        }

        // Add description column if not already present
        if (!Schema::hasColumn('badges', 'description')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->text('description')->nullable()->after('name');
            });
        }

        $typingBadges = [
            [
                'name'            => 'First Keystroke',
                'description'     => 'Bienvenue dans le défi de frappe ! Tu as complété ton premier challenge.',
                'icon'            => '⌨️',
                'required_points' => 999999,
                'condition_key'   => 'typing_first',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Speed Demon Typing',
                'description'     => 'Vitesse exceptionnelle ! Tu as dépassé 60 mots par minute.',
                'icon'            => '⚡',
                'required_points' => 999999,
                'condition_key'   => 'typing_speed_demon',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Perfectionist',
                'description'     => 'Zéro erreur ! Tu as atteint une précision de 100% sur un défi.',
                'icon'            => '💎',
                'required_points' => 999999,
                'condition_key'   => 'typing_perfectionist',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Parkinson Slayer',
                'description'     => 'Tu as terminé le défi avec plus de 40% du temps restant. Maître du temps !',
                'icon'            => '🗡️',
                'required_points' => 999999,
                'condition_key'   => 'typing_parkinson_slayer',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Speed Improver',
                'description'     => 'Ta vitesse a progressé de 20% sur 5 défis consécutifs. Belle progression !',
                'icon'            => '📈',
                'required_points' => 999999,
                'condition_key'   => 'typing_speed_improver',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ];

        foreach ($typingBadges as $badge) {
            $exists = DB::table('badges')->where('condition_key', $badge['condition_key'])->exists();
            if (!$exists) {
                DB::table('badges')->insert($badge);
            }
        }
    }

    public function down(): void
    {
        DB::table('badges')->whereIn('condition_key', [
            'typing_first',
            'typing_speed_demon',
            'typing_perfectionist',
            'typing_parkinson_slayer',
            'typing_speed_improver',
        ])->delete();

        if (Schema::hasColumn('badges', 'condition_key')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->dropColumn('condition_key');
            });
        }
    }
};
