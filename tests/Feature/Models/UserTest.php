<?php

use App\Models\User;

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
