<?php

use App\Rules\ValidMasterScript;
use Illuminate\Support\Facades\Validator;

function masterScriptFixture(): string
{
    return file_get_contents(base_path('tests/Fixtures/master-script.user.js'));
}

it('akzeptiert das Beispiel-Master-Skript', function () {
    $validator = Validator::make(['source' => masterScriptFixture()], ['source' => new ValidMasterScript]);

    expect($validator->passes())->toBeTrue();
});

it('lehnt einen Quelltext ab, dem ein Pflichtbestandteil fehlt', function (string $search, string $replace, string $expectedMessageKey) {
    $source = str_replace($search, $replace, masterScriptFixture());

    $validator = Validator::make(['source' => $source], ['source' => new ValidMasterScript]);

    expect($validator->errors()->first('source'))->toBe(__($expectedMessageKey));
})->with([
    'ohne Header' => ['// ==/UserScript==', '', 'skriptdepot.validation.master_script.header'],
    'feste Versionsnummer' => ['{{VERSION}}', '1.0.0', 'skriptdepot.validation.master_script.version'],
    'falsche Update-URL' => ['{{SLUG}}.meta.js', 'foo.meta.js', 'skriptdepot.validation.master_script.update_url'],
    'falsche Download-URL' => ['{{SLUG}}.user.js', 'foo.user.js', 'skriptdepot.validation.master_script.download_url'],
    'nur ein Build-Platzhalter' => ["const BUILD = '{{BUILD}}';", "const BUILD = 'x';", 'skriptdepot.validation.master_script.build'],
]);
