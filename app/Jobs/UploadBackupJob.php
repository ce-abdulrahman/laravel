<?php

namespace App\Jobs;

use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected string $tempFilePath;
    protected array $options;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, string $tempFilePath, array $options = [])
    {
        $this->userId = $userId;
        $this->tempFilePath = $tempFilePath;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(BackupService $service): void
    {
        if (!file_exists($this->tempFilePath)) {
            return;
        }

        try {
            $file = new File($this->tempFilePath);
            $service->uploadBackup($this->userId, $file, $this->options);
        } finally {
            if (file_exists($this->tempFilePath)) {
                unlink($this->tempFilePath);
            }
        }
    }
}
