<?php

namespace App\Console\Commands;

use App\Jobs\RecalculateUserStatistics;
use App\Jobs\GenerateUserInsightsJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailySnapshots extends Command
{
    protected $signature   = 'statistics:snapshot {--type=daily : Snapshot type: daily|weekly|monthly}';
    protected $description = 'Generate statistics snapshots for all users (run via scheduler)';

    public function handle(): int
    {
        $type  = $this->option('type');
        $users = User::where('status', true)->pluck('id');

        $this->info("Generating {$type} snapshots for {$users->count()} users...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $userId) {
            RecalculateUserStatistics::dispatch($userId, saveSnapshot: true, snapshotType: $type)
                ->onQueue('statistics');

            GenerateUserInsightsJob::dispatch($userId)
                ->onQueue('statistics');

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("All jobs dispatched to 'statistics' queue.");

        return Command::SUCCESS;
    }
}
