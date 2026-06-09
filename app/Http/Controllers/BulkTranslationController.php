<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Services\AiTranslationService;
use App\Jobs\BatchTranslateJob;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkTranslationController extends Controller
{
    protected TranslationService $translationService;
    protected AiTranslationService $aiService;

    public function __construct(TranslationService $translationService, AiTranslationService $aiService)
    {
        $this->translationService = $translationService;
        $this->aiService = $aiService;
    }

    /**
     * Show the bulk translation editing/management grid matrix.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $languages = Language::ordered()->get();
        
        $query = TranslationKey::query();

        if ($request->filled('group')) {
            $query->where('group', $request->input('group'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('group', 'like', "%{$search}%");
            });
        }

        $keys = $query->with('translations')->orderBy('key')->paginate(50)->withQueryString();
        $groups = TranslationKey::select('group')->distinct()->pluck('group')->toArray();

        return view('translations.bulk', compact('keys', 'languages', 'groups'));
    }

    /**
     * Bulk update translations in batch.
     */
    public function bulkUpdate(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'translations' => 'required|array', // Structure: [translation_key_id => [language_id => value]]
        ]);

        UiTranslation::$currentChangeSource = 'manual';

        DB::transaction(function () use ($validated) {
            foreach ($validated['translations'] as $keyId => $langValues) {
                foreach ($langValues as $langId => $val) {
                    UiTranslation::updateOrCreate(
                        [
                            'translation_key_id' => $keyId,
                            'language_id' => $langId,
                        ],
                        [
                            'value' => $val !== '' ? $val : null,
                            'is_auto_generated' => false,
                        ]
                    );
                }
            }
        });

        $this->translationService->clearCache();

        return redirect()->back()->with('success', 'Bulk translations updated successfully.');
    }

    /**
     * Bulk delete translation keys.
     */
    public function bulkDelete(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'keys' => 'required|array',
            'keys.*' => 'exists:translation_keys,id',
        ]);

        DB::transaction(function () use ($validated) {
            TranslationKey::whereIn('id', $validated['keys'])->delete();
        });

        $this->translationService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Selected keys deleted successfully.',
        ]);
    }

    /**
     * Dispatches BatchTranslateJob for background translation.
     */
    public function bulkGenerateAI(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'keys' => 'required|array',
            'keys.*' => 'exists:translation_keys,id',
            'locale' => 'required|exists:languages,code',
        ]);

        $keyNames = TranslationKey::whereIn('id', $validated['keys'])->pluck('key')->toArray();

        // Dispatch batch translation job
        BatchTranslateJob::dispatch($keyNames, $validated['locale']);

        return response()->json([
            'success' => true,
            'message' => 'Batch AI translation job dispatched to queue. It will process in the background.',
        ]);
    }
}
