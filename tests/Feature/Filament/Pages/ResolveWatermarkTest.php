<?php

use App\Filament\Pages\ResolveWatermark;
use App\Models\User;
use App\Models\Watermark;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
});

it('zeigt zu einem Build-Hash den Bezieher an', function () {
    $watermark = Watermark::factory()->create(['build_hash' => '0badf00d']);

    Livewire::test(ResolveWatermark::class)
        ->fillForm(['input' => '0badf00d'])
        ->call('resolve')
        ->assertHasNoFormErrors()
        ->assertSet('searched', true)
        ->assertSee($watermark->entitlement->user->email)
        ->assertSee($watermark->scriptVersion->version);
});

it('meldet, wenn kein Treffer gefunden wurde', function () {
    Livewire::test(ResolveWatermark::class)
        ->fillForm(['input' => 'ffffffff'])
        ->call('resolve')
        ->assertSet('matches', [])
        ->assertSee(__('skriptdepot.resolve.no_match'));
});
