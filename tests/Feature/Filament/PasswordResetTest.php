<?php

use App\Filament\Auth\RequestPasswordReset;
use App\Models\User;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('verlinkt auf beiden Login-Seiten die Passwort-vergessen-Funktion', function (string $panel) {
    $this->get("/{$panel}/login")->assertSee("/{$panel}/password-reset/request");
})->with(['portal', 'admin']);

it('verschickt einem freigeschalteten Nutzer die Reset-Mail und antwortet neutral', function () {
    Notification::fake();
    $user = User::factory()->create();
    Filament::setCurrentPanel('portal');

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $user->email])
        ->call('request')
        ->assertHasNoFormErrors()
        ->assertNotified(__('skriptdepot.password_reset.requested_title'))
        ->assertFormSet(fn (array $state) => expect($state['email'])->toBeEmpty());

    Notification::assertSentTo($user, ResetPassword::class);
});

it('antwortet identisch, wenn keine Mail verschickt werden darf', function (string $case) {
    Notification::fake();
    Filament::setCurrentPanel('portal');
    $email = match ($case) {
        'unbekannte Adresse' => 'niemand@example.test',
        'Einladung noch nicht angenommen' => User::factory()->unverified()->create()->email,
        'kein Admin im Admin-Panel' => User::factory()->create()->email,
    };
    if ($case === 'kein Admin im Admin-Panel') {
        Filament::setCurrentPanel('admin');
    }

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $email])
        ->call('request')
        ->assertHasNoFormErrors()
        ->assertNotified(__('skriptdepot.password_reset.requested_title'))
        ->assertFormSet(fn (array $state) => expect($state['email'])->toBeEmpty());

    Notification::assertNothingSent();
})->with(['unbekannte Adresse', 'Einladung noch nicht angenommen', 'kein Admin im Admin-Panel']);

it('antwortet bei einer zweiten Anfrage innerhalb der Sperrfrist genauso', function () {
    Notification::fake();
    $user = User::factory()->create();
    Filament::setCurrentPanel('portal');

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $user->email])
        ->call('request')
        ->fillForm(['email' => $user->email])
        ->call('request')
        ->assertNotified(__('skriptdepot.password_reset.requested_title'));

    Notification::assertSentToTimes($user, ResetPassword::class, 1);
});
