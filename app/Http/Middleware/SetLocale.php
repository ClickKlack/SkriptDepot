<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stellt die Oberflächensprache auf die im Nutzerprofil hinterlegte Sprache um.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale;

        if (is_string($locale) && in_array($locale, config('skriptdepot.locales'), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
