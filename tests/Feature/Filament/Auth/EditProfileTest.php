<?php

use App\Filament\Auth\EditProfile;
use App\Models\User;
use Filament\Auth\Notifications\NoticeOfEmailChangeRequest;
use Filament\Auth\Notifications\VerifyEmailChange;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('lässt den Nutzer seine Sprache im Profil umstellen', function () {
    $user = User::factory()->create(['locale' => 'de']);
    $this->actingAs($user);
    Filament::setCurrentPanel('portal');

    Livewire::test(EditProfile::class)
        ->fillForm(['locale' => 'en'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->locale)->toBe('en');
});

it('übernimmt eine neue E-Mail-Adresse erst nach Bestätigung über die neue Adresse', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'alt@example.test', 'password' => 'geheim123']);
    $this->actingAs($user);
    Filament::setCurrentPanel('portal');
    $verifyUrl = null;

    Livewire::test(EditProfile::class)
        ->fillForm(['email' => 'neu@example.test', 'currentPassword' => 'geheim123'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->email)->toBe('alt@example.test');
    Notification::assertSentTo($user, NoticeOfEmailChangeRequest::class);
    Notification::assertSentOnDemand(VerifyEmailChange::class, function (VerifyEmailChange $notification, array $channels, $notifiable) use (&$verifyUrl): bool {
        $verifyUrl = $notification->url;

        return $notifiable->routes['mail'] === 'neu@example.test';
    });

    $this->get($verifyUrl)->assertRedirect();

    expect($user->fresh()->email)->toBe('neu@example.test');
});

it('lehnt einen Bestätigungslink ohne hinterlegte Anfrage mit 403 ab', function () {
    $user = User::factory()->create(['email' => 'alt@example.test']);
    $this->actingAs($user);
    Filament::setCurrentPanel('portal');
    $url = Filament::getPanel('portal')->getVerifyEmailChangeUrl($user, 'neu@example.test');

    $this->get($url)->assertForbidden();

    expect($user->fresh()->email)->toBe('alt@example.test');
});

it('zeigt die Profilseite im Panel-Layout mit Navigation', function (string $panel, string $navigationLabel) {
    $this->actingAs(User::factory()->admin()->create());

    $this->get("/{$panel}/profile")
        ->assertOk()
        ->assertSee($navigationLabel);
})->with([
    'Portal' => ['portal', 'Meine Skripte'],
    'Admin' => ['admin', 'Wasserzeichen auflösen'],
]);
