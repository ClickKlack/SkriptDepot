<?php

use App\Models\Entitlement;
use App\Models\ScriptVersion;
use App\Models\Watermark;
use App\Services\ScriptBuilder;

it('gibt den Nutzer zu einem Build-Hash aus', function () {
    $watermark = Watermark::factory()->create(['build_hash' => 'abcdef01']);

    $this->artisan('wm:identify', ['hash' => 'abcdef01'])
        ->expectsOutputToContain($watermark->entitlement->user->email)
        ->assertSuccessful();
});

it('liest eine geleakte Datei und findet den Bezieher', function () {
    $scriptVersion = ScriptVersion::factory()->create();
    $entitlement = Entitlement::factory()->for($scriptVersion->script)->create();
    $path = tempnam(sys_get_temp_dir(), 'leak');
    file_put_contents($path, (new ScriptBuilder)->build($entitlement, $scriptVersion));

    $this->artisan('wm:identify', ['--file' => $path])
        ->expectsOutputToContain($entitlement->user->email)
        ->assertSuccessful();

    unlink($path);
});

it('meldet einen Fehlschlag ohne Treffer', function () {
    $this->artisan('wm:identify', ['hash' => 'ffffffff'])->assertFailed();
});

it('verlangt einen Hash oder eine Datei', function () {
    $this->artisan('wm:identify')->assertExitCode(2);
});
