<?php

namespace App\Filament\Resources\Entitlements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class EntitlementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(__('skriptdepot.fields.user'))
                    ->relationship('user', 'name')
                    ->required()
                    ->native(false)
                    ->disabledOn('edit')
                    // Pro Nutzer und Skript darf es nur eine Freischaltung geben.
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('script_id', $get('script_id')),
                    )
                    ->validationMessages(['unique' => __('skriptdepot.validation.entitlement_exists')]),
                Select::make('script_id')
                    ->label(__('skriptdepot.fields.script'))
                    ->relationship('script', 'name')
                    ->required()
                    ->native(false)
                    ->disabledOn('edit')
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? __('skriptdepot.hints.entitlement_immutable') : null),
                TextInput::make('token')
                    ->label(__('skriptdepot.fields.token'))
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
                Toggle::make('is_active')
                    ->label(__('skriptdepot.fields.is_active'))
                    ->default(true),
            ]);
    }
}
