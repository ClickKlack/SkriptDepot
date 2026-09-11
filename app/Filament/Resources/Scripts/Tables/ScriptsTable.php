<?php

namespace App\Filament\Resources\Scripts\Tables;

use App\Filament\Resources\Entitlements\EntitlementResource;
use App\Filament\Resources\Scripts\ScriptResource;
use App\Models\Script;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScriptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('latestVersion'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('skriptdepot.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('skriptdepot.fields.slug'))
                    ->searchable(),
                TextColumn::make('latestVersion.version')
                    ->label(__('skriptdepot.fields.latest_version'))
                    ->placeholder('-'),
                TextColumn::make('versions_count')
                    ->label(__('skriptdepot.fields.versions_count'))
                    ->counts('versions'),
                TextColumn::make('entitlements_count')
                    ->label(__('skriptdepot.fields.entitlements_count'))
                    ->counts('entitlements'),
            ])
            ->defaultSort('name')
            ->recordUrl(fn (Script $record): string => ScriptResource::getUrl('versions', ['record' => $record]))
            ->recordActions([
                Action::make('versions')
                    ->label(__('skriptdepot.resources.script_version.plural'))
                    ->icon(Heroicon::OutlinedRectangleStack)
                    ->url(fn (Script $record): string => ScriptResource::getUrl('versions', ['record' => $record])),
                Action::make('entitlements')
                    ->label(__('skriptdepot.actions.manage_entitlements'))
                    ->icon(Heroicon::OutlinedKey)
                    ->url(fn (Script $record): string => EntitlementResource::getUrl('manage', ['record' => $record])),
                EditAction::make(),
            ]);
    }
}
