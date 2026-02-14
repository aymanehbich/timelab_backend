<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('typing_challenges', function (Blueprint $table) {
            $table->unsignedInteger('points_earned')->default(0)->after('finished_before_timer');
        });
    }

    public function down(): void
    {
        Schema::table('typing_challenges', function (Blueprint $table) {
            $table->dropColumn('points_earned');
        });
    }
};
