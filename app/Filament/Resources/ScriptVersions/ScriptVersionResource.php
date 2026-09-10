<?php

namespace App\Filament\Resources\ScriptVersions;

use App\Filament\Resources\ScriptVersions\Pages\CreateScriptVersion;
use App\Filament\Resources\ScriptVersions\Pages\EditScriptVersion;
use App\Filament\Resources\ScriptVersions\Pages\ListScriptVersions;
use App\Filament\Resources\ScriptVersions\Schemas\ScriptVersionForm;
use App\Filament\Resources\ScriptVersions\Tables\ScriptVersionsTable;
use App\Models\ScriptVersion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ScriptVersionResource extends Resource
{
    protected static ?string $model = ScriptVersion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 21;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('skriptdepot.nav.scripts');
    }

    public static function getModelLabel(): string
    {
        return __('skriptdepot.resources.script_version.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('skriptdepot.resources.script_version.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return ScriptVersionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScriptVersionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScriptVersions::route('/'),
            'create' => CreateScriptVersion::route('/create'),
            'edit' => EditScriptVersion::route('/{record}/edit'),
        ];
    }
}
