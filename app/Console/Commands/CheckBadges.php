<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckBadges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all badges in database and find duplicates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Badges in Database ===');

        $badges = DB::table('badges')->orderBy('id')->get();

        foreach ($badges as $badge) {
            $this->line(sprintf(
                'ID: %d | Name: %s | Icon: %s',
                $badge->id,
                $badge->name,
                $badge->icon ?? 'N/A'
            ));
        }

        $this->newLine();
        $this->info('=== Checking for Duplicates ===');

        $duplicates = DB::table('badges')
            ->select('name', DB::raw('COUNT(*) as count'))
            ->groupBy('name')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found!');
        } else {
            foreach ($duplicates as $dup) {
                $this->error("Badge '{$dup->name}' appears {$dup->count} times!");
            }
        }

        return 0;
    }
}
