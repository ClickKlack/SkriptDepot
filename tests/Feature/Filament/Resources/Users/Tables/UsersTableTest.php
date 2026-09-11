<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Notifications\UserInvitation;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
});

it('verschickt die Einladung erneut an einen noch nicht aktivierten Nutzer', function () {
    Notification::fake();
    $invited = User::factory()->unverified()->create(['invited_at' => now()->subDays(10)]);

    Livewire::test(ListUsers::class)
        ->callAction(TestAction::make('invite')->table($invited))
        ->assertNotified(__('skriptdepot.notifications.invitation_sent', ['email' => $invited->email]));

    Notification::assertSentTo($invited, UserInvitation::class);
    expect($invited->fresh()->invited_at->isToday())->toBeTrue();
});

it('zeigt für aktivierte Nutzer keine Einladungsaktion', function () {
    $accepted = User::factory()->create();

    Livewire::test(ListUsers::class)
        ->assertActionHidden(TestAction::make('invite')->table($accepted));
});
