<?php

namespace App\Filament\Resources\Scripts;

use App\Filament\Resources\Scripts\Pages\CreateScript;
use App\Filament\Resources\Scripts\Pages\EditScript;
use App\Filament\Resources\Scripts\Pages\ListScripts;
use App\Filament\Resources\Scripts\Schemas\ScriptForm;
use App\Filament\Resources\Scripts\Tables\ScriptsTable;
use App\Models\Script;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ScriptResource extends Resource
{
    protected static ?string $model = Script::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('skriptdepot.nav.scripts');
    }

    public static function getModelLabel(): string
    {
        return __('skriptdepot.resources.script.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('skriptdepot.resources.script.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return ScriptForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScriptsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScripts::route('/'),
            'create' => CreateScript::route('/create'),
            'edit' => EditScript::route('/{record}/edit'),
        ];
    }
}
