<?php

use App\Filament\Resources\Scripts\Resources\ScriptVersions\Pages\CreateScriptVersion;
use App\Models\Script;
use App\Models\ScriptVersion;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
});

function masterScript(): string
{
    return file_get_contents(base_path('tests/Fixtures/master-script.user.js'));
}

it('legt eine neue, höhere Version unter dem Skript an', function () {
    $script = Script::factory()->create();
    ScriptVersion::factory()->for($script)->version('1.0.0')->create();

    Livewire::test(CreateScriptVersion::class, ['parentRecord' => $script])
        ->fillForm(['version' => '1.1.0', 'source' => masterScript(), 'notes' => 'Zweite Version'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($script->latestVersion()->value('version'))->toBe('1.1.0')
        ->and($script->versions()->count())->toBe(2);
});

it('lehnt eine Version ab, die nicht höher als die aktuelle ist', function () {
    $script = Script::factory()->create();
    ScriptVersion::factory()->for($script)->version('1.10.0')->create();

    Livewire::test(CreateScriptVersion::class, ['parentRecord' => $script])
        ->fillForm(['version' => '1.9.0', 'source' => masterScript()])
        ->call('create')
        ->assertHasFormErrors(['version']);

    expect($script->versions()->count())->toBe(1);
});

it('lehnt einen Quelltext ohne Platzhalter ab', function () {
    $script = Script::factory()->create();

    Livewire::test(CreateScriptVersion::class, ['parentRecord' => $script])
        ->fillForm(['version' => '1.0.0', 'source' => "console.log('kein Header');"])
        ->call('create')
        ->assertHasFormErrors(['source']);

    expect($script->versions()->count())->toBe(0);
});

it('fügt beim Speichern eines rohen Skripts die Wasserzeichen automatisch ein', function () {
    $script = Script::factory()->create();

    Livewire::test(CreateScriptVersion::class, ['parentRecord' => $script])
        ->fillForm(['version' => '0.3.1', 'source' => file_get_contents(base_path('tests/Fixtures/raw-script.user.js'))])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($script->latestVersion()->value('source'))
        ->toContain('// @version      {{VERSION}}')
        ->toContain('/* build: {{BUILD}} */')
        ->toContain("globalThis.__skriptdepotBuild = '{{BUILD}}';");
});

it('zeigt die Wasserzeichen über den Knopf im Editor als Vorschau', function () {
    Livewire::test(CreateScriptVersion::class, ['parentRecord' => Script::factory()->create()])
        ->fillForm(['source' => file_get_contents(base_path('tests/Fixtures/raw-script.user.js'))])
        ->callAction(TestAction::make('injectWatermark')->schemaComponent('source'))
        ->assertFormSet(fn (array $state) => expect($state['source'])->toContain('/* build: {{BUILD}} */'));
});

it('meldet einen Syntaxfehler im rohen Skript als Formularfehler', function () {
    $script = Script::factory()->create();

    Livewire::test(CreateScriptVersion::class, ['parentRecord' => $script])
        ->fillForm(['version' => '1.0.0', 'source' => "// ==UserScript==\n// @name x\n// ==/UserScript==\nconst = ;\n"])
        ->call('create')
        ->assertHasFormErrors(['source']);

    expect($script->versions()->count())->toBe(0);
});

it('übernimmt die Versionsnummer aus dem eingefügten Header in das leere Versionsfeld', function () {
    Livewire::test(CreateScriptVersion::class, ['parentRecord' => Script::factory()->create()])
        ->fillForm(['source' => file_get_contents(base_path('tests/Fixtures/raw-script.user.js'))])
        ->assertFormSet(['version' => '0.3.1']);
});

it('überschreibt eine bereits eingetragene Versionsnummer nicht', function () {
    Livewire::test(CreateScriptVersion::class, ['parentRecord' => Script::factory()->create()])
        ->fillForm(['version' => '9.9.9'])
        ->fillForm(['source' => file_get_contents(base_path('tests/Fixtures/raw-script.user.js'))])
        ->assertFormSet(['version' => '9.9.9']);
});
