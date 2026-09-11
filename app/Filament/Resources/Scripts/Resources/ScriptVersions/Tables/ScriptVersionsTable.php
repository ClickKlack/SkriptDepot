<?php

namespace App\Filament\Resources\Scripts\Resources\ScriptVersions\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScriptVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
