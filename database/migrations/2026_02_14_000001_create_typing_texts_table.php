<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('typing_texts', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['beginner', 'intermediate', 'expert']);
            $table->string('title');
            $table->text('content');
            $table->integer('word_count');
            $table->integer('time_limit'); // seconds
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typing_texts');
    }
};
