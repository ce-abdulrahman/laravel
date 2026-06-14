<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\StatisticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateUserStatistics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int $userId,
        public readonly bool $saveSnapshot = false,
        public readonly string $snapshotType = 'daily',
    ) {}

    public function handle(StatisticsService $service): void
    {
        $user = User::find($this->userId);
        if (!$user) return;

        $service->recalculate($user);

        if ($this->saveSnapshot) {
            $service->saveSnapshot($user, $this->snapshotType);
        }
    }
}
