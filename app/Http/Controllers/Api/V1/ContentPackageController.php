<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentPackageController extends Controller
{
    /**
     * Get manifest of available offline packages.
     */
    public function manifest(Request $request): JsonResponse
    {
        return response()->json([
            'packages' => [],
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Download a specific offline package.
     */
    public function download(Request $request, string $package)
    {
        return response()->json([
            'error' => 'Package not found.',
        ], 404);
    }
}
