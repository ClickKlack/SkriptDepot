<?php

namespace App\Filament\Resources\Entitlements\Tables;

use App\Filament\Resources\Entitlements\EntitlementResource;
use App\Models\Script;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Einstiegsliste: alle Skripte mit der Zahl aktiver Freischaltungen.
 */
class EntitlementScriptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('skriptdepot.fields.script'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('skriptdepot.fields.slug')),
                TextColumn::make('active_entitlements_count')
                    ->label(__('skriptdepot.fields.active_entitlements'))
                    ->counts(['entitlements as active_entitlements_count' => fn (Builder $query) => $query->where('is_active', true)])
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray'),
            ])
            ->defaultSort('name')
            ->recordUrl(fn (Script $record): string => EntitlementResource::getUrl('manage', ['record' => $record]))
            ->recordActions([
                Action::make('manage')
                    ->label(__('skriptdepot.actions.manage_entitlements'))
                    ->icon(Heroicon::OutlinedUsers)
                    ->url(fn (Script $record): string => EntitlementResource::getUrl('manage', ['record' => $record])),
            ]);
    }
}
