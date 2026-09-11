<?php

namespace App\Filament\Resources\Entitlements;

use App\Filament\Resources\Entitlements\Pages\ListEntitlements;
use App\Filament\Resources\Entitlements\Pages\ManageEntitlements;
use App\Filament\Resources\Entitlements\Tables\EntitlementScriptsTable;
use App\Models\Script;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Freischaltungen werden je Skript verwaltet: die Liste zeigt die Skripte, die Detailseite alle Nutzer mit Schalter.
 */
class EntitlementResource extends Resource
{
    protected static ?string $model = Script::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = 22;

    protected static ?string $slug = 'entitlements';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('skriptdepot.nav.scripts');
    }

    public static function getModelLabel(): string
    {
        return __('skriptdepot.resources.entitlement.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('skriptdepot.resources.entitlement.plural');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return EntitlementScriptsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEntitlements::route('/'),
            'manage' => ManageEntitlements::route('/{record}'),
        ];
    }
}
