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
        $locale = \App\Helpers\LanguageHelper::resolveLocale($request);

        // Save in session if active
        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        // Set the application locale
        App::setLocale($locale);

        // Set locale for carbon dates
        \Carbon\Carbon::setLocale($locale);

        return $next($request);
    }
}
