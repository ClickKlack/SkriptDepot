<?php

namespace App\Filament\Resources\Deliveries\Tables;

use App\Enums\DeliveryType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['entitlement.user', 'entitlement.script', 'scriptVersion']))
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('skriptdepot.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('entitlement.user.name')
                    ->label(__('skriptdepot.fields.user'))
                    ->searchable(),
                TextColumn::make('entitlement.script.name')
                    ->label(__('skriptdepot.fields.script'))
                    ->searchable(),
                TextColumn::make('scriptVersion.version')
                    ->label(__('skriptdepot.fields.version')),
                TextColumn::make('type')
                    ->label(__('skriptdepot.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (DeliveryType $state): string => __("skriptdepot.delivery_types.{$state->value}")),
                TextColumn::make('ip')
                    ->label(__('skriptdepot.fields.ip'))
                    ->searchable(),
                TextColumn::make('user_agent')
                    ->label(__('skriptdepot.fields.user_agent'))
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label(__('skriptdepot.fields.type'))
                    ->options([
                        DeliveryType::Meta->value => __('skriptdepot.delivery_types.meta'),
                        DeliveryType::User->value => __('skriptdepot.delivery_types.user'),
                    ]),
            ]);
    }
}
