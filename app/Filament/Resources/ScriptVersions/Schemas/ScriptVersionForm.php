<?php

namespace App\Filament\Resources\ScriptVersions\Schemas;

use App\Models\Script;
use App\Rules\ValidMasterScript;
use Closure;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ScriptVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('script_id')
                    ->label(__('skriptdepot.fields.script'))
                    ->relationship('script', 'name')
                    ->required()
                    ->native(false)
                    ->disabledOn('edit'),
                TextInput::make('version')
                    ->label(__('skriptdepot.fields.version'))
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? __('skriptdepot.hints.version_immutable') : null)
                    ->required()
                    ->regex('/^\d+(?:\.\d+)*$/')
                    ->maxLength(32)
                    ->disabledOn('edit')
                    ->rule(fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                        $script = Script::find($get('script_id'));

                        if ($script !== null && ! $script->acceptsVersion((string) $value)) {
                            $fail(__('skriptdepot.validation.version_not_higher', [
                                'latest' => $script->latestVersion()->value('version'),
                            ]));
                        }
                    }),
                CodeEditor::make('source')
                    ->label(__('skriptdepot.fields.source'))
                    ->helperText(__('skriptdepot.hints.source'))
                    ->language(Language::JavaScript)
                    ->required()
                    ->rule(new ValidMasterScript)
                    ->disabledOn('edit')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label(__('skriptdepot.fields.notes'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
