<?php

use App\Filament\Auth\EditProfile;
use App\Models\User;
use Filament\Facades\Filament;
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
