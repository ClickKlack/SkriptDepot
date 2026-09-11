<?php

namespace App\Filament\Resources\Scripts\Pages;

use App\Filament\Resources\Scripts\Resources\ScriptVersions\ScriptVersionResource;
use App\Filament\Resources\Scripts\ScriptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;

/**
 * Unterseite eines Skripts mit seinen Versionen; „Anlegen" führt auf die eigene Seite mit dem Editor.
 */
class ManageScriptVersions extends ManageRelatedRecords
{
    protected static string $resource = ScriptResource::class;

    protected static string $relationship = 'versions';

    protected static ?string $relatedResource = ScriptVersionResource::class;

    public static function getNavigationLabel(): string
    {
        return __('skriptdepot.resources.script_version.plural');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
