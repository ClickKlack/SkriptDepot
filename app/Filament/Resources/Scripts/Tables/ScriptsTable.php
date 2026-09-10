<?php

namespace App\Filament\Resources\Scripts\Tables;

use Filament\Actions\EditAction;
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
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
