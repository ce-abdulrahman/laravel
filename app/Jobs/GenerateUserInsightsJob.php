<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\InsightEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateUserInsightsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public readonly int $userId) {}

    public function handle(InsightEngine $engine): void
    {
        $user = User::find($this->userId);
        if (!$user) return;

        $engine->generate($user);
    }
}
