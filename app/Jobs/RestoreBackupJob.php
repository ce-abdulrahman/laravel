<?php

namespace App\Jobs;

use App\Services\RestoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RestoreBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected string $filePath;
    protected string $conflictResolution;
    protected array $modules;
    protected ?string $password;
    protected ?int $backupId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $userId,
        string $filePath,
        string $conflictResolution = 'replace',
        array $modules = [],
        ?string $password = null,
        ?int $backupId = null
    ) {
        $this->userId = $userId;
        $this->filePath = $filePath;
        $this->conflictResolution = $conflictResolution;
        $this->modules = $modules;
        $this->password = $password;
        $this->backupId = $backupId;
    }

    /**
     * Execute the job.
     */
    public function handle(RestoreService $service): void
    {
        $service->restoreBackup(
            $this->userId,
            $this->filePath,
            $this->conflictResolution,
            $this->modules,
            $this->password,
            $this->backupId
        );

        // Delete temp restore file if it is a local restore file
        if ($this->backupId === null && str_starts_with($this->filePath, sys_get_temp_dir()) && file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
}
