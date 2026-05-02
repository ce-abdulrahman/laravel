<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $supportedLocales = ['en', 'ku', 'ar'];

        // Check session for saved language
        $sessionLocale = Session::get('locale');
        if (is_string($sessionLocale) && in_array($sessionLocale, $supportedLocales, true)) {
            $locale = $sessionLocale;
        }
        // Check browser language
        else {
            $preferred = strtolower((string) $request->getPreferredLanguage());
            $primary = strtolower(strtok(str_replace('_', '-', $preferred), '-') ?: '');

            // Browsers often report Sorani Kurdish as "ckb"
            if ($primary === 'ckb') {
                $primary = 'ku';
            }

            $fallback = (string) config('app.fallback_locale', 'en');
            $locale = in_array($primary, $supportedLocales, true) ? $primary : $fallback;
            Session::put('locale', $locale);
        }

        // Set the application locale
        App::setLocale($locale);

        // Set locale for carbon dates
        \Carbon\Carbon::setLocale($locale);

        return $next($request);
    }
}
