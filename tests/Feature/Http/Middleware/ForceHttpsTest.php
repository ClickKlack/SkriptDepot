<?php

use App\Models\Entitlement;
use App\Models\ScriptVersion;

it('leitet produktiv unverschlüsselte Anfragen dauerhaft auf HTTPS um', function () {
    $this->app->detectEnvironment(fn () => 'production');

    $response = $this->get('http://skriptdepot.test/portal/login');

    $response->assertStatus(301)->assertRedirect('https://skriptdepot.test/portal/login');
});

it('lässt verschlüsselte Anfragen produktiv durch', function () {
    $this->app->detectEnvironment(fn () => 'production');
    $scriptVersion = ScriptVersion::factory()->create();
    $entitlement = Entitlement::factory()->for($scriptVersion->script)->create();

    $this->get("https://skriptdepot.test/s/{$entitlement->token}/{$entitlement->script->slug}.meta.js")->assertOk();
});

it('leitet in der Entwicklung nicht um', function () {
    $this->get('http://localhost/portal/login')->assertOk();
});
