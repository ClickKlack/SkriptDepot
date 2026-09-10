<?php

use App\Models\Entitlement;
use App\Models\Script;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

it('erzeugt beim Anlegen automatisch einen opaken Token', function () {
    $entitlement = Entitlement::factory()->make(['token' => null]);

    $entitlement->save();

    expect($entitlement->fresh()->token)->toMatch('/^[A-Za-z0-9]{40}$/');
});

it('erlaubt pro Nutzer und Skript nur eine Freischaltung', function () {
    $user = User::factory()->create();
    $script = Script::factory()->create();
    Entitlement::factory()->for($user)->for($script)->create();

    expect(fn () => Entitlement::factory()->for($user)->for($script)->create())
        ->toThrow(UniqueConstraintViolationException::class);
});

it('liefert über den Scope nur aktive Freischaltungen', function () {
    $active = Entitlement::factory()->create();
    Entitlement::factory()->inactive()->create();

    expect(Entitlement::active()->pluck('id')->all())->toBe([$active->id]);
});
