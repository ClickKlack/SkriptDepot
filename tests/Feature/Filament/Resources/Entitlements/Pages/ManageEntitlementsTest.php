<?php

use App\Filament\Resources\Entitlements\EntitlementResource;
use App\Filament\Resources\Entitlements\Pages\ListEntitlements;
use App\Filament\Resources\Entitlements\Pages\ManageEntitlements;
use App\Models\Entitlement;
use App\Models\Script;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
});

it('listet alle Skripte mit der Zahl aktiver Freischaltungen', function () {
    $script = Script::factory()->create(['name' => 'Alpha']);
    Entitlement::factory()->for($script)->create();
    Entitlement::factory()->for($script)->inactive()->create();
    $other = Script::factory()->create(['name' => 'Beta']);

    Livewire::test(ListEntitlements::class)
        ->assertCanSeeTableRecords([$script, $other])
        ->assertTableColumnStateSet('active_entitlements_count', 1, $script)
        ->assertTableColumnStateSet('active_entitlements_count', 0, $other);
});

it('zeigt für ein Skript alle Nutzer mit ihrem Freischaltungsstand', function () {
    $script = Script::factory()->create();
    $entitled = User::factory()->create(['name' => 'Anna']);
    $entitlement = Entitlement::factory()->for($entitled)->for($script)->create();
    $revoked = User::factory()->create(['name' => 'Bernd']);
    Entitlement::factory()->for($revoked)->for($script)->inactive()->create();
    $unrelated = User::factory()->create(['name' => 'Clara']);

    $this->get(EntitlementResource::getUrl('manage', ['record' => $script], panel: 'admin'))
        ->assertOk()
        ->assertSee($script->name);

    Livewire::test(ManageEntitlements::class, ['record' => $script->getRouteKey()])
        ->assertCanSeeTableRecords([$entitled, $revoked, $unrelated])
        ->assertTableColumnStateSet('is_active', true, $entitled)
        ->assertTableColumnStateSet('is_active', false, $revoked)
        ->assertTableColumnStateSet('is_active', false, $unrelated)
        ->assertTableColumnStateSet('token', $entitlement->token, $entitled)
        ->assertTableColumnStateSet('token', null, $unrelated);
});

it('legt beim ersten Einschalten die Freischaltung mit Token an', function () {
    $script = Script::factory()->create();
    $user = User::factory()->create();

    Livewire::test(ManageEntitlements::class, ['record' => $script->getRouteKey()])
        ->call('updateTableColumnState', 'is_active', (string) $user->getKey(), true);

    $entitlement = Entitlement::whereBelongsTo($user)->whereBelongsTo($script)->first();
    expect($entitlement)->not->toBeNull()
        ->and($entitlement->is_active)->toBeTrue()
        ->and($entitlement->token)->toHaveLength(40);
});

it('deaktiviert beim Ausschalten und behält den Token für spätere Reaktivierung', function () {
    $entitlement = Entitlement::factory()->create();
    $component = Livewire::test(ManageEntitlements::class, ['record' => $entitlement->script->getRouteKey()]);

    $component->call('updateTableColumnState', 'is_active', (string) $entitlement->user_id, false);

    expect($entitlement->fresh()->is_active)->toBeFalse();
    $this->assertDatabaseCount('entitlements', 1);

    $component->call('updateTableColumnState', 'is_active', (string) $entitlement->user_id, true);

    expect($entitlement->fresh()->is_active)->toBeTrue()
        ->and($entitlement->fresh()->token)->toBe($entitlement->token);
});

it('legt beim Ausschalten eines nie freigeschalteten Nutzers nichts an', function () {
    $script = Script::factory()->create();
    $user = User::factory()->create();

    Livewire::test(ManageEntitlements::class, ['record' => $script->getRouteKey()])
        ->call('updateTableColumnState', 'is_active', (string) $user->getKey(), false);

    $this->assertDatabaseCount('entitlements', 0);
});
