<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PrayerWidgetService;
use Illuminate\Http\Request;

class PrayerWidgetController extends Controller
{
    protected PrayerWidgetService $widgetService;

    public function __construct(PrayerWidgetService $widgetService)
    {
        $this->widgetService = $widgetService;
    }

    public function index(Request $request)
    {
        // Assemble payload
        $payload = $this->widgetService->getWidgetPayload($request);
        
        // Generate version hash incorporating date, location, settings, and user preferences
        $versionHash = $this->widgetService->getVersionHash($request, $payload);
        $payload['version_hash'] = $versionHash;

        $etag = '"' . $versionHash . '"';
        
        // Perform HTTP cache validation
        if ($request->header('If-None-Match') === $etag || $request->input('version_hash') === $versionHash) {
            return response()->json(null, 304)->header('ETag', $etag);
        }

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $payload
        ])->header('ETag', $etag);
    }
}
