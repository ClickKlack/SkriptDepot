<?php

use App\Models\Script;
use App\Models\ScriptVersion;

it('nimmt eine höhere Version an und macht sie zur aktuellen', function () {
    $script = Script::factory()->create();
    ScriptVersion::factory()->for($script)->version('1.9.0')->create();

    ScriptVersion::factory()->for($script)->version('1.10.0')->create();

    expect($script->latestVersion()->value('version'))->toBe('1.10.0');
});

it('lehnt eine Version ab, die nicht höher als die aktuelle ist', function (string $version) {
    $script = Script::factory()->create();
    ScriptVersion::factory()->for($script)->version('1.10.0')->create();

    expect(fn () => ScriptVersion::factory()->for($script)->version($version)->create())
        ->toThrow(InvalidArgumentException::class);

    expect($script->versions()->count())->toBe(1);
})->with([
    'gleiche Version' => '1.10.0',
    'ältere Version' => '1.9.0',
    'lexikalisch größer, aber älter' => '1.2.0',
]);

it('lässt Quelltext und Versionsnummer nach dem Anlegen nicht mehr ändern', function () {
    $scriptVersion = ScriptVersion::factory()->create();

    expect(fn () => $scriptVersion->update(['source' => '// geändert']))
        ->toThrow(InvalidArgumentException::class);

    expect($scriptVersion->fresh()->source)->not->toBe('// geändert');
});

it('erlaubt das Ändern der Notizen', function () {
    $scriptVersion = ScriptVersion::factory()->create();

    $scriptVersion->update(['notes' => 'Bugfix im Login-Handler']);

    expect($scriptVersion->fresh()->notes)->toBe('Bugfix im Login-Handler');
});
