<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Services\TranslationService;
use App\Services\TranslationRegistryService;
use App\Services\MissingTranslationReportService;
use App\Services\TranslationCoverageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class TranslationManagerController extends Controller
{
    protected TranslationService $translationService;

    /**
     * Constructor injection of TranslationService.
     */
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Display a listing of translation keys and values.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $languages = Language::ordered()->get();
        $activeLanguagesCount = $languages->where('is_active', true)->count();

        $query = TranslationKey::query();

        // Filter by group
        if ($request->filled('group')) {
            $query->where('group', $request->input('group'));
        }

        // Search in keys or translation values
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('group', 'like', "%{$search}%")
                  ->orWhereHas('translations', function ($sub) use ($search) {
                      $sub->where('value', 'like', "%{$search}%");
                  });
            });
        }

        // Filter for keys with missing translations
        if ($request->boolean('missing')) {
            $query->where(function ($q) use ($activeLanguagesCount) {
                $q->whereHas('translations', function ($sub) {
                    $sub->whereNull('value')->orWhere('value', '');
                })
                ->orWhereDoesntHave('translations')
                ->orHas('translations', '<', $activeLanguagesCount);
            });
        }

        // Get paginated keys
        $keys = $query->with('translations')
            ->orderBy('group')
            ->orderBy('key')
            ->paginate(30)
            ->withQueryString();

        // Get unique groups for the filter dropdown
        $groups = TranslationKey::select('group')->distinct()->pluck('group')->toArray();

        // Calculate diagnostics stats for the premium dashboard widgets
        $reportService = app(MissingTranslationReportService::class);
        $reportStats = $reportService->getReportStats();
        $lastScan = Cache::get('translation_last_scan_at', 'Never');

        return view('translations.manager', compact('keys', 'languages', 'groups', 'activeLanguagesCount', 'reportStats', 'lastScan'));
    }

    /**
     * Store a newly created translation key.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'key' => 'required|string|unique:translation_keys,key|max:255',
            'group' => 'required|string|max:100',
            'description' => 'nullable|string',
            'translations' => 'nullable|array',
            'translations.*' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $keyRecord = TranslationKey::create([
                'key' => $validated['key'],
                'group' => $validated['group'],
                'description' => $validated['description'] ?? null,
            ]);

            $languages = Language::all();
            foreach ($languages as $lang) {
                $value = $validated['translations'][$lang->id] ?? null;
                UiTranslation::create([
                    'translation_key_id' => $keyRecord->id,
                    'language_id' => $lang->id,
                    'value' => $value !== '' ? $value : null,
                    'is_auto_generated' => false,
                ]);
            }
        });

        $this->translationService->clearCache();

        return redirect()->route('translations-manager.index')
            ->with('success', 'Translation key created successfully.');
    }

    /**
     * Update an individual translation value inline via AJAX.
     */
    public function updateInline(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'translation_key_id' => 'required|exists:translation_keys,id',
            'language_id' => 'required|exists:languages,id',
            'value' => 'nullable|string',
        ]);

        $translation = UiTranslation::updateOrCreate(
            [
                'translation_key_id' => $validated['translation_key_id'],
                'language_id' => $validated['language_id'],
            ],
            [
                'value' => $validated['value'] !== '' ? $validated['value'] : null,
                'is_auto_generated' => false,
            ]
        );

        $language = Language::find($validated['language_id']);
        if ($language) {
            $this->translationService->clearCache($language->code);
        }

        return response()->json([
            'success' => true,
            'message' => 'Translation updated successfully.',
            'is_empty' => empty($validated['value']),
        ]);
    }

    /**
     * Remove a translation key from storage.
     */
    public function destroy(int $id)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $keyRecord = TranslationKey::findOrFail($id);
        
        $keyRecord->delete(); // This cascades deletion to ui_translations due to FK constraint

        $this->translationService->clearCache();

        return redirect()->route('translations-manager.index')
            ->with('success', 'Translation key deleted successfully.');
    }

    /**
     * Export translations to JSON or CSV.
     */
    public function export(Request $request, \App\Services\TranslationImportExportService $importExportService)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'locale' => 'required|exists:languages,code',
            'format' => 'required|in:json,csv',
        ]);

        $locale = $validated['locale'];
        $format = $validated['format'];

        if ($format === 'json') {
            $data = $importExportService->exportToJson($locale);
            $fileName = "translations_{$locale}_" . date('Ymd_His') . ".json";
            return response()->json($data, 200, [
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        } else {
            $csv = $importExportService->exportToCsv($locale);
            $fileName = "translations_{$locale}_" . date('Ymd_His') . ".csv";
            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        }
    }

    /**
     * Import translations from JSON or CSV.
     */
    public function import(Request $request, \App\Services\TranslationImportExportService $importExportService)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'locale' => 'required|exists:languages,code',
            'file' => 'required|file',
            'create_keys' => 'nullable',
        ]);

        $locale = $validated['locale'];
        $file = $request->file('file');
        $createKeys = $request->has('create_keys') && ($request->input('create_keys') === '1' || $request->input('create_keys') === 'true' || $request->input('create_keys') === true);

        $contents = file_get_contents($file->getRealPath());
        $originalName = $file->getClientOriginalName();
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        try {
            if ($extension === 'json') {
                $result = $importExportService->importFromJson($contents, $locale, $createKeys);
            } else {
                $result = $importExportService->importFromCsv($contents, $locale, $createKeys);
            }

            return redirect()->back()->with('success', "Import completed: {$result['imported']} keys created, {$result['updated']} updated, {$result['skipped']} skipped.");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Fetch edit version history for a translation record.
     */
    public function history(int $translationId)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $translation = UiTranslation::with(['key', 'language'])->findOrFail($translationId);
        $versions = $translation->versions()->with('user')->orderBy('id', 'desc')->get();

        return response()->json([
            'translation' => $translation,
            'versions' => $versions,
        ]);
    }

    /**
     * Rollback translation to a specific version.
     */
    public function rollback(int $versionId)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $version = \App\Models\UiTranslationVersion::findOrFail($versionId);
        $translation = UiTranslation::findOrFail($version->ui_translation_id);

        $translation->rollback($versionId);

        $language = Language::find($translation->language_id);
        if ($language) {
            $this->translationService->clearCache($language->code);
        }

        return redirect()->back()->with('success', 'Translation reverted successfully to the selected version.');
    }

    /**
     * Run the full translation integrity scan and display issues.
     */
    public function audit(\App\Services\TranslationIntegrityService $integrityService)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $results = $integrityService->runFullAudit();
        $languages = Language::all();

        return view('translations.audit', compact('results', 'languages'));
    }

    /**
     * Render the environment sync tool.
     */
    public function syncPage()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        return view('translations.sync');
    }

    /**
     * Pull remote translations and run conflict resolution.
     */
    public function syncPull(Request $request, \App\Services\TranslationSyncService $syncService)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'remote_url' => 'required|url',
            'strategy' => 'required|in:latest_wins,remote_wins,local_wins',
        ]);

        $result = $syncService->syncPull($validated['remote_url'], $validated['strategy']);

        if ($result['success']) {
            return redirect()->back()->with('success', "Pull sync completed: {$result['pulled']} new, {$result['updated']} updated, {$result['conflicts']} conflicts, {$result['skipped']} skipped.");
        }

        return redirect()->back()->withErrors(['remote_url' => 'Pull failed: ' . $result['error']]);
    }

    /**
     * Push current translations to a remote server.
     */
    public function syncPush(Request $request, \App\Services\TranslationSyncService $syncService)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'remote_url' => 'required|url',
        ]);

        $result = $syncService->syncPush($validated['remote_url']);

        if ($result['success']) {
            return redirect()->back()->with('success', "Push sync completed: {$result['pushed_count']} translations pushed successfully.");
        }

        return redirect()->back()->withErrors(['remote_url' => 'Push failed: ' . $result['error']]);
    }

    /**
     * API Pull synchronization endpoint.
     */
    public function apiSyncGet(Request $request)
    {
        $token = $request->header('X-Translation-Sync-Token');
        $configuredToken = config('translations.sync_token');

        if (empty($configuredToken) || $token !== $configuredToken) {
            return response()->json(['error' => 'Unauthorized sync token.'], 401);
        }

        $translations = DB::table('ui_translations')
            ->join('translation_keys', 'ui_translations.translation_key_id', '=', 'translation_keys.id')
            ->join('languages', 'ui_translations.language_id', '=', 'languages.id')
            ->select(
                'translation_keys.key',
                'languages.code as locale',
                'ui_translations.value',
                'ui_translations.updated_at'
            )
            ->get()
            ->map(function ($item) {
                return [
                    'key' => $item->key,
                    'locale' => $item->locale,
                    'value' => $item->value,
                    'updated_at' => $item->updated_at
                ];
            })
            ->toArray();

        return response()->json([
            'translations' => $translations,
        ]);
    }

    /**
     * API Push synchronization endpoint.
     */
    public function apiSyncPost(Request $request)
    {
        $token = $request->header('X-Translation-Sync-Token');
        $configuredToken = config('translations.sync_token');

        if (empty($configuredToken) || $token !== $configuredToken) {
            return response()->json(['error' => 'Unauthorized sync token.'], 401);
        }

        $validated = $request->validate([
            'translations' => 'required|array',
        ]);

        $pulled = 0;
        $updated = 0;
        $skipped = 0;

        UiTranslation::$currentChangeSource = 'sync';

        $languages = Language::all()->pluck('id', 'code')->toArray();

        foreach ($validated['translations'] as $remote) {
            $keyStr = $remote['key'] ?? null;
            $locale = $remote['locale'] ?? null;
            $value = $remote['value'] ?? null;

            if (!$keyStr || !$locale) {
                $skipped++;
                continue;
            }

            $languageId = $languages[$locale] ?? null;
            if (!$languageId) {
                $skipped++;
                continue;
            }

            $keyRecord = TranslationKey::firstOrCreate(
                ['key' => $keyStr],
                ['group' => explode('.', $keyStr)[0] ?? 'general']
            );

            $localTranslation = UiTranslation::where('translation_key_id', $keyRecord->id)
                ->where('language_id', $languageId)
                ->first();

            if (!$localTranslation) {
                UiTranslation::create([
                    'translation_key_id' => $keyRecord->id,
                    'language_id' => $languageId,
                    'value' => $value,
                    'is_auto_generated' => false,
                ]);
                $pulled++;
            } else {
                if ($localTranslation->value !== $value) {
                    $localTranslation->update([
                        'value' => $value,
                        'is_auto_generated' => false,
                    ]);
                    $updated++;
                } else {
                    $skipped++;
                }
            }
        }

        UiTranslation::$currentChangeSource = 'manual';

        // Clear all caches
        foreach (array_keys($languages) as $code) {
            $this->translationService->clearCache($code);
        }

        return response()->json([
            'success' => true,
            'pulled' => $pulled,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Display a comprehensive translation coverage report.
     */
    public function report(TranslationCoverageService $coverageService)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $data = $coverageService->getCoverageData();
        return view('translations.report', compact('data'));
    }

    /**
     * Trigger a scan of the codebase for translation keys.
     */
    public function scan(TranslationRegistryService $registryService)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        
        $keys = $registryService->scanCodebase();
        foreach ($keys as $key) {
            $registryService->registerKey($key);
        }

        Cache::put('translation_last_scan_at', now()->format('Y-m-d H:i:s'));
        $this->translationService->clearCache();

        return redirect()->back()->with('success', 'Codebase scanned successfully. Found and registered ' . count($keys) . ' keys.');
    }

    /**
     * Trigger synchronization with files and cache.
     */
    public function sync()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        Artisan::call('localization:sync');
        Cache::put('translation_last_scan_at', now()->format('Y-m-d H:i:s'));

        return redirect()->back()->with('success', 'Translation system fully synchronized and cache rebuilt.');
    }

    /**
     * Manually flush the translation caches.
     */
    public function clearCache()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $this->translationService->clearCache();

        return redirect()->back()->with('success', 'Translation caches flushed successfully.');
    }
}
