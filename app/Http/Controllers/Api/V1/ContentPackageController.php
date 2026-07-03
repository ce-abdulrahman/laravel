<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ContentPackageController extends Controller
{
    /**
     * Get manifests of all available packages.
     */
    public function manifests(Request $request): JsonResponse
    {
        $manifests = [];
        $packagesDir = public_path('packages');
        
        if (File::exists($packagesDir)) {
            $files = File::glob("{$packagesDir}/*_manifest.json");
            foreach ($files as $file) {
                $manifests[] = json_decode(File::get($file), true);
            }
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $manifests,
        ]);
    }

    /**
     * Get manifest of a specific package.
     */
    public function manifest(Request $request, string $package): JsonResponse
    {
        $manifestFile = public_path("packages/{$package}_manifest.json");
        
        if (!File::exists($manifestFile)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Package manifest not found.',
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => json_decode(File::get($manifestFile), true),
        ]);
    }

    /**
     * Download a specific offline package.
     */
    public function download(Request $request, string $package)
    {
        $zipPath = public_path("packages/{$package}.zip");
        
        if (!File::exists($zipPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Package ZIP file not found.',
            ], 404);
        }
        
        return response()->download($zipPath, "{$package}.zip", [
            'Content-Type' => 'application/zip',
        ]);
    }
}
