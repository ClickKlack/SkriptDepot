<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Kein Passwortfeld: Nutzer setzen ihr Passwort selbst über die Einladungsmail.
 */
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
                    ->helperText(fn (string $operation): ?string => $operation === 'create' ? __('skriptdepot.hints.invitation') : null)
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
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
