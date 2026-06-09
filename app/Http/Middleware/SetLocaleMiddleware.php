<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocaleMiddleware
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

        // Save resolved locale to session if available
        if ($locale && $request->hasSession()) {
            Session::put('locale', $locale);
        }

        // Configure runtime settings
        App::setLocale($locale);
        \Carbon\Carbon::setLocale($locale);

        return $next($request);
    }
}
