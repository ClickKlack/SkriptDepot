<?php

use App\Filament\Pages\ResolveWatermark;
use App\Filament\Resources\Deliveries\DeliveryResource;
use App\Filament\Resources\Entitlements\EntitlementResource;
use App\Filament\Resources\Scripts\ScriptResource;
use App\Filament\Resources\ScriptVersions\ScriptVersionResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;

it('leitet nicht angemeldete Besucher zum Admin-Login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('verweigert normalen Nutzern das Admin-Panel mit 403', function () {
    $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
});

it('zeigt Administratoren jede Verwaltungsseite', function (string $url) {
    $this->actingAs(User::factory()->admin()->create())->get($url)->assertOk();
})->with([
    'Nutzer' => fn () => UserResource::getUrl('index', panel: 'admin'),
    'Skripte' => fn () => ScriptResource::getUrl('index', panel: 'admin'),
    'Versionen' => fn () => ScriptVersionResource::getUrl('index', panel: 'admin'),
    'Freischaltungen' => fn () => EntitlementResource::getUrl('index', panel: 'admin'),
    'Auslieferungen' => fn () => DeliveryResource::getUrl('index', panel: 'admin'),
    'Wasserzeichen auflösen' => fn () => ResolveWatermark::getUrl(panel: 'admin'),
]);
