<?php

namespace App\Filament\Resources\Scripts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScriptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('skriptdepot.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label(__('skriptdepot.fields.slug'))
                    ->helperText(__('skriptdepot.hints.slug'))
                    ->required()
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->unique(ignoreRecord: true)
                    ->maxLength(64),
                Textarea::make('description')
                    ->label(__('skriptdepot.fields.description'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
