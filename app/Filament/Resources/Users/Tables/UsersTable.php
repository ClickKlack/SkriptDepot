<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('skriptdepot.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('skriptdepot.fields.email'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_admin')
                    ->label(__('skriptdepot.fields.is_admin'))
                    ->boolean(),
                TextColumn::make('locale')
                    ->label(__('skriptdepot.fields.locale'))
                    ->formatStateUsing(fn (string $state): string => __("skriptdepot.locales.{$state}")),
                TextColumn::make('entitlements_count')
                    ->label(__('skriptdepot.fields.entitlements_count'))
                    ->counts('entitlements'),
                TextColumn::make('created_at')
                    ->label(__('skriptdepot.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
