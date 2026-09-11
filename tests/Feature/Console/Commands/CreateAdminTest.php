<?php

use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('legt einen Administrator an und verschickt die Einladung', function () {
    Notification::fake();

    $this->artisan('admin:create', ['name' => 'Chef', 'email' => 'chef@example.test'])->assertSuccessful();

    $user = User::where('email', 'chef@example.test')->firstOrFail();
    expect($user->is_admin)->toBeTrue()
        ->and($user->hasVerifiedEmail())->toBeFalse()
        ->and($user->invited_at)->not->toBeNull();
    Notification::assertSentTo($user, UserInvitation::class);
});

it('legt mit Passwort einen sofort nutzbaren Administrator an', function () {
    Notification::fake();

    $this->artisan('admin:create', ['name' => 'Chef', 'email' => 'chef@example.test', '--password' => 'sehr-geheim-1'])
        ->assertSuccessful();

    $user = User::where('email', 'chef@example.test')->firstOrFail();
    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and(Hash::check('sehr-geheim-1', $user->password))->toBeTrue()
        ->and($user->canAccessPanel(filament()->getPanel('admin')))->toBeTrue();
    Notification::assertNothingSent();
});

it('lehnt eine bereits vergebene E-Mail-Adresse ab', function () {
    User::factory()->create(['email' => 'chef@example.test']);

    $this->artisan('admin:create', ['name' => 'Chef', 'email' => 'chef@example.test'])->assertFailed();

    expect(User::where('email', 'chef@example.test')->count())->toBe(1);
});
