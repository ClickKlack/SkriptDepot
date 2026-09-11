<?php

use App\Exceptions\ScriptSyntaxException;
use App\Models\Entitlement;
use App\Models\ScriptVersion;
use App\Rules\ValidMasterScript;
use App\Services\ScriptBuilder;
use App\Services\ScriptWatermarker;
use Illuminate\Support\Facades\Validator;
use Peast\Peast;

function rawScriptFixture(): string
{
    return file_get_contents(base_path('tests/Fixtures/raw-script.user.js'));
}

it('setzt Version, Update- und Download-URL im Header auf Platzhalter und behält den Rest', function () {
    $result = (new ScriptWatermarker)->inject(rawScriptFixture());

    expect($result)
        ->toContain("// @version      {{VERSION}}\n")
        ->toContain("// @updateURL    {{BASE}}/s/{{TOKEN}}/{{SLUG}}.meta.js\n")
        ->toContain("// @downloadURL  {{BASE}}/s/{{TOKEN}}/{{SLUG}}.user.js\n")
        ->toContain("// @match        https://example.com/*\n")
        ->toContain("// @grant        none\n")
        ->not->toContain('0.3.1');
});

it('setzt den Build-Kommentar direkt nach dem Header und die Zuweisung nach der Direktive', function () {
    $result = (new ScriptWatermarker)->inject(rawScriptFixture());

    expect($result)
        ->toContain("// ==/UserScript==\n/* build: {{BUILD}} */\n")
        ->toContain("'use strict';\n\nglobalThis.__skriptdepotBuild = '{{BUILD}}';\n(function () {");
});

it('verteilt je nach Länge weitere Build-Kommentare an Anweisungsgrenzen', function (int $linesPerMarker, int $expectedComments) {
    config(['skriptdepot.watermark.lines_per_marker' => $linesPerMarker]);

    $result = (new ScriptWatermarker)->inject(rawScriptFixture());

    expect(substr_count($result, '// build: {{BUILD}}'))->toBe($expectedComments)
        ->and(substr_count($result, '{{BUILD}}'))->toBe($expectedComments + 2);
})->with([
    'Standard: 164 Zeilen, je 40' => [40, 4],
    'dicht: Obergrenze acht' => [10, 8],
    'weit: Untergrenze eins' => [1000, 1],
]);

it('lässt Template-Strings, Blockkommentare und reguläre Ausdrücke unangetastet', function () {
    config(['skriptdepot.watermark.lines_per_marker' => 5]);
    $source = rawScriptFixture();
    preg_match('/const TEMPLATE = `.*?`;/s', $source, $template);
    preg_match('#\n\s*/\*\n.*?\*/#s', $source, $blockComment);

    $result = (new ScriptWatermarker)->inject($source);

    expect($result)
        ->toContain($template[0])
        ->toContain($blockComment[0])
        ->toContain('const regex = /["\'`]/g;');
});

it('liefert gültiges JavaScript, das die Master-Validierung besteht und sich ausliefern lässt', function () {
    $master = (new ScriptWatermarker)->inject(rawScriptFixture());
    $scriptVersion = ScriptVersion::factory()->create(['source' => $master]);
    $entitlement = Entitlement::factory()->for($scriptVersion->script)->create();

    $delivered = (new ScriptBuilder)->build($entitlement, $scriptVersion);

    expect(Validator::make(['source' => $master], ['source' => new ValidMasterScript])->passes())->toBeTrue()
        ->and($delivered)->not->toContain('{{');
    expect(fn () => Peast::latest($delivered)->parse())->not->toThrow(Exception::class);
});

it('verändert einen bereits vorbereiteten Quelltext nicht', function () {
    $watermarker = new ScriptWatermarker;
    $prepared = $watermarker->inject(rawScriptFixture());

    expect($watermarker->inject($prepared))->toBe($prepared);
});

it('ergänzt fehlende Header-Zeilen bündig zu den vorhandenen', function () {
    $source = "// ==UserScript==\n// @name         Ohne URLs\n// @version      1.0\n// ==/UserScript==\nconsole.log(1);\n";

    $result = (new ScriptWatermarker)->inject($source);

    expect($result)
        ->toContain("// @version      {{VERSION}}\n// @updateURL    {{BASE}}/s/{{TOKEN}}/{{SLUG}}.meta.js\n// @downloadURL  {{BASE}}/s/{{TOKEN}}/{{SLUG}}.user.js\n// ==/UserScript==");
});

it('normalisiert Windows-Zeilenumbrüche', function () {
    $source = str_replace("\n", "\r\n", rawScriptFixture());

    $result = (new ScriptWatermarker)->inject($source);

    expect($result)->not->toContain("\r")->toContain('/* build: {{BUILD}} */');
});

it('meldet einen Syntaxfehler mit Zeilennummer im Gesamtquelltext', function () {
    $source = "// ==UserScript==\n// @name x\n// ==/UserScript==\nconst a = 1;\nconst = ;\n";

    expect(fn () => (new ScriptWatermarker)->inject($source))
        ->toThrow(fn (ScriptSyntaxException $exception) => expect($exception->sourceLine)->toBe(5));
});

it('lehnt einen Quelltext ohne Header ab', function () {
    expect(fn () => (new ScriptWatermarker)->inject('console.log(1);'))->toThrow(RuntimeException::class);
});

it('liest die Versionsnummer aus dem Header', function (string $source, ?string $expected) {
    expect((new ScriptWatermarker)->detectVersion($source))->toBe($expected);
})->with([
    'konkrete Version' => [fn () => rawScriptFixture(), '0.3.1'],
    'Platzhalter' => ["// ==UserScript==\n// @version {{VERSION}}\n// ==/UserScript==\n", null],
    'ohne Version' => ["// ==UserScript==\n// @name x\n// ==/UserScript==\n", null],
    'ohne Header' => ['console.log(1);', null],
]);
