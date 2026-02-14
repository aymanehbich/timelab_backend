<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('eisenhower_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->boolean('is_important')->default(false);
            $table->unsignedTinyInteger('quadrant');
            $table->text('explanation')->nullable();
            $table->unsignedTinyInteger('level')->default(1); // 1=Beginner, 2=Intermediate, 3=Advanced
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('eisenhower_tasks');
    }
};
