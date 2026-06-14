<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserBackup;
use App\Services\BackupService;
use App\Services\RestoreService;
use App\Jobs\CreateBackupJob;
use App\Jobs\UploadBackupJob;
use App\Jobs\RestoreBackupJob;
use App\Jobs\DeleteBackupJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function __construct(
        private readonly BackupService $backupService,
        private readonly RestoreService $restoreService
    ) {}

    /**
     * GET /api/v1/backups
     * List user's backups.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $backups = UserBackup::where('user_id', $user->id)
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $backups
        ]);
    }

    /**
     * POST /api/v1/backups/create
     * Trigger a cloud backup generation.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $password = $request->input('password'); // Optional encryption password
        
        $options = [
            'password' => $password,
            'device_type' => $request->input('device_type'),
            'platform' => $request->input('platform'),
            'app_version' => $request->input('app_version'),
        ];

        try {
            // Dispatch to background queue for large operations
            CreateBackupJob::dispatch($user->id, 'manual', $options);

            return response()->json([
                'status' => 'success',
                'success' => true,
                'message' => 'Backup job queued successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * GET /api/v1/backups/download/{id}
     * Download backup file.
     */
    public function download(Request $request, $id)
    {
        $user = $request->user();
        $backup = UserBackup::where('user_id', $user->id)->findOrFail($id);

        if ($backup->status !== 'success') {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Backup file is not ready.'
            ], 400);
        }

        if (!Storage::disk($backup->storage_type)->exists($backup->file_name)) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'File not found on storage disk.'
            ], 404);
        }

        return Storage::disk($backup->storage_type)->download($backup->file_name, basename($backup->file_name));
    }

    /**
     * POST /api/v1/backups/upload
     * Upload backup file from device.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:20480', // max 20MB
            'is_encrypted' => 'nullable|boolean',
            'backup_version' => 'nullable|string',
            'device_type' => 'nullable|string',
            'platform' => 'nullable|string',
            'app_version' => 'nullable|string',
        ]);

        $user = $request->user();
        $file = $request->file('backup_file');

        // Store file in a temporary folder and dispatch queue processing
        $tempPath = tempnam(sys_get_temp_dir(), 'backup_upload_') . '.zip';
        move_uploaded_file($file->getRealPath(), $tempPath);

        $options = [
            'is_encrypted' => $request->boolean('is_encrypted'),
            'backup_version' => $request->input('backup_version', '1.0'),
            'device_type' => $request->input('device_type'),
            'platform' => $request->input('platform'),
            'app_version' => $request->input('app_version'),
        ];

        try {
            UploadBackupJob::dispatch($user->id, $tempPath, $options);

            return response()->json([
                'status' => 'success',
                'success' => true,
                'message' => 'Backup upload is being processed in the background.'
            ]);
        } catch (\Exception $e) {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/v1/backups/restore/preview
     * Generate dry-run preview report before applying restore.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'backup_id' => 'nullable|integer|exists:user_backups,id',
            'backup_file' => 'nullable|file|mimes:zip|max:20480',
            'password' => 'nullable|string',
        ]);

        $user = $request->user();
        $password = $request->input('password');

        try {
            if ($request->has('backup_id')) {
                $backup = UserBackup::where('user_id', $user->id)->findOrFail($request->input('backup_id'));
                $disk = Storage::disk($backup->storage_type);
                $tempFile = tempnam(sys_get_temp_dir(), 'restore_preview_') . '.zip';
                file_put_contents($tempFile, $disk->get($backup->file_name));
            } elseif ($request->hasFile('backup_file')) {
                $file = $request->file('backup_file');
                $tempFile = tempnam(sys_get_temp_dir(), 'restore_preview_') . '.zip';
                move_uploaded_file($file->getRealPath(), $tempFile);
            } else {
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'No backup source provided.'
                ], 400);
            }

            // Perform dry-run preview synchronously
            $report = $this->restoreService->generatePreviewReport($user->id, $tempFile, $password);
            unlink($tempFile);

            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/v1/backups/restore
     * Restore database from cloud or local file payload.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_id' => 'nullable|integer|exists:user_backups,id',
            'backup_file' => 'nullable|file|mimes:zip|max:20480',
            'conflict_resolution' => 'required|string|in:replace,merge',
            'modules' => 'nullable|array',
            'password' => 'nullable|string',
        ]);

        $user = $request->user();
        $conflictResolution = $request->input('conflict_resolution');
        $modules = $request->input('modules', []);
        $password = $request->input('password');

        try {
            if ($request->has('backup_id')) {
                $backup = UserBackup::where('user_id', $user->id)->findOrFail($request->input('backup_id'));
                $disk = Storage::disk($backup->storage_type);
                $tempFile = tempnam(sys_get_temp_dir(), 'restore_') . '.zip';
                file_put_contents($tempFile, $disk->get($backup->file_name));
                $backupId = $backup->id;
            } elseif ($request->hasFile('backup_file')) {
                $file = $request->file('backup_file');
                $tempFile = tempnam(sys_get_temp_dir(), 'restore_') . '.zip';
                move_uploaded_file($file->getRealPath(), $tempFile);
                $backupId = null;
            } else {
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'No backup source provided.'
                ], 400);
            }

            // Verify check-sum integrity first (dry-run extract) before queuing
            try {
                $this->restoreService->extractBackupJson($tempFile, $password);
            } catch (\Exception $ex) {
                unlink($tempFile);
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'Integrity checksum validation failed: ' . $ex->getMessage()
                ], 400);
            }

            // Dispatch to background queue for execution
            RestoreBackupJob::dispatch($user->id, $tempFile, $conflictResolution, $modules, $password, $backupId);

            return response()->json([
                'status' => 'success',
                'success' => true,
                'message' => 'Restore operation queued successfully in the background.'
            ]);

        } catch (\Exception $e) {
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * DELETE /api/v1/backups/{id}
     * Delete user cloud backup.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $backup = UserBackup::where('user_id', $user->id)->findOrFail($id);

        try {
            DeleteBackupJob::dispatch($backup->id);

            return response()->json([
                'status' => 'success',
                'success' => true,
                'message' => 'Backup deletion job queued successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
