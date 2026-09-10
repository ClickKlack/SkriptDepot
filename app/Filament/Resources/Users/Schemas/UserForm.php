<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('skriptdepot.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('skriptdepot.fields.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                // Beim Bearbeiten bleibt das Passwort erhalten, wenn das Feld leer bleibt.
                TextInput::make('password')
                    ->label(__('skriptdepot.fields.password'))
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? __('skriptdepot.hints.password_edit') : null)
                    ->maxLength(255),
                Select::make('locale')
                    ->label(__('skriptdepot.fields.locale'))
                    ->options(collect(config('skriptdepot.locales'))
                        ->mapWithKeys(fn (string $locale) => [$locale => __("skriptdepot.locales.{$locale}")])
                        ->all())
                    ->default('de')
                    ->required()
                    ->native(false),
                Toggle::make('is_admin')
                    ->label(__('skriptdepot.fields.is_admin')),
            ]);
    }
}
