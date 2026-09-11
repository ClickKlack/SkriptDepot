<?php

namespace App\Filament\Resources\Scripts\Resources\ScriptVersions\Schemas;

use App\Exceptions\ScriptSyntaxException;
use App\Models\Script;
use App\Rules\ValidMasterScript;
use App\Services\ScriptWatermarker;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Formular einer Version; das Skript ist durch die Elternseite vorgegeben.
 */
class ScriptVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('version')
                    ->label(__('skriptdepot.fields.version'))
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? __('skriptdepot.hints.version_immutable') : null)
                    ->required()
                    ->regex('/^\d+(?:\.\d+)*$/')
                    ->maxLength(32)
                    ->disabledOn('edit')
                    ->rule(
                        fn (CreateRecord $livewire): Closure => function (string $attribute, mixed $value, Closure $fail) use ($livewire): void {
                            /** @var Script|null $script */
                            $script = $livewire->getParentRecord();

                            if ($script !== null && ! $script->acceptsVersion((string) $value)) {
                                $fail(__('skriptdepot.validation.version_not_higher', [
                                    'latest' => $script->latestVersion()->value('version'),
                                ]));
                            }
                        },
                        fn (string $operation): bool => $operation === 'create',
                    ),
                CodeEditor::make('source')
                    ->label(__('skriptdepot.fields.source'))
                    ->helperText(__('skriptdepot.hints.source'))
                    ->language(Language::JavaScript)
                    ->required()
                    ->rule(new ValidMasterScript)
                    ->disabledOn('edit')
                    // Beim Einfügen wird die Versionsnummer aus dem Header übernommen, solange das Feld leer ist.
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ScriptWatermarker $watermarker): void {
                        $detected = $watermarker->detectVersion((string) $state);

                        if ($detected !== null && blank($get('version'))) {
                            $set('version', $detected);
                        }
                    })
                    // Vorschau der automatischen Wasserzeichen; beim Speichern läuft der Injektor ohnehin.
                    ->hintAction(
                        Action::make('injectWatermark')
                            ->label(__('skriptdepot.actions.inject_watermark'))
                            ->icon(Heroicon::OutlinedFingerPrint)
                            ->visible(fn (string $operation): bool => $operation === 'create')
                            ->action(function (Get $get, Set $set, ScriptWatermarker $watermarker): void {
                                $source = (string) $get('source');

                                if (! $watermarker->hasHeader($source)) {
                                    Notification::make()->title(__('skriptdepot.validation.master_script.header'))->danger()->send();

                                    return;
                                }

                                try {
                                    $set('source', $watermarker->inject($source));
                                } catch (ScriptSyntaxException $exception) {
                                    Notification::make()
                                        ->title(__('skriptdepot.validation.syntax_error', ['line' => $exception->sourceLine, 'message' => $exception->getMessage()]))
                                        ->danger()
                                        ->send();
                                }
                            }),
                    )
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label(__('skriptdepot.fields.notes'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
