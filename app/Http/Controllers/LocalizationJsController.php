<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class LocalizationJsController extends Controller
{
    /**
     * Serve a curated subset of PHP file translations as JSON for JavaScript.
     *
     * Reads directly from lang/{locale}/*.php files (bypasses DB-backed loader).
     * Groups: common, validation, api, notifications.
     * Response is cached per locale for 1 hour.
     */
    public function translations(): JsonResponse
    {
        $locale = App::getLocale();
        $groups = ['common', 'validation', 'api', 'notifications'];

        $cacheKey = "js_translations_file_{$locale}";

        $translations = Cache::remember($cacheKey, 3600, function () use ($locale, $groups) {
            $data = [];
            $langPath = lang_path($locale);
            $fallback = lang_path(config('app.fallback_locale', 'en'));

            foreach ($groups as $group) {
                $path = "{$langPath}/{$group}.php";
                $fallbackPath = "{$fallback}/{$group}.php";

                if (File::exists($path)) {
                    $result = include $path;
                    if (is_array($result)) {
                        $data[$group] = $result;
                    }
                } elseif (File::exists($fallbackPath)) {
                    $result = include $fallbackPath;
                    if (is_array($result)) {
                        $data[$group] = $result;
                    }
                }
            }

            return $data;
        });

        return response()->json($translations)
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Content-Language', $locale);
    }
}
