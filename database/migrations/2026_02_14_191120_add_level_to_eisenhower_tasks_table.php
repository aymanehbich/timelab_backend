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
        Schema::table('eisenhower_tasks', function (Blueprint $table) {
            // $table->unsignedTinyInteger('level')->default(1)->after('explanation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eisenhower_tasks', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
