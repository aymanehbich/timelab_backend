<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained()->onDelete('cascade');
            $table->integer('estimated_time')->comment('Time in minutes');
            $table->integer('actual_time')->nullable()->comment('Time in minutes');
            $table->integer('difference_minutes')->nullable()->comment('Difference: actual - estimated');
            $table->timestamps();

            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('challenge_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_estimations');
    }
};
