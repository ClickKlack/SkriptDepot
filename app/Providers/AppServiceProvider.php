<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Großzügiges Limit: Update-Checks sind selten, das Limit dient nur dem Missbrauchsschutz.
        RateLimiter::for('deliveries', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));
    }
}
