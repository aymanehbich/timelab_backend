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
        Schema::create('time_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Task information
            $table->string('task_title');
            $table->integer('task_duration'); // in minutes
            $table->string('task_color');

            // Time block scheduling
            $table->decimal('start_hour', 4, 2); // e.g., 8.50 = 8:30 AM
            $table->decimal('end_hour', 4, 2);   // e.g., 9.00 = 9:00 AM

            // Status
            $table->boolean('completed')->default(false);

            // Date for the time block
            $table->date('block_date');

            $table->timestamps();

            // Indexes for better query performance
            $table->index(['user_id', 'block_date']);
            $table->index('completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_blocks');
    }
};
