<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $badges = [
            [
                'name'            => 'Eisenhower Débutant',
                'description'     => 'Tu as réussi le niveau 1 de la matrice d\'Eisenhower !',
                'icon'            => '📋',
                'required_points' => 999999,
                'condition_key'   => 'eisenhower_level_1',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Eisenhower Intermédiaire',
                'description'     => 'Tu as réussi le niveau 2 de la matrice d\'Eisenhower !',
                'icon'            => '⚖️',
                'required_points' => 999999,
                'condition_key'   => 'eisenhower_level_2',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Eisenhower Expert',
                'description'     => 'Tu as réussi le niveau 3 de la matrice d\'Eisenhower ! Tu maîtrises la priorisation.',
                'icon'            => '🏅',
                'required_points' => 999999,
                'condition_key'   => 'eisenhower_level_3',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ];

        foreach ($badges as $badge) {
            $exists = DB::table('badges')->where('condition_key', $badge['condition_key'])->exists();
            if (!$exists) {
                DB::table('badges')->insert($badge);
            }
        }
    }

    public function down(): void
    {
        DB::table('badges')->whereIn('condition_key', [
            'eisenhower_level_1',
            'eisenhower_level_2',
            'eisenhower_level_3',
        ])->delete();
    }
};
