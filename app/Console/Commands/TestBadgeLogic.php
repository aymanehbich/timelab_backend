<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Console\Command;

class TestBadgeLogic extends Command
{
    protected $signature = 'badges:test {user_id}';
    protected $description = 'Test badge logic for a user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User not found!");
            return 1;
        }

        $this->info("Testing badge conditions for user: {$user->name}");
        $this->newLine();

        // Get completed challenges count
        $completedCount = Challenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $this->info("Completed challenges: {$completedCount}");

        // Test First Timer
        $this->line("First Timer: " . ($completedCount === 1 ? 'YES ✓' : 'NO ✗'));

        // Test Marathon Runner
        $this->line("Marathon Runner: " . ($completedCount >= 50 ? 'YES ✓' : 'NO ✗'));

        // Test Efficiency Expert
        $recentChallenges = Challenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('accuracy_percentage')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        $avgAccuracy = $recentChallenges->avg('accuracy_percentage') ?? 0;
        $efficiencyExpert = $recentChallenges->count() >= 10 && $avgAccuracy >= 80;
        $this->line("Efficiency Expert: " . ($efficiencyExpert ? 'YES ✓' : 'NO ✗') . " (avg: {$avgAccuracy}%, count: {$recentChallenges->count()})");

        // Test last challenge
        $lastChallenge = Challenge::where('user_id', $user->id)
            ->orderBy('completed_at', 'desc')
            ->first();

        if ($lastChallenge) {
            $this->newLine();
            $this->info("Last challenge:");
            $this->line("  Status: {$lastChallenge->status}");
            $this->line("  Accuracy: {$lastChallenge->accuracy_percentage}%");
            $this->line("  Estimated: {$lastChallenge->estimated_duration} min");
            $this->line("  Actual: {$lastChallenge->actual_duration} min");
            $this->line("  Speed ratio: " . ($lastChallenge->actual_duration / $lastChallenge->estimated_duration));

            $precisionMaster = $lastChallenge->status === 'completed' && $lastChallenge->accuracy_percentage >= 95;
            $this->line("  Precision Master: " . ($precisionMaster ? 'YES ✓' : 'NO ✗'));

            $speedDemon = $lastChallenge->status === 'completed' && $lastChallenge->actual_duration <= $lastChallenge->estimated_duration * 0.8;
            $this->line("  Speed Demon: " . ($speedDemon ? 'YES ✓' : 'NO ✗'));
        }

        return 0;
    }
}
