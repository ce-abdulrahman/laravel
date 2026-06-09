<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Services\TranslationAnalyticsService;
use App\Services\TranslationMetricsService;
use App\Services\MissingTranslationAnalyticsService;
use App\Services\TranslationPerformanceService;
use App\Services\AiTranslationAnalyticsService;
use App\Services\AiTranslationService;
use Illuminate\Http\Request;

class TranslationAnalyticsController extends Controller
{
    protected TranslationAnalyticsService $analyticsService;
    protected TranslationMetricsService $metricsService;
    protected MissingTranslationAnalyticsService $missingService;
    protected TranslationPerformanceService $performanceService;
    protected AiTranslationAnalyticsService $aiAnalyticsService;
    protected AiTranslationService $aiService;

    public function __construct(
        TranslationAnalyticsService $analyticsService,
        TranslationMetricsService $metricsService,
        MissingTranslationAnalyticsService $missingService,
        TranslationPerformanceService $performanceService,
        AiTranslationAnalyticsService $aiAnalyticsService,
        AiTranslationService $aiService
    ) {
        $this->analyticsService = $analyticsService;
        $this->metricsService = $metricsService;
        $this->missingService = $missingService;
        $this->performanceService = $performanceService;
        $this->aiAnalyticsService = $aiAnalyticsService;
        $this->aiService = $aiService;
    }

    /**
     * Render the Translation Analytics Dashboard.
     */
    public function index()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $languages = Language::ordered()->get();
        $heatmapData = $this->metricsService->generateHeatmapData();
        $langDistribution = $this->metricsService->languageDistribution();
        $missingAnalysis = $this->missingService->getMissingAnalysis();
        $performanceStats = $this->performanceService->getPerformanceStats();
        $aiStats = $this->aiAnalyticsService->getAiAnalytics();

        // Get total requests today (sum from translation_analytics)
        $totalHitsToday = \DB::table('translation_analytics')
            ->whereDate('created_at', date('Y-m-d'))
            ->sum('hit_count');

        // Total unique missing keys logged in analytics
        $totalMissingKeys = \DB::table('translation_analytics')
            ->where('is_missing', true)
            ->distinct('key_name')
            ->count('key_name');

        return view('translations.analytics.index', compact(
            'languages',
            'heatmapData',
            'langDistribution',
            'missingAnalysis',
            'performanceStats',
            'aiStats',
            'totalHitsToday',
            'totalMissingKeys'
        ));
    }

    /**
     * Manually trigger writing buffer hits to database.
     */
    public function flushAnalytics()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $this->analyticsService->flush();

        return response()->json([
            'success' => true,
            'message' => 'Analytics buffer flushed successfully.'
        ]);
    }

    /**
     * Manually trigger AI translation generation/fix for missing keys.
     */
    public function generateAiFix(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $request->validate([
            'key' => 'required|string',
            'locale' => 'nullable|string|exists:languages,code',
        ]);

        $key = $request->input('key');
        $locale = $request->input('locale');
        $languages = $locale ? Language::where('code', $locale)->get() : Language::all();

        $fixedLocales = [];
        foreach ($languages as $lang) {
            // Check if translation doesn't exist or is empty
            $translationKey = TranslationKey::where('key', $key)->first();
            $exists = false;
            if ($translationKey) {
                $exists = UiTranslation::where('translation_key_id', $translationKey->id)
                    ->where('language_id', $lang->id)
                    ->whereNotNull('value')
                    ->where('value', '!=', '')
                    ->exists();
            }

            if (!$exists) {
                $translated = $this->aiService->translateWithContext($key, $lang->code);
                if ($translated) {
                    $fixedLocales[] = $lang->name;
                }
            }
        }

        // Delete from missing translation log if we fixed translations
        if (!empty($fixedLocales)) {
            \DB::table('translation_analytics')
                ->where('key_name', $key)
                ->where('is_missing', true)
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'AI translation fixed successfully.',
            'fixed_locales' => $fixedLocales
        ]);
    }
}
