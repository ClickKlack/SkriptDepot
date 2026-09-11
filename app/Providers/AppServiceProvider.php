<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Produktiv nur über HTTPS: betrifft alle erzeugten Links, auch die Update-URLs in den Skripten.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Großzügiges Limit: Update-Checks sind selten, das Limit dient nur dem Missbrauchsschutz.
        RateLimiter::for('deliveries', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));
    }
}
