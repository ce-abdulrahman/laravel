<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Jobs\FlushTranslationAnalyticsJob;
use Symfony\Component\HttpFoundation\Response;

class TranslationAnalyticsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Perform any tasks after the response has been delivered to the client.
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            $buffer = Cache::get('translation_analytics_buffer', []);
            if (count($buffer) >= 50) {
                FlushTranslationAnalyticsJob::dispatch();
            }
        } catch (\Exception $e) {
            // Silence middleware exceptions to ensure request completes
        }
    }
}
