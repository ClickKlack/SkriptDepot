<?php

namespace App\Filament\Resources\ScriptVersions\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScriptVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('script'))
            ->columns([
                TextColumn::make('script.name')
                    ->label(__('skriptdepot.fields.script'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('version')
                    ->label(__('skriptdepot.fields.version'))
                    ->searchable(),
                TextColumn::make('notes')
                    ->label(__('skriptdepot.fields.notes'))
                    ->limit(60)
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label(__('skriptdepot.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('script_id')
                    ->label(__('skriptdepot.fields.script'))
                    ->relationship('script', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
