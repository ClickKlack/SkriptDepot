<?php

use App\Filament\Resources\Scripts\Pages\ManageScriptVersions;
use App\Filament\Resources\Scripts\Resources\ScriptVersions\ScriptVersionResource;
use App\Filament\Resources\Scripts\ScriptResource;
use App\Models\Script;
use App\Models\ScriptVersion;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
});

it('zeigt nur die Versionen des jeweiligen Skripts', function () {
    $script = Script::factory()->create();
    $own = ScriptVersion::factory()->for($script)->version('1.0.0')->create();
    $ownNewer = ScriptVersion::factory()->for($script)->version('1.1.0')->create();
    $foreign = ScriptVersion::factory()->version('9.0.0')->create();

    Livewire::test(ManageScriptVersions::class, ['record' => $script->getRouteKey()])
        ->assertCanSeeTableRecords([$own, $ownNewer])
        ->assertCanNotSeeTableRecords([$foreign]);
});

it('verlinkt von der Versionsliste auf die Anlegen-Seite unter dem Skript', function () {
    $script = Script::factory()->create();

    $this->get(ScriptResource::getUrl('versions', ['record' => $script], panel: 'admin'))
        ->assertOk()
        ->assertSee(ScriptVersionResource::getUrl('create', ['script' => $script], panel: 'admin'));
});
