<?php

namespace App\Jobs;

use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $backupId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $backupId)
    {
        $this->backupId = $backupId;
    }

    /**
     * Execute the job.
     */
    public function handle(BackupService $service): void
    {
        $service->deleteBackup($this->backupId);
    }
}
