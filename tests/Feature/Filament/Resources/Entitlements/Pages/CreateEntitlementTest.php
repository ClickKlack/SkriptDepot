<?php

use App\Filament\Resources\Entitlements\Pages\CreateEntitlement;
use App\Models\Entitlement;
use App\Models\Script;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
});

it('schaltet einen Nutzer für ein Skript frei und erzeugt den Token', function () {
    $user = User::factory()->create();
    $script = Script::factory()->create();

    Livewire::test(CreateEntitlement::class)
        ->fillForm(['user_id' => $user->id, 'script_id' => $script->id, 'is_active' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    $entitlement = Entitlement::whereBelongsTo($user)->whereBelongsTo($script)->first();
    expect($entitlement)->not->toBeNull()
        ->and($entitlement->token)->toHaveLength(40)
        ->and($entitlement->is_active)->toBeTrue();
});

it('lehnt eine zweite Freischaltung für dasselbe Nutzer-Skript-Paar ab', function () {
    $existing = Entitlement::factory()->create();

    Livewire::test(CreateEntitlement::class)
        ->fillForm(['user_id' => $existing->user_id, 'script_id' => $existing->script_id, 'is_active' => true])
        ->call('create')
        ->assertHasFormErrors(['user_id']);

    expect(Entitlement::count())->toBe(1);
});
