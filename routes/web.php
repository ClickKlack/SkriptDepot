<?php

use App\Http\Controllers\ScriptDeliveryController;
use Illuminate\Support\Facades\Route;

// Die Startseite hat keinen eigenen Inhalt: Besucher landen direkt im Nutzerportal.
Route::redirect('/', '/portal')->name('home');

// Auslieferung an Tampermonkey: Auth über den Token in der URL, kein Login, kein Cookie.
Route::middleware('throttle:deliveries')
    ->prefix('s/{token}')
    ->where(['token' => '[A-Za-z0-9]{40}', 'slug' => '[a-z0-9-]+'])
    ->group(function (): void {
        Route::get('{slug}.meta.js', [ScriptDeliveryController::class, 'meta'])->name('scripts.meta');
        Route::get('{slug}.user.js', [ScriptDeliveryController::class, 'user'])->name('scripts.user');
    });
