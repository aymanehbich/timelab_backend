<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('typing_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('typing_text_id')->nullable()->constrained('typing_texts')->nullOnDelete();
            $table->enum('level', ['beginner', 'intermediate', 'expert']);
            $table->text('text_content');
            $table->integer('time_limit'); // seconds
            $table->integer('words_typed')->default(0);
            $table->integer('total_words');
            $table->decimal('accuracy_percentage', 5, 2)->nullable();
            $table->decimal('words_per_minute', 8, 2)->nullable();
            $table->integer('time_used')->nullable(); // seconds
            $table->boolean('completed')->default(false);
            $table->boolean('finished_before_timer')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typing_challenges');
    }
};
