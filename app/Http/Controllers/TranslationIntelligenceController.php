<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Services\TranslationSemanticService;
use App\Services\TranslationSearchService;
use App\Services\TranslationGroupingService;
use App\Services\TranslationSuggestionService;
use App\Services\TranslationConsistencyService;
use App\Services\AiTranslationService;
use Illuminate\Http\Request;

class TranslationIntelligenceController extends Controller
{
    protected TranslationSemanticService $semanticService;
    protected TranslationSearchService $searchService;
    protected TranslationGroupingService $groupingService;
    protected TranslationSuggestionService $suggestionService;
    protected TranslationConsistencyService $consistencyService;
    protected AiTranslationService $aiService;

    public function __construct(
        TranslationSemanticService $semanticService,
        TranslationSearchService $searchService,
        TranslationGroupingService $groupingService,
        TranslationSuggestionService $suggestionService,
        TranslationConsistencyService $consistencyService,
        AiTranslationService $aiService
    ) {
        $this->semanticService = $semanticService;
        $this->searchService = $searchService;
        $this->groupingService = $groupingService;
        $this->suggestionService = $suggestionService;
        $this->consistencyService = $consistencyService;
        $this->aiService = $aiService;
    }

    /**
     * Render the Intelligence Dashboard view.
     */
    public function index()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $languages = Language::ordered()->get();
        $totalKeys = TranslationKey::count();
        
        // Compile quick group summary counts
        $groupsSummary = TranslationKey::select('group', \DB::raw('count(*) as count'))
            ->groupBy('group')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();

        return view('translations.intelligence', compact('languages', 'totalKeys', 'groupsSummary'));
    }

    /**
     * Handle semantic searches.
     */
    public function search(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $request->validate(['query' => 'required|string']);
        
        $results = $this->searchService->search($request->input('query'));

        // Format for JSON response
        $formatted = array_map(function ($item) {
            return [
                'key' => $item['key']->key,
                'group' => $item['key']->group,
                'description' => $item['key']->description,
                'score' => $item['score']
            ];
        }, $results);

        return response()->json([
            'success' => true,
            'results' => $formatted
        ]);
    }

    /**
     * Suggest structured keys from phrase.
     */
    public function suggest(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $request->validate(['text' => 'required|string']);

        $suggestions = $this->suggestionService->suggestKey($request->input('text'));

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Run bulk restructuring of groups.
     */
    public function rebuildGroups(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $result = $this->groupingService->groupRebuild();

        return response()->json($result);
    }

    /**
     * Run consistency diagnostics scans.
     */
    public function consistency()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $reports = $this->consistencyService->checkConsistency();

        return response()->json([
            'success' => true,
            'diagnostics' => $reports
        ]);
    }

    /**
     * Find similar translation keys.
     */
    public function similar(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $request->validate(['key' => 'required|string']);

        $results = $this->semanticService->findSimilarKeys($request->input('key'));

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    /**
     * AI translate a single key with semantic context.
     */
    public function translateAi(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $request->validate([
            'key' => 'required|string',
            'locale' => 'required|exists:languages,code',
        ]);

        $translated = $this->aiService->translateWithContext(
            $request->input('key'),
            $request->input('locale')
        );

        return response()->json([
            'success' => true,
            'translated' => $translated
        ]);
    }
}
