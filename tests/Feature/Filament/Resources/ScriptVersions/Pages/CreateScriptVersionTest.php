<?php

use App\Filament\Resources\ScriptVersions\Pages\CreateScriptVersion;
use App\Models\Script;
use App\Models\ScriptVersion;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
});

it('legt eine neue, höhere Version an', function () {
    $script = Script::factory()->create();
    ScriptVersion::factory()->for($script)->version('1.0.0')->create();

    Livewire::test(CreateScriptVersion::class)
        ->fillForm([
            'script_id' => $script->id,
            'version' => '1.1.0',
            'source' => file_get_contents(base_path('tests/Fixtures/master-script.user.js')),
            'notes' => 'Zweite Version',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($script->latestVersion()->value('version'))->toBe('1.1.0');
});

it('lehnt eine Version ab, die nicht höher als die aktuelle ist', function () {
    $script = Script::factory()->create();
    ScriptVersion::factory()->for($script)->version('1.10.0')->create();

    Livewire::test(CreateScriptVersion::class)
        ->fillForm([
            'script_id' => $script->id,
            'version' => '1.9.0',
            'source' => file_get_contents(base_path('tests/Fixtures/master-script.user.js')),
        ])
        ->call('create')
        ->assertHasFormErrors(['version']);

    expect($script->versions()->count())->toBe(1);
});

it('lehnt einen Quelltext ohne Platzhalter ab', function () {
    $script = Script::factory()->create();

    Livewire::test(CreateScriptVersion::class)
        ->fillForm([
            'script_id' => $script->id,
            'version' => '1.0.0',
            'source' => "console.log('kein Header');",
        ])
        ->call('create')
        ->assertHasFormErrors(['source']);

    expect($script->versions()->count())->toBe(0);
});
