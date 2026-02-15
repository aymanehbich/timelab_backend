<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add condition_key column if not already added by Parkinson migration
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
    }

    public function down(): void
    {
        // condition_key column is managed by Parkinson migration
    }
};
