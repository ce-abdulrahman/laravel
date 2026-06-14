<?php

namespace App\Http\Controllers;

use App\Models\UserBackup;
use App\Models\BackupRestoreLog;
use App\Services\BackupService;
use App\Jobs\AnalyticsSyncJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BackupAdminController extends Controller
{
    public function __construct(
        private readonly BackupService $backupService
    ) {}

    /**
     * Display the analytics and overview.
     */
    public function overview()
    {
        $stats = Cache::get('backup_admin_analytics');
        if (!$stats) {
            // Run synchronously to populate cache immediately on first load
            $job = new AnalyticsSyncJob();
            $job->handle();
            $stats = Cache::get('backup_admin_analytics');
        }

        return view('admin.backups.dashboard', compact('stats'));
    }

    /**
     * Display backup list.
     */
    public function index(Request $request)
    {
        $query = UserBackup::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('backup_type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $backups = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.backups.index', compact('backups'));
    }

    /**
     * Display restore logs.
     */
    public function logs(Request $request)
    {
        $query = BackupRestoreLog::with(['user', 'backup']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.backups.logs', compact('logs'));
    }

    /**
     * Display system backup settings.
     */
    public function settings()
    {
        $settings = [
            'max_count' => $this->backupService->getSetting('max_count', 10),
            'retention_days' => $this->backupService->getSetting('retention_days', 30),
            'storage_provider' => $this->backupService->getSetting('storage_provider', 'local'),
            'encryption_required' => $this->backupService->getSetting('encryption_required', false),
        ];

        return view('admin.backups.settings', compact('settings'));
    }

    /**
     * Update backup settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'max_count' => 'required|integer|min:1|max:100',
            'retention_days' => 'required|integer|min:1|max:365',
            'storage_provider' => 'required|string|in:local,s3',
            'encryption_required' => 'boolean',
        ]);

        $this->backupService->setSetting('max_count', $request->input('max_count'));
        $this->backupService->setSetting('retention_days', $request->input('retention_days'));
        $this->backupService->setSetting('storage_provider', $request->input('storage_provider'));
        $this->backupService->setSetting('encryption_required', $request->boolean('encryption_required') ? '1' : '0');

        return redirect()->route('admin.backups.settings')->with('success', __('backup.messages.settings_updated'));
    }

    /**
     * Download any backup (Admin only).
     */
    public function download($id)
    {
        $backup = UserBackup::findOrFail($id);

        if ($backup->status !== 'success') {
            return redirect()->back()->with('error', __('backup.messages.not_ready'));
        }

        if (!Storage::disk($backup->storage_type)->exists($backup->file_name)) {
            return redirect()->back()->with('error', __('backup.messages.file_missing'));
        }

        return Storage::disk($backup->storage_type)->download($backup->file_name, basename($backup->file_name));
    }

    /**
     * Delete a backup.
     */
    public function destroy($id)
    {
        try {
            $this->backupService->deleteBackup($id);
            // Refresh stats in background
            AnalyticsSyncJob::dispatch();

            return redirect()->back()->with('success', __('backup.messages.deleted'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting backup: ' . $e->getMessage());
        }
    }
}
