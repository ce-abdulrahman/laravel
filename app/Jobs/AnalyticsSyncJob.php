<?php

namespace App\Jobs;

use App\Models\UserBackup;
use App\Models\BackupRestoreLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $stats = [
            'total_count' => UserBackup::count(),
            'success_count' => UserBackup::where('status', 'success')->count(),
            'failed_count' => UserBackup::where('status', 'failed')->count(),
            'total_size' => UserBackup::where('status', 'success')->sum('file_size'),
        ];

        // Backup growth (past 30 days)
        $growth = UserBackup::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'), DB::raw('SUM(file_size) as size'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();

        // Restore frequency (past 30 days)
        $restores = BackupRestoreLog::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();

        $stats['growth'] = $growth;
        $stats['restores'] = $restores;

        Cache::put('backup_admin_analytics', $stats, 86400);
    }
}
