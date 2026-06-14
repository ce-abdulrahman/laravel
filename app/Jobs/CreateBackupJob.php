<?php

namespace App\Jobs;

use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected string $backupType;
    protected array $options;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, string $backupType = 'manual', array $options = [])
    {
        $this->userId = $userId;
        $this->backupType = $backupType;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(BackupService $service): void
    {
        $service->generateBackup($this->userId, $this->backupType, $this->options);
    }
}
