<?php

namespace App\Filament\Resources\Scripts\Resources\ScriptVersions;

use App\Filament\Resources\Scripts\Resources\ScriptVersions\Pages\CreateScriptVersion;
use App\Filament\Resources\Scripts\Resources\ScriptVersions\Pages\EditScriptVersion;
use App\Filament\Resources\Scripts\Resources\ScriptVersions\Schemas\ScriptVersionForm;
use App\Filament\Resources\Scripts\Resources\ScriptVersions\Tables\ScriptVersionsTable;
use App\Filament\Resources\Scripts\ScriptResource;
use App\Models\ScriptVersion;
use BackedEnum;
use Filament\Resources\ParentResourceRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Versionen hängen unter ihrem Skript: URLs wie /admin/scripts/{script}/versions/create, kein eigener Menüpunkt.
 */
class ScriptVersionResource extends Resource
{
    protected static ?string $model = ScriptVersion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = ScriptResource::class;

    protected static ?string $recordTitleAttribute = 'version';

    protected static ?string $slug = 'versions';

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
    {
        return ScriptResource::asParent(childResource: static::class)->relationship('versions');
    }

    public static function getModelLabel(): string
    {
        return __('skriptdepot.resources.script_version.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('skriptdepot.resources.script_version.plural');
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
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
            'create' => CreateScriptVersion::route('/create'),
            'edit' => EditScriptVersion::route('/{record}/edit'),
        ];
    }
}
