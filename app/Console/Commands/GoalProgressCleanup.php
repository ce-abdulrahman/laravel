<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserGoalProgressEvent;
use Carbon\Carbon;

class GoalProgressCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GoalProgress:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge processed event logs older than 7 days for scalability';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = Carbon::now('UTC')->subDays(7);
        $deletedCount = UserGoalProgressEvent::where('created_at', '<', $cutoff)->delete();

        $this->info("Successfully purged {$deletedCount} old goal progress event records.");
        return Command::SUCCESS;
    }
}
