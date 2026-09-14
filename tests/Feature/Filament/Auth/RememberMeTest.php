<?php

use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

it('meldet den Nutzer nach Ablauf der Session über das Remember-Cookie wieder an', function () {
    $user = User::factory()->create(['password' => 'geheim123', 'remember_token' => null]);
    Filament::setCurrentPanel('portal');

    Livewire::test(Login::class)
        ->fillForm(['email' => $user->email, 'password' => 'geheim123', 'remember' => true])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $user->refresh();
    expect($user->remember_token)->not->toBeNull();

    // Session abgelaufen: kein Session-Cookie mehr, nur das Remember-Cookie.
    Auth::forgetGuards();
    $this->flushSession();
    $recaller = $user->id.'|'.$user->remember_token.'|'.$user->password;

    $this->withCookie(Auth::guard('web')->getRecallerName(), $recaller)
        ->get('/portal/scripts')
        ->assertOk()
        ->assertSee(__('skriptdepot.portal.my_scripts'));
    expect(Auth::guard('web')->viaRemember())->toBeTrue();
});

it('meldet nach einem bewussten Abmelden nicht über ein altes Remember-Cookie an', function () {
    $user = User::factory()->create(['password' => 'geheim123']);
    Auth::guard('web')->login($user, remember: true);
    $recaller = $user->id.'|'.$user->fresh()->remember_token.'|'.$user->password;

    Auth::guard('web')->logout();
    Auth::forgetGuards();
    $this->flushSession();

    $this->withCookie(Auth::guard('web')->getRecallerName(), $recaller)
        ->get('/portal/scripts')
        ->assertRedirect('/portal/login');
});
