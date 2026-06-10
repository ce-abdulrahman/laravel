<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TafsirBook;
use App\Models\Reciter;
use App\Models\Qiraat;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, __('common.unauthorized'));
        }

        $settings = Setting::firstOrCreate([]);
        $settings->load(['defaultTafsirBook', 'defaultReciter', 'defaultQiraat']);

        $tafsirBooks = TafsirBook::active()->orderBy('name')->get();
        $reciters = Reciter::active()->orderBy('name')->get();
        $qiraats = Qiraat::active()->orderBy('name')->get();

        // Dynamic language list from DB
        $languages = Language::active()->ordered()->get()
            ->mapWithKeys(fn($lang) => [$lang->code => "{$lang->native_name} ({$lang->name})"])
            ->toArray();

        // Scan available fonts dynamically per active language
        $activeLangCodes = Language::activeCodes();
        $availableFonts = [];
        foreach ($activeLangCodes as $code) {
            $availableFonts[$code] = array_map('basename', glob(public_path("fonts/{$code}/*.*")) ?: []);
        }

        return view('settings.index', compact(
            'settings', 'tafsirBooks', 'reciters', 'qiraats', 'languages', 'availableFonts'
        ));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, __('common.unauthorized'));
        }

        $settings = Setting::firstOrCreate([]);

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|max:2048',
            'default_language' => 'nullable|string|max:10',
            'default_tafsir_book_id' => 'nullable|exists:tafsir_books,id',
            'default_reciter_id' => 'nullable|exists:reciters,id',
            'default_qiraah_id' => 'nullable|exists:qiraats,id',
            'about_text' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
        ]);

        // Dynamic font validation per active language
        $fontRules = [];
        foreach (Language::activeCodes() as $code) {
            $fontRules["font_{$code}"] = 'nullable|string|max:255';
        }
        $validated = array_merge($validated, $request->validate($fontRules));

        if ($request->hasFile('app_logo')) {
            // سڕینەوەی لۆگۆی پێشوو
            if ($settings->app_logo) {
                Storage::disk('public')->delete($settings->app_logo);
            }
            $validated['app_logo'] = $request->file('app_logo')->store('settings', 'public');
        }

        // ئەگەر لۆگۆکە بسڕیتەوە
        if ($request->boolean('remove_logo') && $settings->app_logo) {
            Storage::disk('public')->delete($settings->app_logo);
            $validated['app_logo'] = null;
        }

        $settings->update($validated);

        // پاککردنەوەی کاشی ڕێکخستنەکان
        Cache::forget('app_settings');

        return redirect()
            ->route('settings.index')
            ->with('success', __('settings.messages.updated'));
    }

    /**
     * Get public settings (for API or frontend).
     */
    public function getPublicSettings()
    {
        $settings = Cache::remember('app_settings', 3600, function () {
            return Setting::firstOrCreate([]);
        });

        return response()->json([
            'app_name' => $settings->app_name,
            'app_logo' => $settings->app_logo ? Storage::url($settings->app_logo) : null,
            'default_language' => $settings->default_language,
            'about_text' => $settings->about_text,
            'contact_email' => $settings->contact_email,
        ]);
    }

    /**
     * Get available languages dynamically from the database.
     */
    private function getAvailableLanguages(): array
    {
        return Language::active()->ordered()->get()
            ->mapWithKeys(fn($lang) => [$lang->code => "{$lang->native_name} ({$lang->name})"])
            ->toArray();
    }
}