<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        $supportedLocales = ['en', 'ku', 'ar'];

        if (in_array($locale, $supportedLocales, true)) {
            Session::put('locale', $locale);
            App::setLocale($locale);
        }

        return redirect()->back();
    }

    public function getCurrentLanguage()
    {
        $locale = App::getLocale();
        $direction = in_array($locale, ['ar', 'ku'], true) ? 'rtl' : 'ltr';

        $languages = [
            'en' => [
                'name' => 'English',
                'native' => 'English',
                'dir' => 'ltr',
                'flag' => '🇬🇧',
            ],
            'ku' => [
                'name' => 'Kurdish',
                'native' => 'کوردی',
                'dir' => 'rtl',
                'flag' => '🇮🇶',
            ],
            'ar' => [
                'name' => 'Arabic',
                'native' => 'العربية',
                'dir' => 'rtl',
                'flag' => '🇸🇦',
            ],
        ];

        return response()->json([
            'locale' => $locale,
            'direction' => $direction,
            'language' => $languages[$locale] ?? $languages['en'],
            'available' => $languages,
        ]);
    }
}

