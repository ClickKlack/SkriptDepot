<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\User;
use App\Notifications\UserInvitation;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
});

it('legt den Nutzer ohne Passwort an und verschickt die Einladung', function () {
    Notification::fake();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Max Muster',
            'email' => 'max@example.test',
            'locale' => 'en',
            'is_admin' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified(__('skriptdepot.notifications.invitation_sent', ['email' => 'max@example.test']));

    $user = User::where('email', 'max@example.test')->firstOrFail();
    expect($user->hasVerifiedEmail())->toBeFalse()
        ->and($user->invited_at)->not->toBeNull()
        ->and($user->seed)->toHaveLength(64)
        ->and($user->canAccessPanel(Filament::getPanel('portal')))->toBeFalse();
    Notification::assertSentTo($user, UserInvitation::class);
});

it('bietet im Formular kein Passwortfeld an', function () {
    Livewire::test(CreateUser::class)->assertFormFieldDoesNotExist('password');
});
