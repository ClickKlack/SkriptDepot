<?php

use App\Models\Entitlement;
use App\Models\ScriptVersion;
use App\Models\User;

it('leitet nicht angemeldete Besucher zum Portal-Login', function () {
    $this->get('/portal/scripts')->assertRedirect('/portal/login');
});

it('zeigt dem Nutzer nur seine aktiven Freischaltungen mit Installations-Link', function () {
    $user = User::factory()->create();
    $active = Entitlement::factory()->for($user)->for(ScriptVersion::factory()->create()->script)->create();
    $revoked = Entitlement::factory()->for($user)->inactive()->create();
    $foreign = Entitlement::factory()->create();

    $response = $this->actingAs($user)->get('/portal/scripts');

    $response->assertOk()
        ->assertSee($active->script->name)
        ->assertSee(route('scripts.user', ['token' => $active->token, 'slug' => $active->script->slug]))
        ->assertSee(__('skriptdepot.portal.hint'))
        ->assertDontSee($revoked->script->name)
        ->assertDontSee($foreign->script->name);
});

it('weist auf ein Skript ohne veröffentlichte Version hin', function () {
    $user = User::factory()->create();
    Entitlement::factory()->for($user)->create();

    $this->actingAs($user)->get('/portal/scripts')
        ->assertSee(__('skriptdepot.portal.no_version'))
        ->assertDontSee('.user.js');
});

it('rendert die Oberfläche in der Sprache des Nutzers', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)->get('/portal/scripts')->assertSee('My scripts');
});
