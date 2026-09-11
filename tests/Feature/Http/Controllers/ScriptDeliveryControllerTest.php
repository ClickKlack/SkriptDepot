<?php

use App\Enums\DeliveryType;
use App\Models\Entitlement;
use App\Models\ScriptVersion;
use App\Services\ScriptBuilder;

/**
 * Legt ein Skript mit Version und aktiver Freischaltung an und liefert die Freischaltung zurück.
 */
function deliverableEntitlement(string $version = '1.0.0'): Entitlement
{
    $scriptVersion = ScriptVersion::factory()->version($version)->create();

    return Entitlement::factory()->for($scriptVersion->script)->create();
}

describe('user.js', function () {
    it('liefert das personalisierte Skript als JavaScript ohne Zwischenspeicherung', function () {
        $entitlement = deliverableEntitlement('1.2.3');
        $expectedHash = (new ScriptBuilder)->buildHash($entitlement->user->seed, $entitlement->script_id, '1.2.3');

        $response = $this->get("/s/{$entitlement->token}/{$entitlement->script->slug}.user.js");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/javascript; charset=utf-8')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('// @version      1.2.3', escape: false)
            ->assertSee("const BUILD = '{$expectedHash}';", escape: false);
    });

    it('bettet weder Seed noch E-Mail des Nutzers ein', function () {
        $entitlement = deliverableEntitlement();

        $response = $this->get("/s/{$entitlement->token}/{$entitlement->script->slug}.user.js");

        $response->assertDontSee($entitlement->user->seed)
            ->assertDontSee($entitlement->user->email);
    });

    it('legt beim ersten Abruf ein Wasserzeichen an und protokolliert die Auslieferung', function () {
        $this->freezeTime();
        $entitlement = deliverableEntitlement();
        $scriptVersion = $entitlement->script->latestVersion;
        $expectedHash = (new ScriptBuilder)->buildHashFor($entitlement, $scriptVersion);

        $this->withHeaders(['User-Agent' => 'Tampermonkey-Test'])
            ->get("/s/{$entitlement->token}/{$entitlement->script->slug}.user.js")
            ->assertOk();

        $this->assertDatabaseHas('watermarks', [
            'entitlement_id' => $entitlement->id,
            'script_version_id' => $scriptVersion->id,
            'build_hash' => $expectedHash,
            'first_delivered_at' => now(),
        ]);
        $this->assertDatabaseHas('deliveries', [
            'entitlement_id' => $entitlement->id,
            'script_version_id' => $scriptVersion->id,
            'type' => DeliveryType::User->value,
            'ip' => '127.0.0.1',
            'user_agent' => 'Tampermonkey-Test',
        ]);
    });

    it('legt bei wiederholtem Abruf kein zweites Wasserzeichen an, aber einen weiteren Protokolleintrag', function () {
        $entitlement = deliverableEntitlement();
        $url = "/s/{$entitlement->token}/{$entitlement->script->slug}.user.js";

        $first = $this->get($url)->getContent();
        $second = $this->get($url)->getContent();

        expect($second)->toBe($first);
        $this->assertDatabaseCount('watermarks', 1);
        $this->assertDatabaseCount('deliveries', 2);
    });

    it('liefert zwei Nutzern unterschiedliche Build-Hashes für dieselbe Version', function () {
        $first = deliverableEntitlement();
        $second = Entitlement::factory()->for($first->script)->create();
        $slug = $first->script->slug;

        $firstContent = $this->get("/s/{$first->token}/{$slug}.user.js")->getContent();
        $secondContent = $this->get("/s/{$second->token}/{$slug}.user.js")->getContent();

        preg_match('/build: ([0-9a-f]{8})/', $firstContent, $firstHash);
        preg_match('/build: ([0-9a-f]{8})/', $secondContent, $secondHash);
        expect($firstHash[1])->not->toBe($secondHash[1]);
    });

    it('liefert nach dem Anlegen einer höheren Version deren Inhalt aus', function () {
        $entitlement = deliverableEntitlement('1.0.0');
        ScriptVersion::factory()->for($entitlement->script)->version('1.1.0')->create();

        $response = $this->get("/s/{$entitlement->token}/{$entitlement->script->slug}.user.js");

        $response->assertSee('// @version      1.1.0', escape: false);
    });
});

describe('meta.js', function () {
    it('liefert nur den Metadaten-Block und protokolliert den Abruf ohne Wasserzeichen', function () {
        $entitlement = deliverableEntitlement('3.0.0');

        $response = $this->get("/s/{$entitlement->token}/{$entitlement->script->slug}.meta.js");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/javascript; charset=utf-8')
            ->assertSee('// @version      3.0.0', escape: false)
            ->assertDontSee('build:');
        $this->assertDatabaseHas('deliveries', [
            'entitlement_id' => $entitlement->id,
            'type' => DeliveryType::Meta->value,
        ]);
        $this->assertDatabaseCount('watermarks', 0);
    });
});

describe('Ablehnung', function () {
    it('antwortet mit 403 und protokolliert nichts', function (string $reason, string $suffix) {
        $entitlement = deliverableEntitlement();
        $url = match ($reason) {
            'unbekannter Token' => '/s/'.str_repeat('x', 40)."/{$entitlement->script->slug}",
            'falscher Slug' => "/s/{$entitlement->token}/anderes-skript",
        };

        $response = $this->get($url.$suffix);

        $response->assertForbidden();
        $this->assertDatabaseCount('deliveries', 0);
        $this->assertDatabaseCount('watermarks', 0);
    })->with(['unbekannter Token', 'falscher Slug'])->with(['.user.js', '.meta.js']);

    it('antwortet mit 403, sobald die Freischaltung deaktiviert ist', function (string $suffix) {
        $entitlement = deliverableEntitlement();
        $url = "/s/{$entitlement->token}/{$entitlement->script->slug}{$suffix}";
        $this->get($url)->assertOk();

        $entitlement->update(['is_active' => false]);

        $this->get($url)->assertForbidden();
    })->with(['.user.js', '.meta.js']);

    it('antwortet mit 403, sobald der Nutzer gesperrt ist', function (string $suffix) {
        $entitlement = deliverableEntitlement();
        $url = "/s/{$entitlement->token}/{$entitlement->script->slug}{$suffix}";
        $this->get($url)->assertOk();

        $entitlement->user->forceFill(['blocked_at' => now()])->save();

        $this->get($url)->assertForbidden();
    })->with(['.user.js', '.meta.js']);

    it('antwortet mit 403, wenn das Skript noch keine Version hat', function () {
        $entitlement = Entitlement::factory()->create();

        $this->get("/s/{$entitlement->token}/{$entitlement->script->slug}.user.js")->assertForbidden();
    });

    it('drosselt nach 60 Abrufen pro Minute mit 429', function () {
        $entitlement = deliverableEntitlement();
        $url = "/s/{$entitlement->token}/{$entitlement->script->slug}.meta.js";

        foreach (range(1, 60) as $attempt) {
            $this->get($url)->assertOk();
        }

        $this->get($url)->assertTooManyRequests();
    });
});
