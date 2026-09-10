<?php

use App\Models\Entitlement;
use App\Models\ScriptVersion;
use App\Services\ScriptBuilder;

it('berechnet einen deterministischen Build-Hash aus acht Hex-Zeichen', function () {
    $builder = new ScriptBuilder;

    $first = $builder->buildHash('seed-a', 7, '1.0.0');
    $second = $builder->buildHash('seed-a', 7, '1.0.0');

    expect($first)->toMatch('/^[0-9a-f]{8}$/')
        ->and($second)->toBe($first);
});

it('liefert unterschiedliche Build-Hashes bei anderem Seed, Skript oder Version', function () {
    $builder = new ScriptBuilder;
    $reference = $builder->buildHash('seed-a', 7, '1.0.0');

    expect($builder->buildHash('seed-b', 7, '1.0.0'))->not->toBe($reference)
        ->and($builder->buildHash('seed-a', 8, '1.0.0'))->not->toBe($reference)
        ->and($builder->buildHash('seed-a', 7, '1.0.1'))->not->toBe($reference);
});

it('ersetzt alle Platzhalter im vollständigen Skript', function () {
    config(['app.url' => 'https://portal.example.test/']);
    $scriptVersion = ScriptVersion::factory()->version('2.3.4')->create();
    $entitlement = Entitlement::factory()->for($scriptVersion->script)->create();
    $builder = new ScriptBuilder;
    $expectedHash = $builder->buildHash($entitlement->user->seed, $scriptVersion->script_id, '2.3.4');

    $output = $builder->build($entitlement, $scriptVersion);

    expect($output)
        ->toContain('// @version      2.3.4')
        ->toContain("// @updateURL    https://portal.example.test/s/{$entitlement->token}/{$scriptVersion->script->slug}.meta.js")
        ->toContain("// @downloadURL  https://portal.example.test/s/{$entitlement->token}/{$scriptVersion->script->slug}.user.js")
        ->toContain("/* build: {$expectedHash} */")
        ->toContain("const BUILD = '{$expectedHash}';")
        ->not->toContain('{{');
});

it('liefert für die Metadaten nur den UserScript-Block ohne Build-Hash', function () {
    $scriptVersion = ScriptVersion::factory()->create();
    $entitlement = Entitlement::factory()->for($scriptVersion->script)->create();

    $meta = (new ScriptBuilder)->buildMeta($entitlement, $scriptVersion);

    expect($meta)
        ->toStartWith('// ==UserScript==')
        ->toEndWith("// ==/UserScript==\n")
        ->toContain("/s/{$entitlement->token}/")
        ->not->toContain('build:')
        ->not->toContain('{{');
});

it('wirft einen Fehler, wenn der Quelltext keinen UserScript-Block hat', function () {
    expect(fn () => (new ScriptBuilder)->extractHeader('console.log(1);'))
        ->toThrow(RuntimeException::class);
});
