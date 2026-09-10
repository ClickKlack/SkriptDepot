<?php

use App\Models\Entitlement;
use App\Models\ScriptVersion;
use App\Models\Watermark;
use App\Services\ScriptBuilder;
use App\Services\WatermarkResolver;
use App\Support\WatermarkMatchMethod;

it('löst einen gespeicherten Build-Hash über die Wasserzeichen-Tabelle auf', function () {
    $watermark = Watermark::factory()->create(['build_hash' => 'a1b2c3d4']);

    $matches = app(WatermarkResolver::class)->resolve('A1B2C3D4');

    expect($matches)->toHaveCount(1)
        ->and($matches[0]->method)->toBe(WatermarkMatchMethod::Watermark)
        ->and($matches[0]->entitlement->id)->toBe($watermark->entitlement_id)
        ->and($matches[0]->scriptVersion->id)->toBe($watermark->script_version_id);
});

it('rechnet einen unbekannten Build-Hash über alle Nutzer-Seeds nach', function () {
    $scriptVersion = ScriptVersion::factory()->version('2.0.0')->create();
    $entitlement = Entitlement::factory()->for($scriptVersion->script)->create();
    Entitlement::factory()->for($scriptVersion->script)->create();
    $buildHash = (new ScriptBuilder)->buildHashFor($entitlement, $scriptVersion);

    $matches = app(WatermarkResolver::class)->resolve($buildHash);

    expect($matches)->toHaveCount(1)
        ->and($matches[0]->method)->toBe(WatermarkMatchMethod::Computed)
        ->and($matches[0]->entitlement->id)->toBe($entitlement->id)
        ->and($matches[0]->scriptVersion->version)->toBe('2.0.0');
});

it('erkennt in einem kompletten Skript-Text Token und Build-Hash', function () {
    $scriptVersion = ScriptVersion::factory()->create();
    $entitlement = Entitlement::factory()->for($scriptVersion->script)->create();
    $script = (new ScriptBuilder)->build($entitlement, $scriptVersion);

    $matches = app(WatermarkResolver::class)->resolve($script);

    expect($matches->pluck('method')->all())->toBe([WatermarkMatchMethod::Token, WatermarkMatchMethod::Computed])
        ->and($matches->pluck('entitlement.id')->unique()->all())->toBe([$entitlement->id]);
});

it('liefert keine Treffer für unbekannte Merkmale', function () {
    Entitlement::factory()->create();

    $matches = app(WatermarkResolver::class)->resolve('ffffffff');

    expect($matches)->toBeEmpty();
});

it('extrahiert nur Build-Hashes aus den bekannten Injektionsstellen', function () {
    $resolver = app(WatermarkResolver::class);

    $hashes = $resolver->extractBuildHashes("/* build: 01234567 */\nconst BUILD = '01234567';\nconst other = 'deadbeef';");

    expect($hashes)->toBe(['01234567']);
});
