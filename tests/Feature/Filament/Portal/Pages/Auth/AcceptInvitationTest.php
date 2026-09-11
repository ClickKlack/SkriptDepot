<?php

use App\Filament\Portal\Pages\Auth\AcceptInvitation;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

function invitationUrl(User $user, int $days = 7): string
{
    return URL::temporarySignedRoute('filament.portal.invitation.accept', now()->addDays($days), ['user' => $user]);
}

it('zeigt die Einladungsseite bei gültiger Signatur', function () {
    $user = User::factory()->unverified()->create();

    $this->get(invitationUrl($user))
        ->assertOk()
        ->assertSee(__('skriptdepot.invitation.heading'))
        ->assertSee($user->email);
});

it('lehnt einen Link ohne gültige Signatur mit 403 ab', function () {
    $user = User::factory()->unverified()->create();

    $this->get("/portal/invitation/{$user->id}")->assertForbidden();
});

it('lehnt einen abgelaufenen Link mit 403 ab', function () {
    $user = User::factory()->unverified()->create();
    $url = invitationUrl($user);

    $this->travel(8)->days();

    $this->get($url)->assertForbidden();
});

it('leitet bei bereits angenommener Einladung zum Login', function () {
    $user = User::factory()->create();

    $this->get(invitationUrl($user))->assertRedirect('/portal/login');
});

it('setzt das Passwort, bestätigt die E-Mail und meldet den Nutzer an', function () {
    $user = User::factory()->unverified()->create();
    Filament::setCurrentPanel('portal');

    Livewire::test(AcceptInvitation::class, ['user' => $user])
        ->fillForm(['password' => 'sicheres-passwort-1', 'passwordConfirmation' => 'sicheres-passwort-1'])
        ->call('acceptInvitation')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $user->refresh();
    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and(Hash::check('sicheres-passwort-1', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

it('lehnt ein Passwort ab, das nicht mit der Wiederholung übereinstimmt', function () {
    $user = User::factory()->unverified()->create();
    Filament::setCurrentPanel('portal');

    Livewire::test(AcceptInvitation::class, ['user' => $user])
        ->fillForm(['password' => 'sicheres-passwort-1', 'passwordConfirmation' => 'anderes-passwort-2'])
        ->call('acceptInvitation')
        ->assertHasFormErrors(['password']);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
    $this->assertGuest();
});

it('lehnt ein zu kurzes Passwort ab', function () {
    $user = User::factory()->unverified()->create();
    Filament::setCurrentPanel('portal');

    Livewire::test(AcceptInvitation::class, ['user' => $user])
        ->fillForm(['password' => 'kurz', 'passwordConfirmation' => 'kurz'])
        ->call('acceptInvitation')
        ->assertHasFormErrors(['password']);
});

it('lehnt die Einladung eines gesperrten Nutzers mit 403 ab', function () {
    $user = User::factory()->unverified()->create(['blocked_at' => now()]);

    $this->get(invitationUrl($user))->assertForbidden();
});
