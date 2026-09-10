<?php

namespace App\Filament\Resources\Entitlements\Tables;

use App\Models\Entitlement;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EntitlementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'script']))
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('skriptdepot.fields.user'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('script.name')
                    ->label(__('skriptdepot.fields.script'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('token')
                    ->label(__('skriptdepot.fields.token'))
                    ->limit(12)
                    ->copyable()
                    ->copyMessage(__('skriptdepot.actions.copied')),
                ToggleColumn::make('is_active')
                    ->label(__('skriptdepot.fields.is_active')),
                TextColumn::make('created_at')
                    ->label(__('skriptdepot.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('skriptdepot.fields.is_active')),
            ])
            ->recordActions([
                Action::make('install')
                    ->label(__('skriptdepot.actions.open_install_link'))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Entitlement $record): string => route('scripts.user', ['token' => $record->token, 'slug' => $record->script->slug]))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ]);
    }
}
