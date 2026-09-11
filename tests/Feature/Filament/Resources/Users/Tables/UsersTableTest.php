<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Delivery;
use App\Models\Entitlement;
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

it('sperrt und entsperrt einen Nutzer', function () {
    $user = User::factory()->create();
    $component = Livewire::test(ListUsers::class);

    $component->callAction(TestAction::make('block')->table($user));

    expect($user->fresh()->isBlocked())->toBeTrue();

    $component->callAction(TestAction::make('unblock')->table($user));

    expect($user->fresh()->isBlocked())->toBeFalse();
});

it('bietet für das eigene Konto weder Sperren noch Löschen an', function () {
    $self = auth()->user();

    Livewire::test(ListUsers::class)
        ->assertActionHidden(TestAction::make('block')->table($self))
        ->assertActionHidden(TestAction::make('delete')->table($self));
});

it('löscht Nutzer samt Freischaltungen, solange nichts ausgeliefert wurde', function () {
    $neverDelivered = Entitlement::factory()->inactive()->create()->user;
    $delivered = Delivery::factory()->create()->entitlement->user;

    Livewire::test(ListUsers::class)
        ->assertActionHidden(TestAction::make('delete')->table($delivered))
        ->assertActionVisible(TestAction::make('delete')->table($neverDelivered))
        ->callAction(TestAction::make('delete')->table($neverDelivered));

    $this->assertModelMissing($neverDelivered);
    $this->assertDatabaseMissing('entitlements', ['user_id' => $neverDelivered->id]);
    $this->assertModelExists($delivered);
});
