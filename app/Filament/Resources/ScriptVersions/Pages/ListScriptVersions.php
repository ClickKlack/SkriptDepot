<?php

namespace App\Filament\Resources\ScriptVersions\Pages;

use App\Filament\Resources\ScriptVersions\ScriptVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScriptVersions extends ListRecords
{
    protected static string $resource = ScriptVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
