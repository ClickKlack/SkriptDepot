<?php

namespace App\Filament\Resources\Entitlements;

use App\Filament\Resources\Entitlements\Pages\CreateEntitlement;
use App\Filament\Resources\Entitlements\Pages\EditEntitlement;
use App\Filament\Resources\Entitlements\Pages\ListEntitlements;
use App\Filament\Resources\Entitlements\Schemas\EntitlementForm;
use App\Filament\Resources\Entitlements\Tables\EntitlementsTable;
use App\Models\Entitlement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EntitlementResource extends Resource
{
    protected static ?string $model = Entitlement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = 22;

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

    public static function form(Schema $schema): Schema
    {
        return EntitlementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EntitlementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEntitlements::route('/'),
            'create' => CreateEntitlement::route('/create'),
            'edit' => EditEntitlement::route('/{record}/edit'),
        ];
    }
}
