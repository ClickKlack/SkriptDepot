<?php

namespace App\Filament\Resources\Users\Tables;

use App\Actions\SendUserInvitation;
use App\Enums\AccountStatus;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
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
                TextColumn::make('account_status')
                    ->label(__('skriptdepot.fields.status'))
                    ->state(fn (User $record): AccountStatus => $record->accountStatus())
                    ->badge()
                    ->formatStateUsing(fn (AccountStatus $state): string => __("skriptdepot.account_status.{$state->value}"))
                    ->color(fn (AccountStatus $state): string => match ($state) {
                        AccountStatus::Accepted => 'success',
                        AccountStatus::Pending => 'warning',
                        AccountStatus::None => 'gray',
                        AccountStatus::Blocked => 'danger',
                    }),
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
                Action::make('invite')
                    ->label(fn (User $record): string => $record->invited_at === null
                        ? __('skriptdepot.actions.send_invitation')
                        : __('skriptdepot.actions.resend_invitation'))
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->visible(fn (User $record): bool => ! $record->hasVerifiedEmail() && ! $record->isBlocked())
                    ->requiresConfirmation()
                    ->action(function (User $record, SendUserInvitation $sendInvitation): void {
                        $sendInvitation->handle($record);

                        Notification::make()
                            ->title(__('skriptdepot.notifications.invitation_sent', ['email' => $record->email]))
                            ->success()
                            ->send();
                    }),
                // Sperren betrifft nie das eigene Konto, damit sich der Admin nicht selbst aussperrt.
                Action::make('block')
                    ->label(__('skriptdepot.actions.block'))
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->visible(fn (User $record): bool => ! $record->isBlocked() && $record->isNot(auth()->user()))
                    ->requiresConfirmation()
                    ->modalDescription(__('skriptdepot.hints.block'))
                    ->action(function (User $record): void {
                        $record->forceFill(['blocked_at' => now()])->save();

                        Notification::make()->title(__('skriptdepot.notifications.blocked', ['name' => $record->name]))->success()->send();
                    }),
                Action::make('unblock')
                    ->label(__('skriptdepot.actions.unblock'))
                    ->icon(Heroicon::OutlinedLockOpen)
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->isBlocked())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill(['blocked_at' => null])->save();

                        Notification::make()->title(__('skriptdepot.notifications.unblocked', ['name' => $record->name]))->success()->send();
                    }),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => UserResource::canDelete($record)),
            ]);
    }
}
