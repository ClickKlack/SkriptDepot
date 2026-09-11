<?php

use App\Models\User;
use App\Models\Watermark;

it('erzeugt beim Anlegen automatisch einen geheimen Seed', function () {
    $user = User::factory()->make(['seed' => null]);

    $user->save();

    expect($user->fresh()->seed)->toMatch('/^[0-9a-f]{64}$/');
});

it('gibt den Seed nicht in der Serialisierung preis', function () {
    $user = User::factory()->create();

    expect($user->toArray())->not->toHaveKey('seed');
});

it('lässt nur Administratoren in das Admin-Panel', function () {
    $adminPanel = filament()->getPanel('admin');
    $portalPanel = filament()->getPanel('portal');
    $member = User::factory()->create();
    $admin = User::factory()->admin()->create();

    expect($member->canAccessPanel($adminPanel))->toBeFalse()
        ->and($member->canAccessPanel($portalPanel))->toBeTrue()
        ->and($admin->canAccessPanel($adminPanel))->toBeTrue()
        ->and($admin->canAccessPanel($portalPanel))->toBeTrue();
});

it('lässt gesperrte Nutzer in kein Panel', function () {
    $user = User::factory()->admin()->create(['blocked_at' => now()]);

    expect($user->canAccessPanel(filament()->getPanel('admin')))->toBeFalse()
        ->and($user->canAccessPanel(filament()->getPanel('portal')))->toBeFalse();
});

it('verweigert das Löschen, sobald ein Wasserzeichen ausgeliefert wurde', function () {
    $user = Watermark::factory()->create()->entitlement->user;

    expect(fn () => $user->delete())->toThrow(LogicException::class);
    $this->assertModelExists($user);
});
