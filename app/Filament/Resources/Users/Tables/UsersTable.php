<?php

namespace App\Filament\Resources\Users\Tables;

use App\Actions\SendUserInvitation;
use App\Enums\InvitationStatus;
use App\Models\User;
use Filament\Actions\Action;
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
                TextColumn::make('invitation_status')
                    ->label(__('skriptdepot.fields.invitation'))
                    ->state(fn (User $record): InvitationStatus => $record->invitationStatus())
                    ->badge()
                    ->formatStateUsing(fn (InvitationStatus $state): string => __("skriptdepot.invitation_status.{$state->value}"))
                    ->color(fn (InvitationStatus $state): string => match ($state) {
                        InvitationStatus::Accepted => 'success',
                        InvitationStatus::Pending => 'warning',
                        InvitationStatus::None => 'gray',
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
                    ->visible(fn (User $record): bool => ! $record->hasVerifiedEmail())
                    ->requiresConfirmation()
                    ->action(function (User $record, SendUserInvitation $sendInvitation): void {
                        $sendInvitation->handle($record);

                        Notification::make()
                            ->title(__('skriptdepot.notifications.invitation_sent', ['email' => $record->email]))
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }
}
