<?php

namespace App\Filament\Resources\Scripts\Resources\ScriptVersions\Pages;

use App\Exceptions\ScriptSyntaxException;
use App\Filament\Resources\Scripts\Resources\ScriptVersions\ScriptVersionResource;
use App\Services\ScriptWatermarker;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;

class CreateScriptVersion extends CreateRecord
{
    protected static string $resource = ScriptVersionResource::class;

    /**
     * Ein roher Quelltext ohne Build-Platzhalter bekommt die Wasserzeichen automatisch, bevor validiert wird.
     */
    protected function beforeValidate(): void
    {
        $source = (string) ($this->data['source'] ?? '');
        $watermarker = app(ScriptWatermarker::class);

        if ($source === '' || str_contains($source, '{{BUILD}}') || ! $watermarker->hasHeader($source)) {
            return;
        }

        try {
            $this->data['source'] = $watermarker->inject($source);
        } catch (ScriptSyntaxException $exception) {
            $this->addError('data.source', __('skriptdepot.validation.syntax_error', [
                'line' => $exception->sourceLine,
                'message' => $exception->getMessage(),
            ]));

            throw new Halt;
        }
    }
}
